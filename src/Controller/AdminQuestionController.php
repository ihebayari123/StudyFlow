<?php

namespace App\Controller;

use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Question;
use App\Form\QuestionType; 

final class AdminQuestionController extends AbstractController
{
    #[Route('/admin/question', name: 'app_admin_question')]
    public function index(): Response
    {
        return $this->render('admin_question/index.html.twig', [
            'controller_name' => 'AdminQuestionController',
        ]);
    }

    
    #[Route('/admin/showquestion', name: 'app_showquestion')]
public function showQuestion(Request $request, QuestionRepository $questionRepo): Response
{
    $search = $request->query->get('q');           
    $sort   = $request->query->get('sort', 'id');  
    $order  = $request->query->get('order', 'ASC'); 

    $qb = $questionRepo->createQueryBuilder('q')
        ->leftJoin('q.quiz', 'quiz')
        ->addSelect('quiz');

    if ($search) {
        $qb->andWhere('q.texte LIKE :search OR q.choixA LIKE :search OR q.choixB LIKE :search OR q.choixC LIKE :search OR q.choixD LIKE :search')
           ->setParameter('search', '%' . $search . '%');
    }

    $allowedSort = ['id', 'texte', 'choixA', 'choixB', 'choixC', 'choixD', 'bonneReponse', 'indice'];
    if (!in_array($sort, $allowedSort)) {
        $sort = 'id';
    }

    $qb->orderBy('q.' . $sort, $order);

    return $this->render('admin_question/showQuestion.html.twig', [
        'listQuestion' => $qb->getQuery()->getResult(),
        'sort' => $sort,
        'order' => $order,
    ]);
}


    
    #[Route('/deletequestion/{id}', name: 'app_deletequestion')]
    public function deleteQuestion($id, ManagerRegistry $m, QuestionRepository $questionRepo): Response
    {
        $em = $m->getManager();
        $question = $questionRepo->find($id);
        if ($question) {
            $em->remove($question);
            $em->flush();
        }
        return $this->redirectToRoute('app_showquestion');
    }

    
    

    
    #[Route('/admin/addformquestion', name: 'app_addformquestion')]
    public function addFormQuestion(Request $req, ManagerRegistry $m): Response
    {
        $em = $m->getManager();
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($question);
            $em->flush();
            return $this->redirectToRoute('app_showquestion');
        }

        return $this->render('admin_question/addformquestion.html.twig', [
            'f' => $form,
        ]);
    }

    
    #[Route('/admin/updateformquestion/{id}', name: 'app_updateformquestion')]
    public function updateFormQuestion($id, Request $req, ManagerRegistry $m, QuestionRepository $questionRepo): Response
    {
        $em = $m->getManager();
        $question = $questionRepo->find($id);
        if (!$question) {
            throw $this->createNotFoundException('Question non trouvée');
        }

        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($question);
            $em->flush();
            return $this->redirectToRoute('app_showquestion');
        }

        return $this->render('admin_question/updateformquestion.html.twig', [
            'f' => $form,
        ]);
    }
}
