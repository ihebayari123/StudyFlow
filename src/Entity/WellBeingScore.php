<?php

namespace App\Entity;

use App\Repository\WellBeingScoreRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WellBeingScoreRepository::class)]
class WellBeingScore
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $recommendation = null;

    #[ORM\Column(length: 255)]
    private ?string $actionPlan = null;

    #[ORM\Column(length: 255)]
    private ?string $comment = null;

    #[ORM\Column]
    private ?int $score = null;

    #[ORM\OneToOne(inversedBy: 'wellBeingScore', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?StressSurvey $survey = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecommendation(): ?string
    {
        return $this->recommendation;
    }

    public function setRecommendation(string $recommendation): static
    {
        $this->recommendation = $recommendation;

        return $this;
    }

    public function getActionPlan(): ?string
    {
        return $this->actionPlan;
    }

    public function setActionPlan(string $actionPlan): static
    {
        $this->actionPlan = $actionPlan;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getSurveyId(): ?StressSurvey
    {
        return $this->surveyId;
    }

    public function setSurveyId(StressSurvey $surveyId): static
    {
        $this->surveyId = $surveyId;

        return $this;
    }
}
