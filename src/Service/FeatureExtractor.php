<?php

namespace App\Service;

use App\Entity\Utilisateur;

class FeatureExtractor
{
    public function extractFeatures(Utilisateur $user): array
    {
        $now = new \DateTime();
        
        $loginFrequency = $user->getLoginFrequency() ?? 0;
        $failedAttempts = $user->getFailedLoginAttempts() ?? 0;
        
        // ✅ Correction : maintenant en JOURS
        $timeSinceLastLogin = $this->calculateDaysSince($user->getLastLogin(), $now);
        $hourOfLogin = (int) $now->format('H');
        $accountAgeDays = $this->calculateAccountAge($user, $now);
        $roleEncoded = $this->encodeRole($user->getRole());
        $isWeekend = (int) ($now->format('N') >= 6);
        
        return [
            $loginFrequency,
            $failedAttempts,
            $timeSinceLastLogin,  // ← Maintenant en jours !
            $hourOfLogin,
            $accountAgeDays,
            $roleEncoded,
            $isWeekend
        ];
    }

    // ✅ Nouvelle méthode en JOURS
    private function calculateDaysSince(?\DateTimeInterface $lastLogin, \DateTime $now): int
    {
        if (!$lastLogin) {
            return 9999;
        }
        return (int) $lastLogin->diff($now)->days;
    }

    private function calculateAccountAge(Utilisateur $user, \DateTime $now): int
    {
        $createdAt = $user->getCreatedAt();
        if ($createdAt) {
            return (int) $createdAt->diff($now)->days;
        }
        return 30;
    }

    private function encodeRole(?string $role): int
    {
        return match($role) {
            'ROLE_ADMIN' => 2,
            'ROLE_ENSEIGNANT' => 1,
            default => 0
        };
    }

    public function getFeatureNames(): array
    {
        return [
            'login_frequency',
            'failed_login_attempts',
            'time_since_last_login',
            'hour_of_login',
            'account_age_days',
            'role_encoded',
            'is_weekend'
        ];
    }

    public function debugFeatures(Utilisateur $user): array
    {
        $features = $this->extractFeatures($user);
        $names = $this->getFeatureNames();
        
        $result = [];
        foreach ($names as $index => $name) {
            $result[$name] = $features[$index];
        }
        return $result;
    }
}