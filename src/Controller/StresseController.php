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


    
    #[Route('/stresse/statistiques', name: 'app_stresse_statistiques')]
    public function statistiques(StressSurveyRepository $surveyRepo): Response
    {
        $surveys = $surveyRepo->findAll();
        
        // Statistiques par heures d'étude
        $studyHoursStats = [
            '0-2' => ['count' => 0, 'avgScore' => 0, 'scores' => []],
            '3-5' => ['count' => 0, 'avgScore' => 0, 'scores' => []],
            '6-8' => ['count' => 0, 'avgScore' => 0, 'scores' => []],
            '9-12' => ['count' => 0, 'avgScore' => 0, 'scores' => []],
            '13+' => ['count' => 0, 'avgScore' => 0, 'scores' => []],
        ];
        
        // Statistiques générales
        $totalSurveys = count($surveys);
        $totalStudyHours = 0;
        $totalSleepHours = 0;
        $totalScore = 0;
        $scoreCount = 0;
        
        // Données pour les graphiques
        $chartLabels = [];
        $chartData = [];
        $chartColors = [];
        
        foreach ($surveys as $survey) {
            $studyHours = $survey->getStudyHours();
            $sleepHours = $survey->getSleepHours();
            $wellBeingScore = $survey->getWellBeingScore();
            
            $totalStudyHours += $studyHours;
            $totalSleepHours += $sleepHours;
            
            if ($wellBeingScore) {
                $score = $wellBeingScore->getScore();
                $totalScore += $score;
                $scoreCount++;
                
                // Classification par heures d'étude
                if ($studyHours <= 2) {
                    $studyHoursStats['0-2']['count']++;
                    $studyHoursStats['0-2']['scores'][] = $score;
                } elseif ($studyHours <= 5) {
                    $studyHoursStats['3-5']['count']++;
                    $studyHoursStats['3-5']['scores'][] = $score;
                } elseif ($studyHours <= 8) {
                    $studyHoursStats['6-8']['count']++;
                    $studyHoursStats['6-8']['scores'][] = $score;
                } elseif ($studyHours <= 12) {
                    $studyHoursStats['9-12']['count']++;
                    $studyHoursStats['9-12']['scores'][] = $score;
                } else {
                    $studyHoursStats['13+']['count']++;
                    $studyHoursStats['13+']['scores'][] = $score;
                }
            }
        }
        
        // Calculer les moyennes pour chaque tranche
        $colors = ['#4CAF50', '#8BC34A', '#FFC107', '#FF9800', '#F44336'];
        $i = 0;
        foreach ($studyHoursStats as $range => &$stats) {
            if ($stats['count'] > 0) {
                $stats['avgScore'] = round(array_sum($stats['scores']) / count($stats['scores']), 2);
                $chartLabels[] = $range . ' heures';
                $chartData[] = $stats['count'];
                $chartColors[] = $colors[$i];
            }
            $i++;
        }
        
        // Statistiques de corrélation
        $avgStudyHours = $totalSurveys > 0 ? round($totalStudyHours / $totalSurveys, 2) : 0;
        $avgSleepHours = $totalSurveys > 0 ? round($totalSleepHours / $totalSurveys, 2) : 0;
        $avgWellBeingScore = $scoreCount > 0 ? round($totalScore / $scoreCount, 2) : 0;
        
        return $this->render('stresse/statistiques.html.twig', [
            'studyHoursStats' => $studyHoursStats,
            'totalSurveys' => $totalSurveys,
            'avgStudyHours' => $avgStudyHours,
            'avgSleepHours' => $avgSleepHours,
            'avgWellBeingScore' => $avgWellBeingScore,
            'chartLabels' => json_encode($chartLabels),
            'chartData' => json_encode($chartData),
            'chartColors' => json_encode($chartColors),
        ]);
    }
}




