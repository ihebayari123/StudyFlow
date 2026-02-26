<?php

namespace App\Controller;

use App\Service\PredictionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    private $predictionService;

    public function __construct(PredictionService $predictionService)
    {
        $this->predictionService = $predictionService;
    }

    #[Route('/chat', name: 'chat_page')]
    public function index()
    {
        $modelsStatus = $this->predictionService->getModelsStatus();
        return $this->render('chat/index.html.twig', [
            'modelsStatus' => $modelsStatus
        ]);
    }

    #[Route('/chat/send', name: 'chat_send', methods: ['GET', 'POST'])]
    public function send(
        Request $request,
        HttpClientInterface $client
    ) {
        // Si c'est une requête GET, on affiche la page du chat
        if ($request->isMethod('GET')) {
            $modelsStatus = $this->predictionService->getModelsStatus();
            return $this->render('chat/index.html.twig', [
                'modelsStatus' => $modelsStatus
            ]);
        }

        // 1. On récupère le message envoyé par le formulaire
        $message = $request->request->get('message');

        if (empty($message)) {
            return new JsonResponse([
                'reply' => 'Veuillez entrer un message.',
                'stress_analysis' => ['success' => false, 'error' => 'Message vide'],
                'happiness_analysis' => ['success' => false, 'error' => 'Message vide'],
                'models_status' => $this->predictionService->getModelsStatus()
            ], 400);
        }

        // 2. GESTION DE L'ID UTILISATEUR VIA SESSION
        // On récupère la session Symfony
        $session = $request->getSession();
        
        // Si l'utilisateur n'a pas encore d'ID de chat, on lui en crée un unique
        if (!$session->has('chat_user_id')) {
            $session->set('chat_user_id', 'user_' . bin2hex(random_bytes(5)));
        }
        
        $userId = $session->get('chat_user_id');

        // Check if user wants to start questionnaire
        $messageLower = strtolower(trim($message));
        if (strpos($messageLower, 'questionnaire') !== false ||
            strpos($messageLower, 'évaluation') !== false ||
            strpos($messageLower, 'test') !== false ||
            strpos($messageLower, 'analyse') !== false) {
            
            // Initialize questionnaire in session
            $session->set('questionnaire_active', true);
            $session->set('questionnaire_step', 1);
            $session->remove('questionnaire_data');
            
            return new JsonResponse([
                'reply' => $this->getQuestionnaireQuestion(1),
                'questionnaire_active' => true,
                'questionnaire_step' => 1,
                'models_status' => $this->predictionService->getModelsStatus()
            ]);
        }

        // Handle questionnaire flow
        if ($session->get('questionnaire_active', false)) {
            return $this->handleQuestionnaireResponse($request, $session);
        }

        // 3. ANALYSE AVEC LES MODÈLES DE PRÉDICTION (mode normal)
        $stressAnalysis = $this->predictionService->analyzeStress($message);
        $happinessAnalysis = $this->predictionService->analyzeHappiness($message);

        // 4. Construire une réponse enrichie avec les prédictions
        $enrichedReply = $this->buildEnrichedReply($message, $stressAnalysis, $happinessAnalysis);

        // 5. ENVOI À L'API FLASK (optionnel)
        try {
            $response = $client->request('POST', 'http://127.0.0.1:5000/api/chat', [
                'json' => [
                    'message' => $message,
                    'user_id' => $userId,
                    'stress_analysis' => $stressAnalysis,
                    'happiness_analysis' => $happinessAnalysis
                ],
                'timeout' => 3 // Timeout de 3 secondes
            ]);

            $apiResponse = $response->toArray();

            // 6. CONSTRUIRE LA RÉPONSE FINALE avec Flask
            $finalResponse = [
                'reply' => $apiResponse['reply'] ?? $enrichedReply,
                'stress_analysis' => $stressAnalysis,
                'happiness_analysis' => $happinessAnalysis,
                'models_status' => $this->predictionService->getModelsStatus()
            ];

            return new JsonResponse($finalResponse);
            
        } catch (\Exception $e) {
            // En cas d'erreur (si Flask est éteint par exemple), utiliser la réponse enrichie
            $finalResponse = [
                'reply' => $enrichedReply,
                'stress_analysis' => $stressAnalysis,
                'happiness_analysis' => $happinessAnalysis,
                'models_status' => $this->predictionService->getModelsStatus(),
                'flask_status' => 'offline'
            ];
            
            return new JsonResponse($finalResponse);
        }
    }

    /**
     * Construit une réponse enrichie avec les analyses de prédiction
     */
    private function buildEnrichedReply(string $message, array $stressAnalysis, array $happinessAnalysis): string
    {
        $reply = "📊 **Analyse de votre message :**\n\n";

        // Analyse du stress
        if ($stressAnalysis['success']) {
            $stressEmoji = $this->getStressEmoji($stressAnalysis['score']);
            $reply .= "🧠 **Niveau de stress** : {$stressEmoji} {$stressAnalysis['level']} ({$stressAnalysis['score']}%)\n";
            $reply .= "💡 {$stressAnalysis['advice']['message']}\n\n";
        } else {
            $reply .= "⚠️ Analyse du stress indisponible : {$stressAnalysis['error']}\n\n";
        }

        // Analyse du bonheur
        if ($happinessAnalysis['success']) {
            $happinessEmoji = $this->getHappinessEmoji($happinessAnalysis['score']);
            $reply .= "😊 **Niveau de bonheur** : {$happinessEmoji} {$happinessAnalysis['level']} ({$happinessAnalysis['score']}%)\n";
            $reply .= "💡 {$happinessAnalysis['advice']['message']}\n\n";
        } else {
            $reply .= "⚠️ Analyse du bonheur indisponible : {$happinessAnalysis['error']}\n\n";
        }

        $reply .= "---\n\n";
        $reply .= "💬 Comment puis-je vous aider davantage ?";

        return $reply;
    }

    /**
     * Retourne un emoji en fonction du niveau de stress
     */
    private function getStressEmoji(int $score): string
    {
        if ($score >= 80) return "🔴";
        if ($score >= 60) return "🟠";
        if ($score >= 40) return "🟡";
        return "🟢";
    }

    /**
     * Retourne un emoji en fonction du niveau de bonheur
     */
    private function getHappinessEmoji(int $score): string
    {
        if ($score >= 80) return "😄";
        if ($score >= 60) return "🙂";
        if ($score >= 40) return "😐";
        return "😔";
    }

    /**
     * Handle questionnaire response flow
     */
    private function handleQuestionnaireResponse(Request $request, $session): JsonResponse
    {
        $message = trim($request->request->get('message'));
        $currentStep = $session->get('questionnaire_step', 1);
        $questionnaireData = $session->get('questionnaire_data', []);

        // Validate and store the answer
        $validationResult = $this->validateQuestionnaireAnswer($currentStep, $message);
        
        if (!$validationResult['valid']) {
            return new JsonResponse([
                'reply' => $validationResult['error'] . "\n\n" . $this->getQuestionnaireQuestion($currentStep),
                'questionnaire_active' => true,
                'questionnaire_step' => $currentStep,
                'models_status' => $this->predictionService->getModelsStatus()
            ]);
        }

        // Store the validated answer
        $questionnaireData[$validationResult['key']] = $validationResult['value'];
        $session->set('questionnaire_data', $questionnaireData);

        // Move to next step
        $nextStep = $currentStep + 1;

        // Check if questionnaire is complete
        if ($nextStep > 5) {
            // Calculate predictions
            $stressAnalysis = $this->predictionService->calculateStressFromQuestionnaire($questionnaireData);
            $joyAnalysis = $this->predictionService->calculateJoyFromQuestionnaire($questionnaireData);

            // Clear questionnaire session
            $session->remove('questionnaire_active');
            $session->remove('questionnaire_step');
            $session->remove('questionnaire_data');

            // Build final response
            $reply = $this->buildQuestionnaireResults($questionnaireData, $stressAnalysis, $joyAnalysis);

            return new JsonResponse([
                'reply' => $reply,
                'stress_analysis' => $stressAnalysis,
                'happiness_analysis' => $joyAnalysis,
                'questionnaire_complete' => true,
                'models_status' => $this->predictionService->getModelsStatus()
            ]);
        }

        // Ask next question
        $session->set('questionnaire_step', $nextStep);
        
        return new JsonResponse([
            'reply' => "✅ Réponse enregistrée !\n\n" . $this->getQuestionnaireQuestion($nextStep),
            'questionnaire_active' => true,
            'questionnaire_step' => $nextStep,
            'models_status' => $this->predictionService->getModelsStatus()
        ]);
    }

    /**
     * Get questionnaire question by step
     */
    private function getQuestionnaireQuestion(int $step): string
    {
        $questions = [
            1 => "📊 **Questionnaire d'évaluation du bien-être**\n\n" .
                 "Je vais vous poser quelques questions pour évaluer votre niveau de stress et de joie.\n\n" .
                 "**Question 1/5** 😴\n" .
                 "Combien d'heures dormez-vous par nuit en moyenne ?\n" .
                 "_(Exemple: 7 ou 7.5)_",
            
            2 => "**Question 2/5** 📚\n" .
                 "Combien d'heures étudiez-vous par jour en moyenne ?\n" .
                 "_(Exemple: 4 ou 5.5)_",
            
            3 => "**Question 3/5** 🏃‍♂️\n" .
                 "Combien de minutes de sport pratiquez-vous par jour ?\n" .
                 "_(Exemple: 30 ou 60)_",
            
            4 => "**Question 4/5** ☕\n" .
                 "Combien de tasses de café buvez-vous par jour ?\n" .
                 "_(Exemple: 2 ou 3)_",
            
            5 => "**Question 5/5** 🎂\n" .
                 "Quel est votre âge ?\n" .
                 "_(Exemple: 25)_"
        ];

        return $questions[$step] ?? "Question inconnue";
    }

    /**
     * Validate questionnaire answer
     */
    private function validateQuestionnaireAnswer(int $step, string $answer): array
    {
        $answer = trim($answer);
        
        // Replace comma with dot for decimal numbers
        $answer = str_replace(',', '.', $answer);

        switch ($step) {
            case 1: // Sleep hours
                if (!is_numeric($answer) || floatval($answer) < 0 || floatval($answer) > 24) {
                    return [
                        'valid' => false,
                        'error' => "❌ Veuillez entrer un nombre valide entre 0 et 24 heures."
                    ];
                }
                return [
                    'valid' => true,
                    'key' => 'sleep_hours',
                    'value' => floatval($answer)
                ];

            case 2: // Study hours
                if (!is_numeric($answer) || floatval($answer) < 0 || floatval($answer) > 24) {
                    return [
                        'valid' => false,
                        'error' => "❌ Veuillez entrer un nombre valide entre 0 et 24 heures."
                    ];
                }
                return [
                    'valid' => true,
                    'key' => 'study_hours',
                    'value' => floatval($answer)
                ];

            case 3: // Sport minutes
                if (!is_numeric($answer) || floatval($answer) < 0 || floatval($answer) > 1440) {
                    return [
                        'valid' => false,
                        'error' => "❌ Veuillez entrer un nombre valide entre 0 et 1440 minutes."
                    ];
                }
                return [
                    'valid' => true,
                    'key' => 'sport_minutes',
                    'value' => floatval($answer)
                ];

            case 4: // Coffee cups
                if (!is_numeric($answer) || intval($answer) < 0 || intval($answer) > 20) {
                    return [
                        'valid' => false,
                        'error' => "❌ Veuillez entrer un nombre entier valide entre 0 et 20."
                    ];
                }
                return [
                    'valid' => true,
                    'key' => 'coffee_cups',
                    'value' => intval($answer)
                ];

            case 5: // Age
                if (!is_numeric($answer) || intval($answer) < 1 || intval($answer) > 120) {
                    return [
                        'valid' => false,
                        'error' => "❌ Veuillez entrer un âge valide entre 1 et 120 ans."
                    ];
                }
                return [
                    'valid' => true,
                    'key' => 'age',
                    'value' => intval($answer)
                ];

            default:
                return [
                    'valid' => false,
                    'error' => "❌ Question invalide."
                ];
        }
    }

    /**
     * Build questionnaire results message
     */
    private function buildQuestionnaireResults(array $data, array $stressAnalysis, array $joyAnalysis): string
    {
        $reply = "🎉 **Questionnaire terminé !**\n\n";
        $reply .= "📋 **Vos réponses :**\n";
        $reply .= "• Sommeil : {$data['sleep_hours']} heures/nuit\n";
        $reply .= "• Étude : {$data['study_hours']} heures/jour\n";
        $reply .= "• Sport : {$data['sport_minutes']} minutes/jour\n";
        $reply .= "• Café : {$data['coffee_cups']} tasses/jour\n";
        $reply .= "• Âge : {$data['age']} ans\n\n";
        $reply .= "---\n\n";

        // Stress analysis
        if ($stressAnalysis['success']) {
            $stressEmoji = $this->getStressEmoji($stressAnalysis['score']);
            $reply .= "🧠 **Niveau de stress** : {$stressEmoji} {$stressAnalysis['level']}\n";
            $reply .= "📊 **Score** : {$stressAnalysis['score']}%\n";
            $reply .= "💡 {$stressAnalysis['advice']['message']}\n\n";
        } else {
            $reply .= "⚠️ Analyse du stress indisponible : {$stressAnalysis['error']}\n\n";
        }

        // Joy analysis
        if ($joyAnalysis['success']) {
            $joyEmoji = $this->getHappinessEmoji($joyAnalysis['score']);
            $reply .= "😊 **Niveau de joie** : {$joyEmoji} {$joyAnalysis['level']}\n";
            $reply .= "📊 **Score** : {$joyAnalysis['score']}%\n";
            $reply .= "💡 {$joyAnalysis['advice']['message']}\n\n";
        } else {
            $reply .= "⚠️ Analyse de la joie indisponible : {$joyAnalysis['error']}\n\n";
        }

        $reply .= "---\n\n";
        $reply .= "💬 Tapez 'questionnaire' pour refaire le test ou posez-moi une autre question !";

        return $reply;
    }
}











