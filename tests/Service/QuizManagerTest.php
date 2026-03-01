<?php
// tests/Service/QuizManagerTest.php
namespace App\Tests\Service;

use App\Entity\Quiz;
use App\Service\QuizManager;
use PHPUnit\Framework\TestCase;

class QuizManagerTest extends TestCase
{
    // Test 1 — Quiz valide
    public function testValidQuiz(): void
    {
        $quiz = new Quiz();
        $quiz->setTitre('Quiz Algorithmique');
        $quiz->setDuree(30);

        $manager = new QuizManager();
        $this->assertTrue($manager->validate($quiz));
    }

    // Test 2 — Titre vide
    public function testQuizWithoutTitre(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le titre du quiz est obligatoire');

        $quiz = new Quiz();
        $quiz->setTitre('');
        $quiz->setDuree(30);

        $manager = new QuizManager();
        $manager->validate($quiz);
    }

    // Test 3 — Durée négative
    public function testQuizWithNegativeDuree(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La durée doit être supérieure à zéro');

        $quiz = new Quiz();
        $quiz->setTitre('Quiz Valide');
        $quiz->setDuree(-10);

        $manager = new QuizManager();
        $manager->validate($quiz);
    }

    // Test 4 — Durée zéro
    public function testQuizWithZeroDuree(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $quiz = new Quiz();
        $quiz->setTitre('Quiz Test');
        $quiz->setDuree(0);

        $manager = new QuizManager();
        $manager->validate($quiz);
    }

    // Test 5 — Durée trop longue
    public function testQuizWithDureeTropLongue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La durée ne peut pas dépasser 180 minutes');

        $quiz = new Quiz();
        $quiz->setTitre('Quiz Trop Long');
        $quiz->setDuree(200);

        $manager = new QuizManager();
        $manager->validate($quiz);
    }

    // Test 6 — Durée limite exacte (180 min)
    public function testQuizWithDureeLimite(): void
    {
        $quiz = new Quiz();
        $quiz->setTitre('Quiz Limite');
        $quiz->setDuree(180);

        $manager = new QuizManager();
        $this->assertTrue($manager->validate($quiz));
    }
}