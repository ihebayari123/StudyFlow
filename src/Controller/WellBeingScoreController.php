<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\WellBeingScore;
use App\Entity\StressSurvey;
use App\Form\WellBeingScoreType;
use App\Repository\WellBeingScoreRepository;
use App\Repository\StressSurveyRepository;
final class WellBeingScoreController extends AbstractController

{
    #[Route('/well/being/score', name: 'app_well_being_score')]
    public function index(): Response
    {
        return $this->render('well_being_score/index.html.twig', [
            'controller_name' => 'WellBeingScoreController',
        ]);
    }



    #[Route('/showscore', name: 'app_showscore')]
    public function showscore(WellBeingScoreRepository $bookrepo): Response
    {
        $a = $bookrepo->findAll();
        return $this->render('well_being_score/showscore.html.twig', [
            'listwellbeingscore' => $a,
        ]);
    }

#[Route('/delete_score/{id}', name: 'app_delete_score')]
    public function delete_score($id, ManagerRegistry $m, WellBeingScoreRepository $authorrepo): Response
    {
        $em = $m->getManager();
        $del = $authorrepo->find($id);
        $em->remove($del);
        $em->flush();
        return $this->redirectToRoute('app_showscore');
    }

//#[Route('/add_well_being_score', name: 'app_add_well_being_score')]
    //public function addWellBeingScore(ManagerRegistry $m, Request $request): Response
    //{
        //$em = $m->getManager();
        //$wellBeingScore = new WellBeingScore();
        //$form = $this->createForm(WellBeingScoreType::class, $wellBeingScore);
        //$form->handleRequest($request);
        
        //if ($form->isSubmitted() && $form->isValid()) {
            //$em->persist($wellBeingScore);
            //$em->flush();
          //  return $this->redirectToRoute('app_showscore');
        //}
        //
        //return $this->render('well_being_score/addform.html.twig', [
      //      'form' => $form->createView(),
    /////    ]);
  //  }



    #[Route('/showscore/sort/score', name: 'app_showscore_sort_score')]
    public function showscoreSortByScore(WellBeingScoreRepository $repo): Response
    {
        $scores = $repo->findBy([], ['score' => 'ASC']);
        return $this->render('well_being_score/showscore.html.twig', [
            'listwellbeingscore' => $scores,
        ]);
    }

    #[Route('/showscore/recherche', name: 'app_showscore_recherche')]
    public function rechercheBySurveyId(Request $request, WellBeingScoreRepository $repo, StressSurveyRepository $stressRepo, ManagerRegistry $m): Response
    {
        $surveyId = $request->query->get('survey_id');
        $results = [];
        $calculatedScore = null;

        if ($surveyId) {
            // Chercher le sondage par ID
            $stressSurvey = $stressRepo->find($surveyId);
            
            if ($stressSurvey) {
                // Calculer le score à partir des heures de sommeil
                // Formule: (sleepHours / 8) * 100, max 100
                $sleepHours = $stressSurvey->getSleepHours();
                $calculatedScore = min(100, intval(($sleepHours / 8) * 100));
                
                // Chercher si un WellBeingScore existe déjà pour ce sondage
                $existingScore = $repo->findOneBy(['survey' => $stressSurvey]);
                
                $em = $m->getManager();
                
                if ($existingScore) {
                    // Mettre à jour le score existant
                    $existingScore->setScore($calculatedScore);
                    $em->flush();
                } else {
                    // Créer un nouveau WellBeingScore
                    $newScore = new WellBeingScore();
                    $newScore->setSurvey($stressSurvey);
                    $newScore->setScore($calculatedScore);
                    $newScore->setRecommendation('Recommandation basée sur ' . $sleepHours . 'h de sommeil');
                    $newScore->setActionPlan('Plan d\'action à définir');
                    $newScore->setComment('Score calculé automatiquement');
                    
                    $em->persist($newScore);
                    $em->flush();
                }
            }
            
            // Récupérer les résultats pour affichage
            $results = $repo->createQueryBuilder('w')
                ->join('w.survey', 's')
                ->where('s.id = :surveyId')
                ->setParameter('surveyId', $surveyId)
                ->getQuery()
                ->getResult();
        }

        return $this->render('well_being_score/rechercheserveyid.html.twig', [
            'results' => $results,
            'survey_id' => $surveyId,
            'calculated_score' => $calculatedScore,
        ]);
    }


    

    #[Route('/update_score/{id}', name: 'app_update_score', methods: ['GET', 'POST'])]
    public function updateScore($id, ManagerRegistry $m, Request $request, WellBeingScoreRepository $repo): Response
    {
        $em = $m->getManager();
        $wellBeingScore = $repo->find($id);

        // Vérifier si l'objet existe
        if (!$wellBeingScore) {
            throw $this->createNotFoundException('Le score avec l\'id '.$id.' n\'existe pas');
        }

        // Créer le formulaire avec les données existantes
        $form = $this->createForm(WellBeingScoreType::class, $wellBeingScore);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Pas besoin de persist() pour une mise à jour, juste flush()
            $em->flush();

            $this->addFlash('success', 'Score mis à jour avec succès !');
            return $this->redirectToRoute('app_showscore');
        }

        return $this->render('well_being_score/updateform.html.twig', [
            'form' => $form->createView(),
            'wellBeingScore' => $wellBeingScore,
        ]);
    }


















}
