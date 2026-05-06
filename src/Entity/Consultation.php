<?php

namespace App\Entity;

use App\Repository\ConsultationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ConsultationRepository::class)]
class Consultation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Date obligatoirement dans le futur (contrainte back)
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: 'La date de consultation est obligatoire')]
    #[Assert\GreaterThan('now', message: 'La date de consultation doit être dans le futur')]
    private ?\DateTime $date_de_consultation = null;

    /**
     * Motif : 5 à 255 caractères (contrainte back)
     */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le motif est obligatoire')]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: 'Le motif doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le motif ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $motif = null;

    /**
     * Genre : exactement "Homme", "Femme" ou "Etudiant" (contrainte back)
     */
    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le genre est obligatoire')]
    #[Assert\Choice(
        choices: ['Homme', 'Femme', 'Etudiant'],
        message: 'Le genre doit être exactement "Homme", "Femme" ou "Etudiant"'
    )]
    private ?string $genre = null;

    /**
     * Niveau d'étude : 2 à 30 caractères (contrainte back)
     */
    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Le niveau d'étude est obligatoire")]
    #[Assert\Length(
        min: 2,
        max: 30,
        minMessage: "Le niveau d'étude doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le niveau d'étude ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $niveau = null;

    /**
     * Médecin vérifié en base avant enregistrement (contrainte back via NotNull)
     */
    #[ORM\ManyToOne(inversedBy: 'consultations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le médecin est obligatoire et doit exister en base')]
    private ?Medecin $medecin = null;

    /**
     * Survey vérifié en base avant enregistrement (contrainte back via NotNull)
     */
    #[ORM\ManyToOne(inversedBy: 'consultations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le sondage de stress est obligatoire et doit exister en base')]
    private ?StressSurvey $stress_survey = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDeConsultation(): ?\DateTime
    {
        return $this->date_de_consultation;
    }

    public function setDateDeConsultation(\DateTime $date_de_consultation): static
    {
        $this->date_de_consultation = $date_de_consultation;

        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(string $motif): static
    {
        $this->motif = $motif;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getNiveau(): ?string
    {
        return $this->niveau;
    }

    public function setNiveau(string $niveau): static
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getMedecin(): ?Medecin
    {
        return $this->medecin;
    }

    public function setMedecin(?Medecin $medecin): static
    {
        $this->medecin = $medecin;

        return $this;
    }

    public function getStressSurvey(): ?StressSurvey
    {
        return $this->stress_survey;
    }

    public function setStressSurvey(?StressSurvey $stress_survey): static
    {
        $this->stress_survey = $stress_survey;

        return $this;
    }
}
