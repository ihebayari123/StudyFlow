<?php
// src/Controller/ForgotPasswordController.php

namespace App\Controller;

use App\Entity\PasswordResetToken;
use App\Entity\Utilisateur;
use App\Form\ForgotPasswordRequestType;
use App\Form\ResetPasswordType;
use App\Repository\PasswordResetTokenRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ForgotPasswordController extends AbstractController
{
    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password_request')]
public function request(
    Request $request,
    UtilisateurRepository $userRepo,
    EntityManagerInterface $em,
    MailerInterface $mailer
): Response {
    $form = $this->createForm(ForgotPasswordRequestType::class);
    $form->handleRequest($request);

    // 🚨 DEBUG 1: Voir si le formulaire est soumis
    if ($form->isSubmitted()) {
        error_log("=== FORMULAIRE SOUMIS ===");
        error_log("Valid? " . ($form->isValid() ? 'OUI' : 'NON'));
        
        if (!$form->isValid()) {
            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
                error_log("Erreur: " . $error->getMessage());
            }
        }
    }

    if ($form->isSubmitted() && $form->isValid()) {
        $emailAddress = $form->get('email')->getData();
        error_log("📧 Email saisi: " . $emailAddress);
        
        $user = $userRepo->findOneBy(['email' => $emailAddress]);
        error_log("👤 Utilisateur trouvé: " . ($user ? 'OUI' : 'NON'));

        if ($user) {
            error_log("✅ Utilisateur ID: " . $user->getId());
            
            // Supprimer les anciens tokens
            $deleted = $em->createQueryBuilder()
                ->delete(PasswordResetToken::class, 't')
                ->where('t.user = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->execute();
            error_log("🗑️ Anciens tokens supprimés: " . $deleted);

            // Créer un nouveau token
            $token = new PasswordResetToken();
            $token->setUser($user);
            $em->persist($token);
            $em->flush();
            error_log("🔑 Nouveau token créé: " . $token->getToken());

            // Générer le lien
            $resetUrl = $this->generateUrl('app_reset_password', [
                'token' => $token->getToken()
            ], UrlGeneratorInterface::ABSOLUTE_URL);
            error_log("🔗 Lien généré: " . $resetUrl);

            // Tenter d'envoyer l'email
            try {
                $emailMessage = (new Email())
                    ->from('agrebi.98.oussema@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->html($this->renderView('emails/reset_password.html.twig', [
                        'user' => $user,
                        'resetUrl' => $resetUrl,
                        'expiresIn' => '1 heure'
                    ]));

                $mailer->send($emailMessage);
                error_log("✅ EMAIL ENVOYÉ avec succès à: " . $user->getEmail());
                
            } catch (\Exception $e) {
                error_log("❌ ERREUR D'ENVOI: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
            }
        }

        $this->addFlash('success', 'Si votre email existe dans notre base, vous recevrez un lien de réinitialisation.');
        return $this->redirectToRoute('app_login');
    }

    return $this->render('security/forgot_password.html.twig', [
        'form' => $form->createView()
    ]);
}

    #[Route('/reinitialiser-mot-de-passe/{token}', name: 'app_reset_password')]
    public function reset(
        string $token,
        Request $request,
        PasswordResetTokenRepository $tokenRepo,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Vérifier le token
        $resetToken = $tokenRepo->findOneBy(['token' => $token]);

        if (!$resetToken || !$resetToken->isValid()) {
            $this->addFlash('error', 'Ce lien de réinitialisation est invalide ou a expiré.');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        $user = $resetToken->getUser();

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Marquer le token comme utilisé
            $resetToken->setIsUsed(true);

            // Changer le mot de passe
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setMotDePasse($hashedPassword);

            $em->flush();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'form' => $form->createView(),
            'token' => $token
        ]);
    }

    #[Route('/test-mailer', name: 'test_mailer')]
public function testMailer(MailerInterface $mailer): Response
{
    try {
        $email = (new Email())
            ->from('agrebi.98.oussema@gmail.com')
            ->to('agrebiamira63@gmail.com')
            ->subject('Test SMTP')
            ->text('Ceci est un test direct sans messenger');

        $mailer->send($email);
        return new Response('✅ Email envoyé !');
    } catch (\Exception $e) {
        return new Response('❌ Erreur : ' . $e->getMessage());
    }
}
}