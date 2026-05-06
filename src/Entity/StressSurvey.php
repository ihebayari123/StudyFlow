<?php

namespace App\Entity;

use App\Repository\StressSurveyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StressSurveyRepository::class)]
class StressSurvey
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date est obligatoire')]
    #[Assert\Type(\DateTime::class)]
    private ?\DateTime $date = null;

    /**
     * Heures de sommeil : entier entre 0 et 24 (contrainte back)
     */
    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: "Le nombre d'heures de sommeil est obligatoire")]
    #[Assert\Type(type: 'integer', message: 'Doit être un entier')]
    #[Assert\Range(min: 0, max: 24, notInRangeMessage: 'Les heures de sommeil doivent être entre {{ min }} et {{ max }}')]
    private ?int $sleepHours = null;

    /**
     * Heures d'étude : entier entre 0 et 24 (contrainte back)
     */
    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: "Le nombre d'heures d'étude est obligatoire")]
    #[Assert\Type(type: 'integer', message: 'Doit être un entier')]
    #[Assert\Range(min: 0, max: 24, notInRangeMessage: "Les heures d'étude doivent être entre {{ min }} et {{ max }}")]
    private ?int $studyHours = null;

    #[ORM\ManyToOne(inversedBy: 'stressSurveys')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Veuillez sélectionner un utilisateur')]
    private ?Utilisateur $user = null;

    #[ORM\OneToOne(mappedBy: 'survey', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?WellBeingScore $wellBeingScore = null;

    #[ORM\OneToMany(mappedBy: 'stress_survey', targetEntity: Consultation::class, cascade: ['remove'], orphanRemoval: true)]
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
}

