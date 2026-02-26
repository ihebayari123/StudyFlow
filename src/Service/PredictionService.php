<?php

namespace App\Service;

class PredictionService
{
    private $stressModel;
    private $happinessModel;

    public function __construct()
    {
        $this->loadModels();
    }

    private function loadModels()
    {
        try {
            $kerasModelPath = __DIR__ . '/../../best_stress_model.keras';
            if (file_exists($kerasModelPath)) {
                $this->stressModel = [
                    'type' => 'keras',
                    'path' => $kerasModelPath,
                    'loaded' => true
                ];
            } else {
                $this->stressModel = [
                    'type' => 'keras',
                    'path' => $kerasModelPath,
                    'loaded' => false,
                    'error' => 'Fichier non trouvé'
                ];
            }

            $pickleModelPath = __DIR__ . '/../../best_happiness_model.pkl';
            if (file_exists($pickleModelPath)) {
                $this->happinessModel = [
                    'type' => 'pickle',
                    'path' => $pickleModelPath,
                    'loaded' => true
                ];
            } else {
                $this->happinessModel = [
                    'type' => 'pickle',
                    'path' => $pickleModelPath,
                    'loaded' => false,
                    'error' => 'Fichier non trouvé'
                ];
            }
        } catch (\Exception $e) {
            $this->stressModel = [
                'type' => 'keras',
                'loaded' => false,
                'error' => $e->getMessage()
            ];
            $this->happinessModel = [
                'type' => 'pickle',
                'loaded' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function analyzeStress($text)
    {
        if (!$this->stressModel['loaded']) {
            return [
                'success' => false,
                'error' => 'Modèle de stress non chargé: ' . ($this->stressModel['error'] ?? 'Erreur inconnue')
            ];
        }

        $stressScore = $this->simulateStressAnalysis($text);

        return [
            'success' => true,
            'score' => $stressScore,
            'level' => $this->getStressLevel($stressScore),
            'advice' => $this->getStressAdvice($stressScore)
        ];
    }

    public function analyzeHappiness($text)
    {
        if (!$this->happinessModel['loaded']) {
            return [
                'success' => false,
                'error' => 'Modèle de bonheur non chargé: ' . ($this->happinessModel['error'] ?? 'Erreur inconnue')
            ];
        }

        $happinessScore = $this->simulateHappinessAnalysis($text);

        return [
            'success' => true,
            'score' => $happinessScore,
            'level' => $this->getHappinessLevel($happinessScore),
            'advice' => $this->getHappinessAdvice($happinessScore)
        ];
    }

    private function simulateStressAnalysis($text)
    {
        $stressWords = [
            'anxi', 'stress', 'angoiss', 'paniqu', 'déprim', 'fatigu', 'burnout',
            'insomni', 'malade', 'souffr', 'douleur', 'craint', 'peur', 'nerveux'
        ];

        $textLower = strtolower($text);
        $stressCount = 0;
        
        foreach ($stressWords as $word) {
            if (strpos($textLower, $word) !== false) {
                $stressCount++;
            }
        }

        $baseScore = min(80, $stressCount * 15);
        
        if (strpos($textLower, 'très') !== false || strpos($textLower, 'beaucoup') !== false) {
            $baseScore = min(100, $baseScore + 10);
        }
        
        if (strpos($textLower, 'pas') !== false || strpos($textLower, 'non') !== false) {
            $baseScore = max(0, $baseScore - 10);
        }

        return $baseScore;
    }

    private function simulateHappinessAnalysis($text)
    {
        $positiveWords = [
            'heureux', 'content', 'joyeux', 'enthousiaste', 'optimiste', 'bien',
            'positif', 'souri', 'amour', 'paix', 'calme', 'repos', 'détendu'
        ];

        $negativeWords = [
            'triste', 'déprim', 'anxieux', 'stress', 'fatigu', 'malade', 'souffr',
            'douleur', 'colère', 'frustration', 'déception', 'peur'
        ];

        $textLower = strtolower($text);
        $positiveCount = 0;
        $negativeCount = 0;
        
        foreach ($positiveWords as $word) {
            if (strpos($textLower, $word) !== false) {
                $positiveCount++;
            }
        }
        
        foreach ($negativeWords as $word) {
            if (strpos($textLower, $word) !== false) {
                $negativeCount++;
            }
        }

        $baseScore = min(90, $positiveCount * 20);
        
        if ($negativeCount > $positiveCount) {
            $baseScore = max(10, $baseScore - ($negativeCount * 15));
        }

        return $baseScore;
    }

    private function getStressLevel($score)
    {
        if ($score >= 80) return 'Élevé';
        if ($score >= 60) return 'Modéré';
        if ($score >= 40) return 'Normal';
        return 'Bas';
    }

    private function getHappinessLevel($score)
    {
        if ($score >= 80) return 'Élevé';
        if ($score >= 60) return 'Modéré';
        if ($score >= 40) return 'Normal';
        return 'Bas';
    }

    private function getStressAdvice($score)
    {
        if ($score >= 80) {
            return [
                'title' => 'Niveau de stress élevé détecté',
                'message' => 'Votre niveau de stress est très élevé. Il est important de prendre des mesures pour vous détendre. Essayez des techniques de respiration profonde, la méditation, ou parlez à un professionnel de santé.',
                'severity' => 'danger'
            ];
        } elseif ($score >= 60) {
            return [
                'title' => 'Stress modéré',
                'message' => 'Vous ressentez un stress modéré. Pensez à faire des pauses régulières, à pratiquer une activité physique, et à maintenir une bonne hygiène de vie.',
                'severity' => 'warning'
            ];
        } elseif ($score >= 40) {
            return [
                'title' => 'Stress normal',
                'message' => 'Votre niveau de stress est normal. Continuez à prendre soin de vous et à gérer votre stress au quotidien.',
                'severity' => 'info'
            ];
        } else {
            return [
                'title' => 'Stress faible',
                'message' => 'Votre niveau de stress est faible. Continuez vos bonnes habitudes de gestion du stress et de bien-être.',
                'severity' => 'success'
            ];
        }
    }

    private function getHappinessAdvice($score)
    {
        if ($score >= 80) {
            return [
                'title' => 'Bonheur élevé',
                'message' => 'Votre niveau de bonheur est excellent ! Continuez à cultiver les aspects positifs de votre vie et partagez votre énergie avec les autres.',
                'severity' => 'success'
            ];
        } elseif ($score >= 60) {
            return [
                'title' => 'Bonheur modéré',
                'message' => 'Vous ressentez un bon niveau de bonheur. Continuez à pratiquer des activités qui vous apportent de la joie et du bien-être.',
                'severity' => 'info'
            ];
        } elseif ($score >= 40) {
            return [
                'title' => 'Bonheur normal',
                'message' => 'Votre niveau de bonheur est dans la moyenne. Pensez à identifier ce qui pourrait améliorer votre bien-être et à prendre des initiatives positives.',
                'severity' => 'warning'
            ];
        } else {
            return [
                'title' => 'Bonheur faible',
                'message' => 'Votre niveau de bonheur est faible. Il pourrait être bénéfique de parler à un professionnel de santé ou à un proche de confiance. Pensez aussi à pratiquer des activités qui vous apportent du plaisir.',
                'severity' => 'danger'
            ];
        }
    }

    public function getModelsStatus()
    {
        return [
            'stress' => $this->stressModel,
            'happiness' => $this->happinessModel
        ];
    }

    /**
     * Calculate stress level based on questionnaire data
     * Factors: sleep hours, study hours, sport minutes, coffee cups, age
     */
    public function calculateStressFromQuestionnaire(array $data): array
    {
        $sleepHours = floatval($data['sleep_hours'] ?? 0);
        $studyHours = floatval($data['study_hours'] ?? 0);
        $sportMinutes = floatval($data['sport_minutes'] ?? 0);
        $coffeeCups = intval($data['coffee_cups'] ?? 0);
        $age = intval($data['age'] ?? 0);

        // Validation
        if ($sleepHours <= 0 || $studyHours < 0 || $sportMinutes < 0 || $coffeeCups < 0 || $age <= 0) {
            return [
                'success' => false,
                'error' => 'Données invalides. Veuillez vérifier vos réponses.'
            ];
        }

        // Calculate stress score (0-100)
        $stressScore = 0;

        // Sleep factor (0-30 points) - Less sleep = more stress
        if ($sleepHours < 5) {
            $stressScore += 30;
        } elseif ($sleepHours < 6) {
            $stressScore += 25;
        } elseif ($sleepHours < 7) {
            $stressScore += 15;
        } elseif ($sleepHours < 8) {
            $stressScore += 5;
        }
        // 8+ hours = 0 stress points

        // Study hours factor (0-25 points) - Too much study = more stress
        if ($studyHours > 10) {
            $stressScore += 25;
        } elseif ($studyHours > 8) {
            $stressScore += 20;
        } elseif ($studyHours > 6) {
            $stressScore += 15;
        } elseif ($studyHours > 4) {
            $stressScore += 10;
        } elseif ($studyHours > 2) {
            $stressScore += 5;
        }

        // Sport factor (0-20 points) - Less sport = more stress
        if ($sportMinutes < 15) {
            $stressScore += 20;
        } elseif ($sportMinutes < 30) {
            $stressScore += 15;
        } elseif ($sportMinutes < 45) {
            $stressScore += 10;
        } elseif ($sportMinutes < 60) {
            $stressScore += 5;
        }
        // 60+ minutes = 0 stress points

        // Coffee factor (0-15 points) - Too much coffee = more stress
        if ($coffeeCups >= 5) {
            $stressScore += 15;
        } elseif ($coffeeCups >= 4) {
            $stressScore += 12;
        } elseif ($coffeeCups >= 3) {
            $stressScore += 8;
        } elseif ($coffeeCups >= 2) {
            $stressScore += 4;
        }

        // Age factor (0-10 points) - Younger people may have more stress
        if ($age < 20) {
            $stressScore += 10;
        } elseif ($age < 25) {
            $stressScore += 8;
        } elseif ($age < 30) {
            $stressScore += 5;
        } elseif ($age > 50) {
            $stressScore += 5;
        }

        // Cap at 100
        $stressScore = min(100, $stressScore);

        return [
            'success' => true,
            'score' => $stressScore,
            'level' => $this->getStressLevel($stressScore),
            'advice' => $this->getStressAdvice($stressScore),
            'factors' => [
                'sleep' => $sleepHours,
                'study' => $studyHours,
                'sport' => $sportMinutes,
                'coffee' => $coffeeCups,
                'age' => $age
            ]
        ];
    }

    /**
     * Calculate joy/happiness level based on questionnaire data
     * Factors: sleep hours, study hours
     */
    public function calculateJoyFromQuestionnaire(array $data): array
    {
        $sleepHours = floatval($data['sleep_hours'] ?? 0);
        $studyHours = floatval($data['study_hours'] ?? 0);

        // Validation
        if ($sleepHours <= 0 || $studyHours < 0) {
            return [
                'success' => false,
                'error' => 'Données invalides. Veuillez vérifier vos réponses.'
            ];
        }

        // Calculate joy score (0-100)
        $joyScore = 50; // Base score

        // Sleep factor (0-35 points) - Good sleep = more joy
        if ($sleepHours >= 7 && $sleepHours <= 9) {
            $joyScore += 35; // Optimal sleep
        } elseif ($sleepHours >= 6 && $sleepHours < 7) {
            $joyScore += 25;
        } elseif ($sleepHours >= 5 && $sleepHours < 6) {
            $joyScore += 10;
        } elseif ($sleepHours < 5) {
            $joyScore -= 20; // Very low sleep reduces joy
        } elseif ($sleepHours > 10) {
            $joyScore += 10; // Too much sleep is okay but not optimal
        }

        // Study hours factor (0-15 points) - Balanced study = more joy
        if ($studyHours >= 2 && $studyHours <= 4) {
            $joyScore += 15; // Optimal study time
        } elseif ($studyHours >= 4 && $studyHours <= 6) {
            $joyScore += 10;
        } elseif ($studyHours > 6 && $studyHours <= 8) {
            $joyScore += 5;
        } elseif ($studyHours > 8) {
            $joyScore -= 10; // Too much study reduces joy
        } elseif ($studyHours < 2 && $studyHours > 0) {
            $joyScore += 5;
        }

        // Cap between 0 and 100
        $joyScore = max(0, min(100, $joyScore));

        return [
            'success' => true,
            'score' => $joyScore,
            'level' => $this->getHappinessLevel($joyScore),
            'advice' => $this->getHappinessAdvice($joyScore),
            'factors' => [
                'sleep' => $sleepHours,
                'study' => $studyHours
            ]
        ];
    }
}