<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\StressSurveyRepository;
use App\Entity\StressSurvey;
use App\Form\StressSurveyType;

final class StresseController extends AbstractController
{
    #[Route('/stresse', name: 'app_stresse')]
    public function index(): Response
    {
        return $this->render('stresse/index.html.twig', [
            'controller_name' => 'StresseController',
        ]);
    }

 #[Route('/showstresse', name: 'app_showstresse')]
    public function showstresse(StressSurveyRepository $bookrepo): Response
    {
        $a = $bookrepo->findAll();
        return $this->render('stresse/showstresse.html.twig', [
            'liststresse' => $a,
        ]);
    }


#[Route('/delete_user/{id}', name: 'app_delete_user')]
    public function delete_user($id, ManagerRegistry $m, StressSurveyRepository $authorrepo): Response
    {
        $em = $m->getManager();
        $del = $authorrepo->find($id);
        $em->remove($del);
        $em->flush();
        return $this->redirectToRoute('app_showstresse');
    }

    #[Route('/add_stresse', name: 'app_add_stresse')]
    public function addStresse(ManagerRegistry $m, Request $request): Response
    {
        $em = $m->getManager();
        $stresse = new StressSurvey();
        $form = $this->createForm(StressSurveyType::class, $stresse);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($stresse);
            $em->flush();
            return $this->redirectToRoute('app_showstresse');
        }
        
        return $this->render('stresse/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/updateformstresse/{id}', name: 'app_updatestresse')]
    public function updateformstresse($id, Request $req, ManagerRegistry $m, StressSurveyRepository $authorrepo): Response
    {
        $em = $m->getManager();
        $author = $authorrepo->find($id);

        if (!$author) {
            throw $this->createNotFoundException('Sondage Stress non trouvé');
        }

        $form = $this->createForm(StressSurveyType::class, $author);
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_showstresse');
        }
        return $this->render('stresse/updateformstresse.html.twig', [
            'f' => $form,
        ]);
    }

    #[Route('/showstresse/sort/sleep', name: 'app_showstresse_sort_sleep')]
    public function showstresseSortBySleep(StressSurveyRepository $bookrepo): Response
    {
        $a = $bookrepo->findBy([], ['sleepHours' => 'DESC']);
        return $this->render('stresse/showstresse.html.twig', [
            'liststresse' => $a,
        ]);
    }

   

    #[Route('/showstresse/sort/user', name: 'app_showstresse_sort_user')]
    public function showstresseSortByUser(StressSurveyRepository $bookrepo): Response
    {
        $a = $bookrepo->findBy([], ['user' => 'ASC']);
        return $this->render('stresse/showstresse.html.twig', [
            'liststresse' => $a,
        ]);
    }

    #[Route('/showstresse/sort/date', name: 'app_showstresse_sort_date')]
    public function showstresseSortByDate(StressSurveyRepository $bookrepo): Response
    {
        $a = $bookrepo->findBy([], ['date' => 'DESC']);
        return $this->render('stresse/showstresse.html.twig', [
            'liststresse' => $a,
        ]);
    }

    #[Route('/showstresse/recherche', name: 'app_showstresse_recherche')]
    public function rechercheStresse(Request $request, StressSurveyRepository $bookrepo): Response
    {
        $date = $request->query->get('date');
        $results = [];

        if ($date) {
            $results = $bookrepo->createQueryBuilder('s')
                ->where('s.date = :date')
                ->setParameter('date', new \DateTime($date))
                ->getQuery()
                ->getResult();
        }

        return $this->render('stresse/recherche.html.twig', [
            'results' => $results,
            'date' => $date,
        ]);
    }






















    
}




