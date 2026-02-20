<?php
// src/Service/UserRiskCalculator.php
namespace App\Service;

use App\Entity\Utilisateur;

class UserRiskCalculator
{
    public function calculateRisk(Utilisateur $user): int
    {
        // Admins always safe
        if ($user->getRole() === 'ROLE_ADMIN') {
            return 0;
        }

        $score = 0;

        // 1️⃣ Login frequency (less = higher risk)
        if ($user->getLoginFrequency() < 5) { // e.g., less than 5 logins/month
            $score += 20;
        }

        // 2️⃣ Last login
        if ($user->getLastLogin() !== null) {
            $daysAgo = (new \DateTime())->diff($user->getLastLogin())->days;
            if ($daysAgo > 30) $score += 25; // inactive for 1+ month
        } else {
            $score += 30; // never logged in
        }

        // 3️⃣ Failed login attempts
        if ($user->getFailedLoginAttempts() > 3) {
            $score += 25;
        }

        return $score; // 0–100
    }
}
