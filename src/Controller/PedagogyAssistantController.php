<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PedagogyAssistantController extends AbstractController
{
    // ✅ Front Office
    #[Route('/front/chat', name: 'chat_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('Pedagogy_Assistant/index.html.twig');
    }

    
    #[Route('/admin/chat', name: 'admin_chat_index', methods: ['GET'])]
    public function adminIndex(): Response
    {
        return $this->render('Pedagogy_Assistant/admin_index.html.twig');
    }

    
    #[Route('/chat/send', name: 'chat_send', methods: ['POST'])]
    public function send(Request $request, HttpClientInterface $http): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? '';

        if (!$message) {
            return $this->json(['error' => 'Message vide'], 400);
        }

        try {
            $response = $http->request('POST', 'http://localhost:11434/api/chat', [
                'json' => [
                    'model' => 'mistral',
                    'messages' => [
                        ['role' => 'user', 'content' => $message]
                    ],
                    'stream' => false
                ]
            ]);

            $result = $response->toArray();
            $reply = $result['message']['content'] ?? 'Pas de réponse';

            return $this->json(['reply' => $reply]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}