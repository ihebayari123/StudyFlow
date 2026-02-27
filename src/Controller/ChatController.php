<?php
// src/Controller/ChatController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    // URL du serveur Flask
    private const FLASK_URL = 'http://127.0.0.1:5000';

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

            // Sinon, utiliser l'endpoint de chat standard
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
            return new JsonResponse([
                'reply' => 'Désolé, le serveur de diagnostic est hors ligne. 🔌 Erreur: ' . $e->getMessage()
            ], 500);
        }
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
