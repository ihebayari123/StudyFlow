<?php

namespace App\Entity;

use App\Repository\MedecinRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MedecinRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Medecin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Nom stocké en majuscules (contrainte back)
     */
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(min: 2, max: 100)]
    #[Assert\Regex(pattern: '/^[A-ZÀ-Ÿ\s\-]+$/', message: 'Le nom doit être en majuscules')]
    private ?string $nom = null;

    /**
     * Prénom stocké en majuscules (contrainte back)
     */
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire')]
    #[Assert\Length(min: 2, max: 100)]
    #[Assert\Regex(pattern: '/^[A-ZÀ-Ÿ\s\-]+$/', message: 'Le prénom doit être en majuscules')]
    private ?string $prenom = null;

    /**
     * Email doit se terminer par @gmail.com (contrainte back)
     */
    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "L'email est obligatoire")]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9._%+\-]+@gmail\.com$/',
        message: "L'email doit se terminer par @gmail.com"
    )]
    private ?string $email = null;

    /**
     * Téléphone au format +XXX suivi de 8 chiffres (contrainte back)
     */
    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\NotBlank(message: 'Le téléphone est obligatoire')]
    #[Assert\Regex(
        pattern: '/^\+\d{1,4}\d{8}$/',
        message: 'Le téléphone doit être au format +XXX suivi de 8 chiffres (ex: +21612345678)'
    )]
    private ?string $telephone = null;

    /**
     * Disponibilité : "disponible" ou "indisponible" (contrainte back)
     */
    #[ORM\Column(type: 'boolean')]
    #[Assert\NotNull(message: 'La disponibilité est obligatoire')]
    private ?bool $disponibilite = null;

    #[ORM\OneToMany(mappedBy: 'medecin', targetEntity: Consultation::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $consultations;

    public function __construct()
    {
        $this->consultations = new ArrayCollection();
    }

    /**
     * Convertit nom et prénom en majuscules avant persistance
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function uppercaseNames(): void
    {
        if ($this->nom !== null) {
            $this->nom = mb_strtoupper($this->nom, 'UTF-8');
        }
        if ($this->prenom !== null) {
            $this->prenom = mb_strtoupper($this->prenom, 'UTF-8');
        }
    }

    // ================= GETTERS & SETTERS =================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = mb_strtoupper($nom, 'UTF-8');
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = mb_strtoupper($prenom, 'UTF-8');
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    /**
     * Retourne true si disponible, false si indisponible
     */
    public function getDisponibilite(): ?bool
    {
        return $this->disponibilite;
    }

    public function setDisponibilite(bool $disponibilite): static
    {
        $this->disponibilite = $disponibilite;
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
            $consultation->setMedecin($this);
        }
        return $this;
    }

    public function removeConsultation(Consultation $consultation): static
    {
        if ($this->consultations->removeElement($consultation)) {
            if ($consultation->getMedecin() === $this) {
                $consultation->setMedecin(null);
            }
        }
        return $this;
    }
}
