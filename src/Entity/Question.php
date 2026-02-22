<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le texte de la question est obligatoire.")]
    #[Assert\Length(
        min: 5,
        minMessage: "Le texte de la question doit contenir au moins {{ limit }} caractères."
    )]
    private ?string $texte = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le choix A est obligatoire.")]
    private ?string $choixA = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le choix B est obligatoire.")]
    private ?string $choixB = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le choix C est obligatoire.")]
    private ?string $choixC = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le choix D est obligatoire.")]
    private ?string $choixD = null;

    #[ORM\Column(length: 1)]
    #[Assert\NotBlank(message: "Veuillez sélectionner la bonne réponse.")]
    #[Assert\Choice(
        choices: ['A', 'B', 'C', 'D'],
        message: "La bonne réponse doit être A, B, C ou D."
    )]
    private ?string $bonneReponse = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        min: 3,
        minMessage: "L’indice doit contenir au moins {{ limit }} caractères."
    )]
    private ?string $indice = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le quiz associé est obligatoire.")]
    private ?Quiz $quiz = null;

    // ================= GETTERS & SETTERS =================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(string $texte): self
    {
        $this->texte = $texte;
        return $this;
    }

    public function getChoixA(): ?string
    {
        return $this->choixA;
    }

    public function setChoixA(string $choixA): self
    {
        $this->choixA = $choixA;
        return $this;
    }

    public function getChoixB(): ?string
    {
        return $this->choixB;
    }

    public function setChoixB(string $choixB): self
    {
        $this->choixB = $choixB;
        return $this;
    }

    public function getChoixC(): ?string
    {
        return $this->choixC;
    }

    public function setChoixC(string $choixC): self
    {
        $this->choixC = $choixC;
        return $this;
    }

    public function getChoixD(): ?string
    {
        return $this->choixD;
    }

    public function setChoixD(string $choixD): self
    {
        $this->choixD = $choixD;
        return $this;
    }

    public function getBonneReponse(): ?string
    {
        return $this->bonneReponse;
    }

    public function setBonneReponse(string $bonneReponse): self
    {
        $this->bonneReponse = $bonneReponse;
        return $this;
    }

    public function getIndice(): ?string
    {
        return $this->indice;
    }

    public function setIndice(?string $indice): self
    {
        $this->indice = $indice;
        return $this;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): self
    {
        $this->quiz = $quiz;
        return $this;
    }
}