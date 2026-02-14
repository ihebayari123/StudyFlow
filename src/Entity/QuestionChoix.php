<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class QuestionChoix extends Question
{
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $choixA = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $choixB = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $choixC = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $choixD = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $bonneReponseChoix = null;


    // ===== Getters & Setters =====

    public function getChoixA(): ?string { return $this->choixA; }
    public function setChoixA(?string $choixA): self { $this->choixA = $choixA; return $this; }

    public function getChoixB(): ?string { return $this->choixB; }
    public function setChoixB(?string $choixB): self { $this->choixB = $choixB; return $this; }

    public function getChoixC(): ?string { return $this->choixC; }
    public function setChoixC(?string $choixC): self { $this->choixC = $choixC; return $this; }

    public function getChoixD(): ?string { return $this->choixD; }
    public function setChoixD(?string $choixD): self { $this->choixD = $choixD; return $this; }
public function getBonneReponseChoix(): ?string
{
    return $this->bonneReponseChoix;
}

public function setBonneReponseChoix(?string $bonneReponseChoix): self
{
    $this->bonneReponseChoix = $bonneReponseChoix;
    return $this;
}
}
