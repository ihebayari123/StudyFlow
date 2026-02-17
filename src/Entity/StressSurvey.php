<?php

namespace App\Entity;

use App\Repository\StressSurveyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\OneToOne(mappedBy: 'survey', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?WellBeingScore $wellBeingScore = null;

    /**
     * @var Collection<int, Consultation>
     */
    #[ORM\OneToMany(targetEntity: Consultation::class, mappedBy: 'stress_survey', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $consultations;

    public function __construct()
    {
        $this->consultations = new ArrayCollection();
    }

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

    public function getUser(): ?Utilisateur
    {
        return $this->user;
    }

    public function setUser(?Utilisateur $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getWellBeingScore(): ?WellBeingScore
    {
        return $this->wellBeingScore;
    }

    public function setWellBeingScore(WellBeingScore $wellBeingScore): static
    {
        // set the owning side of the relation if necessary
        if ($wellBeingScore->getSurvey() !== $this) {
            $wellBeingScore->setSurvey($this);
        }

        $this->wellBeingScore = $wellBeingScore;

        return $this;
    }

    /**
     * @return Collection<int, Consultation>
     */
    public function getConsultations(): Collection
    {
        return $this->consultations;
    }

    public function addConsultation(Consultation $consultation): static
    {
        if (!$this->consultations->contains($consultation)) {
            $this->consultations->add($consultation);
            $consultation->setStressSurvey($this);
        }

        return $this;
    }

    public function removeConsultation(Consultation $consultation): static
    {
        if ($this->consultations->removeElement($consultation)) {
            // set the owning side to null (unless already changed)
            if ($consultation->getStressSurvey() === $this) {
                $consultation->setStressSurvey(null);
            }
        }

        return $this;
    }
}
