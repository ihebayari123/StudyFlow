<?php 

namespace App\Service;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EmailVerificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private LoggerInterface $logger,
        private ParameterBagInterface $params
    ) {}

    /**
     * Generate token and send verification email
     */
    public function sendVerification(Utilisateur $user): bool
{
    try {
        // 🔴 ADD THIS TEMPORARY DEBUG
        dump('1. sendVerification STARTED for user: ' . $user->getEmail());
        
        // 1. Generate secure token
        $token = $this->generateSecureToken();
        dump('2. Token generated: ' . $token);
        
        // 2. Set token with expiry (24 hours)
        $user->setEmailVerificationToken($token);
        $user->setEmailVerificationTokenExpiresAt(new \DateTime('+24 hours'));
        
        $this->entityManager->flush();
        dump('3. Token saved to database');
        
        // 3. Generate verification link
        $verificationLink = $this->urlGenerator->generate(
            'app_verify_email',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        dump('4. Link generated: ' . $verificationLink);
        
        // 4. Send email
        $this->sendVerificationEmail($user, $verificationLink);
        dump('5. Email sent');
        
        $this->logger->info('Verification email sent to: ' . $user->getEmail());
        dump('6. Logger called');
        
        return true;
        
    } catch (\Exception $e) {
        dump('❌ ERROR: ' . $e->getMessage());
        $this->logger->error('Verification email failed: ' . $e->getMessage());
        return false;
    }
}

    /**
     * Verify token and activate account
     */
    public function verifyToken(string $token): ?Utilisateur
    {
        // Find user by token
        $user = $this->entityManager
            ->getRepository(Utilisateur::class)
            ->findOneBy(['emailVerificationToken' => $token]);
        
        if (!$user) {
            $this->logger->warning('Invalid verification token used: ' . $token);
            return null;
        }
        
        // Check expiry
        if ($user->getEmailVerificationTokenExpiresAt() < new \DateTime()) {
            $this->logger->warning('Expired verification token used: ' . $token);
            return null;
        }
        
        // Mark as verified (this clears the token)
        $user->markEmailAsVerified();
        $this->entityManager->flush();
        
        $this->logger->info('Email verified for user: ' . $user->getEmail());
        
        return $user;
    }

    /**
     * Generate a cryptographically secure unique token
     */
    private function generateSecureToken(): string
    {
        do {
            // Generate random bytes and encode safely for URLs
            $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
            
            // Check uniqueness (rare collision, but possible)
            $existing = $this->entityManager
                ->getRepository(Utilisateur::class)
                ->findOneBy(['emailVerificationToken' => $token]);
                
        } while ($existing);
        
        return $token;
    }

    /**
     * Send the verification email
     */
    private function sendVerificationEmail(Utilisateur $user, string $link): void
    {
        $name = $user->getPrenom() ?: $user->getNom() ?: $user->getEmail();
        
        $email = (new Email())
            ->from($this->params->get('app.email_from'))
            ->to($user->getEmail())
            ->subject('Vérifiez votre adresse email')
            ->html($this->getEmailTemplate($name, $link));
        
        $this->mailer->send($email);
    }

    /**
     * HTML email template
     */
    private function getEmailTemplate(string $name, string $link): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4F46E5; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background-color: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
                .button { 
                    display: inline-block; 
                    padding: 14px 32px; 
                    background-color: #4F46E5; 
                    color: white; 
                    text-decoration: none; 
                    border-radius: 6px;
                    margin: 20px 0;
                    font-weight: bold;
                }
                .button:hover { background-color: #4338CA; }
                .footer { margin-top: 30px; font-size: 12px; color: #6c757d; text-align: center; }
                .link-box { background-color: white; padding: 15px; border-radius: 4px; word-break: break-all; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Bienvenue sur notre plateforme !</h1>
                </div>
                
                <div class="content">
                    <h2>Bonjour {$name},</h2>
                    
                    <p>Merci de vous être inscrit. Pour activer votre compte et accéder à toutes nos fonctionnalités, veuillez vérifier votre adresse email en cliquant sur le bouton ci-dessous :</p>
                    
                    <div style="text-align: center;">
                        <a href="{$link}" class="button">Vérifier mon email</a>
                    </div>
                    
                    <p><strong>Ce lien expirera dans 24 heures.</strong></p>
                    
                    <p>Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :</p>
                    
                    <div class="link-box">
                        <small>{$link}</small>
                    </div>
                    
                    <p>Si vous n'avez pas créé de compte, vous pouvez ignorer cet email en toute sécurité.</p>
                    
                    <hr style="margin: 30px 0;">
                    
                    <p><small>Pour des raisons de sécurité, ce lien est à usage unique.</small></p>
                </div>
                
                <div class="footer">
                    <p>© {$this->params->get('app.year')} Votre Application. Tous droits réservés.</p>
                    <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
}