<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\QuestionChoix;
use App\Entity\QuestionVraiFaux;
use App\Entity\QuestionTexteLibre;


#[ORM\Entity(repositoryClass: QuestionRepository::class)]
#[ORM\Table(name: "question")]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: "type", type: "string")]
#[ORM\DiscriminatorMap([
    "choix" => QuestionChoix::class,
    "vraifaux" => QuestionVraiFaux::class,
    "texte" => QuestionTexteLibre::class
])]
abstract class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(length: 255)]
    protected ?string $texte = null;

    #[ORM\Column(length: 20)]
    protected ?string $niveau = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $indice = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    protected ?Quiz $quiz = null;

    // ===== Getters & Setters =====

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

    public function getNiveau(): ?string
    {
        return $this->niveau;
    }

    public function setNiveau(string $niveau): self
    {
        $this->niveau = $niveau;
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

    public function getType(): string
{
    return match (true) {
        $this instanceof QuestionChoix => 'choix',
        $this instanceof QuestionVraiFaux => 'vraifaux',
        $this instanceof QuestionTexteLibre => 'texte',
        default => 'unknown',
    };
}

}
