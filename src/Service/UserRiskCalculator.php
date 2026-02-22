<?php
// src/Service/UserRiskCalculator.php
namespace App\Service;

use App\Entity\Utilisateur;

class UserRiskCalculator
{
    public function __construct(
        private PythonMlService $mlService,
        private FeatureExtractor $featureExtractor
    ) {}

    public function calculateRisk(Utilisateur $user): int
    {
        $features = $this->featureExtractor->extractFeatures($user);
        $mlResult = $this->mlService->predictRisk($features);

        return (int)($mlResult['probability'] * 100); // 0–100%
    }
}