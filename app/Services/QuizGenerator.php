<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class QuizGenerator
{
    private const MCQ = <<<'TXT'
Create %d multiple-choice questions from the notes below.
Return JSON in exactly this shape:
{"questions": [{"question": "...", "options": ["...", "...", "...", "..."], "answer": 0, "explanation": "..."}]}
Rules: exactly 4 options. "answer" is the index (0-3) of the correct option.
"explanation" is one short sentence. Use only facts from the notes.

Notes:
%s
TXT;

    private const FILL = <<<'TXT'
Create %d fill-in-the-blank questions from the notes below.
Return JSON in exactly this shape:
{"questions": [{"question": "The ____ is the powerhouse of the cell.", "answer": "mitochondria", "explanation": "..."}]}
Rules: each question has exactly one blank written as ____. "answer" is one or two words.
"explanation" is one short sentence. Use only facts from the notes.

Notes:
%s
TXT;

    /**
     * Generate quiz questions using the configured AI provider.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws RuntimeException
     */
    public function generate(string $notes, string $type, int $n = 3, ?string $provider = null, int $retries = 2): array
    {
        $provider = $provider ?: config('services.ai_provider', 'gemini');

        if ($provider === 'gemini') {
            return $this->generateWithGemini($notes, $type, $n, $retries);
        }

        return $this->generateWithOllama($notes, $type, $n, $retries);
    }

    /**
     * Generate quiz questions via Google Gemini API.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function generateWithGemini(string $notes, string $type, int $n, int $retries): array
    {
        $apiKey = config('services.gemini.api_key');

        if (empty($apiKey)) {
            throw new RuntimeException('Gemini API key is missing. Please set GEMINI_API_KEY in your .env file.');
        }

        $model = config('services.gemini.model', 'gemini-2.5-flash');
        $notes = mb_substr($notes, 0, 10000);
        $template = $type === 'fill' ? self::FILL : self::MCQ;
        $prompt = sprintf($template, $n, $notes);

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        for ($i = 0; $i <= $retries; $i++) {
            $response = Http::timeout(60)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature' => 0.3,
                    ],
                ]);

            if (! $response->successful()) {
                $errorMsg = $response->json('error.message') ?? $response->body();
                throw new RuntimeException("Google Gemini API error ({$response->status()}): {$errorMsg}");
            }

            $rawText = $response->json('candidates.0.content.parts.0.text') ?? '';
            $data = json_decode($rawText, true);

            if (! is_array($data)) {
                $cleaned = preg_replace('/^```(?:json)?\s*|\s*```$/im', '', trim($rawText));
                $data = json_decode($cleaned, true);
            }

            $questions = array_values(array_filter(
                $data['questions'] ?? [],
                fn ($q) => $type === 'fill' ? $this->validFill($q) : $this->validMcq($q)
            ));

            if (! empty($questions)) {
                return $questions;
            }
        }

        return [];
    }

    /**
     * Generate quiz questions via local Ollama API.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function generateWithOllama(string $notes, string $type, int $n, int $retries): array
    {
        $notes = mb_substr($notes, 0, 4000);
        $template = $type === 'fill' ? self::FILL : self::MCQ;

        for ($i = 0; $i <= $retries; $i++) {
            $response = Http::timeout(300)->post(config('services.ollama.url').'/api/chat', [
                'model' => config('services.ollama.model'),
                'messages' => [['role' => 'user', 'content' => sprintf($template, $n, $notes)]],
                'format' => 'json',
                'stream' => false,
                'options' => ['temperature' => 0.3],
            ]);

            if (! $response->successful()) {
                throw new RuntimeException('Could not reach Ollama server. Ensure Ollama is running at '.config('services.ollama.url'));
            }

            $data = json_decode($response->json('message.content') ?? '', true);
            $questions = array_values(array_filter(
                $data['questions'] ?? [],
                fn ($q) => $type === 'fill' ? $this->validFill($q) : $this->validMcq($q)
            ));

            if (! empty($questions)) {
                return $questions;
            }
        }

        return [];
    }

    private function validMcq($q): bool
    {
        return is_array($q)
            && is_string($q['question'] ?? null)
            && is_array($q['options'] ?? null)
            && count($q['options']) === 4
            && is_int($q['answer'] ?? null)
            && $q['answer'] >= 0 && $q['answer'] <= 3;
    }

    private function validFill($q): bool
    {
        return is_array($q)
            && is_string($q['question'] ?? null)
            && str_contains($q['question'], '____')
            && is_string($q['answer'] ?? null)
            && trim($q['answer']) !== '';
    }
}
