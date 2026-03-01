<?php

namespace App\Tests\Service;

use App\Entity\Consultation;
use App\Entity\Medecin;
use App\Entity\StressSurvey;
use App\Service\ConsultationManager;
use PHPUnit\Framework\TestCase;

class ConsultationManagerTest extends TestCase
{
    private function createMockMedecin(): Medecin
    {
        return $this->createMock(Medecin::class);
    }

    private function createMockStressSurvey(): StressSurvey
    {
        return $this->createMock(StressSurvey::class);
    }

    public function testValidConsultation()
    {
        $consultation = new Consultation();
        $consultation->setDateDeConsultation(new \DateTime('2024-01-15'));
        $consultation->setMotif('Consultation stress');
        $consultation->setGenre('Homme');
        $consultation->setNiveau('Intermédiaire');
        $consultation->setMedecin($this->createMockMedecin());
        $consultation->setStressSurvey($this->createMockStressSurvey());

        $manager = new ConsultationManager();

        $this->assertTrue($manager->validate($consultation));
    }

    public function testConsultationWithoutDate()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date de consultation est obligatoire');

        $consultation = new Consultation();
        $consultation->setMotif('Consultation stress');
        $consultation->setGenre('Homme');
        $consultation->setNiveau('Intermédiaire');
        $consultation->setMedecin($this->createMockMedecin());
        $consultation->setStressSurvey($this->createMockStressSurvey());

        $manager = new ConsultationManager();
        $manager->validate($consultation);
    }

    public function testConsultationWithoutMotif()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le motif est obligatoire');

        $consultation = new Consultation();
        $consultation->setDateDeConsultation(new \DateTime('2024-01-15'));
        $consultation->setMotif('');
        $consultation->setGenre('Homme');
        $consultation->setNiveau('Intermédiaire');
        $consultation->setMedecin($this->createMockMedecin());
        $consultation->setStressSurvey($this->createMockStressSurvey());

        $manager = new ConsultationManager();
        $manager->validate($consultation);
    }

    public function testConsultationWithoutGenre()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le genre est obligatoire');

        $consultation = new Consultation();
        $consultation->setDateDeConsultation(new \DateTime('2024-01-15'));
        $consultation->setMotif('Consultation stress');
        $consultation->setGenre('');
        $consultation->setNiveau('Intermédiaire');
        $consultation->setMedecin($this->createMockMedecin());
        $consultation->setStressSurvey($this->createMockStressSurvey());

        $manager = new ConsultationManager();
        $manager->validate($consultation);
    }

    public function testConsultationWithoutMedecin()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le médecin est obligatoire');

        $consultation = new Consultation();
        $consultation->setDateDeConsultation(new \DateTime('2024-01-15'));
        $consultation->setMotif('Consultation stress');
        $consultation->setGenre('Homme');
        $consultation->setNiveau('Intermédiaire');
        $consultation->setMedecin(null);
        $consultation->setStressSurvey($this->createMockStressSurvey());

        $manager = new ConsultationManager();
        $manager->validate($consultation);
    }

    public function testConsultationWithoutStressSurvey()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le stress survey est obligatoire');

        $consultation = new Consultation();
        $consultation->setDateDeConsultation(new \DateTime('2024-01-15'));
        $consultation->setMotif('Consultation stress');
        $consultation->setGenre('Homme');
        $consultation->setNiveau('Intermédiaire');
        $consultation->setMedecin($this->createMockMedecin());
        $consultation->setStressSurvey(null);

        $manager = new ConsultationManager();
        $manager->validate($consultation);
    }

    public function testConsultationWithInvalidGenre()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le genre doit être: Homme, Femme ou Autre');

        $consultation = new Consultation();
        $consultation->setDateDeConsultation(new \DateTime('2024-01-15'));
        $consultation->setMotif('Consultation stress');
        $consultation->setGenre('Invalide');
        $consultation->setNiveau('Intermédiaire');
        $consultation->setMedecin($this->createMockMedecin());
        $consultation->setStressSurvey($this->createMockStressSurvey());

        $manager = new ConsultationManager();
        $manager->validate($consultation);
    }
}
