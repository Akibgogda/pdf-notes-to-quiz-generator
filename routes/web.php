<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

// Authentication Routes (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Public / Guest & Authenticated Accessible Routes
Route::get('/', [QuizController::class, 'index'])->name('quiz.index');
Route::post('/generate', [QuizController::class, 'generate'])->name('quiz.generate');

// Guest Session Quiz Routes (No DB Persistence)
Route::get('/quiz/session', [QuizController::class, 'guestShow'])->name('quiz.guest.show');
Route::post('/quiz/session/submit', [QuizController::class, 'guestSubmit'])->name('quiz.guest.submit');
Route::get('/quiz/session/result', [QuizController::class, 'guestResult'])->name('quiz.guest.result');

// Protected Routes (Requires Account)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Authenticated Quiz Taking & History
    Route::get('/quiz/{quiz}', [QuizController::class, 'show'])->name('quiz.show');
    Route::post('/quiz/{quiz}/submit', [QuizController::class, 'submit'])->name('quiz.submit');
    Route::get('/quiz/{quiz}/result/{attempt}', [QuizController::class, 'result'])->name('quiz.result');

    // PDF Documents Library
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // User Quiz History
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::get('/history/{attempt}', [HistoryController::class, 'show'])->name('history.show');
});
