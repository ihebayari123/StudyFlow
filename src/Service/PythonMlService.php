<?php
// src/Service/PythonMlService.php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PythonMlService
{
    private string $python;
    private string $script;
    private LoggerInterface $logger;

    public function __construct(string $python, LoggerInterface $logger)
    {
        $this->python = $python;
        $this->logger = $logger;
        $this->script = __DIR__ . '/../../ml_models/predict_model.py';
        
        // Vérifie que le script existe
        if (!file_exists($this->script)) {
            $this->logger->error('ML script not found at: ' . $this->script);
        }
    }

    public function predictRisk(array $features): array
    {
        try {
            // ✅ Méthode 1: Utiliser Process (recommandé, plus sécurisé)
            $featuresJson = json_encode($features);
            
            $process = new Process([
                $this->python,
                $this->script,
                $featuresJson
            ]);
            
            $process->setTimeout(5); // Timeout de 5 secondes
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output = $process->getOutput();
            $result = json_decode($output, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Erreur de parsing JSON: " . json_last_error_msg());
            }

            if (isset($result['error'])) {
                throw new \Exception("Erreur Python: " . $result['error']);
            }

            $this->logger->info('ML prediction successful', [
                'features' => $features,
                'result' => $result
            ]);

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('ML prediction failed: ' . $e->getMessage(), [
                'features' => $features
            ]);

            // ✅ Fallback sécurisé
            return $this->getFallbackResult($features);
        }
    }

    /**
     * Version alternative avec shell_exec (si Process n'est pas disponible)
     * Moins sécurisé mais plus simple
     */
    public function predictRiskSimple(array $features): array
    {
        try {
            $featuresStr = implode(',', $features);
            
            // Échapper les caractères dangereux
            $featuresStr = escapeshellarg($featuresStr);
            $cmd = escapeshellcmd("{$this->python} {$this->script} {$featuresStr}");
            
            $output = shell_exec($cmd);
            
            if ($output === null) {
                throw new \Exception("shell_exec a échoué");
            }
            
            $result = json_decode($output, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Erreur de parsing JSON");
            }
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logger->error('ML prediction (simple) failed: ' . $e->getMessage());
            return $this->getFallbackResult($features);
        }
    }

    /**
     * Résultat de secours si le ML échoue
     */
    private function getFallbackResult(array $features): array
    {
        // Calcul basé sur des règles simples
        $score = 0;
        
        if (isset($features[0]) && $features[0] < 5) $score += 20; // login_frequency
        if (isset($features[1]) && $features[1] > 3) $score += 25; // failed_attempts
        if (isset($features[2]) && $features[2] > 30) $score += 25; // > 30 jours
        
        $probability = min(100, $score) / 100;
        
        return [
            'probability' => $probability,
            'prediction' => $probability > 0.5 ? 1 : 0,
            'level' => $probability > 0.7 ? 'HIGH' : ($probability > 0.3 ? 'MEDIUM' : 'LOW'),
            'fallback' => true
        ];
    }

    /**
     * Vérifie si Python est disponible
     */
    public function checkPython(): bool
    {
        try {
            $process = new Process([$this->python, '--version']);
            $process->run();
            return $process->isSuccessful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Teste le script Python
     */
    public function testScript(): array
    {
        $testFeatures = [5, 0, 2, 14, 30, 0, 0]; // Features de test
        return $this->predictRisk($testFeatures);
    }
}