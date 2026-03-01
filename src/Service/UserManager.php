<?php
// src/Service/UserManager.php
namespace App\Service;

use App\Entity\Utilisateur;

class UserManager
{
    public function validate(Utilisateur $user): bool
    {
        // Règle 1: Nom obligatoire
        if (empty($user->getNom())) {
            throw new \InvalidArgumentException('Le nom est obligatoire');
        }

        // Règle 2: Prénom obligatoire
        if (empty($user->getPrenom())) {
            throw new \InvalidArgumentException('Le prénom est obligatoire');
        }

        // Règle 3: Email valide
        if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email invalide');
        }

        // Règle 4: Mot de passe ≥ 6 caractères
        if (strlen($user->getMotDePasse()) < 6) {
            throw new \InvalidArgumentException('Le mot de passe doit contenir au moins 6 caractères');
        }

        // Règle 5: Rôle valide
        $rolesValides = ['ROLE_ADMIN', 'ROLE_ETUDIANT', 'ROLE_ENSEIGNANT'];
        if (!in_array($user->getRole(), $rolesValides)) {
            throw new \InvalidArgumentException('Rôle invalide');
        }

        // Règle 6: EmailVerified ne peut pas être null
        if ($user->isEmailVerified() === null) {
            throw new \InvalidArgumentException('Le statut de vérification email ne peut pas être null');
        }

        $statutsValides = ['ACTIF', 'INACTIF', 'BLOQUE'];
        if (!in_array($user->getStatutCompte(), $statutsValides)) {
            throw new \InvalidArgumentException('Statut compte invalide');
        }

        return true;
    }
}