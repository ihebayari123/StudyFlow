<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use App\Entity\Utilisateur;
use App\Service\UserRiskCalculator;
use Doctrine\ORM\EntityManagerInterface; // ← AJOUTE CET IMPORT

class UserStatusChecker implements UserCheckerInterface
{
    private UserRiskCalculator $riskCalculator;
    private EntityManagerInterface $entityManager; // ← AJOUTE CETTE PROPRIÉTÉ

    // ✅ MODIFIE LE CONSTRUCTEUR
    public function __construct(UserRiskCalculator $riskCalculator, EntityManagerInterface $entityManager)
    {
        $this->riskCalculator = $riskCalculator;
        $this->entityManager = $entityManager; // ← INITIALISE ICI
    }

    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof Utilisateur) return;

        // Admins skip
        if ($user->getRole() === 'ROLE_ADMIN') return;

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
            // 🔴 BLOQUE LE COMPTE EN BASE DE DONNÉES
            $user->setStatutCompte('BLOQUE');
            $this->entityManager->flush(); // ← UTILISE L'ENTITY MANAGER
            
            throw new CustomUserMessageAccountStatusException(
                'Your account is temporarily blocked due to unusual activity.'
            );
        }

        if ($risk > 30) {
            // optional: log or notify admin
            error_log("⚠️ High risk user: " . $user->getEmail());
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
        // Optional: logic after login
    }
}