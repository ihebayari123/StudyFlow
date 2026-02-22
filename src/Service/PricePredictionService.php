<?php

namespace App\Service;

use Symfony\Component\Process\Process;
use Psr\Log\LoggerInterface;

class PricePredictionService
{
    private string $projectDir;
    private LoggerInterface $logger;

    public function __construct(string $projectDir, LoggerInterface $logger)
    {
        $this->projectDir = $projectDir;
        $this->logger = $logger;
    }

    /**
     * Prédire le prix d'un produit via le modèle ML Python
     *
     * @param string $nom Nom du produit
     * @param string $description Description du produit
     * @param string $categorie Nom de la catégorie
     * @return array {'success': bool, 'prix_predit': float, 'confidence': string, 'error'?: string}
     */
    public function predictPrice(string $nom, string $description, string $categorie): array
    {
        try {
            // Utiliser le chemin absolu du python du venv Windows
            $pythonBin = $this->projectDir . '/.venv/Scripts/python.exe';
            $process = new Process([
                $pythonBin,
                $this->projectDir . '/predict_price.py',
                $nom,
                $description,
                $categorie
            ]);

            // Exécuter le processus
            $process->run();

            // Log temporaire pour debug
            $debugLog = __DIR__ . '/../../var/log/predict_debug.log';
            file_put_contents($debugLog, "\n==== PREDICT REQUEST ====".PHP_EOL, FILE_APPEND);
            file_put_contents($debugLog, 'CMD: ' . $pythonBin . ' ' . $this->projectDir . '/predict_price.py "' . $nom . '" "' . $description . '" "' . $categorie . '"' . PHP_EOL, FILE_APPEND);
            file_put_contents($debugLog, 'Exit code: ' . $process->getExitCode() . PHP_EOL, FILE_APPEND);
            file_put_contents($debugLog, 'Error output: ' . $process->getErrorOutput() . PHP_EOL, FILE_APPEND);
            file_put_contents($debugLog, 'Stdout: ' . $process->getOutput() . PHP_EOL, FILE_APPEND);

            // Vérifier l'exécution
            if (!$process->isSuccessful()) {
                $this->logger->error('Python process failed', [
                    'error_output' => $process->getErrorOutput(),
                    'exit_code' => $process->getExitCode()
                ]);
                return [
                    'success' => false,
                    'error' => 'Erreur lors de la prédiction'
                ];
            }

            // Décoder le JSON retourné
            $output = $process->getOutput();
            $result = json_decode($output, true);

            if (!is_array($result)) {
                $this->logger->error('Invalid JSON response from Python', [
                    'output' => $output
                ]);
                return [
                    'success' => false,
                    'error' => 'Réponse invalide du serveur'
                ];
            }

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('Price prediction exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            // Log l'exception
            $debugLog = __DIR__ . '/../../var/log/predict_debug.log';
            file_put_contents($debugLog, 'Exception: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
            return [
                'success' => false,
                'error' => 'Erreur lors de la prédiction: ' . $e->getMessage()
            ];
        }
    }
}
