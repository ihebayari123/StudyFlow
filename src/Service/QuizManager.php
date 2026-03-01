<?php
// src/Service/QuizManager.php
namespace App\Service;

use App\Entity\Quiz;

class QuizManager
{
    public function validate(Quiz $quiz): bool
    {
        // Règle 1: Le titre est obligatoire
        if (empty($quiz->getTitre())) {
            throw new \InvalidArgumentException('Le titre du quiz est obligatoire');
        }

        // Règle 2: La durée doit être supérieure à zéro
        if ($quiz->getDuree() <= 0) {
            throw new \InvalidArgumentException('La durée doit être supérieure à zéro');
        }

        // Règle 3: La durée ne peut pas dépasser 180 minutes
        if ($quiz->getDuree() > 180) {
            throw new \InvalidArgumentException('La durée ne peut pas dépasser 180 minutes');
        }

        return true;
    }
}