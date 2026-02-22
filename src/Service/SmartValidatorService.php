<?php
// src/Service/SmartValidatorService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SmartValidatorService
{
    public function __construct(private HttpClientInterface $http) {}

    public function validate(string $question, string $expectedAnswer, string $studentAnswer): array
    {
        if (empty(trim($studentAnswer))) {
            return [
                'isCorrect' => false,
                'confidence' => 0,
                'explanation' => 'Aucune réponse fournie.',
            ];
        }

        $prompt = "Tu es un correcteur pédagogique strict mais juste.\n\n"
            . "Question: {$question}\n"
            . "Réponse attendue: {$expectedAnswer}\n"
            . "Réponse de l'étudiant: {$studentAnswer}\n\n"
            . "Analyse si la réponse de l'étudiant est correcte SEMANTIQUEMENT "
            . "(pas forcément mot pour mot).\n\n"
            . "Réponds UNIQUEMENT avec ce JSON (sans markdown):\n"
            . '{"isCorrect": true/false, "confidence": 0-100, "explanation": "courte explication"}';

        try {
            $response = $this->http->request('POST', 'http://localhost:11434/api/chat', [
                'timeout' => 30,
                'json' => [
                    'model' => 'mistral',
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'stream' => false
                ]
            ]);

            $content = $response->toArray()['message']['content'] ?? '';

            
            $content = preg_replace('/```json|```/', '', $content);
            $content = trim($content);

            $result = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($result['isCorrect'])) {
                return [
                    'isCorrect'   => (bool) $result['isCorrect'],
                    'confidence'  => (int) ($result['confidence'] ?? 80),
                    'explanation' => $result['explanation'] ?? '',
                ];
            }

        } catch (\Exception $e) {}

       
        return [
            'isCorrect'   => strtolower(trim($studentAnswer)) === strtolower(trim($expectedAnswer)),
            'confidence'  => 100,
            'explanation' => 'Correction automatique (Mistral indisponible)',
        ];
    }
}