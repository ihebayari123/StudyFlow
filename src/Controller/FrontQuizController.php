<?php

namespace App\Controller;

use App\Repository\QuizRepository;
use App\Repository\ChapitreRepository;
use App\Repository\TentativeQuizRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Quiz;
use App\Entity\Question;
use App\Entity\TentativeQuiz;
use App\Entity\ReponseUtilisateur;
use App\Entity\Chapitre;
use App\Repository\UtilisateurRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;


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
}
