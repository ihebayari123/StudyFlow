<?php

namespace App\Entity;

use App\Repository\ChapitreRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChapitreRepository::class)]
class Chapitre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    private ?string $contenu = null;

    #[ORM\Column(length: 255)]
    private ?string $ordre = null;

    #[ORM\ManyToOne(inversedBy: 'chapitres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cours $course = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOrdre(): ?string
    {
        return $this->ordre;
    }

    public function setOrdre(string $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getCourseId(): ?Cours
    {
        return $this->courseId;
    }

    public function setCourseId(?Cours $courseId): static
    {
        $this->courseId = $courseId;

        return $this;
    }
}
