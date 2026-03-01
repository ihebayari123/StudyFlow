<?php

namespace App\Service;

use App\Entity\Cours;
use App\Entity\Utilisateur;

class CourseManager
{
    public function validate(Cours $cours): bool
    {
        if (empty($cours->getTitre())) {
            throw new \InvalidArgumentException('Le titre est obligatoire');
        }

        if (!preg_match('/^[A-Za-z]/', $cours->getTitre())) {
    throw new \InvalidArgumentException('Le titre doit commencer par une lettre');
}
if (!preg_match('/^[\p{L}0-9\s]+$/u', $cours->getTitre())) {
    throw new \InvalidArgumentException('Le titre contient des caractères non autorisés');
}
if (strlen($cours->getTitre()) > 100) {
    throw new \InvalidArgumentException('Le titre est trop long');
}

        if (strlen($cours->getDescription()) < 10) {
            throw new \InvalidArgumentException('La description doit contenir au moins 10 caractères');
        }

        if (str_word_count($cours->getDescription()) < 3) {
    throw new \InvalidArgumentException('La description doit contenir au moins 3 mots');
}
        if ($cours->getUser() === null) {
    throw new \InvalidArgumentException('Un cours doit être associé à un utilisateur');
    }
    if ($cours->getTitre() === $cours->getDescription()) {
    throw new \InvalidArgumentException('Le titre et la description ne peuvent pas être identiques');
    }

        return true;
    }
}