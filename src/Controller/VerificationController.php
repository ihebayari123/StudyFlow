<?php 

// src/Controller/VerificationController.php
namespace App\Controller;

use App\Service\EmailVerificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VerificationController extends AbstractController
{
    #[Route('/verify/email/{token}', name: 'app_verify_email')]
    public function verifyEmail(
        string $token,
        EmailVerificationService $verificationService
    ): Response {
        // Verify token
        $user = $verificationService->verifyToken($token);
        
        if (!$user) {
            $this->addFlash('error', 'Lien de vérification invalide ou expiré.');
            return $this->redirectToRoute('app_login');
        }
        
        $this->addFlash('success', 'Email vérifié avec succès !');
        
        // ✅ REDIRECT TO HOME PAGE AFTER VERIFICATION
        return $this->redirectToRoute('app_home');
    }
}
