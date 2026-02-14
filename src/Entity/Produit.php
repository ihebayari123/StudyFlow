<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du produit ne peut pas être vide.")]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÿ0-9\s]+$/",
        message: "Le nom du produit ne doit contenir que des lettres et des chiffres (pas de caractères spéciaux)."
    )]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: "Le nom du produit doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom du produit ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    #[Assert\Length(
        min: 10,
        minMessage: "La description doit contenir au moins {{ limit }} caractères."
    )]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le prix ne peut pas être vide.")]
    #[Assert\Positive(message: "Le prix doit être un nombre positif.")]
    #[Assert\Range(
        min: 1,
        max: 1000000,
        notInRangeMessage: "Le prix doit être entre {{ min }} et {{ max }} DT."
    )]
    private ?int $prix = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'image ne peut pas être vide.")]
    #[Assert\Url(message: "L'URL de l'image n'est pas valide.")]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Vous devez sélectionner une catégorie.")]
    private ?TypeCategorie $typeCategorie = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Vous devez sélectionner un utilisateur.")]
    private ?Utilisateur $user = null;

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
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): static
    {
        $this->prix = $prix;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getTypeCategorie(): ?TypeCategorie
    {
        return $this->typeCategorie;
    }

    public function setTypeCategorie(?TypeCategorie $typeCategorie): static
    {
        $this->typeCategorie = $typeCategorie;
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
}
