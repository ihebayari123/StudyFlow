<?php

namespace App\Entity;

use App\Repository\ConsultationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsultationRepository::class)]
class Consultation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $date_de_consultation = null;

    #[ORM\Column(length: 255)]
    private ?string $motif = null;

    #[ORM\Column(length: 255)]
    private ?string $genre = null;

    #[ORM\Column(length: 255)]
    private ?string $niveau = null;

    #[ORM\ManyToOne(inversedBy: 'consultations')]
    private ?Medecin $medecin = null;

    #[ORM\ManyToOne(inversedBy: 'consultations')]
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
