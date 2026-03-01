<?php

namespace App\Service;

use App\Entity\StressSurvey;

class StressSurveyManager
{
    public function validate(StressSurvey $survey): bool
    {
        // Validate date is not null
        if ($survey->getDate() === null) {
            throw new \InvalidArgumentException('La date est obligatoire');
        }

        // Validate user is not null
        if ($survey->getUser() === null) {
            throw new \InvalidArgumentException('L\'utilisateur est obligatoire');
        }

        // Validate sleep hours is not null
        if ($survey->getSleepHours() === null) {
            throw new \InvalidArgumentException('Les heures de sommeil sont obligatoires');
        }

        // Validate study hours is not null
        if ($survey->getStudyHours() === null) {
            throw new \InvalidArgumentException('Les heures d\'étude sont obligatoires');
        }

        // Validate sleep hours is positive
        if ($survey->getSleepHours() <= 0) {
            throw new \InvalidArgumentException('Les heures de sommeil doivent être positives');
        }

        // Validate study hours is positive
        if ($survey->getStudyHours() <= 0) {
            throw new \InvalidArgumentException('Les heures d\'étude doivent être positives');
        }

        // Validate sleep hours does not exceed 24
        if ($survey->getSleepHours() > 24) {
            throw new \InvalidArgumentException('Les heures de sommeil ne peuvent pas dépasser 24');
        }

        // Validate study hours does not exceed 24
        if ($survey->getStudyHours() > 24) {
            throw new \InvalidArgumentException('Les heures d\'étude ne peuvent pas dépasser 24');
        }

        // Validate total hours does not exceed 24
        if ($survey->getSleepHours() + $survey->getStudyHours() > 24) {
            throw new \InvalidArgumentException('La somme des heures de sommeil et d\'étude ne peut pas dépasser 24');
        }

        return true;
    }
}
