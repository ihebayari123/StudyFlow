<?php

namespace App\Security;

// src/Security/UserStatusChecker.php
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use App\Entity\Utilisateur;
use App\Service\UserRiskCalculator;

class UserStatusChecker implements UserCheckerInterface
{

    private UserRiskCalculator $riskCalculator;

    public function __construct(UserRiskCalculator $riskCalculator)
    {
        $this->riskCalculator = $riskCalculator;
    }

    public function checkPreAuth(UserInterface $user)
    {
        // Make sure this is our Utilisateur entity
        if (!$user instanceof Utilisateur) {
            return;
        }

        // Skip admin accounts
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

    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof Utilisateur) return;

        // Admins skip
        if ($user->getRole() === 'ADMIN') return;

        // Rule 1 — status block
        if ($user->getStatutCompte() === 'BLOQUE') {
            throw new CustomUserMessageAccountStatusException(
                'Your account is blocked. Contact admin.'
            );
        }

        if ($user->getStatutCompte() === 'INACTIF') {
            throw new CustomUserMessageAccountStatusException(
                'Your account is inactive. Please activate it.'
            );
        }

        // ✅ AI: calculate risk score
        $risk = $this->riskCalculator->calculateRisk($user);

        if ($risk > 50) {
            throw new CustomUserMessageAccountStatusException(
                'Your account is temporarily blocked due to unusual activity.'
            );
        }

        if ($risk > 30) {
            // optional: log or notify admin
            // e.g., send email or create admin notification
        }
    }


    public function checkPostAuth(UserInterface $user)
    {
        // Optional: logic after login
    }
}
