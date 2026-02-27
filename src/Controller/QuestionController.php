<?php
// src/Controller/QuestionController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Filesystem\Filesystem;

class QuestionController extends AbstractController
{
    #[Route('/questions', name: 'app_questions')]
    public function index(): Response
    {
        return $this->render('admin_question/generateur_questions.html.twig');
    }
    
    #[Route('/questions/load/{filename}', name: 'app_questions_load')]
    public function loadQuestions(string $filename): JsonResponse
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/questions/' . $filename;
        
        if (!file_exists($filePath)) {
            return $this->json([
                'success' => false,
                'error' => 'Fichier non trouvé'
            ], 404);
        }
        
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);
        
        return $this->json($data);
    }
    
    #[Route('/questions/list', name: 'app_questions_list')]
    public function listQuestions(): JsonResponse
    {
        $questionsDir = $this->getParameter('kernel.project_dir') . '/public/questions/';
        
        if (!is_dir($questionsDir)) {
            return $this->json([
                'success' => false,
                'error' => 'Dossier questions non trouvé'
            ]);
        }
        
        $files = glob($questionsDir . '*.json');
        $fileList = [];
        
        foreach ($files as $file) {
            $fileList[] = [
                'name' => basename($file),
                'path' => basename($file),
                'size' => filesize($file),
                'modified' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        
        return $this->json([
            'success' => true,
            'files' => $fileList
        ]);
    }
}