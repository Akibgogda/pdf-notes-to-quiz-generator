<?php

namespace App\Http\Controllers;

use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $attempts = $user->quizAttempts()
            ->with(['quiz.document'])
            ->latest()
            ->paginate(12);

        $totalAttempts = $user->quizAttempts()->count();
        $totalDocuments = $user->documents()->count();
        $totalQuizzes = $user->quizzes()->count();

        $averageScore = $totalAttempts > 0
            ? (int) round($user->quizAttempts()->get()->avg('percentage'))
            : 0;

        return view('history.index', compact(
            'attempts',
            'totalAttempts',
            'totalDocuments',
            'totalQuizzes',
            'averageScore'
        ));
    }

    public function show(Request $request, QuizAttempt $attempt): View
    {
        abort_if($attempt->user_id !== $request->user()->id, 403, 'Unauthorized access to this history record.');

        $attempt->load(['quiz.document']);

        return view('history.show', compact('attempt'));
    }
}
