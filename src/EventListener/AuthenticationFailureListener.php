<?php
// src/EventListener/AuthenticationFailureListener.php
namespace App\EventListener;

use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Utilisateur;

class AuthenticationFailureListener
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onAuthenticationFailure(LoginFailureEvent $event)
    {
        $email = $event->getAuthenticationToken()->getUser();

        $user = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);
        if ($user) {
            // increment failed login attempts
            $user->setFailedLoginAttempts($user->getFailedLoginAttempts() + 1);
            $this->entityManager->flush();
        }
    }
}
