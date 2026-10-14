   <?php

   use App\Http\Controllers\QuizController;
   use Illuminate\Support\Facades\Route;

   Route::get('/', [QuizController::class, 'index']);
   Route::post('/generate', [QuizController::class, 'generate']);
   Route::post('/submit', [QuizController::class, 'submit']);