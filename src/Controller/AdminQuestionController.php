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
use App\Entity\QuestionChoix;
use App\Entity\QuestionVraiFaux;
use App\Entity\QuestionTexteLibre;

use App\Form\QuestionChoixType;
use App\Form\QuestionVraiFauxType;
use App\Form\QuestionTexteLibreType;
 

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
    $niveau = $request->query->get('niveau');
 

    $qb = $questionRepo->createQueryBuilder('q')
        ->leftJoin('q.quiz', 'quiz')
        ->addSelect('quiz');

    if ($search) {
    $qb->andWhere('
        q.texte LIKE :search 
        OR q.niveau LIKE :search
        OR q.indice LIKE :search
    ')
    ->setParameter('search', '%' . $search . '%');
 }

 


    $allowedSort = ['id', 'texte', 'niveau', 'indice'];


    if (!in_array($sort, $allowedSort)) {
        $sort = 'id';
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';


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

    
    

    
    #[Route('/admin/addformquestion/{type}', name: 'app_addformquestion')]
public function addFormQuestion(string $type, Request $req, ManagerRegistry $m): Response
{
    $em = $m->getManager();

    switch ($type) {
        case 'choix':
            $question = new QuestionChoix();
            $form = $this->createForm(QuestionChoixType::class, $question);
            break;

        case 'vraifaux':
            $question = new QuestionVraiFaux();
            $form = $this->createForm(QuestionVraiFauxType::class, $question);
            break;

        case 'texte':
            $question = new QuestionTexteLibre();
            $form = $this->createForm(QuestionTexteLibreType::class, $question);
            break;

        default:
            throw $this->createNotFoundException('Type invalide');
    }

    $form->handleRequest($req);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($question);
        $em->flush();

        return $this->redirectToRoute('app_showquestion');
    }

    return $this->render('admin_question/addformquestion.html.twig', [
        'f' => $form,
        'type' => $type
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

    if ($question instanceof QuestionChoix) {
        $form = $this->createForm(QuestionChoixType::class, $question);
    } elseif ($question instanceof QuestionVraiFaux) {
        $form = $this->createForm(QuestionVraiFauxType::class, $question);
    } elseif ($question instanceof QuestionTexteLibre) {
        $form = $this->createForm(QuestionTexteLibreType::class, $question);
    } else {
        throw new \Exception("Type inconnu");
    }

    $form->handleRequest($req);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();
        return $this->redirectToRoute('app_showquestion');
    }

    return $this->render('admin_question/updateformquestion.html.twig', [
        'f' => $form,
    ]);
}

}
