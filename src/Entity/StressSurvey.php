<?php

namespace App\Entity;

use App\Repository\StressSurveyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StressSurveyRepository::class)]
class StressSurvey
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column]
    private ?int $sleepHours = null;

    #[ORM\Column]
    private ?int $studyHours = null;

    #[ORM\ManyToOne(inversedBy: 'stressSurveys')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $user = null;

    #[ORM\OneToOne(mappedBy: 'surveyId', cascade: ['persist', 'remove'])]
    private ?WellBeingScore $wellBeingScore = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getSleepHours(): ?int
    {
        return $this->sleepHours;
    }

    public function setSleepHours(int $sleepHours): static
    {
        $this->sleepHours = $sleepHours;

        return $this;
    }

    public function getStudyHours(): ?int
    {
        return $this->studyHours;
    }

    public function setStudyHours(int $studyHours): static
    {
        $this->studyHours = $studyHours;

        return $this;
    }

    public function getUserId(): ?Utilisateur
    {
        return $this->userId;
    }

    public function setUserId(?Utilisateur $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getWellBeingScore(): ?WellBeingScore
    {
        return $this->wellBeingScore;
    }

    public function setWellBeingScore(WellBeingScore $wellBeingScore): static
    {
        // set the owning side of the relation if necessary
        if ($wellBeingScore->getSurveyId() !== $this) {
            $wellBeingScore->setSurveyId($this);
        }

        $this->wellBeingScore = $wellBeingScore;

        return $this;
    }
}
