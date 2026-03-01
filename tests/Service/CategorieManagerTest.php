<?php
namespace App\Tests\Service;

use App\Entity\TypeCategorie;
use App\Service\CategorieManager;
use PHPUnit\Framework\TestCase;

class CategorieManagerTest extends TestCase
{
    public function testValidCategorie()
    {
        $categorie = new TypeCategorie();
        $categorie->setNomCategorie('Electronique');
        $categorie->setDescription('Produits électroniques');

        $manager = new CategorieManager();
        $this->assertTrue($manager->validate($categorie));
    }

    public function testCategorieWithoutNom()
    {
        $this->expectException(\InvalidArgumentException::class);

        $categorie = new TypeCategorie();
        $categorie->setDescription('Une description');

        $manager = new CategorieManager();
        $manager->validate($categorie);
    }

    public function testCategorieWithInvalidNom()
    {
        $this->expectException(\InvalidArgumentException::class);

        $categorie = new TypeCategorie();
        $categorie->setNomCategorie('Cat123!!');
        $categorie->setDescription('Une description');

        $manager = new CategorieManager();
        $manager->validate($categorie);
    }

    public function testCategorieWithoutDescription()
    {
        $this->expectException(\InvalidArgumentException::class);

        $categorie = new TypeCategorie();
        $categorie->setNomCategorie('Electronique');

        $manager = new CategorieManager();
        $manager->validate($categorie);
    }
}
