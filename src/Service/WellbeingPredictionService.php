<?php

namespace App\Service;

use Symfony\Component\Process\Process;
use Psr\Log\LoggerInterface;

/**
 * Service de prédiction du bien-être (bonheur et stress)
 * Utilise les modèles ML:
 * - best_happiness_model.pkl: prédit le bonheur basé sur les heures de sommeil et d'étude
 * - best_stress_model.keras: prédit le stress basé sur les 5 paramètres
 */
class WellbeingPredictionService
{
    private string $projectDir;
    private LoggerInterface $logger;
    private string $pythonPath;
    private string $scriptPath;

    public function __construct(
        string $projectDir,
        LoggerInterface $logger,
        string $pythonPath = 'python'
    ) {
        $this->projectDir = $projectDir;
        $this->logger = $logger;
        $this->pythonPath = $pythonPath;
        $this->scriptPath = $this->projectDir . '/ml_models/predict_wellbeing.py';
    }

    /**
     * Prédire le bien-être (bonheur et stress) via les modèles ML Python
     *
     * @param float $sleepHours Heures de sommeil (0-24)
     * @param float $studyHours Heures d'étude (0-24)
     * @param int $coffeeCups Nombre de tasses de café (0-50)
     * @param int $age Âge (1-120)
     * @param float $sportHours Heures de sport par semaine (0-24)
     * @return array {'success': bool, 'predictions': array, 'error'?: string}
     */
    public function predictWellbeing(
        float $sleepHours,
        float $studyHours,
        int $coffeeCups,
        int $age,
        float $sportHours = 0
    ): array {
        // Validation des entrées
        $errors = [];
        
        if ($sleepHours < 0 || $sleepHours > 24) {
            $errors[] = 'Les heures de sommeil doivent être entre 0 et 24';
        }
        
        if ($studyHours < 0 || $studyHours > 24) {
            $errors[] = 'Les heures d\'étude doivent être entre 0 et 24';
        }
        
        if ($coffeeCups < 0 || $coffeeCups > 50) {
            $errors[] = 'Le nombre de cafés doit être entre 0 et 50';
        }
        
        if ($age < 1 || $age > 120) {
            $errors[] = 'L\'âge doit être entre 1 et 120';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'error' => implode(', ', $errors),
                'predictions' => []
            ];
        }

        try {
            // Vérifier que le script existe
            if (!file_exists($this->scriptPath)) {
                $errorMsg = 'Le script de prédiction n\'existe pas: ' . $this->scriptPath;
                $this->logger->error($errorMsg);
                return [
                    'success' => false,
                    'error' => $errorMsg,
                    'predictions' => []
                ];
            }

            // Préparer les données pour Python (forcer UTF-8)
            $inputData = json_encode([
                'sleep_hours' => $sleepHours,
                'study_hours' => $studyHours,
                'coffee_cups' => $coffeeCups,
                'age' => $age,
                'sport_hours' => $sportHours
            ], JSON_UNESCAPED_UNICODE);

            // Exécuter le script Python avec shell_exec
            $cmd = sprintf(
                '%s "%s" --sleep_hours %s --study_hours %s --coffee_cups %s --age %s --sport_hours %s',
                $this->pythonPath,
                $this->scriptPath,
                $sleepHours,
                $studyHours,
                $coffeeCups,
                $age,
                $sportHours
            );
            
            $output = shell_exec($cmd);
            
            if ($output === null) {
                return [
                    'success' => false,
                    'error' => 'Python execution failed',
                    'predictions' => []
                ];
            }

            // Parser le JSON directement
            $result = json_decode($output, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'error' => 'JSON parse failed',
                    'predictions' => []
                ];
            }

            if (isset($result['error'])) {
                return [
                    'success' => false,
                    'error' => $result['error'],
                    'predictions' => []
                ];
            }

            return [
                'success' => true,
                'predictions' => $result['predictions'] ?? [],
                'input' => $result['input'] ?? []
            ];

        } catch (\Exception $e) {
            $this->logger->error('Wellbeing prediction exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            return [
                'success' => false,
                'error' => 'Erreur lors de la prédiction: ' . $e->getMessage(),
                'predictions' => []
            ];
        }
    }

    /**
     * Formater la réponse de prédiction pour l'affichage dans le chat
     */
    public function formatResponse(array $result): string
    {
        $predictions = $result['predictions'] ?? [];
        $input = $result['input'] ?? [];
        
        if (!$result['success'] ?? false) {
            return '❌ Erreur: ' . ($result['error'] ?? 'Erreur inconnue');
        }
        
        $reply = "📊 <strong>Résultats de votre analyse bien-être</strong>\n\n";
        
        // Afficher les deux résultats en même temps
        $reply .= "<div class='results-container'>";
        
        // Prédiction de bonheur
        $reply .= "<div class='result-card happiness'>";
        $reply .= "<div class='icon'>😊</div>";
        $reply .= "<div class='title'>Niveau de Bonheur</div>";
        
        if (isset($predictions['happiness'])) {
            $happiness = $predictions['happiness'];
            if (isset($happiness['error'])) {
                $reply .= "<div class='value'>❌</div>";
                $reply .= "<div class='level'>Erreur</div>";
            } else {
                $level = $happiness['level'] ?? 'Inconnu';
                $normalized = $happiness['normalized'] ?? $happiness['value'];
                $reply .= "<div class='value'>" . number_format($normalized, 1) . "/10</div>";
                $reply .= "<div class='level'>$level</div>";
            }
        }
        $reply .= "</div>";
        
        // Prédiction de stress
        $reply .= "<div class='result-card ";
        if (isset($predictions['stress']) && isset($predictions['stress']['percentage'])) {
            $reply .= ($predictions['stress']['percentage'] < 40) ? 'low-stress' : 'stress';
        } else {
            $reply .= 'stress';
        }
        $reply .= "'>";
        $reply .= "<div class='icon'>😰</div>";
        $reply .= "<div class='title'>Niveau de Stress</div>";
        
        if (isset($predictions['stress'])) {
            $stress = $predictions['stress'];
            if (isset($stress['error'])) {
                $reply .= "<div class='value'>❌</div>";
                $reply .= "<div class='level'>Erreur</div>";
            } else {
                $level = $stress['level'] ?? 'Inconnu';
                $percentage = $stress['percentage'] ?? ($stress['value'] * 100);
                $reply .= "<div class='value'>" . number_format($percentage, 0) . "%</div>";
                $reply .= "<div class='level'>$level</div>";
            }
        }
        $reply .= "</div>";
        $reply .= "</div>";
        
        // Conseils
        $reply .= "<div class='tips-section'>";
        $reply .= "<h4>💡 Conseils personnalisés:</h4>";
        $reply .= "<ul>";
        
        if (isset($predictions['stress']) && isset($predictions['stress']['percentage'])) {
            if ($predictions['stress']['percentage'] >= 70) {
                $reply .= "<li>🔥 Votre niveau de stress est élevé. Essayez la méditation ou des exercices de respiration.</li>";
            } elseif ($predictions['stress']['percentage'] >= 40) {
                $reply .= "<li>⚠️ Votre stress est modéré. Pensez à prendre des pauses régulières.</li>";
            } else {
                $reply .= "<li>✅ Votre niveau de stress est bien géré. Continuez comme ça!</li>";
            }
        }
        
        if (isset($predictions['happiness']) && isset($predictions['happiness']['normalized'])) {
            if ($predictions['happiness']['normalized'] < 5) {
                $reply .= "<li>😢 Votre bonheur semble faible. Accordez-vous du temps pour vous reposer et faire des activités agréables.</li>";
            } elseif ($predictions['happiness']['normalized'] >= 7) {
                $reply .= "<li>😊 Votre niveau de bonheur est excellent! Partagez votre bonne humeur!</li>";
            }
        }

        if (($input['sleep_hours'] ?? 0) < 7) {
            $reply .= "<li>😴 Vous dormez moins de 7h. Essayez de dormir plus pour améliorer votre bien-être.</li>";
        }
        
        if (($input['sport_hours'] ?? 0) < 2) {
            $reply .= "<li>🏃 Essayez de faire au moins 2h de sport par semaine pour réduire le stress.</li>";
        }
        
        $reply .= "</ul>";
        $reply .= "</div>";

        return $reply;
    }
}
