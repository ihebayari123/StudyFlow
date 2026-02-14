<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class QuestionTexteLibre extends Question
{
    #[ORM\Column(type: "text", nullable: true)]
    private ?string $reponseAttendue = null;

    public function getReponseAttendue(): ?string
    {
        return $this->reponseAttendue;
    }

    public function setReponseAttendue(?string $reponseAttendue): self
    {
        $this->reponseAttendue = $reponseAttendue;
        return $this;
    }
}
