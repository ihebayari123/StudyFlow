<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $texte = null;

    #[ORM\Column(length: 255)]
    private ?string $choix = null;

    #[ORM\Column(length: 255)]
    private ?string $bonneReponse = null;

    #[ORM\Column(length: 255)]
    private ?string $indice = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(string $texte): static
    {
        $this->texte = $texte;

        return $this;
    }

    public function getChoix(): ?string
    {
        return $this->choix;
    }

    public function setChoix(string $choix): static
    {
        $this->choix = $choix;

        return $this;
    }

    public function getBonneReponse(): ?string
    {
        return $this->bonneReponse;
    }

    public function setBonneReponse(string $bonneReponse): static
    {
        $this->bonneReponse = $bonneReponse;

        return $this;
    }

    public function getIndice(): ?string
    {
        return $this->indice;
    }

    public function setIndice(string $indice): static
    {
        $this->indice = $indice;

        return $this;
    }

    public function getQuizId(): ?Quiz
    {
        return $this->quizId;
    }

    public function setQuizId(?Quiz $quizId): static
    {
        $this->quizId = $quizId;

        return $this;
    }
}
