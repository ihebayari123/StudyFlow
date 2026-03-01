<?php

namespace App\Service;

use App\Entity\Consultation;

class ConsultationManager
{
    /** @var string[] */
    private array $validGenres = ['Homme', 'Femme', 'Autre'];
    
    /** @var string[] */
    private array $validNiveaux = ['Débutant', 'Intermédiaire', 'Avancé', 'Expert'];

    public function validate(Consultation $consultation): bool
    {
        // Validate date de consultation is not null
        if ($consultation->getDateDeConsultation() === null) {
            throw new \InvalidArgumentException('La date de consultation est obligatoire');
        }

        // Validate motif is not empty
        if (empty($consultation->getMotif())) {
            throw new \InvalidArgumentException('Le motif est obligatoire');
        }

        // Validate genre is not empty
        if (empty($consultation->getGenre())) {
            throw new \InvalidArgumentException('Le genre est obligatoire');
        }

        // Validate niveau is not empty
        if (empty($consultation->getNiveau())) {
            throw new \InvalidArgumentException('Le niveau est obligatoire');
        }

        // Validate medecin is not null
        if ($consultation->getMedecin() === null) {
            throw new \InvalidArgumentException('Le médecin est obligatoire');
        }

        // Validate stress survey is not null
        if ($consultation->getStressSurvey() === null) {
            throw new \InvalidArgumentException('Le stress survey est obligatoire');
        }

        // Validate genre is valid
        if (!in_array($consultation->getGenre(), $this->validGenres, true)) {
            throw new \InvalidArgumentException('Le genre doit être: Homme, Femme ou Autre');
        }

        // Validate niveau is valid
        if (!in_array($consultation->getNiveau(), $this->validNiveaux, true)) {
            throw new \InvalidArgumentException('Le niveau doit être: Débutant, Intermédiaire, Avancé ou Expert');
        }

        return true;
    }
}
