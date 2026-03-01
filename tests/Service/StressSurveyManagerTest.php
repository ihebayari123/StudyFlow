<?php

namespace App\Tests\Service;

use App\Entity\StressSurvey;
use App\Entity\Utilisateur;
use App\Service\StressSurveyManager;
use PHPUnit\Framework\TestCase;

class StressSurveyManagerTest extends TestCase
{
    private function createMockUser(): Utilisateur
    {
        return $this->createMock(Utilisateur::class);
    }

    public function testValidStressSurvey()
    {
        $survey = new StressSurvey();
        $survey->setDate(new \DateTime('2024-01-15'));
        $survey->setSleepHours(7);
        $survey->setStudyHours(8);
        $survey->setUser($this->createMockUser());

        $manager = new StressSurveyManager();

        $this->assertTrue($manager->validate($survey));
    }

    public function testStressSurveyWithoutDate()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date est obligatoire');

        $survey = new StressSurvey();
        $survey->setSleepHours(7);
        $survey->setStudyHours(8);
        $survey->setUser($this->createMockUser());

        $manager = new StressSurveyManager();
        $manager->validate($survey);
    }

    public function testStressSurveyWithoutUser()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('L\'utilisateur est obligatoire');

        $survey = new StressSurvey();
        $survey->setDate(new \DateTime('2024-01-15'));
        $survey->setSleepHours(7);
        $survey->setStudyHours(8);
        $survey->setUser(null);

        $manager = new StressSurveyManager();
        $manager->validate($survey);
    }

    public function testStressSurveyWithSleepHoursExceeding24()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Les heures de sommeil ne peuvent pas dépasser 24');

        $survey = new StressSurvey();
        $survey->setDate(new \DateTime('2024-01-15'));
        $survey->setSleepHours(25);
        $survey->setStudyHours(8);
        $survey->setUser($this->createMockUser());

        $manager = new StressSurveyManager();
        $manager->validate($survey);
    }

    public function testStressSurveyWithStudyHoursExceeding24()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Les heures d\'étude ne peuvent pas dépasser 24');

        $survey = new StressSurvey();
        $survey->setDate(new \DateTime('2024-01-15'));
        $survey->setSleepHours(7);
        $survey->setStudyHours(25);
        $survey->setUser($this->createMockUser());

        $manager = new StressSurveyManager();
        $manager->validate($survey);
    }

    public function testStressSurveyWithZeroSleepHours()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Les heures de sommeil doivent être positives');

        $survey = new StressSurvey();
        $survey->setDate(new \DateTime('2024-01-15'));
        $survey->setSleepHours(0);
        $survey->setStudyHours(8);
        $survey->setUser($this->createMockUser());

        $manager = new StressSurveyManager();
        $manager->validate($survey);
    }

    public function testStressSurveyWithZeroStudyHours()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Les heures d\'étude doivent être positives');

        $survey = new StressSurvey();
        $survey->setDate(new \DateTime('2024-01-15'));
        $survey->setSleepHours(7);
        $survey->setStudyHours(0);
        $survey->setUser($this->createMockUser());

        $manager = new StressSurveyManager();
        $manager->validate($survey);
    }
}
