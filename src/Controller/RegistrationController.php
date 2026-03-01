<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use App\Service\EmailVerificationService; // ← ADD THIS
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager,
        EmailVerificationService $verificationService // ← ADD THIS PARAMETER
    ): Response
    {
        $user = new Utilisateur();
        $user->setRole('ROLE_ETUDIANT');
        $user->setStatutCompte('ACTIF');
        $user->setEmailVerified(false); // ← ADD THIS LINE
        
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setMotDePasse($userPasswordHasher->hashPassword($user, $plainPassword));
            
            $entityManager->persist($user);
            $entityManager->flush();

            // ✅ ADD THIS - SEND VERIFICATION EMAIL
            $verificationService->sendVerification($user);

            // ✅ ADD THIS - USER FEEDBACK
            $this->addFlash('success', 'Compte créé ! Un email de vérification vous a été envoyé.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
