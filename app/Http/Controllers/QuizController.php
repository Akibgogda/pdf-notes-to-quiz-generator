<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\QuizGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Smalot\PdfParser\Parser;

class QuizController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $savedDocuments = $user ? $user->documents()->latest()->get() : collect();
        $recentQuizzes = $user ? $user->quizzes()->with('latestAttempt')->latest()->take(5)->get() : collect();

        $selectedDocumentId = $request->query('document_id');

        return view('quiz.index', compact('savedDocuments', 'recentQuizzes', 'selectedDocumentId'));
    }

    public function generate(Request $request, QuizGenerator $generator): RedirectResponse
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:20000'],
            'file' => ['nullable', 'file', 'mimes:pdf,txt', 'max:10240'],
            'document_id' => ['nullable', 'integer', 'exists:documents,id'],
            'type' => ['required', 'in:mcq,fill'],
            'n' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $user = $request->user();
        $text = '';
        $documentId = null;
        $title = 'Quiz on Notes';

        // 1. If existing saved document selected
        if (! empty($data['document_id'])) {
            if (! $user) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Please sign in to generate quizzes from your saved documents.',
                ]);
            }

            $doc = $user->documents()->findOrFail($data['document_id']);
            $text = $doc->extracted_text;
            $documentId = $doc->id;
            $title = 'Quiz: '.$doc->title;
        } elseif ($file = $request->file('file')) {
            // 2. If new file uploaded
            $extension = strtolower($file->getClientOriginalExtension());
            try {
                if ($extension === 'pdf') {
                    $text = (new Parser)->parseFile($file->getRealPath())->getText();
                } else {
                    $text = file_get_contents($file->getRealPath());
                }
            } catch (\Throwable $e) {
                return back()->withInput()->withErrors([
                    'file' => 'Could not parse uploaded file. Please make sure the PDF has readable text.',
                ]);
            }

            if (mb_strlen(trim($text)) < 50) {
                return back()->withInput()->withErrors([
                    'file' => 'The uploaded file must have at least 50 characters of readable text. Scanned images are not supported.',
                ]);
            }

            $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $title = 'Quiz: '.$fileName;

            // Only persist to disk & DB if user is authenticated!
            if ($user) {
                $path = $file->store('documents', 'local');
                $doc = $user->documents()->create([
                    'title' => $fileName,
                    'original_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType() ?: 'application/pdf',
                    'extracted_text' => $text,
                ]);

                $documentId = $doc->id;
            }
        } elseif (! empty($data['notes'])) {
            // 3. Raw notes pasted
            $text = $data['notes'];
            $title = 'Quiz: '.Str::limit(preg_replace('/\s+/', ' ', trim($text)), 25);
        }

        if (mb_strlen(trim($text)) < 50) {
            return back()->withInput()->withErrors([
                'notes' => 'Please provide at least 50 characters of text via upload, saved document, or direct notes.',
            ]);
        }

        set_time_limit(0);

        try {
            $questions = $generator->generate($text, $data['type'], (int) $data['n']);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'notes' => $e->getMessage() ?: 'Could not generate quiz from the configured AI service.',
            ]);
        }

        if (empty($questions)) {
            return back()->withInput()->withErrors([
                'notes' => 'The AI model could not return a valid quiz from the provided notes. Please try with clearer text or fewer questions.',
            ]);
        }

        // Guest Session Handling (No DB Persistence)
        if (! $user) {
            session([
                'guest_quiz' => [
                    'title' => $title,
                    'type' => $data['type'],
                    'question_count' => count($questions),
                    'questions' => $questions,
                    'created_at' => now()->toFormattedDateString(),
                ],
            ]);
            session()->forget('guest_result');

            return redirect()->route('quiz.guest.show')->with('status', 'Guest quiz generated! Your answers will be graded in this browser session.');
        }

        // Authenticated Persistent Handling
        $quiz = $user->quizzes()->create([
            'document_id' => $documentId,
            'title' => $title,
            'type' => $data['type'],
            'question_count' => count($questions),
            'questions' => $questions,
        ]);

        return redirect()->route('quiz.show', $quiz)->with('status', 'Quiz generated successfully! Test your knowledge below.');
    }

    public function show(Request $request, Quiz $quiz): View
    {
        abort_if($quiz->user_id !== $request->user()->id, 403, 'Unauthorized access to this quiz.');

        $quiz->load('document');

        return view('quiz.show', compact('quiz'));
    }

    public function submit(Request $request, Quiz $quiz): RedirectResponse
    {
        abort_if($quiz->user_id !== $request->user()->id, 403, 'Unauthorized access to this quiz.');

        $answers = $request->input('answers', []);
        $questions = $quiz->questions ?? [];
        $score = 0;
        $details = [];

        foreach ($questions as $i => $q) {
            $given = $answers[$i] ?? null;

            if ($quiz->type === 'mcq') {
                $correct = $given !== null && (int) $given === (int) $q['answer'];
                $yourText = $given !== null ? ($q['options'][(int) $given] ?? '') : '';
                $rightText = $q['options'][(int) $q['answer']] ?? '';
            } else {
                $correct = $given !== null && $this->norm((string) $given) === $this->norm((string) $q['answer']);
                $yourText = (string) ($given ?? '');
                $rightText = (string) $q['answer'];
            }

            if ($correct) {
                $score++;
            }

            $details[] = [
                'question' => $q['question'],
                'correct' => $correct,
                'yours' => $yourText,
                'right' => $rightText,
                'explanation' => $q['explanation'] ?? '',
            ];
        }

        $attempt = $quiz->attempts()->create([
            'user_id' => $request->user()->id,
            'score' => $score,
            'total' => count($questions),
            'answers' => $answers,
            'details' => $details,
        ]);

        return redirect()->route('quiz.result', [$quiz, $attempt]);
    }

    public function result(Request $request, Quiz $quiz, QuizAttempt $attempt): View
    {
        abort_if($quiz->user_id !== $request->user()->id || $attempt->user_id !== $request->user()->id, 403);
        abort_if($attempt->quiz_id !== $quiz->id, 404);

        $quiz->load('document');

        return view('quiz.result', compact('quiz', 'attempt'));
    }

    public function guestShow(): View|RedirectResponse
    {
        $quiz = session('guest_quiz');

        if (! $quiz) {
            return redirect()->route('quiz.index')->withErrors([
                'notes' => 'No active session quiz found. Please generate a new quiz below.',
            ]);
        }

        return view('quiz.guest-show', compact('quiz'));
    }

    public function guestSubmit(Request $request): RedirectResponse
    {
        $quiz = session('guest_quiz');

        if (! $quiz) {
            return redirect()->route('quiz.index');
        }

        $answers = $request->input('answers', []);
        $questions = $quiz['questions'] ?? [];
        $score = 0;
        $details = [];

        foreach ($questions as $i => $q) {
            $given = $answers[$i] ?? null;

            if ($quiz['type'] === 'mcq') {
                $correct = $given !== null && (int) $given === (int) $q['answer'];
                $yourText = $given !== null ? ($q['options'][(int) $given] ?? '') : '';
                $rightText = $q['options'][(int) $q['answer']] ?? '';
            } else {
                $correct = $given !== null && $this->norm((string) $given) === $this->norm((string) $q['answer']);
                $yourText = (string) ($given ?? '');
                $rightText = (string) $q['answer'];
            }

            if ($correct) {
                $score++;
            }

            $details[] = [
                'question' => $q['question'],
                'correct' => $correct,
                'yours' => $yourText,
                'right' => $rightText,
                'explanation' => $q['explanation'] ?? '',
            ];
        }

        $total = count($questions);
        $percentage = $total > 0 ? (int) round(($score / $total) * 100) : 0;

        session([
            'guest_result' => [
                'score' => $score,
                'total' => $total,
                'percentage' => $percentage,
                'answers' => $answers,
                'details' => $details,
            ],
        ]);

        return redirect()->route('quiz.guest.result');
    }

    public function guestResult(): View|RedirectResponse
    {
        $quiz = session('guest_quiz');
        $result = session('guest_result');

        if (! $quiz || ! $result) {
            return redirect()->route('quiz.index');
        }

        return view('quiz.guest-result', compact('quiz', 'result'));
    }

    private function norm(string $s): string
    {
        $s = mb_strtolower($s);
        $s = preg_replace('/[^\p{L}\p{N}\s]/u', '', $s);

        return trim(preg_replace('/\s+/', ' ', $s));
    }
}
