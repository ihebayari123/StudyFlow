<?php

namespace App\Entity;

use App\Repository\ChapitreVersionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChapitreVersionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ChapitreVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Chapitre::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Chapitre $chapitre = null;

    #[ORM\Column]
    private ?int $versionNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: 'text')]
    private ?string $contenu = null;

    #[ORM\Column]
    private ?int $ordre = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $contentType = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $videoUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fileName = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $links = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(nullable: true)]
    private ?int $durationMinutes = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $changeDescription = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $changesDetected = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $modificationPercentage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $modifiedBy = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
    }

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChapitre(): ?Chapitre
    {
        return $this->chapitre;
    }

    public function setChapitre(?Chapitre $chapitre): static
    {
        $this->chapitre = $chapitre;
        return $this;
    }

    public function getVersionNumber(): ?int
    {
        return $this->versionNumber;
    }

    public function setVersionNumber(int $versionNumber): static
    {
        $this->versionNumber = $versionNumber;
        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;
        return $this;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(?string $contentType): static
    {
        $this->contentType = $contentType;
        return $this;
    }

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(?string $videoUrl): static
    {
        $this->videoUrl = $videoUrl;
        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): static
    {
        $this->fileName = $fileName;
        return $this;
    }

    public function getLinks(): ?array
    {
        return $this->links;
    }

    public function setLinks(?array $links): static
    {
        $this->links = $links;
        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    public function getDurationMinutes(): ?int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(?int $durationMinutes): static
    {
        $this->durationMinutes = $durationMinutes;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getChangeDescription(): ?string
    {
        return $this->changeDescription;
    }

    public function setChangeDescription(?string $changeDescription): static
    {
        $this->changeDescription = $changeDescription;
        return $this;
    }

    public function getChangesDetected(): ?array
    {
        return $this->changesDetected;
    }

    public function setChangesDetected(?array $changesDetected): static
    {
        $this->changesDetected = $changesDetected;
        return $this;
    }

    public function getModificationPercentage(): ?float
    {
        return $this->modificationPercentage;
    }

    public function setModificationPercentage(?float $modificationPercentage): static
    {
        $this->modificationPercentage = $modificationPercentage;
        return $this;
    }

    public function getModifiedBy(): ?string
    {
        return $this->modifiedBy;
    }

    public function setModifiedBy(?string $modifiedBy): static
    {
        $this->modifiedBy = $modifiedBy;
        return $this;
    }
}
