<?php

namespace App\Controller;

use App\Repository\QuizRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Quiz;
use App\Entity\Question;
use App\Entity\QuestionChoix;
use App\Entity\QuestionVraiFaux;
use App\Entity\QuestionTexteLibre;

use App\Repository\UtilisateurRepository;
use App\Repository\QuestionRepository;


class FrontQuizController extends AbstractController
{
    #[Route('/front/quiz', name: 'quiz_index')]
    public function index(QuizRepository $repo): Response
    {
        return $this->render('front_quiz/index.html.twig', [
            'quizzes' => $repo->findAll(),
        ]);
    }

    #[Route('/front/showquiz', name: 'app_frontshowquiz')]
public function showQuiz(Request $request, QuizRepository $repo): Response
{
    $search = $request->query->get('q');
    $sort   = $request->query->get('sort', 'id');
    $order  = $request->query->get('order', 'ASC');

    $qb = $repo->createQueryBuilder('q')
        ->leftJoin('q.course', 'c')
        ->addSelect('c');

    if ($search) {
        $qb->andWhere('q.titre LIKE :search')
           ->setParameter('search', '%' . $search . '%');
    }

    
    $allowedSort = ['id', 'titre', 'duree', 'dateCreation'];
    if (!in_array($sort, $allowedSort)) {
        $sort = 'id';
    }

    $qb->orderBy('q.' . $sort, $order);

    return $this->render('front_quiz/showQuiz.html.twig', [
        'listQuiz' => $qb->getQuery()->getResult(),
        'sort' => $sort,
        'order' => $order,
    ]);
}


 #[Route('/front/quiz/{id}/play', name: 'app_quiz_play')]
public function quizPlay(
    int $id,
    QuizRepository $quizRepository,
    QuestionRepository $questionRepository
): Response
{
    
    $quiz = $quizRepository->find($id);

    if (!$quiz) {
        throw $this->createNotFoundException('Quiz introuvable');
    }

    
    $questions = $questionRepository->findBy([
        'quiz' => $quiz
    ]);

    return $this->render('front_quiz/quiz_play.html.twig', [
        'quiz' => $quiz,
        'questions' => $questions,
    ]);
}
  #[Route('/front/quiz/{id}/submit', name: 'quiz_submit', methods: ['POST'])]
public function quizSubmit(
    int $id,
    Request $request,
    QuizRepository $quizRepository,
    QuestionRepository $questionRepository
): Response
{
    $quiz = $quizRepository->find($id);

    if (!$quiz) {
        throw $this->createNotFoundException('Quiz introuvable');
    }

    $questions = $questionRepository->findBy([
        'quiz' => $quiz
    ]);

    $answers = $request->request->all('answers') ?? [];

    $scoreQuestions = 0;
    $scorePoints = 0;
    $total = count($questions);
    $maxPoints = 0;

    foreach ($questions as $question) {

    $userAnswer = $answers[$question->getId()] ?? null;
    $isCorrect = false;

    // ===== QCM =====
    if ($question instanceof QuestionChoix) {
        $isCorrect = $userAnswer === $question->getBonneReponseChoix();
    }

    // ===== VRAI / FAUX =====
    elseif ($question instanceof QuestionVraiFaux) {
        $userBool = $userAnswer === "1";
        $isCorrect = $userBool === $question->getBonneReponseBool();
    }

    // ===== TEXTE LIBRE =====
    elseif ($question instanceof QuestionTexteLibre) {

        $bonneReponse = $question->getReponseAttendue();

        $isCorrect =
            strtolower(trim($userAnswer)) ===
            strtolower(trim($bonneReponse));
    }
     

    switch (strtolower($question->getNiveau())) {
    case 'facile':
        $maxPoints += 1;
        break;
    case 'moyen':
        $maxPoints += 2;
        break;
    case 'difficile':
        $maxPoints += 3;
        break;
}

    // ===== score=====
    if ($isCorrect) {

        $scoreQuestions++;

        
        switch (strtolower($question->getNiveau())) {
            case 'facile':
                $scorePoints += 1;
                break;
            case 'moyen':
                $scorePoints += 2;
                break;
            case 'difficile':
                $scorePoints += 3;
                break;
        }
    }
}



    return $this->render('front_quiz/quiz_result.html.twig', [
    'quiz' => $quiz,
    'scoreQuestions' => $scoreQuestions,
    'scorePoints' => $scorePoints,
    'maxPoints' => $maxPoints,
    'total' => $total,
]);

}

}
