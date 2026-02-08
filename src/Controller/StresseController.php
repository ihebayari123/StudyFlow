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

    #[Route('/plan-education/{id}', name: 'app_plan_education')]
    public function planEducation(int $id, StressSurveyRepository $repo): Response
    {
        $survey = $repo->find($id);

        if (!$survey) {
            throw $this->createNotFoundException('Survey not found');
        }

        $sleepHours = $survey->getSleepHours();
        $studyHours = $survey->getStudyHours();

        // Generate education plan based on stress indicators
        $plan = [];

        // Sleep recommendations
        if ($sleepHours < 6) {
            $plan['sleep'] = [
                'status' => 'critical',
                'title' => 'Sommeil insuffisant',
                'recommendations' => [
                    'Établir une heure de coucher fixe et la respecter chaque jour',
                    'Éviter les écrans (téléphone, ordinateur) 1 heure avant le coucher',
                    'Créer une routine de détente (lecture, méditation, musique douce)',
                    'Limiter la caféine après 14h',
                    'Aim pour 7-9 heures de sommeil par nuit',
                ],
            ];
        } elseif ($sleepHours < 7) {
            $plan['sleep'] = [
                'status' => 'warning',
                'title' => 'Sommeil à améliorer',
                'recommendations' => [
                    'Essayer d\'ajouter 30-60 minutes de sommeil',
                    'Maintenir un environnement de sommeil calme et sombre',
                    'Éviter les repas lourds avant le coucher',
                ],
            ];
        } else {
            $plan['sleep'] = [
                'status' => 'good',
                'title' => 'Sommeil adéquat',
                'recommendations' => [
                    'Continuez à maintenir vos bonnes habitudes de sommeil',
                    'Utilisez votre énergie pour des activités productives',
                ],
            ];
        }

        // Study recommendations
        if ($studyHours > 10) {
            $plan['study'] = [
                'status' => 'critical',
                'title' => 'Charge de travail élevée',
                'recommendations' => [
                    'Prendre des pauses régulières (technique Pomodoro: 25min travail, 5min pause)',
                    'Répartir la charge de travail sur plusieurs jours',
                    'Prioriser les tâches les plus importantes',
                    'Demander de l\'aide si nécessaire (tuteurs, camarades)',
                    'Prévoir du temps pour des activités relaxantes',
                ],
            ];
        } elseif ($studyHours > 8) {
            $plan['study'] = [
                'status' => 'warning',
                'title' => 'Charge de travail importante',
                'recommendations' => [
                    'Planifier des pauses actives toutes les heures',
                    'Alterner entre différentes matières pour éviter la fatigue',
                    'S\'assurer de prendre du temps pour soi',
                ],
            ];
        } else {
            $plan['study'] = [
                'status' => 'good',
                'title' => 'Charge de travail équilibrée',
                'recommendations' => [
                    'Maintenir un bon équilibre travail-repos',
                    'Utiliser le temps libre pour des activités enrichissantes',
                ],
            ];
        }

        // General wellness recommendations
        $plan['wellness'] = [
            'status' => 'info',
            'title' => 'Conseils généraux de bien-être',
            'recommendations' => [
                'Pratiquer une activité physique régulière (30 min par jour)',
                'Maintenir une alimentation équilibrée',
                'Prendre le temps de socialiser avec amis et famille',
                'Apprendre des techniques de gestion du stress (respiration, méditation)',
                'Ne pas hésiter à consulter un professionnel de santé si le stress persiste',
            ],
        ];

        return $this->render('stresse/plan_education.html.twig', [
            'survey' => $survey,
            'plan' => $plan,
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

    #[Route('/stresse/avance', name: 'app_stresse_avance')]
    public function stresseAvance(StressSurveyRepository $surveyRepo): Response
    {
        $surveys = $surveyRepo->findAll();
        
        // Analyse avancée des données de stress
        $advancedAnalysis = [
            'totalSurveys' => count($surveys),
            'highRiskUsers' => [],
            'stressTrends' => [],
            'recommendations' => [],
        ];
        
        // Identifier les utilisateurs à haut risque
        foreach ($surveys as $survey) {
            $riskScore = 0;
            $sleepHours = $survey->getSleepHours();
            $studyHours = $survey->getStudyHours();
            $wellBeingScore = $survey->getWellBeingScore();
            
            // Calcul du score de risque
            if ($sleepHours < 6) {
                $riskScore += 3;
            } elseif ($sleepHours < 7) {
                $riskScore += 1;
            }
            
            if ($studyHours > 10) {
                $riskScore += 3;
            } elseif ($studyHours > 8) {
                $riskScore += 1;
            }
            
            if ($wellBeingScore && $wellBeingScore->getScore() < 5) {
                $riskScore += 2;
            }
            
            if ($riskScore >= 5) {
                $advancedAnalysis['highRiskUsers'][] = [
                    'user' => $survey->getUser(),
                    'riskScore' => $riskScore,
                    'sleepHours' => $sleepHours,
                    'studyHours' => $studyHours,
                ];
            }
        }
        
        // Générer des recommandations personnalisées
        $highRiskCount = count($advancedAnalysis['highRiskUsers']);
        if ($highRiskCount > 0) {
            $advancedAnalysis['recommendations'][] = [
                'priority' => 'high',
                'message' => $highRiskCount . ' utilisateur(s) identifié(s) avec un niveau de stress élevé. Intervention recommandée.',
            ];
        }
        
        // Calculer les tendances
        $totalSleep = 0;
        $totalStudy = 0;
        $count = count($surveys);
        
        foreach ($surveys as $survey) {
            $totalSleep += $survey->getSleepHours();
            $totalStudy += $survey->getStudyHours();
        }
        
        $avgSleep = $count > 0 ? $totalSleep / $count : 0;
        $avgStudy = $count > 0 ? $totalStudy / $count : 0;
        
        $advancedAnalysis['stressTrends'] = [
            'averageSleep' => round($avgSleep, 2),
            'averageStudy' => round($avgStudy, 2),
            'sleepStatus' => $avgSleep < 7 ? 'insuffisant' : 'adéquat',
            'studyStatus' => $avgStudy > 8 ? 'élevé' : 'normal',
        ];
        
        // Recommandations basées sur les tendances
        if ($avgSleep < 7) {
            $advancedAnalysis['recommendations'][] = [
                'priority' => 'medium',
                'message' => 'La moyenne de sommeil est insuffisante (' . round($avgSleep, 1) . 'h). Organiser des ateliers sur l\'hygiène du sommeil.',
            ];
        }
        
        if ($avgStudy > 8) {
            $advancedAnalysis['recommendations'][] = [
                'priority' => 'medium',
                'message' => 'Charge d\'étude élevée en moyenne (' . round($avgStudy, 1) . 'h). Proposer des sessions de gestion du temps.',
            ];
        }
        
        return $this->render('stresse/stresse_avance.html.twig', [
            'analysis' => $advancedAnalysis,
        ]);
    }
}




