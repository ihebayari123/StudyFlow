<?php

namespace App\Entity;

use App\Repository\SponsorRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SponsorRepository::class)]
class Sponsor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du sponsor est obligatoire.")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[A-Z].*$/",
        message: "Le nom du sponsor doit commencer par une majuscule."
    )]
    private ?string $nomSponsor = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type de sponsor est obligatoire.")]
    #[Assert\Choice(
        choices: ['Gold', 'Silver', 'Bronze'],
        message: "Veuillez respecter le format demandé."
    )]
    private ?string $type = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le montant est obligatoire.")]
    #[Assert\Positive(message: "Le montant doit être supérieur à 0.")]
    private ?int $montant = null;

    #[ORM\ManyToOne(inversedBy: 'sponsors')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'événement associé est obligatoire.")]
    private ?Event $eventTitre = null;

    // ------------------- Getters & Setters -------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomSponsor(): ?string
    {
        return $this->nomSponsor;
    }

    public function setNomSponsor(string $nomSponsor): static
    {
        $this->nomSponsor = $nomSponsor;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getMontant(): ?int
    {
        return $this->montant;
    }

    public function setMontant(int $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getEventTitre(): ?Event
    {
        return $this->eventTitre;
    }

    public function setEventTitre(?Event $eventTitre): static
    {
        $this->eventTitre = $eventTitre;

        return $this;
    }
}
