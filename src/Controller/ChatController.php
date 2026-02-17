<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
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

        // 1. On récupère le message envoyé par le formulaire
        $message = $request->request->get('message');

        // 2. GESTION DE L'ID UTILISATEUR VIA SESSION
        // On récupère la session Symfony
        $session = $request->getSession();
        
        // Si l'utilisateur n'a pas encore d'ID de chat, on lui en crée un unique
        if (!$session->has('chat_user_id')) {
            $session->set('chat_user_id', 'user_' . bin2hex(random_bytes(5)));
        }
        
        $userId = $session->get('chat_user_id');

        // 3. ENVOI À L'API FLASK
        try {
            $response = $client->request('POST', 'http://127.0.0.1:5000/api/chat', [
                'json' => [
                    'message' => $message,
                    'user_id' => $userId // On envoie l'ID stable
                ]
            ]);

            return new JsonResponse($response->toArray());
            
        } catch (\Exception $e) {
            // En cas d'erreur (si Flask est éteint par exemple)
            return new JsonResponse([
                'reply' => 'Désolé, le serveur de diagnostic est hors ligne. 🔌'
            ], 500);
        }
    }
}











