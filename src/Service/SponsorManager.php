<?php

namespace App\Service;

use App\Entity\Sponsor;

class SponsorManager
{
    public function validate(Sponsor $sponsor): bool
    {
        if (empty($sponsor->getNomSponsor())) {
            throw new \InvalidArgumentException('Le nom du sponsor est obligatoire.');
        }

        if (!in_array($sponsor->getType(), ['Gold', 'Silver', 'Bronze'])) {
            throw new \InvalidArgumentException('Le type doit être Gold, Silver ou Bronze.');
        }

        if ($sponsor->getMontant() <= 0) {
            throw new \InvalidArgumentException('Le montant doit être supérieur à 0.');
        }

        return true;
    }
}