<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class QuestionVraiFaux extends Question
{
    #[ORM\Column(type: "boolean", nullable: true)]
    private ?bool $bonneReponseBool = null;

    public function getBonneReponseBool(): ?bool
    {
        return $this->bonneReponseBool;
    }

    public function setBonneReponseBool(?bool $bonneReponseBool): self
    {
        $this->bonneReponseBool = $bonneReponseBool;
        return $this;
    }
}

