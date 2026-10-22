<?php

namespace Tests\Feature;

use App\Services\QuizGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class GuestModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_generate_quiz_without_saving_to_database(): void
    {
        $this->mock(QuizGenerator::class, function (MockInterface $mock) {
            $mock->shouldReceive('generate')
                ->once()
                ->andReturn([
                    [
                        'question' => 'What is the powerhouse of the cell?',
                        'options' => ['Mitochondria', 'Nucleus', 'Ribosome', 'Chloroplast'],
                        'answer' => 0,
                        'explanation' => 'Mitochondria generate energy.',
                    ],
                ]);
        });

        $notes = str_repeat('Mitochondria are the organelles that generate energy for the cell. ', 3);

        $response = $this->post(route('quiz.generate'), [
            'notes' => $notes,
            'type' => 'mcq',
            'n' => 1,
        ]);

        $response->assertRedirect(route('quiz.guest.show'));
        $this->assertDatabaseCount('quizzes', 0);
        $this->assertDatabaseCount('documents', 0);

        $response->assertSessionHas('guest_quiz');
    }

    public function test_guest_uploading_file_does_not_save_to_documents_table_or_storage(): void
    {
        Storage::fake('local');

        $this->mock(QuizGenerator::class, function (MockInterface $mock) {
            $mock->shouldReceive('generate')
                ->once()
                ->andReturn([
                    [
                        'question' => 'Photosynthesis produces what gas?',
                        'options' => ['Oxygen', 'Carbon Dioxide', 'Nitrogen', 'Helium'],
                        'answer' => 0,
                        'explanation' => 'Oxygen is released during photosynthesis.',
                    ],
                ]);
        });

        $content = str_repeat('Photosynthesis is the process in plants producing oxygen and glucose from sunlight. ', 3);
        $file = UploadedFile::fake()->createWithContent('biology.txt', $content);

        $response = $this->post(route('quiz.generate'), [
            'file' => $file,
            'type' => 'mcq',
            'n' => 1,
        ]);

        $response->assertRedirect(route('quiz.guest.show'));
        $this->assertDatabaseCount('documents', 0);
        $this->assertDatabaseCount('quizzes', 0);
    }

    public function test_guest_can_take_and_submit_session_quiz_without_saving_attempts(): void
    {
        $guestQuiz = [
            'title' => 'Biology Session Quiz',
            'type' => 'mcq',
            'question_count' => 1,
            'questions' => [
                [
                    'question' => 'What is H2O?',
                    'options' => ['Water', 'Oxygen', 'Hydrogen', 'Carbon'],
                    'answer' => 0,
                    'explanation' => 'H2O is water.',
                ],
            ],
            'created_at' => now()->toFormattedDateString(),
        ];

        // 1. Visit guest quiz page
        $showResponse = $this->withSession(['guest_quiz' => $guestQuiz])->get(route('quiz.guest.show'));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Biology Session Quiz');
        $showResponse->assertSee('What is H2O?');

        // 2. Submit answers
        $submitResponse = $this->withSession(['guest_quiz' => $guestQuiz])->post(route('quiz.guest.submit'), [
            'answers' => [0 => 0],
        ]);

        $submitResponse->assertRedirect(route('quiz.guest.result'));
        $this->assertDatabaseCount('quiz_attempts', 0);

        // 3. View results page
        $resultData = [
            'score' => 1,
            'total' => 1,
            'percentage' => 100,
            'answers' => [0 => 0],
            'details' => [
                [
                    'question' => 'What is H2O?',
                    'correct' => true,
                    'yours' => 'Water',
                    'right' => 'Water',
                    'explanation' => 'H2O is water.',
                ],
            ],
        ];

        $resultResponse = $this->withSession([
            'guest_quiz' => $guestQuiz,
            'guest_result' => $resultData,
        ])->get(route('quiz.guest.result'));

        $resultResponse->assertStatus(200);
        $resultResponse->assertSee('/1');
        $resultResponse->assertSee('100%');
        $resultResponse->assertSee('Create Free Account');
    }
}
