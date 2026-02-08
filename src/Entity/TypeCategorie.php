<?php

namespace App\Entity;

use App\Repository\TypeCategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TypeCategorieRepository::class)]
class TypeCategorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de la catégorie ne peut pas être vide.")]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÿ\s]+$/",
        message: "Le nom de la catégorie ne doit contenir que des lettres (pas de chiffres ni de caractères spéciaux)."
    )]
    #[Assert\Length(
        max: 50,
        maxMessage: "Le nom de la catégorie ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $nomCategorie = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    private ?string $description = null;

    /**
     * @var Collection<int, Produit>
     */
    #[ORM\OneToMany(
        targetEntity: Produit::class, 
        mappedBy: 'typeCategorie',
        cascade: ['remove']  // This will delete all products when category is deleted
    )]
    private Collection $produits;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomCategorie(): ?string
    {
        return $this->nomCategorie;
    }

    public function setNomCategorie(string $nomCategorie): static
    {
        $this->nomCategorie = $nomCategorie;
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

    /**
     * @return Collection<int, Produit>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): static
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->setTypeCategorie($this);
        }
        return $this;
    }

    public function removeProduit(Produit $produit): static
    {
        if ($this->produits->removeElement($produit)) {
            if ($produit->getTypeCategorie() === $this) {
                $produit->setTypeCategorie(null);
            }
        }
        return $this;
    }
}