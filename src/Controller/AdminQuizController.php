<?php

namespace App\Controller;

use App\Repository\QuizRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Quiz;
use App\Entity\Question;
use App\Form\QuizType;
use App\Repository\UtilisateurRepository;
use App\Repository\QuestionRepository;


final class AdminQuizController extends AbstractController
{
   #[Route('/admin/showquiz', name: 'admin_show_quiz')]
public function index(Request $request, QuizRepository $repo): Response
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

    return $this->render('admin_quiz/showQuiz.html.twig', [
        'listQuiz' => $qb->getQuery()->getResult(),
        'sort' => $sort,
        'order' => $order,
    ]);
}


    #[Route('/deletequiz/{id}', name: 'app_deletequiz')]
    public function deletequiz($id, ManagerRegistry $m, QuizRepository $Quizrepo): Response
    {
        $em = $m->getManager();
        $del = $Quizrepo->find($id);
        //$del = $authorrepo->findBookbyAuthor($id);
        //var_dump($del) . die();
        $em->remove($del);
        $em->flush();
        return $this->redirectToRoute('admin_show_quiz');
    }

   

   
#[Route('/admin/addformquiz', name: 'app_addformquiz')]
public function addformquiz(
    Request $req,
    ManagerRegistry $m,
    UtilisateurRepository $userRepo
): Response {
    $em = $m->getManager();
    $quiz = new Quiz();

    $form = $this->createForm(QuizType::class, $quiz);
    $form->handleRequest($req);

    if ($form->isSubmitted() && $form->isValid()) {

        
        $quiz->setDateCreation(new \DateTime());

        
        $user = $userRepo->find(1); 
        $quiz->setUser($user);

        $em->persist($quiz);
        $em->flush();

        return $this->redirectToRoute('admin_show_quiz');
    }

    return $this->render('admin_quiz/addformquiz.html.twig', [
        'f' => $form,
    ]);
}

     #[Route('/admin/updateformquiz/{id}', name: 'app_updateformquiz')]
    public function updateformauthors($id, Request $req, ManagerRegistry $m, QuizRepository $Quizrep): Response
    {
        $em = $m->getManager();
        $quiz = $Quizrep->find($id);
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($quiz);
            $em->flush();
            return $this->redirectToRoute('admin_show_quiz');
        }
        return $this->render('admin_quiz/updateformquiz.html.twig', [
            'f' => $form,
        ]);
    }

    #[Route('/admin/quiz/{id}/questions', name: 'app_quiz_questions')]
public function showQuizQuestions(int $id, QuestionRepository $questionRepo, QuizRepository $quizRepo): Response
{
    $quiz = $quizRepo->find($id);
    if (!$quiz) {
        throw $this->createNotFoundException("Quiz introuvable");
    }

    $questions = $questionRepo->findBy(['quiz' => $quiz]);

    return $this->render('admin_question/showQuestion.html.twig', [
    'listQuestion' => $questions,
    'quizTitle' => $quiz->getTitre(),
    'sort' => 'id',          
    'order' => 'ASC',       
]);

}

}
