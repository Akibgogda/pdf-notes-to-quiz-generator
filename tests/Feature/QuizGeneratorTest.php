<?php

namespace Tests\Feature;

use App\Services\QuizGenerator;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class QuizGeneratorTest extends TestCase
{
    public function test_gemini_throws_exception_if_api_key_is_missing(): void
    {
        config([
            'services.ai_provider' => 'gemini',
            'services.gemini.api_key' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Gemini API key is missing');

        $generator = new QuizGenerator;
        $generator->generate('Some long sample notes for generating a quiz test.', 'mcq', 2);
    }

    public function test_gemini_successfully_generates_mcq_questions(): void
    {
        config([
            'services.ai_provider' => 'gemini',
            'services.gemini.api_key' => 'fake-gemini-key',
            'services.gemini.model' => 'gemini-2.5-flash',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'questions' => [
                                            [
                                                'question' => 'What is the primary function of chlorophyll in photosynthesis?',
                                                'options' => ['Absorb light energy', 'Store water', 'Produce carbon dioxide', 'Fix nitrogen'],
                                                'answer' => 0,
                                                'explanation' => 'Chlorophyll captures solar photons to drive photosynthetic reactions.',
                                            ],
                                        ],
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $generator = new QuizGenerator;
        $questions = $generator->generate('Chlorophyll is the green pigment in plants that absorbs sunlight.', 'mcq', 1);

        $this->assertCount(1, $questions);
        $this->assertSame('What is the primary function of chlorophyll in photosynthesis?', $questions[0]['question']);
        $this->assertSame(0, $questions[0]['answer']);
    }

    public function test_gemini_successfully_generates_fill_in_the_blank_questions(): void
    {
        config([
            'services.ai_provider' => 'gemini',
            'services.gemini.api_key' => 'fake-gemini-key',
            'services.gemini.model' => 'gemini-2.5-flash',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'questions' => [
                                            [
                                                'question' => 'The ____ is the powerhouse of the cell.',
                                                'answer' => 'mitochondria',
                                                'explanation' => 'Mitochondria generate cellular ATP.',
                                            ],
                                        ],
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $generator = new QuizGenerator;
        $questions = $generator->generate('Mitochondria are organelles that produce energy for the cell.', 'fill', 1);

        $this->assertCount(1, $questions);
        $this->assertSame('mitochondria', $questions[0]['answer']);
    }

    public function test_ollama_successfully_generates_mcq_questions(): void
    {
        config([
            'services.ai_provider' => 'ollama',
            'services.ollama.url' => 'http://localhost:11434',
            'services.ollama.model' => 'qwen2.5:3b',
        ]);

        Http::fake([
            'localhost:11434/*' => Http::response([
                'message' => [
                    'content' => json_encode([
                        'questions' => [
                            [
                                'question' => 'What is the speed of light in vacuum?',
                                'options' => ['300,000 km/s', '150,000 km/s', '1,000 km/s', '3,000 km/s'],
                                'answer' => 0,
                                'explanation' => 'Light travels at approximately 300,000 km per second.',
                            ],
                        ],
                    ]),
                ],
            ], 200),
        ]);

        $generator = new QuizGenerator;
        $questions = $generator->generate('Light travels at approximately 300,000 km per second in a vacuum.', 'mcq', 1);

        $this->assertCount(1, $questions);
        $this->assertSame('What is the speed of light in vacuum?', $questions[0]['question']);
    }
}
