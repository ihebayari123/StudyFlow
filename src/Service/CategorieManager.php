<?php
namespace App\Service;

use App\Entity\TypeCategorie;

class CategorieManager
{
    public function validate(TypeCategorie $categorie): bool
    {
        if (empty($categorie->getNomCategorie())) {
            throw new \InvalidArgumentException('Le nom de la catégorie est obligatoire');
        }

        if (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/", $categorie->getNomCategorie())) {
            throw new \InvalidArgumentException('Le nom ne doit contenir que des lettres');
        }

        if (strlen($categorie->getNomCategorie()) > 50) {
            throw new \InvalidArgumentException('Le nom ne peut pas dépasser 50 caractères');
        }

        if (empty($categorie->getDescription())) {
            throw new \InvalidArgumentException('La description est obligatoire');
        }

        return true;
    }
}