<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use App\Entity\Utilisateur;
use App\Service\UserRiskCalculator;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\NotificationService;

class UserStatusChecker implements UserCheckerInterface
{
    private UserRiskCalculator $riskCalculator;
    private EntityManagerInterface $entityManager;
    private NotificationService $notificationService;

    public function __construct(
        UserRiskCalculator $riskCalculator,
        EntityManagerInterface $entityManager,
        NotificationService $notificationService
    ) {
        $this->riskCalculator = $riskCalculator;
        $this->entityManager = $entityManager;
        $this->notificationService = $notificationService;
    }

    public function checkPreAuth(UserInterface $user): void
{
    if (!$user instanceof Utilisateur) return;

    $risk = $this->riskCalculator->calculateRisk($user);
    error_log("🔍 SCORE CALCULÉ: " . $risk . "% pour " . $user->getEmail());

    // ✅ EXCEPTION : Si le compte vient d'être créé (jamais connecté)
    if ($user->getLastLogin() === null) {
        // Première connexion - on laisse passer
        return;
    }
    
    // 🟢 NIVEAU 1: RISQUE FAIBLE (0-30%)
    if ($risk <= 30) {
        return; // Connexion normale
    }
    
    // 🟡 NIVEAU 2: INACTIF (31-50%)
    // 🟡 NIVEAU 2: WARNING (31-50%) - avec notification
if ($risk > 30 && $risk <= 50) {
    $this->notificationService->notifyHighRiskUser($user, $risk);
    return; // Connexion autorisée mais surveillée
}

// 🟠 NIVEAU 3: INACTIF (51-75%)
if ($risk > 50 && $risk <= 75) {
    $user->setStatutCompte('INACTIF');
    $this->entityManager->flush();
    throw new CustomUserMessageAccountStatusException('Compte inactif.');
}
    
    // 🔴 NIVEAU 4: BLOQUE (76-100%)
    if ($risk > 75) {
        $this->notificationService->notifyUserBlocked($user);
        $user->setStatutCompte('BLOQUE');
        $this->entityManager->flush();
        
        throw new CustomUserMessageAccountStatusException(
            'Compte bloqué. Contactez l\'administration.'
        );
    }
}

    public function checkPostAuth(UserInterface $user): void
    {
        // Rien à faire après l'authentification
    }
}