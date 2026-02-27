<?php
// src/Controller/QuizCoachController.php

namespace App\Controller;

use App\Repository\QuizAttemptRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class QuizCoachController extends AbstractController
{
    #[Route('/quiz/coach/{attemptId}', name: 'quiz_coach', methods: ['GET'])]
    public function index(
        int $attemptId,
        QuizAttemptRepository $attemptRepo,
        Request $request
    ): Response {
        $attempt = $attemptRepo->find($attemptId);
        if (!$attempt) throw $this->createNotFoundException();

        
        $questionsData = $request->getSession()->get('coach_data_' . $attemptId, []);

        return $this->render('quiz_coach/index.html.twig', [
            'attempt'       => $attempt,
            'quiz'          => $attempt->getQuiz(),
            'questionsData' => $questionsData,
        ]);
    }

    #[Route('/quiz/coach/analyze', name: 'quiz_coach_analyze', methods: ['POST'])]
    public function analyze(Request $request, HttpClientInterface $http): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $questionsData = $data['questions'] ?? [];
        $score = $data['score'] ?? 0;
        $total = $data['total'] ?? 0;
        $points = $data['points'] ?? 0;
        $maxPoints = $data['maxPoints'] ?? 0;

        
        $context = "Tu es un coach pédagogique bienveillant et motivant.\n\n";
        $context .= "Voici les résultats d'un étudiant:\n";
        $context .= "- Score: {$score}/{$total} questions correctes\n";
        $context .= "- Points: {$points}/{$maxPoints}\n\n";
        $context .= "Détail question par question:\n\n";

        foreach ($questionsData as $i => $q) {
            $status = $q['isCorrect'] ? '✅' : '❌';
            $context .= "{$status} Question " . ($i+1) . " ({$q['niveau']}): {$q['texte']}\n";
            $context .= "   → Réponse de l'étudiant: {$q['userAnswer']}\n";
            if (!$q['isCorrect']) {
                $context .= "   → Bonne réponse: {$q['correct']}\n";
            }
            $context .= "\n";
        }

        $context .= "Donne un feedback complet en français avec:\n";
        $context .= "1. 🎯 Évaluation globale encourageante\n";
        $context .= "2. 💪 Points forts\n";
        $context .= "3. 📚 Points à améliorer (avec explications courtes)\n";
        $context .= "4. 💡 Conseils pratiques pour progresser\n";
        $context .= "Sois bienveillant, précis et utilise des emojis.";

        try {
            $response = $http->request('POST', 'http://localhost:11434/api/chat', [
                'timeout' => 120,
                'json' => [
                    'model' => 'gemma:2b',
                    'messages' => [['role' => 'user', 'content' => $context]],
                    'stream' => false
                ]
            ]);

            $result = $response->toArray();
            return $this->json(['feedback' => $result['message']['content'] ?? '']);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/quiz/coach/chat', name: 'quiz_coach_chat', methods: ['POST'])]
    public function chat(Request $request, HttpClientInterface $http): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $history = $data['history'] ?? [];

        try {
            $response = $http->request('POST', 'http://localhost:11434/api/chat', [
                'timeout' => 120,
                'json' => [
                    'model' => 'gemma:2b',
                    'messages' => $history,
                    'stream' => false
                ]
            ]);

            $result = $response->toArray();
            return $this->json(['reply' => $result['message']['content'] ?? '']);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}