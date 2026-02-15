<?php
// src/Security/UserStatusChecker.php
namespace App\Security;

use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use App\Entity\Utilisateur;

class UserStatusChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {

        // Make sure this is our Utilisateur entity
        if (!$user instanceof Utilisateur) {
            return;
        }

        if ($user->getRole() === 'ROLE_ADMIN') return;

        // Rule 1 — Blocked
        if ($user->getStatutCompte() === 'BLOQUE') {
            throw new CustomUserMessageAccountStatusException(
                'Your account is blocked. Contact admin.'
            );
        }

        // Rule 2 — Inactive
        if ($user->getStatutCompte() === 'INACTIF') {
            throw new CustomUserMessageAccountStatusException(
                'Your account is inactive. Please activate it.'
            );
        }

        // Rule 3 — ACTIF => nothing to do
    }

    public function checkPostAuth(UserInterface $user)
    {
        // Optional: logic after login
    }
}
