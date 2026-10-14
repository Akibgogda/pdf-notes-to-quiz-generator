<?php

namespace App\Http\Controllers;

use App\Services\QuizGenerator;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;

class QuizController extends Controller
{
    public function index()
    {
        return view('quiz', [
            'quiz'   => session('quiz'),
            'type'   => session('type'),
            'result' => session('result'),
        ]);
    }

    public function generate(Request $request, QuizGenerator $generator)
    {
        $data = $request->validate([
            'notes' => 'nullable|string|max:20000',
            'file'  => 'nullable|file|mimes:pdf,txt|max:10240',
            'type'  => 'required|in:mcq,fill',
            'n'     => 'required|integer|min:1|max:5',
        ]);

        $text = $this->extractText($request, $data['notes'] ?? '');

        if (mb_strlen(trim($text)) < 50) {
            return back()->withInput()->withErrors([
                'notes' => 'Paste or upload at least 50 characters of text. Scanned PDFs (images) will not work.',
            ]);
        }

        set_time_limit(0);

        try {
            $quiz = $generator->generate($text, $data['type'], (int) $data['n']);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['notes' => 'Could not reach Ollama. Is it running?']);
        }

        if (!$quiz) {
            return back()->withInput()->withErrors(['notes' => 'The model did not return a usable quiz. Try again or use shorter text.']);
        }

        session(['quiz' => $quiz, 'type' => $data['type']]);
        session()->forget('result');

        return redirect('/');
    }

    public function submit(Request $request)
    {
        $quiz = session('quiz', []);
        $type = session('type', 'mcq');
        $answers = $request->input('answers', []);
        $score = 0;
        $rows = [];

        foreach ($quiz as $i => $q) {
            $given = $answers[$i] ?? null;

            if ($type === 'mcq') {
                $correct = $given !== null && (int) $given === $q['answer'];
                $yourText = $given !== null ? ($q['options'][(int) $given] ?? '') : '';
                $rightText = $q['options'][$q['answer']];
            } else {
                $correct = $given !== null && $this->norm($given) === $this->norm($q['answer']);
                $yourText = (string) $given;
                $rightText = $q['answer'];
            }

            $score += $correct ? 1 : 0;
            $rows[] = ['correct' => $correct, 'yours' => $yourText, 'right' => $rightText];
        }

        session(['result' => ['score' => $score, 'total' => count($quiz), 'rows' => $rows]]);

        return redirect('/');
    }

    private function extractText(Request $request, string $notes): string
    {
        if ($file = $request->file('file')) {
            if (strtolower($file->getClientOriginalExtension()) === 'pdf') {
                return (new Parser())->parseFile($file->getRealPath())->getText();
            }
            return file_get_contents($file->getRealPath());
        }

        return $notes;
    }

    private function norm(string $s): string
    {
        $s = mb_strtolower($s);
        $s = preg_replace('/[^\p{L}\p{N}\s]/u', '', $s);
        return trim(preg_replace('/\s+/', ' ', $s));
    }
}