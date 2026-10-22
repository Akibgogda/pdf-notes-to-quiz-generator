<?php

namespace Tests\Feature;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_history_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('history.index'));

        $response->assertStatus(200);
        $response->assertSee('Quiz History');
    }

    public function test_quiz_attempt_is_recorded_and_visible_in_history(): void
    {
        $user = User::factory()->create();

        $quiz = Quiz::create([
            'user_id' => $user->id,
            'title' => 'Chemistry Quiz',
            'type' => 'mcq',
            'question_count' => 2,
            'questions' => [
                [
                    'question' => 'What is H2O?',
                    'options' => ['Water', 'Hydrogen', 'Oxygen', 'Salt'],
                    'answer' => 0,
                    'explanation' => 'H2O represents water.',
                ],
                [
                    'question' => 'What is NaCl?',
                    'options' => ['Sugar', 'Salt', 'Acid', 'Base'],
                    'answer' => 1,
                    'explanation' => 'NaCl is table salt.',
                ],
            ],
        ]);

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'score' => 2,
            'total' => 2,
            'answers' => [0 => 0, 1 => 1],
            'details' => [
                ['question' => 'What is H2O?', 'correct' => true, 'yours' => 'Water', 'right' => 'Water', 'explanation' => 'H2O represents water.'],
                ['question' => 'What is NaCl?', 'correct' => true, 'yours' => 'Salt', 'right' => 'Salt', 'explanation' => 'NaCl is table salt.'],
            ],
        ]);

        $response = $this->actingAs($user)->get(route('history.index'));

        $response->assertStatus(200);
        $response->assertSee('Chemistry Quiz');
        $response->assertSee('2/2');
    }

    public function test_user_can_view_detailed_attempt_breakdown(): void
    {
        $user = User::factory()->create();

        $quiz = Quiz::create([
            'user_id' => $user->id,
            'title' => 'History Quiz',
            'type' => 'mcq',
            'question_count' => 1,
            'questions' => [
                [
                    'question' => 'Who was the first president of the United States?',
                    'options' => ['George Washington', 'Thomas Jefferson', 'John Adams', 'James Madison'],
                    'answer' => 0,
                    'explanation' => 'George Washington served as the first US president.',
                ],
            ],
        ]);

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'score' => 1,
            'total' => 1,
            'answers' => [0 => 0],
            'details' => [
                [
                    'question' => 'Who was the first president of the United States?',
                    'correct' => true,
                    'yours' => 'George Washington',
                    'right' => 'George Washington',
                    'explanation' => 'George Washington served as the first US president.',
                ],
            ],
        ]);

        $response = $this->actingAs($user)->get(route('history.show', $attempt));

        $response->assertStatus(200);
        $response->assertSee('Who was the first president of the United States?');
        $response->assertSee('George Washington served as the first US president.');
    }

    public function test_user_cannot_view_another_users_attempt(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $quiz = Quiz::create([
            'user_id' => $owner->id,
            'title' => 'Private Quiz',
            'type' => 'mcq',
            'question_count' => 1,
            'questions' => [['question' => 'Sample?', 'options' => ['A', 'B', 'C', 'D'], 'answer' => 0]],
        ]);

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $owner->id,
            'score' => 1,
            'total' => 1,
            'answers' => [0 => 0],
            'details' => [],
        ]);

        $response = $this->actingAs($otherUser)->get(route('history.show', $attempt));

        $response->assertStatus(403);
    }
}
