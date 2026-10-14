<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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

    public function generate(string $notes, string $type, int $n = 3, int $retries = 2): array
    {
        $notes = mb_substr($notes, 0, 4000); // keep it short for CPU speed
        $template = $type === 'fill' ? self::FILL : self::MCQ;

        for ($i = 0; $i <= $retries; $i++) {
            $response = Http::timeout(300)->post(config('services.ollama.url') . '/api/chat', [
                'model'    => config('services.ollama.model'),
                'messages' => [['role' => 'user', 'content' => sprintf($template, $n, $notes)]],
                'format'   => 'json',
                'stream'   => false,
                'options'  => ['temperature' => 0.3],
            ]);

            $data = json_decode($response->json('message.content') ?? '', true);
            $questions = array_values(array_filter(
                $data['questions'] ?? [],
                fn ($q) => $type === 'fill' ? $this->validFill($q) : $this->validMcq($q)
            ));

            if ($questions) {
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