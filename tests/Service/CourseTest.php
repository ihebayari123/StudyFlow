<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\Cours;
use App\Entity\Utilisateur;
use App\Service\CourseManager;


class CourseTest extends TestCase
{
    public function testValidCours()
{
    $user = $this->createMock(\App\Entity\Utilisateur::class);

    $cours = new Cours();
    $cours->setTitre('Symfony Avancé');
    $cours->setDescription('Cours complet sur Symfony avec projet pratique.');
    $cours->setUser($user);

    $manager = new CourseManager();

    $this->assertTrue($manager->validate($cours));
}

    public function testCoursWithoutTitre()
    {
        $this->expectException(\InvalidArgumentException::class);

        $cours = new Cours();
        $cours->setDescription('Description valide avec plus de 10 caractères.');

        $manager = new CourseManager();
        $manager->validate($cours);
    }

    public function testCoursWithShortDescription()
    {
        $this->expectException(\InvalidArgumentException::class);

        $cours = new Cours();
        $cours->setTitre('Test');
        $cours->setDescription('court');

        $manager = new CourseManager();
        $manager->validate($cours);
    }
}
