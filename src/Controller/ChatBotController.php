<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ChatBotController extends AbstractController
{
    private const OPENAI_API_URL = 'https://api.openai.com/v1/chat/completions';
    private const SEARCH_API_URL = 'https://serpapi.com/search';
    private const MODEL = 'gpt-3.5-turbo';
    private const MAX_HISTORY = 20;

    // Keywords that trigger a web search
    private const SEARCH_TRIGGER_WORDS = [
        'actuellement', '2024', '2025', '2026', 'dernier', 'dernière',
        'latest', 'recent', 'news', 'actualité', 'aujourd\'hui',
        'recherche', 'trouve', 'cherche', 'internet', 'web',
        'wikipedia', 'wiki', 'article', 'source', 'vérifie',
        'météo', 'weather', 'stock', 'bourse', 'prix',
    ];

    #[Route('/chat/bot', name: 'app_chat_bot')]
    public function index(): Response
    {
        return $this->render('chat_bot/chatbot.html.twig');
    }

    #[Route('/chat/bot/send', name: 'app_chat_bot_send', methods: ['POST'])]
    public function sendMessage(Request $request): JsonResponse
    {
        $message = $request->request->get('message', '');
        
        if (empty($message)) {
            return $this->json(['error' => 'Le message est vide'], 400);
        }

        $session = $request->getSession();
        $chatHistory = $session->get('chat_history', []);
        
        // Add user message to history
        $chatHistory[] = ['role' => 'user', 'content' => $message];
        
        // Keep history limited
        if (count($chatHistory) > self::MAX_HISTORY) {
            $chatHistory = array_slice($chatHistory, -self::MAX_HISTORY);
        }

        try {
            // Check if we need to search the web
            $searchResults = [];
            $needsSearch = $this->needsWebSearch($message);
            
            if ($needsSearch) {
                $searchResults = $this->performWebSearch($message);
            }

            // Call OpenAI with or without search results
            $response = $this->callOpenAI($chatHistory, $searchResults);
            
            if (isset($response['error'])) {
                return $this->json(['error' => $response['error']['message']], 400);
            }

            $botReply = $response['choices'][0]['message']['content'];
            
            // Add bot response to history
            $chatHistory[] = ['role' => 'assistant', 'content' => $botReply];
            
            // Keep history limited
            if (count($chatHistory) > self::MAX_HISTORY) {
                $chatHistory = array_slice($chatHistory, -self::MAX_HISTORY);
            }
            
            $session->set('chat_history', $chatHistory);

            return $this->json([
                'reply' => $botReply,
                'history' => $chatHistory,
                'searchPerformed' => !empty($searchResults),
                'searchResults' => $searchResults
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur de communication avec le serveur: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/chat/bot/search', name: 'app_chat_bot_search', methods: ['POST'])]
    public function webSearch(Request $request): JsonResponse
    {
        $query = $request->request->get('query', '');
        
        if (empty($query)) {
            return $this->json(['error' => 'La requête de recherche est vide'], 400);
        }

        try {
            $results = $this->performWebSearch($query);
            return $this->json(['results' => $results]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur de recherche: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/chat/bot/clear', name: 'app_chat_bot_clear', methods: ['POST'])]
    public function clearHistory(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $session->remove('chat_history');
        
        return $this->json(['success' => true]);
    }

    #[Route('/chat/bot/history', name: 'app_chat_bot_history', methods: ['GET'])]
    public function getHistory(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $chatHistory = $session->get('chat_history', []);
        
        return $this->json(['history' => $chatHistory]);
    }

    private function needsWebSearch(string $message): bool
    {
        $lowerMessage = strtolower($message);
        
        foreach (self::SEARCH_TRIGGER_WORDS as $word) {
            if (strpos($lowerMessage, strtolower($word)) !== false) {
                return true;
            }
        }
        
        // Also search for questions starting with specific patterns
        $questionPatterns = [
            '/^comment/i', '/^pourquoi/i', '/^qu\'est-ce que/i',
            '/^what is/i', '/^who is/i', '/^where is/i',
            '/^quand/i', '/^combien/i', '/^quel/i', '/^quelle/i',
        ];
        
        foreach ($questionPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }
        
        return false;
    }

    private function performWebSearch(string $query): array
    {
        $searchApiKey = $this->getParameter('SEARCH_API_KEY');
        
        if (empty($searchApiKey)) {
            // Fallback: simulate search results
            return $this->simulateSearch($query);
        }

        // Using SerpAPI (Google Search)
        $url = self::SEARCH_API_URL . '?' . http_build_query([
            'q' => $query,
            'api_key' => $searchApiKey,
            'num' => 5,
            'hl' => 'fr',
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            curl_close($ch);
            // Fallback to simulated results
            return $this->simulateSearch($query);
        }
        
        curl_close($ch);

        if ($httpCode !== 200) {
            return $this->simulateSearch($query);
        }

        $data = json_decode($response, true);
        
        if (!isset($data['organic_results'])) {
            return $this->simulateSearch($query);
        }

        $results = [];
        foreach ($data['organic_results'] as $result) {
            $results[] = [
                'title' => $result['title'] ?? '',
                'link' => $result['link'] ?? '',
                'snippet' => $result['snippet'] ?? '',
            ];
        }

        return array_slice($results, 0, 5);
    }

    private function simulateSearch(string $query): array
    {
        // Simulated search results for testing
        return [
            [
                'title' => 'Recherche pour: ' . $query,
                'link' => 'https://fr.wikipedia.org/wiki/Recherche',
                'snippet' => 'Résultats de recherche simulés pour "' . $query . '". Pour utiliser la recherche réelle, configurez SEARCH_API_KEY dans votre fichier .env avec une clé SerpAPI.',
            ],
            [
                'title' => 'StudyFlow - Votre assistant d\'apprentissage',
                'link' => 'https://example.com/studyflow',
                'snippet' => 'StudyFlow est une plateforme d\'accompagnement pour les étudiants.',
            ],
            [
                'title' => 'Intelligence Artificielle et Apprentissage',
                'link' => 'https://example.com/ai-learning',
                'snippet' => 'L\'IA transforme la façon dont nous apprenons et enseignons.',
            ],
        ];
    }

    private function callOpenAI(array $messages, array $searchResults = []): array
    {
        $apiKey = $this->getParameter('CHATBOT_API_KEY');
        
        if (empty($apiKey)) {
            return $this->simulateResponse($messages);
        }

        // Enhance system message with search results if available
        $enhancedMessages = $messages;
        
        if (!empty($searchResults)) {
            $searchContext = "\n\n=== INFORMATIONS RECHERCHÉES SUR LE WEB ===\n";
            foreach ($searchResults as $index => $result) {
                $searchContext .= ($index + 1) . ". " . $result['title'] . "\n";
                $searchContext .= "   URL: " . $result['link'] . "\n";
                $searchContext .= "   Description: " . $result['snippet'] . "\n\n";
            }
            $searchContext .= "==========================================\n";
            
            // Add search context to the last user message
            if (!empty($enhancedMessages)) {
                $enhancedMessages[count($enhancedMessages) - 1]['content'] .= $searchContext;
            }
        }

        $data = [
            'model' => self::MODEL,
            'messages' => $enhancedMessages,
            'max_tokens' => 1500,
            'temperature' => 0.7,
        ];

        $ch = curl_init(self::OPENAI_API_URL);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            curl_close($ch);
            throw new \Exception('Erreur cURL: ' . curl_error($ch));
        }
        
        curl_close($ch);

        if ($httpCode !== 200) {
            return $this->simulateResponse($messages);
        }

        return json_decode($response, true);
    }

    private function simulateResponse(array $messages): array
    {
        $lastMessage = end($messages);
        $userMessage = strtolower($lastMessage['content'] ?? '');
        
        $responses = [
            'greeting' => ['bonjour', 'salut', 'hello', 'hi', 'coucou', 'hey'],
            'how_are_you' => ['comment ça va', 'comment vas tu', 'ca va', 'ça va', 'how are you'],
            'help' => ['aide', 'help', 'assistant', 'que peux tu faire', 'que faites-vous'],
            'thanks' => ['merci', 'thanks', 'thank you', 'remerciements'],
            'goodbye' => ['au revoir', 'bye', 'goodbye', 'salut', 'ciao'],
            'study' => ['étude', 'etude', 'studying', 'study', 'apprendre', 'learning'],
            'usage' => ['utilisation', 'utiliser', 'comment', 'usage', 'use'],
            'search' => ['cherche', 'recherche', 'trouve', 'internet', 'web'],
        ];

        $reply = "Je suis votre assistant StudyFlow. Je peux:\n\n• Répondre à vos questions\n• Rechercher des informations sur internet\n• Vous fournir des informations actuelles\n• Vous aider avec vos études\n\nComment puis-je vous aider ?";

        foreach ($responses as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($userMessage, $keyword) !== false) {
                    switch ($category) {
                        case 'greeting':
                            $reply = "Bonjour ! 👋\n\nJe suis StudyFlow, votre assistant intelligent.\n\nJe peux rechercher des informations sur internet pour vous !\n\nQue souhaitez-vous savoir ?";
                            break;
                        case 'how_are_you':
                            $reply = "Je vais très bien ! 😊\n\nJe suis prêt à vous aider avec vos recherches et vos études.";
                            break;
                        case 'help':
                            $reply = "🤖 **Fonctionnalités de StudyFlow:**\n\n🔍 **Recherche Web**\nJe peux rechercher des informations actuelles sur internet\n\n📚 **Aide aux études**\nRéponses à vos questions académiques\n\n💡 **Informations générales**\nActualités, définitions, et plus encore\n\nN'hésitez pas à me poser des questions !";
                            break;
                        case 'thanks':
                            $reply = "De rien ! 🌟\n\nN'hésitez pas à revenir pour d'autres questions.";
                            break;
                        case 'goodbye':
                            $reply = "Au revoir ! 👋\n\nBonne journée et à bientôt sur StudyFlow !";
                            break;
                        case 'study':
                            $reply = "📚 Je suis là pour vous aider avec vos études !\n\nPosez-moi vos questions et je ferai une recherche si nécessaire pour vous donner les meilleures réponses.";
                            break;
                        case 'usage':
                            $reply = "🏫 **Comment utiliser StudyFlow:**\n\n1️⃣ Posez votre question\n2️⃣ Je recherche si nécessaire\n3️⃣ Je vous donne une réponse détaillée\n\nEssayez: \"Quelles sont les dernières nouvelles en IA ?\"";
                            break;
                        case 'search':
                            $reply = "🔍 Je vais rechercher cette information pour vous !\n\nVenez d'activer la recherche web. Posez votre question et je consulterai les sources disponibles.";
                            break;
                    }
                    break 2;
                }
            }
        }

        return [
            'choices' => [
                [
                    'message' => [
                        'content' => $reply
                    ]
                ]
            ]
        ];
    }
}
