<?php
// tests/Service/UserManagerTest.php
namespace App\Tests\Service;

use App\Entity\Utilisateur;
use App\Service\UserManager;
use PHPUnit\Framework\TestCase;

class UserManagerTest extends TestCase
{
    private $userManager;

    protected function setUp(): void
    {
        $this->userManager = new UserManager();
    }

    // ✅ TEST 1: Utilisateur valide
    public function testValidUser()
    {
        $user = $this->createValidUser();
        $this->assertTrue($this->userManager->validate($user));
    }

    // ✅ TEST 2: Nom obligatoire
    public function testUserWithoutName()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom est obligatoire');

        $user = $this->createValidUser();
        $user->setNom('');
        
        $this->userManager->validate($user);
    }

    // ✅ TEST 3: Prénom obligatoire
    public function testUserWithoutPrenom()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le prénom est obligatoire');

        $user = $this->createValidUser();
        $user->setPrenom('');
        
        $this->userManager->validate($user);
    }

    // ✅ TEST 4: Email invalide
    public function testUserWithInvalidEmail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email invalide');

        $user = $this->createValidUser();
        $user->setEmail('pas-un-email');
        
        $this->userManager->validate($user);
    }

    // ✅ TEST 5: Mot de passe trop court (<6)
    public function testUserWithShortPassword()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le mot de passe doit contenir au moins 6 caractères');

        $user = $this->createValidUser();
        $user->setMotDePasse('12345'); // 5 caractères
        
        $this->userManager->validate($user);
    }

    // ✅ TEST 6: Rôle invalide
    public function testUserWithInvalidRole()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rôle invalide');

        $user = $this->createValidUser();
        $user->setRole('ROLE_INVALIDE');
        
        $this->userManager->validate($user);
    }

    public function testUserWithInvalidStatutCompte()
{
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Statut compte invalide');

    $user = $this->createValidUser();
    $user->setStatutCompte('INVALIDE'); // Valeur non autorisée
    
    $this->userManager->validate($user);
}
    private function createValidUser(): Utilisateur
    {
        $user = new Utilisateur();
        $user->setNom('Agrebi');
        $user->setPrenom('Oussema');
        $user->setEmail('oussema@test.com');
        $user->setMotDePasse('password123');
        $user->setRole('ROLE_ETUDIANT');
        $user->setStatutCompte('ACTIF');
        $user->setEmailVerified(false);
        
        return $user;
    }
}