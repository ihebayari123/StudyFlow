<?php

namespace App\Entity;

use App\Repository\QuizAttemptRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizAttemptRepository::class)]
class QuizAttempt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    
    #[ORM\Column]
    private ?int $scoreQuestions = 0;

    
    #[ORM\Column]
    private ?int $scorePoints = 0;

    
    #[ORM\Column]
    private ?int $totalQuestions = 0;

    
    #[ORM\Column]
    private ?\DateTimeImmutable $startedAt = null;

    
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $finishedAt = null;

    #[ORM\ManyToOne(inversedBy: 'quizAttempts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $user = null;

    #[ORM\ManyToOne(inversedBy: 'quizAttempts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScoreQuestions(): ?int
    {
        return $this->scoreQuestions;
    }

    public function setScoreQuestions(int $scoreQuestions): static
    {
        $this->scoreQuestions = $scoreQuestions;
        return $this;
    }

    public function getScorePoints(): ?int
    {
        return $this->scorePoints;
    }

    public function setScorePoints(int $scorePoints): static
    {
        $this->scorePoints = $scorePoints;
        return $this;
    }

    public function getTotalQuestions(): ?int
    {
        return $this->totalQuestions;
    }

    public function setTotalQuestions(int $totalQuestions): static
    {
        $this->totalQuestions = $totalQuestions;
        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;
        return $this;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTimeImmutable $finishedAt): static
    {
        $this->finishedAt = $finishedAt;
        return $this;
    }

    public function getUser(): ?Utilisateur
    {
        return $this->user;
    }

    public function setUser(?Utilisateur $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): static
    {
        $this->quiz = $quiz;
        return $this;
    }
}
