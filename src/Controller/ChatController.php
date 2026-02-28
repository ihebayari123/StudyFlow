<?php
// src/Controller/ChatController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Psr\Log\LoggerInterface;
use App\Service\WellbeingPredictionService;

class ChatController extends AbstractController
{
    // URL du serveur Flask
    private const FLASK_URL = 'http://127.0.0.1:5000';
    
    private WellbeingPredictionService $wellbeingService;

    public function __construct(WellbeingPredictionService $wellbeingService)
    {
        $this->wellbeingService = $wellbeingService;
    }

    #[Route('/chat', name: 'chat_page')]
    public function index()
    {
        return $this->render('chat/index.html.twig');
    }

    #[Route('/chat/send', name: 'chat_send', methods: ['POST'])]
    public function send(
        Request $request,
        HttpClientInterface $client
    ): JsonResponse {
        // Vérifier si c'est une demande de prédiction de bien-être EN PREMIER
        $isWellbeingRequest = $request->request->has('wellbeing_mode') && 
                              $request->request->get('wellbeing_mode') === 'true';
        
        if ($isWellbeingRequest) {
            return $this->handleWellbeingPrediction($request);
        }
        
        $message = $request->request->get('message');
        
        // Récupérer les informations de prédiction de prix si présentes
        $productName = $request->request->get('product_name');
        $description = $request->request->get('description');
        $category = $request->request->get('category');

        $session = $request->getSession();
        if (!$session->has('chat_user_id')) {
            $session->set('chat_user_id', 'user_' . bin2hex(random_bytes(5)));
        }
        $userId = $session->get('chat_user_id');

        try {
            // Vérifier si c'est une demande de prédiction de bien-être
            if ($isWellbeingRequest) {
                return $this->handleWellbeingPrediction($request);
            }

            // Vérifier si c'est une demande de prédiction de prix
            $priceKeywords = ['prix', 'prédire', 'prediction', 'combien', 'cout', 'coût'];
            $isPriceRequest = false;
            
            if ($message) {
                foreach ($priceKeywords as $keyword) {
                    if (stripos($message, $keyword) !== false) {
                        $isPriceRequest = true;
                        break;
                    }
                }
            }

            // Si tous les paramètres de prédiction sont présents, utiliser l'endpoint de prédiction
            if ($productName && $description && $category) {
                return $this->handlePricePrediction($client, $productName, $description, $category);
            }

            // Sinon, utiliser l'endpoint de chat standard (si Flask est disponible)
            try {
                $response = $client->request('POST', self::FLASK_URL . '/api/chat', [
                    'json' => [
                        'message' => $message,
                        'user_id' => $userId,
                        'product_name' => $productName,
                        'description' => $description,
                        'category' => $category
                    ],
                    'timeout' => 30
                ]);

                $data = $response->toArray();
                
                return new JsonResponse([
                    'reply' => $data['reply'] ?? 'Réponse reçue'
                ]);
            } catch (\Exception $e) {
                // Si Flask n'est pas disponible, retourner un message par défaut
                return new JsonResponse([
                    'reply' => "Bonjour ! Je suis MediBot.\n\nJe peux vous aider à:\n- Évaluer votre bien-être (stress et bonheur)\n- Vous donner des conseils santé\n\nCliquez sur 'Faire le test' pour commencer l'évaluation de bien-être!"
                ]);
            }
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'reply' => 'Désolé, une erreur est survenue. Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gère la prédiction de bien-être (stress et bonheur) via les modèles ML
     */
    private function handleWellbeingPrediction(Request $request): JsonResponse
    {
        // Récupérer les 5 paramètres
        $sleepHours = (float) $request->request->get('sleep_hours', 0);
        $studyHours = (float) $request->request->get('study_hours', 0);
        $coffeeCups = (int) $request->request->get('coffee_cups', 0);
        $age = (int) $request->request->get('age', 0);
        $sportHours = (float) $request->request->get('sport_hours', 0);

        // Validation des données
        $errors = [];
        
        if ($sleepHours < 0 || $sleepHours > 24) {
            $errors[] = 'Les heures de sommeil doivent être entre 0 et 24';
        }
        
        if ($studyHours < 0 || $studyHours > 24) {
            $errors[] = 'Les heures d\'étude doivent être entre 0 et 24';
        }
        
        if ($coffeeCups < 0 || $coffeeCups > 50) {
            $errors[] = 'Le nombre de cafés n\'est pas valide';
        }
        
        if ($age < 1 || $age > 120) {
            $errors[] = 'L\'âge n\'est pas valide';
        }

        if (!empty($errors)) {
            return new JsonResponse([
                'reply' => 'Erreur de validation:\n- ' . implode('\n- ', $errors)
            ], 400);
        }

        try {
            // Utiliser le service de prédiction de bien-être
            $result = $this->wellbeingService->predictWellbeing(
                $sleepHours,
                $studyHours,
                $coffeeCups,
                $age,
                $sportHours
            );

            if (!$result['success']) {
                return new JsonResponse([
                    'reply' => 'Erreur lors de la prédiction: ' . ($result['error'] ?? 'Erreur inconnue')
                ], 500);
            }

            // Formater la réponse
            $reply = $this->formatWellbeingResponse($result);

            return new JsonResponse([
                'reply' => $reply,
                'predictions' => $result['predictions'] ?? []
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'reply' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formate la réponse de prédiction de bien-être
     */
    private function formatWellbeingResponse(array $result): string
    {
        $predictions = $result['predictions'] ?? [];
        $input = $result['input'] ?? [];
        
        $reply = "📊 <strong>Résultats de votre analyse bien-être</strong>\n\n";
        
        // Afficher les deux résultats en même temps
        $reply .= "<div class='results-container'>";
        
        // Prédiction de bonheur
        $reply .= "<div class='result-card happiness'>";
        $reply .= "<div class='icon'>😊</div>";
        $reply .= "<div class='title'>Niveau de Bonheur</div>";
        
        if (isset($predictions['happiness'])) {
            $happiness = $predictions['happiness'];
            if (isset($happiness['error'])) {
                $reply .= "<div class='value'>❌</div>";
                $reply .= "<div class='level'>Erreur</div>";
            } else {
                $level = $happiness['level'] ?? 'Inconnu';
                $normalized = $happiness['normalized'] ?? $happiness['value'];
                $reply .= "<div class='value'>" . number_format($normalized, 1) . "/10</div>";
                $reply .= "<div class='level'>$level</div>";
            }
        }
        $reply .= "</div>";
        
        // Prédiction de stress
        $reply .= "<div class='result-card '";
        if (isset($predictions['stress']) && isset($predictions['stress']['percentage'])) {
            $reply .= ($predictions['stress']['percentage'] < 40) ? 'low-stress' : 'stress';
        } else {
            $reply .= 'stress';
        }
        $reply .= "'>";
        $reply .= "<div class='icon'>😰</div>";
        $reply .= "<div class='title'>Niveau de Stress</div>";
        
        if (isset($predictions['stress'])) {
            $stress = $predictions['stress'];
            if (isset($stress['error'])) {
                $reply .= "<div class='value'>❌</div>";
                $reply .= "<div class='level'>Erreur</div>";
            } else {
                $level = $stress['level'] ?? 'Inconnu';
                $percentage = $stress['percentage'] ?? ($stress['value'] * 100);
                $reply .= "<div class='value'>" . number_format($percentage, 0) . "%</div>";
                $reply .= "<div class='level'>$level</div>";
            }
        }
        $reply .= "</div>";
        $reply .= "</div>";
        
        // Conseils
        $reply .= "<div class='tips-section'>";
        $reply .= "<h4>💡 Conseils personnalisés:</h4>";
        $reply .= "<ul>";
        
        if (isset($predictions['stress']) && isset($predictions['stress']['percentage'])) {
            if ($predictions['stress']['percentage'] >= 70) {
                $reply .= "<li>🔥 Votre niveau de stress est élevé. Essayez la méditation ou des exercices de respiration.</li>";
            } elseif ($predictions['stress']['percentage'] >= 40) {
                $reply .= "<li>⚠️ Votre stress est modéré. Pensez à prendre des pauses régulières.</li>";
            } else {
                $reply .= "<li>✅ Votre niveau de stress est bien géré. Continuez comme ça!</li>";
            }
        }
        
        if (isset($predictions['happiness']) && isset($predictions['happiness']['normalized'])) {
            if ($predictions['happiness']['normalized'] < 5) {
                $reply .= "<li>😢 Votre bonheur semble faible. Accordez-vous du temps pour vous reposer et faire des activités agréables.</li>";
            } elseif ($predictions['happiness']['normalized'] >= 7) {
                $reply .= "<li>😊 Votre niveau de bonheur est excellent! Partagez votre bonne humeur!</li>";
            }
        }

        if (($input['sleep_hours'] ?? 0) < 7) {
            $reply .= "<li>😴 Vous dormez moins de 7h. Essayez de dormir plus pour améliorer votre bien-être.</li>";
        }
        
        if (($input['sport_hours'] ?? 0) < 2) {
            $reply .= "<li>🏃 Essayez de faire au moins 2h de sport par semaine pour réduire le stress.</li>";
        }
        
        $reply .= "</ul>";
        $reply .= "</div>";

        return $reply;
    }

    /**
     * Gère la prédiction de prix via l'API dédiée
     */
    private function handlePricePrediction(
        HttpClientInterface $client,
        string $productName,
        string $description,
        string $category
    ): JsonResponse {
        try {
            $response = $client->request('POST', self::FLASK_URL . '/api/predict', [
                'json' => [
                    'product_name' => $productName,
                    'description' => $description,
                    'category' => $category
                ],
                'timeout' => 30
            ]);

            $data = $response->toArray();

            if (isset($data['error'])) {
                return new JsonResponse([
                    'reply' => 'Erreur lors de la prédiction: ' . $data['error']
                ], 500);
            }

            $reply = sprintf(
                "💰 **Prix estimé pour %s:** %s €\n\n📂 Catégorie: %s\n📝 Description: %s\n\n📊 Fourchette de prix dans cette catégorie:\n- Min: %s €\n- Max: %s €\n- Moyenne: %s €",
                $data['product_name'],
                $data['predicted_price'],
                $data['category'],
                $description,
                $data['price_range']['min'],
                $data['price_range']['max'],
                $data['price_range']['mean']
            );

            return new JsonResponse([
                'reply' => $reply
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'reply' => 'Désolé, le serveur de prédiction est hors ligne. 🔌 Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Route pour obtenir les catégories disponibles pour la prédiction
     */
    #[Route('/chat/categories', name: 'chat_categories', methods: ['GET'])]
    public function getCategories(HttpClientInterface $client): JsonResponse
    {
        try {
            $response = $client->request('GET', self::FLASK_URL . '/api/categories', [
                'timeout' => 10
            ]);

            $data = $response->toArray();

            return new JsonResponse([
                'categories' => $data['categories'] ?? [],
                'price_stats' => $data['price_stats'] ?? []
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Impossible de charger les catégories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérification de la connexion au serveur Flask
     */
    #[Route('/chat/health', name: 'chat_health', methods: ['GET'])]
    public function healthCheck(HttpClientInterface $client): JsonResponse
    {
        try {
            $response = $client->request('GET', self::FLASK_URL . '/health', [
                'timeout' => 5
            ]);

            $data = $response->toArray();

            return new JsonResponse([
                'status' => $data['status'] ?? 'unknown',
                'models_loaded' => $data['models_loaded'] ?? false,
                'connected' => true
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'connected' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/chat/send', name: 'chat_send_get', methods: ['GET'])]
public function sendGet(): JsonResponse
{
    return new JsonResponse([
        'reply' => 'Le chat utilise POST. Cette requête GET est ignorée.'
    ]);
}
}
