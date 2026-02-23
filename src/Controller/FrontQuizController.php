<?php

namespace App\Controller;

use App\Repository\QuizRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\QuizAttempt;
use App\Entity\Quiz;
use App\Entity\Question;
use App\Entity\QuestionChoix;
use App\Entity\QuestionVraiFaux;
use App\Entity\QuestionTexteLibre;
use App\Service\SmartValidatorService;
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
    QuestionRepository $questionRepository,
    EntityManagerInterface $em,
     SmartValidatorService $validator 
): Response {
    $quiz = $quizRepository->find($id);
    if (!$quiz) throw $this->createNotFoundException('Quiz introuvable');

    $questions = $questionRepository->findBy(['quiz' => $quiz]);
    $answers = $request->request->all('answers') ?? [];

    $scoreQuestions = 0;
    $scorePoints = 0;
    $total = count($questions);
    $maxPoints = 0;
    $questionsData = []; 

    foreach ($questions as $question) {
        $userAnswer = $answers[$question->getId()] ?? null;
        $isCorrect = false;
        $correctAnswer = '';
        $explanation   = '';
        $confidence    = 100;

        if ($question instanceof QuestionChoix) {
            $correctAnswer = $question->getBonneReponseChoix();
            $isCorrect = $userAnswer === $correctAnswer;
        } elseif ($question instanceof QuestionVraiFaux) {
            $userBool = $userAnswer === "1";
            $correctAnswer = $question->getBonneReponseBool() ? 'Vrai' : 'Faux';
            $userAnswer = $userBool ? 'Vrai' : 'Faux';
            $isCorrect = $userBool === $question->getBonneReponseBool();
        } elseif ($question instanceof QuestionTexteLibre) {
            $correctAnswer = $question->getReponseAttendue();
           $validation  = $validator->validate(
                $question->getTexte(),
                $correctAnswer,
                $userAnswer ?? ''
            );

            $isCorrect   = $validation['isCorrect'];
            $explanation = $validation['explanation'];
            $confidence  = $validation['confidence'];
        }

        switch (strtolower($question->getNiveau())) {
            case 'facile':   $maxPoints += 1; break;
            case 'moyen':    $maxPoints += 2; break;
            case 'difficile': $maxPoints += 3; break;
        }

        if ($isCorrect) {
            $scoreQuestions++;
            switch (strtolower($question->getNiveau())) {
                case 'facile':   $scorePoints += 1; break;
                case 'moyen':    $scorePoints += 2; break;
                case 'difficile': $scorePoints += 3; break;
            }
        }

        
        $questionsData[] = [
            'texte'       => $question->getTexte(),
            'type'        => $question->getType(),
            'niveau'      => $question->getNiveau(),
            'userAnswer'  => $userAnswer,
            'correct'     => $correctAnswer,
            'isCorrect'   => $isCorrect,
            'explanation' => $explanation,  
            'confidence'  => $confidence,
        ];
    }

    
    $attempt = new QuizAttempt();
    $attempt->setQuiz($quiz);
    $attempt->setScoreQuestions($scoreQuestions);
    $attempt->setScorePoints($scorePoints);
    $attempt->setTotalQuestions($total);
    $attempt->setStartedAt(new \DateTimeImmutable());
    $attempt->setFinishedAt(new \DateTimeImmutable());

    
    if ($this->getUser()) {
        $attempt->setUser($this->getUser());
    }

    $em->persist($attempt);
    $em->flush();

    
    $request->getSession()->set('coach_data_' . $attempt->getId(), $questionsData);

    
   return $this->render('front_quiz/quiz_result.html.twig', [
    'quiz'           => $quiz,
    'scoreQuestions' => $scoreQuestions,
    'scorePoints'    => $scorePoints,
    'maxPoints'      => $maxPoints,
    'total'          => $total,
    'attempt'        => $attempt,  
]);
}
}
