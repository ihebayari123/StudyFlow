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
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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
        
        if (!$del) {
            throw $this->createNotFoundException('Sondage Stress non trouvé avec l\'ID: ' . $id);
        }
        
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
            return $this->redirectToRoute('app_studyflow_score');
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



   
    #[Route('/stresse/coach', name: 'app_stresse_coach')]
    public function coachStressManagement(Request $request): Response
    {
        // Paramètres par défaut (identiques à generateEmploiTempsAvance)
        $defaultMatiereParJour = 4;
        $defaultHeureParMatiere = 1.5;
        $defaultPause = 0.5;
        $defaultHeureDebut = 8;
        $defaultPeriode = 'semaine';
        
        // Récupération et validation des paramètres
        $matiereParJour = $this->validateInt($request->query->get('matiere_par_jour', $defaultMatiereParJour), 1, 8);
        $heureParMatiere = $this->validateFloat($request->query->get('heure_par_matiere', $defaultHeureParMatiere), 0.5, 4);
        $pause = $this->validateFloat($request->query->get('pause', $defaultPause), 0.25, 2);
        $heureDebut = $this->validateInt($request->query->get('heure_debut', $defaultHeureDebut), 6, 22);
        $periode = $request->query->get('periode', $defaultPeriode);
        $inclureWeekend = $request->query->getBoolean('inclure_weekend', false);
        $niveauStress = $request->query->getInt('niveau_stress', 5);
        
        // Récupérer les matières
        $matieresSelectionnees = $this->getMatieresFromRequest($request);
        
        // Générer l'emploi du temps
        $emploiTemps = $this->genererEmploiTempsAvance(
            $matieresSelectionnees,
            $matiereParJour,
            $heureParMatiere,
            $pause,
            $heureDebut,
            $periode,
            $inclureWeekend,
            $niveauStress
        );
        
        // Générer les notifications pour chaque cours
        $notifications = $this->genererNotificationsCours($emploiTemps);
        
        // Calculer les cours à venir
        $coursAVoir = $this->getCoursAVoir($emploiTemps);
        
        // Message motivant basé sur le niveau de stress
        $motivationalMessages = [
            1 => "🌟 Excellent ! Votre niveau de stress est très bas. Continuez comme ça !",
            2 => "💪 Très bien ! Vous gérez parfaitement votre stress.",
            3 => "👍 Bon travail ! Vous êtes sur la bonne voie.",
            4 => "💫 Bien joué ! Continuez à appliquer vos techniques de relaxation.",
            5 => "✨ Correct ! N'oubliez pas de prendre soin de vous.",
            6 => "💪 Vous pouvez le faire ! Respirez profondément et restez positif.",
            7 => "🌈 Ne vous inquiétez pas ! Chaque pas compte vers le calme.",
            8 => "🌻 C'est normal de se sentir stressed. Prenez du temps pour vous.",
            9 => "💙 Soyez gentil avec vous-même. Demandez de l'aide si nécessaire.",
            10 => "🌺 Votre bien-être est important. Consultez un professionnel si besoin."
        ];
        $messageMotivant = $motivationalMessages[$niveauStress] ?? $motivationalMessages[5];

        // Techniques de respiration pour gérer le stress
        $breathingTechniques = [
            [
                'name' => 'Respiration diaphragmatique',
                'description' => 'Une technique simple pour calmer le système nerveux',
                'steps' => [
                    'Asseyez-vous ou allongez-vous confortablement',
                    'Placez une main sur votre poitrine et l\'autre sur votre ventre',
                    'Inspirez lentement par le nez en gonflant votre ventre (pas votre poitrine)',
                    'Retenez votre respiration pendant 2-3 secondes',
                    'Expirez lentement par la bouche en vidant votre ventre',
                    'Répétez 5-10 fois'
                ],
                'duration' => '3-5 minutes',
                'icon' => 'lungs'
            ],
            [
                'name' => 'Technique 4-7-8',
                'description' => 'Méthode efficace pour réduire l\'anxiété avant un examen',
                'steps' => [
                    'Expirez complètement par la bouche',
                    'Inspirez par le nez en comptant jusqu\'à 4',
                    'Retenez votre respiration en comptant jusqu\'à 7',
                    'Expirez complètement par la bouche en comptant jusqu\'à 8',
                    'Répétez le cycle 3-4 fois'
                ],
                'duration' => '2-3 minutes',
                'icon' => 'clock'
            ],
            [
                'name' => 'Respiration carrée',
                'description' => 'Technique pour maintenir la concentration',
                'steps' => [
                    'Inspirez pendant 4 secondes',
                    'Retenez votre souffle pendant 4 secondes',
                    'Expirez pendant 4 secondes',
                    'Faites une pause de 4 secondes',
                    'Répétez 4-5 fois'
                ],
                'duration' => '2-3 minutes',
                'icon' => 'square'
            ]
        ];

        // Techniques de relaxation
        $relaxationTechniques = [
            [
                'name' => 'Relaxation musculaire progressive',
                'description' => 'Détendez chaque muscle de votre corps',
                'steps' => [
                    'Allongez-vous dans un endroit calme',
                    'Commencez par vos orteils: contractez-les 5 secondes, puis relâchez',
                    'Montez progressivement vers les mollets, cuisses, abdomen...',
                    '精神z le haut du corps: poitrine, bras, mains, cou, visage',
                    'Prenez conscience de la sensation de détente'
                ],
                'duration' => '10-15 minutes',
                'icon' => 'spa'
            ],
            [
                'name' => 'Visualisation positive',
                'description' => 'Créez mentalement un lieu de paix',
                'steps' => [
                    'Fermez les yeux et respirez profondément',
                    'Imaginez un lieu réconfortant (plage, forêt, montagne...)',
                    'Engagez tous vos sens: visuels, sons, odeurs, sensations',
                    'Restez dans ce lieu quelques minutes',
                    'Revenez doucement à la réalité en ouvrant les yeux'
                ],
                'duration' => '5-10 minutes',
                'icon' => 'sun'
            ],
            [
                'name' => 'Méditation de pleine conscience',
                'description' => 'Ancrez-vous dans le moment présent',
                'steps' => [
                    'Asseyez-vous confortablement, dos droit',
                    'Fermez les yeux et concentrez-vous sur votre respiration',
                    'Laissez les pensées venir et partir sans vous y attacher',
                    'Ramenez doucement votre attention sur votre souffle',
                    'Augmentez progressivement la durée'
                ],
                'duration' => '5-20 minutes',
                'icon' => 'lotus'
            ]
        ];

        // Organisation du temps
        $timeOrganization = [
            [
                'name' => 'Technique Pomodoro',
                'description' => 'Gérez votre temps d\'étude efficacement',
                'steps' => [
                    'Choisissez une tâche à accomplir',
                    'Travaillez pendant 25 minutes sans interruption',
                    'Faites une pause de 5 minutes',
                    'Après 4 cycles, prenez une pause plus longue (15-30 min)',
                    'Ajustez les durées selon votre concentration'
                ],
                'tips' => [
                    'Désactivez les notifications pendant les sessions',
                    'Utilisez un minuteur pour vous concentrer',
                    'Planifiez vos pauses à l\'avance'
                ],
                'icon' => 'tomato'
            ],
            [
                'name' => 'Matrice Eisenhower',
                'description' => 'Priorisez vos tâches par importance et urgence',
                'steps' => [
                    'Listez toutes vos tâches',
                    'Classez-les en 4 catégories:',
                    '- Urgent + Important: À faire immédiatement',
                    '- Important mais pas urgent: À planifier',
                    '- Urgent mais pas important: À déléguer',
                    '- Ni urgent ni important: À éliminer',
                    'Concentrez-vous sur le quadrant important/non-urgent'
                ],
                'tips' => [
                    'Réviser quotidiennement votre matrice',
                    'Prévoir du temps pour les tâches importantes',
                    'Dire non aux distractions'
                ],
                'icon' => 'matrix'
            ],
            [
                'name' => 'Planification de la semaine',
                'description' => 'Organisez votre semaine de manière équilibrée',
                'steps' => [
                    'Dimanche soir: listez vos objectifs de la semaine',
                    'Divisez les grandes tâches en sous-tâches',
                    'Allouez du temps pour chaque matière/sujet',
                    'Prévoyez des plages de révision régulières',
                    'Incluez du temps pour le repos et les activités',
                    'Laissez de la flexibilité pour l\'imprévu'
                ],
                'tips' => [
                    'Équilibrez études et repos (minimum 1h/jour)',
                    'Planifiez des moments de détente',
                    'Incluez une activité physique'
                ],
                'icon' => 'calendar'
            ]
        ];

        // Ateliers disponibles
        $workshops = [
            [
                'title' => 'Atelier: Respiration anti-stress',
                'description' => 'Apprenez les techniques de respiration pour calmer votre esprit avant les examens',
                'duration' => '30 minutes',
                'level' => 'Débutant',
                'icon' => 'air',
                'topics' => [
                    'Respiration diaphragmatique',
                    'Technique 4-7-8',
                    'Exercices pratiques'
                ]
            ],
            [
                'title' => 'Atelier: Gestion du temps étudiant',
                'description' => 'Maîtrisez votre temps entre cours, révisions et vie personnelle',
                'duration' => '45 minutes',
                'level' => 'Tous niveaux',
                'icon' => 'clock',
                'topics' => [
                    'Technique Pomodoro',
                    'Planification hebdomadaire',
                    'Gestion des deadlines'
                ]
            ],
            [
                'title' => 'Atelier: Relaxation et pleine conscience',
                'description' => 'Découvrez comment vous détendre efficacement pendant la période d\'examens',
                'duration' => '40 minutes',
                'level' => 'Débutant',
                'icon' => 'leaf',
                'topics' => [
                    'Relaxation musculaire',
                    'Méditation guidée',
                    'Visualisation'
                ]
            ],
            [
                'title' => 'Atelier: Préparation mentale aux examens',
                'description' => 'Construisez une mentalité gagnante pour vos évaluations',
                'duration' => '50 minutes',
                'level' => 'Intermédiaire',
                'icon' => 'brain',
                'topics' => [
                    'Gestion du trac',
                    'Confiance en soi',
                    'Techniques de concentration'
                ]
            ]
        ];

        // Conseils rapides du jour
        $dailyTips = [
            'Faites au moins 5 minutes de respiration le matin avant de commencer vos révisions',
            'Planifiez vos pauses: elles sont essentielles pour la mémorisation',
            'Évitez les comparaisons avec vos camarades: chacun a son rythme',
            'Un peu d\'exercice physique chaque jour réduit significativement le stress',
            'Dormez suffisamment: le sommeil consolide les apprentissages',
            'N\'hésitez pas à faire des pauses technologiques régulières',
            'Maintenez une alimentation équilibrée pour rester concentré',
            'Parlez de vos difficultés: vous n\'êtes pas seul'
        ];

        return $this->render('stresse/catch.html.twig', [
            'breathingTechniques' => $breathingTechniques,
            'relaxationTechniques' => $relaxationTechniques,
            'timeOrganization' => $timeOrganization,
            'workshops' => $workshops,
            'dailyTips' => $dailyTips,
            'coursAVoir' => $coursAVoir,
            'notifications' => $notifications,
            'parametres' => [
                'matiereParJour' => $matiereParJour,
                'heureParMatiere' => $heureParMatiere,
                'pause' => $pause,
                'heureDebut' => $heureDebut,
                'periode' => $periode,
                'inclureWeekend' => $inclureWeekend,
                'niveauStress' => $niveauStress
            ],
            'emploiTemps' => $emploiTemps,
            'matieres' => $matieresSelectionnees,
            'messageMotivant' => $messageMotivant
        ]);
    }
   
    #[Route('/stresse/gestion-temps', name: 'app_stresse_gestion_temps')]
    public function gestionTempsEtudiant(): Response
    {
        // =============================================
        // GESTION DU TEMPS ÉTUDIANT - MODULE AVANCÉ
        // =============================================
        
        // Techniques principales de gestion du temps
        $techniques = [
            [
                'id' => 'pomodoro',
                'nom' => 'Technique Pomodoro',
                'description' => 'Méthode de gestion du temps révolutionnaire développée par Francesco Cirillo',
                'icone' => '🍅',
                'categorie' => 'Concentration',
                'principe' => 'Alterner périodes de travail intense et pauses régulières pour maximiser la productivité',
                'etapes' => [
                    ['titre' => 'Préparation', 'description' => 'Choisissez une tâche spécifique à accomplir et éliminez toutes les distractions'],
                    ['titre' => 'Travail (25 min)', 'description' => 'Concentrez-vous entièrement sur la tâche sans interruption'],
                    ['titre' => 'Pause courte (5 min)', 'description' => 'Levez-vous, étirez-vous, prenez un verre d\'eau'],
                    ['titre' => 'Répétition', 'description' => 'Après 4 pomodoros, prenez une pause longue de 15-30 minutes']
                ],
                'avantages' => [
                    'Combat la procrastination en divisant le travail en petites tâches',
                    'Préviens la fatigue mentale grâce aux pauses régulières',
                    'Crée un sentiment d\'urgence qui booste la concentration',
                    'Permet une meilleure estimation du temps nécessaire par tâche'
                ],
                'conseils' => [
                    'Utilisez un minuteur spécifique pour ne pas être tenté de regarder l\'heure',
                    'Adjust le temps de travail selon votre capacité de concentration (15-50 min)',
                    'Durante les pauses, évitez les écrans et les réseaux sociaux',
                    'Notez les interruptions pour améliorer votre environnement de travail'
                ],
                'duree' => '25/5 min (cycle)',
                'niveau' => 'Débutant'
            ],
            [
                'id' => 'eisenhower',
                'nom' => 'Matrice Eisenhower',
                'description' => 'Outil de priorisation basé sur l\'urgence et l\'importance',
                'icone' => '📊',
                'categorie' => 'Priorisation',
                'principe' => 'Classer les tâches selon leur urgence et importance pour se concentrer sur l\'essentiel',
                'etapes' => [
                    ['titre' => 'Quadrant 1: Urgent + Important', 'description' => 'Crises, deadlines proches - À faire immédiatement'],
                    ['titre' => 'Quadrant 2: Important + Non Urgent', 'description' => 'Planification, prévention - À planifier'],
                    ['titre' => 'Quadrant 3: Urgent + Non Important', 'description' => 'Interruptions, certaines réunions - À déléguer'],
                    ['titre' => 'Quadrant 4: Non Urgent + Non Important', 'description' => 'Divertissements, procrastinations - À éliminer']
                ],
                'avantages' => [
                    'Aide à identifier les tâches vraiment importantes',
                    'Réduit le stress en traitant les tâches urgentes en premier',
                    'Permet de prendre du recul sur les vraies priorités',
                    'Encourage la réflexion stratégique plutôt que réactive'
                ],
                'conseils' => [
                    'Réviser votre matrice quotidiennement le matin',
                    'Passer 80% de votre temps sur le quadrant 2',
                    'Dire non aux tâches du quadrant 4',
                    'Déléguer le quadrant 3 quand possible'
                ],
                'duree' => '15-20 min par planification',
                'niveau' => 'Intermédiaire'
            ],
            [
                'id' => 'time-blocking',
                'nom' => 'Time Blocking',
                'description' => 'Méthode consistant à bloquer des plages horaires spécifiques pour chaque tâche',
                'icone' => '🗓️',
                'categorie' => 'Planification',
                'principe' => 'Allouer des créneaux horaires précis à des tâches ou types d\'activité précis',
                'etapes' => [
                    ['titre' => 'Analyse hebdomadaire', 'description' => 'Le dimanche, listez toutes vos tâches de la semaine'],
                    ['titre' => 'Estimation', 'description' => 'Estimez le temps nécessaire pour chaque tâche'],
                    ['titre' => 'Blocage', 'description' => 'Assignez des créneaux spécifiques dans votre agenda'],
                    ['titre' => 'Flexibilité', 'description' => 'Laissez 1-2 heures de flexibilité pour l\'imprévu']
                ],
                'avantages' => [
                    'Élimine la fatigue décisionnelle en prédéfinissant les tâches',
                    'Permet une visualisation claire de la semaine',
                    'Réduit les multitasking et augmente la concentration',
                    'Facilite le suivi de l\'avancement des objectifs'
                ],
                'conseils' => [
                    'Grouper les tâches similaires dans le même bloc',
                    'Inclure des buffers entre les blocs importants',
                    'Bloquer du temps pour les imprévus (10-20%)',
                    'Protéger vos heures de pointe pour le travail profond'
                ],
                'duree' => '1-2h par semaine',
                'niveau' => 'Avancé'
            ],
            [
                'id' => 'eat-that-frog',
                'nom' => 'Eat That Frog',
                'description' => 'Technique consistant à accomplir la tâche la plus difficile en premier',
                'icone' => '🐸',
                'categorie' => 'Productivité',
                'principe' => 'Commencer par la tâche la plus importante et difficile quand l\'énergie est maximale',
                'etapes' => [
                    ['titre' => 'Identification', 'description' => 'La veille, identifiez vos 3 tâches les plus importantes'],
                    ['titre' => 'Classement', 'description' => 'Classez-les de la plus difficile à la plus facile'],
                    ['titre' => 'Exécution matinale', 'description' => 'Accomplissez la première tâche avant toute autre activité'],
                    ['titre' => 'Continuation', 'description' => 'Passez à la tâche suivante une fois la première terminée']
                ],
                'avantages' => [
                    'Bénéfice du momentum matinal pour les tâches difficiles',
                    'Réduction significative du stress et de l\'anxiété',
                    'Sentiment d\'accomplissement qui motive pour la suite',
                    'Meilleure utilisation de l\'énergie matinale (généralement plus élevée)'
                ],
                'conseils' => [
                    'Ne pas vérifier ses emails ou messages avant d\'avoir mangé \'la grenouille\'',
                    'La \'grenouille\' doit prendre 60-90 minutes maximum',
                    'Si la tâche prend plus de 2h, la diviser en sous-tâches',
                    'Récompenser après avoir accompli la tâche difficile'
                ],
                'duree' => '1-2h le matin',
                'niveau' => 'Débutant'
            ],
            [
                'id' => 'abcde',
                'nom' => 'Méthode ABCDE',
                'description' => 'Système de priorisation alphabétique pour classer les tâches',
                'icone' => '🔤',
                'categorie' => 'Priorisation',
                'principe' => 'Classer chaque tâche par ordre d\'importance avec des conséquences claires',
                'etapes' => [
                    ['titre' => 'A - Vital', 'description' => 'Conséquences graves si non fait (must do today)'],
                    ['titre' => 'B - Important', 'description' => 'Conséquences légères si non fait (should do)'],
                    ['titre' => 'C - Neutre', 'description' => 'Conséquences nulles (nice to do)'],
                    ['titre' => 'D - Délégable', 'description' => 'Peut être fait par quelqu\'un d\'autre'],
                    ['titre' => 'E - Éliminable', 'description' => 'Pas nécessaire, supprimer de la liste']
                ],
                'avantages' => [
                    'Méthode simple et rapide à appliquer',
                    'Permet de se concentrer sur l\'essentiel',
                    'Élimine les tâches non essentielles',
                    'Crée une vision claire des priorités'
                ],
                'conseils' => [
                    'Ne jamais avoir plus de 3-5 tâches de catégorie A',
                    'Une tâche A non faite devient automatiquement A+ pour le lendemain',
                    'Les tâches C sont souvent des distracteurs',
                    'Revoir quotidiennement les catégories'
                ],
                'duree' => '10 min par jour',
                'niveau' => 'Débutant'
            ],
            [
                'id' => 'bullet-journal',
                'nom' => 'Bullet Journal Académique',
                'description' => 'Système de suivi personnalisé pour organiser sa vie étudiante',
                'icone' => '📓',
                'categorie' => 'Organisation',
                'principe' => 'Combiner journal, planner et to-do list dans un système analogique flexible',
                'etapes' => [
                    ['titre' => 'Index', 'description' => 'Créer un index pour organiser les sections'],
                    ['titre' => 'Future Log', 'description' => 'Planifier les événements et deadlines à long terme'],
                    ['titre' => 'Monthly Log', 'description' => 'Vue d\'ensemble du mois avec objectifs'],
                    ['titre' => 'Daily Log', 'description' => 'Tâches quotidiennes avec notation rapide']
                ],
                'avantages' => [
                    'Totalement personnalisable selon vos besoins',
                    'Réduit le stress par une vue d\'ensemble claire',
                    'Encourage la réflexion sur vos objectifs',
                    'Améliore la mémoire et la prise de conscience'
                ],
                'conseils' => [
                    'Commencer simplement, ajouter des éléments progressivement',
                    'Utiliser des symboles pour categorize les tâches',
                    'Faire des revues quotidiennes et hebdomadaires',
                    'Intégrer des collections pour chaque matière/sujet'
                ],
                'duree' => '15 min par jour',
                'niveau' => 'Avancé'
            ]
        ];

        // Modèles de planification hebdomadaire
        $modelesPlanning = [
            [
                'nom' => 'Modèle Étudiant Universitaire',
                'description' => 'Planning typique pour un étudiant avec cours, TD et travail personnel',
                'structure' => [
                    ['jour' => 'Lundi', 'matin' => 'Cours (9h-12h)', 'apres-midi' => 'Travail personnel (14h-17h)', 'soir' => 'Pause détente (18h-20h)', 'nuit' => 'Révision légère (20h-22h)'],
                    ['jour' => 'Mardi', 'matin' => 'TD/TP (9h-12h)', 'apres-midi' => 'Bibliothèque (14h-17h)', 'soir' => 'Sport/Loisirs', 'nuit' => '自由时间'],
                    ['jour' => 'Mercredi', 'matin' => 'Cours (9h-12h)', 'apres-midi' => 'Projet personnel (14h-17h)', 'soir' => 'Vie sociale', 'nuit' => 'Préparation lendemain'],
                    ['jour' => 'Jeudi', 'matin' => 'TD (9h-12h)', 'apres-midi' => 'Travail bibliothèque (14h-17h)', 'soir' => 'Sport', 'nuit' => 'Révision'],
                    ['jour' => 'Vendredi', 'matin' => 'Cours (9h-12h)', 'apres-midi' => 'Fin de semaine (14h-17h)', 'soir' => 'Sortie détente', 'nuit' => '自由时间'],
                    ['jour' => 'Samedi', 'matin' => 'Rattrtrapage/Travail intensif', 'apres-midi' => 'Travail intensif', 'soir' => 'Repos', 'nuit' => '自由时间'],
                    ['jour' => 'Dimanche', 'matin' => 'Repos', 'apres-midi' => 'Préparation semaine (15h-17h)', 'soir' => 'Repos', 'nuit' => 'Préparation lendemain']
                ],
                'conseils' => [
                    'Adapter selon votre emploi du temps de cours',
                    'Prévoir 2h de sport minimum par semaine',
                    'Maintenir 7-8h de sommeil',
                    'Ne pas travailler le soir après 22h'
                ]
            ],
            [
                'nom' => 'Modèle Periode d\'Examens',
                'description' => 'Planning intensif pour la révision des examens',
                'structure' => [
                    ['jour' => 'Lundi', 'matin' => 'Révision matinale (8h-12h)', 'apres-midi' => 'Pause + exercice (12h-14h)', 'soir' => 'Révision (14h-18h)', 'nuit' => 'Détente (18h-22h)'],
                    ['jour' => 'Mardi', 'matin' => 'Révision (8h-12h)', 'apres-midi' => 'Examen blanc', 'soir' => 'Correction', 'nuit' => '自由时间'],
                    ['jour' => 'Mercredi', 'matin' => 'Révision (8h-12h)', 'apres-midi' => 'Pause nature', 'soir' => 'Révision (14h-18h)', 'nuit' => '自由时间'],
                    ['jour' => 'Jeudi', 'matin' => 'Révision (8h-12h)', 'apres-midi' => 'Examen blanc', 'soir' => 'Correction', 'nuit' => '自由时间'],
                    ['jour' => 'Vendredi', 'matin' => 'Révision légère', 'apres-midi' => 'Repos total', 'soir' => '自由时间', 'nuit' => 'Repos'],
                    ['jour' => 'Samedi', 'matin' => '自由时间', 'apres-midi' => '自由时间', 'soir' => '自由时间', 'nuit' => 'Repos'],
                    ['jour' => 'Dimanche', 'matin' => '自由时间', 'apres-midi' => 'Préparation', 'soir' => 'Repos', 'nuit' => 'Repos']
                ],
                'conseils' => [
                    'Ne pas réviser plus de 6-7h par jour efficacement',
                    'Faire au moins 1 exercice par jour',
                    'Dormir 8h minimum la semaine d\'examens',
                    'Arrêter révision 24h avant l\'examen'
                ]
            ]
        ];

        // Outils recommandés
        $outils = [
            [
                'nom' => 'Google Calendar',
                'categorie' => 'Digital',
                'description' => 'Calendrier gratuit avec rappels et synchronisation',
                'icone' => '📅',
                'features' => ['Synchronisation multi-appareils', 'Rappels automatiques', 'Partage de calendriers']
            ],
            [
                'nom' => 'Notion',
                'categorie' => 'Digital',
                'description' => 'Application tout-en-un pour prise de notes et organisation',
                'icone' => '📝',
                'features' => ['Templates prédéfinis', 'Base de données', 'Collaboration']
            ],
            [
                'nom' => 'Todoist',
                'categorie' => 'Digital',
                'description' => 'Gestionnaire de tâches puissant et intuitif',
                'icone' => '✅',
                'features' => ['Projets hiérarchiques', 'Récurrences', 'Intégrations']
            ],
            [
                'nom' => 'Bullet Journal',
                'categorie' => 'Analogique',
                'description' => 'Journal papier pour organisation et mindfulness',
                'icone' => '📓',
                'features' => ['100% personnalisable', 'Pas de distractions', 'Gratuit']
            ],
            [
                'nom' => 'Pomodoro Timer',
                'categorie' => 'App',
                'description' => 'Minuteur spécifique pour technique Pomodoro',
                'icone' => '🍅',
                'features' => ['Sessions personnalisables', 'Statistiques', 'Sons']
            ]
        ];

        // Erreurs courantes à éviter
        $erreurs = [
            [
                'titre' => 'Multitasking',
                'description' => 'Tenter de faire plusieurs choses simultanément réduit la productivité de 40%',
                'solution' => 'Concentrez-vous sur une seule tâche à la fois'
            ],
            [
                'titre' => 'Pas de pause',
                'description' => 'Travailler sans repos mène à l\'épuisement et diminue la concentration',
                'solution' => 'Faites des pauses de 5-10 minutes toutes les heures'
            ],
            [
                'titre' => 'Procrastination',
                'description' => 'Reporter les tâches importantes crée du stress et降低了学习效率',
                'solution' => 'Utilisez la technique \'Eat That Frog\' pour les tâches difficiles'
            ],
            [
                'titre' => 'Perfectionnisme',
                'description' => 'Vouloir tout faire parfaitement prend trop de temps',
                'solution' => 'Appliquez la règle 80/20: 20% d\'effort pour 80% du résultat'
            ],
            [
                'titre' => 'Pas de planification',
                'description' => 'Commencer la journée sans objectif clair perte de temps',
                'solution' => 'Planifiez vos 3 priorités chaque soir pour le lendemain'
            ],
            [
                'titre' => 'Surcharge d\'information',
                'description' => 'Trop d\'outils et de méthodes peut submerger',
                'solution' => 'Maîtrisez une méthode avant d\'en ajouter une autre'
            ]
        ];

        // Conseils par profil étudiant
        $conseilsParProfil = [
            [
                'profil' => 'Étudiant en Sciences',
                'conseils' => [
                    'Allouer du temps pour les TP et laboratoires',
                    'Faire des exercices régulièrement (pas juste lire)',
                    'Travailler en groupe pour les problèmes difficiles',
                    'Commencer les révisions 3 semaines avant les exams'
                ]
            ],
            [
                'profil' => 'Étudiant en Lettres',
                'conseils' => [
                    'Prvoir du temps pour la lecture et la recherche documentaire',
                    'Apprendre à gérer les dissertations longues',
                    'Constituer des fiches de synthèse',
                    'Soumettre des brouillons à temps pour feedback'
                ]
            ],
            [
                'profil' => 'Étudiant en Langues',
                'conseils' => [
                    'Immersion quotidienne (podcasts, films, lectures)',
                    'Pratique orale régulière avec locuteurs natifs',
                    'Révision vocabulaire petite dose quotidienne',
                    'Tenir un journal dans la langue apprise'
                ]
            ],
            [
                'profil' => 'Étudiant en Prépa',
                'conseils' => [
                    'Prioriser le travail régulier au bachotage',
                    'Gérer les khôlles avec préparation hebdomadaire',
                    'Maintenir une activité physique pour déstresser',
                    'Ne pas négliger le sommeil malgré la charge'
                ]
            ]
        ];

        // Indicateurs de productivite
        $indicateurs = [
            [
                'nom' => 'Taux d\'achèvement',
                'description' => 'Pourcentage des tâches prevues effectivement réalisées',
                'cible' => '80%',
                'icone' => '📈'
            ],
            [
                'nom' => 'Temps de concentration',
                'description' => 'Durée moyenne de concentration sur une tâche',
                'cible' => '45-90 min',
                'icone' => '⏱️'
            ],
            [
                'nom' => 'Heures productives',
                'description' => 'Nombre d\'heures effectives de travail par jour',
                'cible' => '4-6h',
                'icone' => '⏰'
            ],
            [
                'nom' => 'Qualité du sommeil',
                'description' => 'Heures de sommeil et qualité de repos',
                'cible' => '7-8h',
                'icone' => '😴'
            ]
        ];

        return $this->render('stresse/Gestion.html.twig', [
            'techniques' => $techniques,
            'modelesPlanning' => $modelesPlanning,
            'outils' => $outils,
            'erreurs' => $erreurs,
            'conseilsParProfil' => $conseilsParProfil,
            'indicateurs' => $indicateurs,
        ]);
    }


    #[Route('/stresse/preparation examens', name: 'app_stresse_preparation_examens')]
    public function preparationExamens(): Response
    {
        // =============================================
        // PRÉPARATION MENTALE AUX EXAMENS
        // =============================================
        
        // Techniques de préparation mentale
        $techniquesPreparation = [
            [
                'id' => 'visualisation',
                'nom' => 'Visualisation du succès',
                'description' => 'Technique de mentalisation positive pour améliorer la confiance en soi',
                'icone' => '🎯',
                'etapes' => [
                    ['titre' => 'Relaxation initiale', 'description' => 'Asseyez-vous confortablement et fermez les yeux. Faites 3 profondes respirations.'],
                    ['titre' => 'Scène de succès', 'description' => 'Visualisez-vous entrant dans la salle d\'examen avec confiance. Imaginez-vous calme et préparé.'],
                    ['titre' => 'Pendant l\'examen', 'description' => 'Visualisez-vous répondant aux questions avec facilité. Sentez la satisfaction de trouver les réponses.'],
                    ['titre' => 'Fin de l\'examen', 'description' => 'Imaginez-vous rendant votre copie avec un sentiment d\'accomplissement.'],
                    ['titre' => 'Résultats positifs', 'description' => 'Visualisez-vous recevant vos résultats avec fierté. Ressentez cette joie.']
                ],
                'duree' => '10-15 minutes',
                'quand' => 'Le matin avant l\'examen ou le soir avant de dormir'
            ],
            [
                'id' => 'self-talk',
                'nom' => 'Dialogue intérieur positif',
                'description' => 'Technique de reprogrammation mentale pour transformer les pensées négatives',
                'icone' => '💬',
                'etapes' => [
                    ['titre' => 'Identification', 'description' => 'Identifiez vos pensées négatives récurrentes avant et pendant les examens.'],
                    ['titre' => 'Questionnement', 'description' => 'Demandez-vous: « Cette pensée est-elle réellement vraie ? » « Ai-je des preuves du contraire ? »'],
                    ['titre' => ' reformulation', 'description' => 'Transformez les pensées négatives en affirmations positives et réalistes.'],
                    ['titre' => 'Ancrage', 'description' => 'Créez une phrase positive personnelle à répéter avant l\'examen.'],
                    ['titre' => 'Pratique', 'description' => 'Répétez vos affirmations positives chaque jour pendant 2-3 semaines avant l\'examen.']
                ],
                'duree' => '5-10 minutes par jour',
                'quand' => 'Quotidien, especially les semaines précédant l\'examen',
                'exemples' => [
                    '« Je suis bien préparé et je vais réussir »',
                    '« Je reste calme et concentré »',
                    '« Je fais de mon mieux, c\'est tout ce qui compte »',
                    '« Les examens ne définissent pas ma valeur »',
                    '« J\'ai les compétences nécessaires pour réussir »'
                ]
            ],
            [
                'id' => 'pleine-conscience',
                'nom' => 'Pleine conscience avant l\'examen',
                'description' => 'Technique d\'ancrage pour gérer le stress et l\'anxiété le jour de l\'examen',
                'icone' => '🧘',
                'etapes' => [
                    ['titre' => 'Arrivée', 'description' => 'Arrivez tôt à l\'examen. Trouvez un moment pour vous recentrer.'],
                    ['titre' => 'Respiration 4-7-8', 'description' => 'Inspirez 4 secondes, retenez 7 secondes, expirez 8 secondes. Répétez 3 fois.'],
                    ['titre' => 'Ancrage sensoriel', 'description' => 'Identifiez 5 choses que vous voyez, 4 que vous touchez, 3 que vous entendez.'],
                    ['titre' => 'Acceptation', 'description' => 'Acceptez que vous êtes nerveux, c\'est normal. Accueillez cette sensation sans la combattre.'],
                    ['titre' => 'Regroupement', 'description' => 'Prenez 30 secondes pour vous visualiser entrant dans la salle et commençant calmement.']
                ],
                'duree' => '5 minutes',
                'quand' => 'Avant d\'entrer dans la salle d\'examen'
            ],
            [
                'id' => 'routine-jour-j',
                'nom' => 'Routine Jour J',
                'description' => 'Planifier sa journée pour optimiser ses performances le jour de l\'examen',
                'icone' => '📅',
                'etapes' => [
                    ['titre' => 'La veille', 'description' => 'Préparez votre sac, vérifiez les documents nécessaires. Ne revisez pas tard.'],
                    ['titre' => 'Le matin', 'description' => 'Réveil normal, évitez de vérifier vos notes dernière minute. Petit-déjeuner équilibrée.'],
                    ['titre' => 'Avant l\'examen', 'description' => 'Arrivez 15-20 minutes en avance. Utilisez les dernières minutes pour vous relaxer.'],
                    ['titre' => 'Pendant', 'description' => 'Lisez toutes les questions. Commencez par les plus simples. Gérez votre temps.'],
                    ['titre' => 'Après', 'description' => 'Ne ressortez pas immédiatement vos réponses. Permettez-vous de décompresser.']
                ],
                'duree' => 'Organisation préalable',
                'quand' => 'À mettre en place la veille et le jour de l\'examen'
            ],
            [
                'id' => 'gestion-panie',
                'nom' => 'Gestion de la panique',
                'description' => 'Techniques pour reprendre le contrôle lors d\'un blocage pendant l\'examen',
                'icone' => '🚨',
                'etapes' => [
                    ['titre' => 'Stop', 'description' => 'Arrêtez immédiatement d\'essayer de répondre. Posez votre stylo.'],
                    ['titre' => 'Respiration', 'description' => 'Faites 3 respirations profondes. Expirez plus longtemps que vous n\'inspirez.'],
                    ['titre' => 'Grounding', 'description' => 'Feel your feet on the floor. Notez 3 détails dans la salle pour vous ancrer.'],
                    ['titre' => 'Regroupement', 'description' => 'Rappelez-vous: vous êtes préparé. Regardez la question suivante plus facile.'],
                    ['titre' => 'Reprise', 'description' => 'Reprenez avec une question que vous savez faire. Le momentum reviendra.']
                ],
                'duree' => '1-2 minutes',
                'quand' => 'En cas de blocage pendant l\'examen'
            ]
        ];

        // Préparation pratique
        $preparationPratique = [
            [
                'categorie' => 'La veille',
                'actions' => [
                    'Préparer tout le matériel nécessaire (pièce d\'identité, stylos, calculatrice, etc.)',
                    'Vérifier l\'horaire et le lieu de l\'examen',
                    'Faire une révision légère (pas de bachotage)',
                    'Dîner équilibré et tôt',
                    'Prévoir son trajet le matin',
                    'Se coucher à une heure raisonnable (pas trop tard)'
                ]
            ],
            [
                'categorie' => 'Le jour J',
                'actions' => [
                    'Se lever à l\'heure habituelle',
                    'Prendre un petit-déjeuner équilibré', 
                    'S\'habiller confortablement mais correctement',
                    'Arriver 15-20 minutes en avance',
                    'Avoir une bouteille d\'eau et une collation légère',
                    'Éteindre son téléphone ou le mettre en mode avion'
                ]
            ],
            [
                'categorie' => 'Pendant l\'examen',
                'actions' => [
                    'Lire toutes les questions avant de commencer',
                    'Commencer par les questions les plus simples',
                    'Gérer son temps: allouer un temps par question',
                    'Ne pas s\'acharner sur une question difficile',
                    'Relire ses réponses avant de rendre la copie',
                    'Rester positif et confiant tout au long de l\'examen'
                ]
            ],
            [
                'categorie' => 'Après les examens',
                'actions' => [
                    'Ne pas comparer ses réponses avec les autres',
                    'Se permettre de décompresser',
                    'Faire une activité relaxante',
                    'Ne pas attendre les résultats avec anxiété',
                    'Analyser ce qui a bien fonctionné et améliorer',
                    'Se récompenser pour les efforts fournis'
                ]
            ]
        ];

        // Signes de stress et solutions
        $signesStress = [
            [
                'signe' => 'Cœur qui bat vite',
                'solution' => 'Technique de respiration: inspirez 4s, expirez 6-8s pour activer le système parasympathique'
            ],
            [
                'signe' => 'Pensées négatives',
                'solution' => 'Dialogue intérieur: notez 3 preuves que vous pouvez réussir cela'
            ],
            [
                'signe' => 'Trouble de la concentration',
                'solution' => 'Pause mentale: fermez les yeux 30 secondes et concentrez-vous sur votre souffle'
            ],
            [
                'signe' => 'Mains moites/tremblements',
                'solution' => 'Secouez vigoureusement vos mains pendant 10 secondes, puis serrez-les fort 5s'
            ],
            [
                'signe' => 'Sensation de vide',
                'solution' => 'Manger quelque chose de sucré (si permits): le cerveau a besoin de glucose'
            ],
            [
                'signe' => 'Envie de tout abandonner',
                'solution' => 'Rappelez-vous: « Je n\'ai qu\'à faire de mon mieux. C\'est assez. »'
            ]
        ];

        // Ressources complémentaires
        $ressources = [
            [
                'type' => 'Application',
                'nom' => 'Headspace',
                'description' => 'Méditation guidée pour la concentration et le sommeil',
                'icone' => '📱'
            ],
            [
                'type' => 'Application',
                'nom' => 'Calm',
                'description' => 'Exercices de relaxation et histoires pour dormir',
                'icone' => '🌙'
            ],
            [
                'type' => 'Livre',
                'nom' => '« Réussir ses examens »',
                'description' => 'Guide pratique pour gérer le stress scolaire',
                'icone' => '📚'
            ],
            [
                'type' => 'Vidéo',
                'nom' => 'Chaîne YouTube',
                'description' => 'Tutos sur les techniques de mémorisation',
                'icone' => '🎬'
            ]
        ];

        // Checklist personnalisée
        $checklist = [
            'categorie' => 'Checklist Examen',
            'items' => [
                ['item' => 'Pièce d\'identité', 'checked' => false],
                ['item' => 'Stylos de rechange (au moins 2)', 'checked' => false],
                ['item' => 'Gomme et taille-crayon', 'checked' => false],
                ['item' => 'Calculatrice (si permise)', 'checked' => false],
                ['item' => 'Règle et compas', 'checked' => false],
                ['item' => 'Horloge/ Montre', 'checked' => false],
                ['item' => 'Eau (en bouteille transparente)', 'checked' => false],
                ['item' => 'Snack énergétique', 'checked' => false],
                ['item' => 'Tissu/ Mouchoirs', 'checked' => false],
                ['item' => 'Montre pour gérer le temps', 'checked' => false]
            ]
        ];

        return $this->render('stresse/preparationetudiant.html.twig', [
            'techniquesPreparation' => $techniquesPreparation,
            'preparationPratique' => $preparationPratique,
            'signesStress' => $signesStress,
            'ressources' => $ressources,
            'checklist' => $checklist,
        ]);
    }


    #[Route('/study_score', name: 'app_studyflow_score')]
    public function studyflowscore(Request $request): Response
    {
        // Initialiser les variables
        $score = null;
        $scoreClass = '';
        $resultTitle = '';
        $resultMessage = '';
        $recommendations = [];
        $sleepScore = 0;
        $studyScore = 0;

        // Traiter le formulaire si soumis
        if ($request->isMethod('POST')) {
            $sleepHours = floatval($request->request->get('sleepHours', 0));
            $studyHours = floatval($request->request->get('studyHours', 0));

            // Calcul du score de sommeil (0-50 points)
            // 7-9 heures = score parfait (50 points)
            // Moins de 7h ou plus de 9h = pénalité progressive
            if ($sleepHours >= 7 && $sleepHours <= 9) {
                $sleepScore = 50;
            } elseif ($sleepHours < 7) {
                // Pénalité de 10 points par heure manquante sous 7h
                $sleepScore = max(0, 50 - (7 - $sleepHours) * 10);
            } else {
                // Pénalité de 5 points par heure au-dessus de 9h
                $sleepScore = max(0, 50 - ($sleepHours - 9) * 5);
            }

            // Calcul du score d'étude (0-50 points)
            // 4-6 heures = score parfait (50 points)
            // Plus d'heures = pénalité pour surcharge
            // Moins d'heures = pénalité légère
            if ($studyHours >= 4 && $studyHours <= 6) {
                $studyScore = 50;
            } elseif ($studyHours > 6) {
                // Pénalité de 8 points par heure au-dessus de 6h
                $studyScore = max(0, 50 - ($studyHours - 6) * 8);
            } else {
                // Pénalité de 5 points par heure sous 4h
                $studyScore = max(0, 50 - (4 - $studyHours) * 5);
            }

            // Score total sur 100
            $score = round($sleepScore + $studyScore);

            // Déterminer la classe et les messages en fonction du score
            if ($score >= 80) {
                $scoreClass = 'excellent';
                $resultTitle = '🌟 Excellent équilibre !';
                $resultMessage = 'Félicitations ! Votre équilibre sommeil/études est optimal. Vous êtes sur la bonne voie pour réussir tout en préservant votre santé.';
                $recommendations = [
                    'Continuez à maintenir vos habitudes de sommeil régulières',
                    'Votre charge d\'étude est bien équilibrée',
                    'Profitez de votre temps libre pour des activités enrichissantes',
                    'Partagez vos bonnes pratiques avec vos camarades'
                ];
            } elseif ($score >= 60) {
                $scoreClass = 'good';
                $resultTitle = '👍 Bon équilibre';
                $resultMessage = 'Votre équilibre est satisfaisant, mais il y a encore place pour l\'amélioration. Quelques ajustements pourraient vous aider à optimiser vos performances.';
                $recommendations = [];
                
                if ($sleepHours < 7) {
                    $recommendations[] = 'Essayez d\'ajouter ' . round(7 - $sleepHours, 1) . ' heure(s) de sommeil pour atteindre 7-9 heures recommandées';
                } elseif ($sleepHours > 9) {
                    $recommendations[] = 'Votre sommeil semble excessif. Essayez de vous lever un peu plus tôt pour plus de productivité';
                }
                
                if ($studyHours > 6) {
                    $recommendations[] = 'Réduisez progressivement vos heures d\'étude à 6h maximum pour éviter le burnout';
                } elseif ($studyHours < 4) {
                    $recommendations[] = 'Vous pourriez augmenter légèrement votre temps d\'étude pour de meilleurs résultats';
                }
                
                $recommendations[] = 'Prenez des pauses régulières pendant vos sessions d\'étude';
            } else {
                $scoreClass = 'warning';
                $resultTitle = '⚠️ Attention, déséquilibre détecté';
                $resultMessage = 'Votre équilibre actuel pourrait impacter votre santé et vos performances. Des changements sont recommandés pour améliorer votre bien-être.';
                $recommendations = [];
                
                if ($sleepHours < 6) {
                    $recommendations[] = 'URGENT : Votre sommeil est insuffisant. Visez au moins 7 heures par nuit';
                } elseif ($sleepHours < 7) {
                    $recommendations[] = 'Priorisez votre sommeil : ajoutez ' . round(7 - $sleepHours, 1) . ' heure(s) pour mieux récupérer';
                }
                
                if ($studyHours > 10) {
                    $recommendations[] = 'Votre charge d\'étude est excessive. Réduisez de ' . round($studyHours - 6, 1) . ' heure(s) pour éviter l\'épuisement';
                } elseif ($studyHours > 8) {
                    $recommendations[] = 'Diminuez votre temps d\'étude et intégrez plus de pauses';
                }
                
                $recommendations[] = 'Établissez une routine fixe pour le coucher et le réveil';
                $recommendations[] = 'Envisagez de parler à un conseiller si le stress persiste';
            }

            // Convertir les scores individuels en pourcentage pour les barres de progression
            $sleepScore = round(($sleepScore / 50) * 100);
            $studyScore = round(($studyScore / 50) * 100);
        }

        return $this->render('stresse/page2.html.twig', [
            'score' => $score,
            'scoreClass' => $scoreClass,
            'resultTitle' => $resultTitle,
            'resultMessage' => $resultMessage,
            'recommendations' => $recommendations,
            'sleepScore' => $sleepScore,
            'studyScore' => $studyScore,
            'sleepHours' => $sleepHours ?? null,
            'studyHours' => $studyHours ?? null,
        ]);
    }

    #[Route('/stresse/jeux', name: 'app_stresse_jeux')]
    public function jeuxAntiStress(): Response
    {
        // Statistiques des jeux pour suivi thérapeutique
        $gameStats = [
            'respiration' => [
                'title' => 'Respiration Guidée',
                'description' => 'Exercice de cohérence cardiaque pour réduire l\'anxiété',
                'duration' => '3-5 minutes',
                'benefits' => ['Réduit le cortisol', 'Calme le système nerveux', 'Améliore la concentration'],
                'difficulty' => 'Facile',
                'color' => '#4CAF50'
            ],
            'mandala' => [
                'title' => 'Mandala Thérapeutique',
                'description' => 'Coloriage artistique pour la méditation active',
                'duration' => '10-15 minutes',
                'benefits' => ['Stimule la créativité', 'Réduit les pensées intrusives', 'Effet apaisant'],
                'difficulty' => 'Moyen',
                'color' => '#9C27B0'
            ],
            'puzzle' => [
                'title' => 'Puzzle Zen',
                'description' => 'Jeu de logique relaxant pour détendre l\'esprit',
                'duration' => '5-10 minutes',
                'benefits' => ['Améliore la concentration', 'Réduit le stress', 'Exerce la patience'],
                'difficulty' => 'Moyen',
                'color' => '#2196F3'
            ],
            'memoire' => [
                'title' => 'Mémoire Positive',
                'description' => 'Cartes de gratitude pour cultiver la positivité',
                'duration' => '5-8 minutes',
                'benefits' => ['Renforce la résilience', 'Améliore l\'humeur', 'Développe la gratitude'],
                'difficulty' => 'Facile',
                'color' => '#FF9800'
            ],
            'bulles' => [
                'title' => 'Bulles Anti-Stress',
                'description' => 'Éclatez les bulles pour libérer la tension',
                'duration' => 'Libre',
                'benefits' => ['Libération immédiate', 'Sensoriel apaisant', 'Amusant'],
                'difficulty' => 'Très facile',
                'color' => '#00BCD4'
            ]
        ];

        // Conseils de psychiatre pour l'utilisation des jeux
        $psychiatristTips = [
            [
                'title' => 'Régularité avant intensité',
                'content' => 'Jouez 10 minutes par jour plutôt qu\'une heure une fois par semaine. La régularité renforce les effets thérapeutiques.'
            ],
            [
                'title' => 'Respiration avant tout',
                'content' => 'Commencez toujours par le jeu de respiration pour préparer votre esprit aux autres activités.'
            ],
            [
                'title' => 'Sans pression',
                'content' => 'Ces jeux ne sont pas des compétitions. Il n\'y a pas de score à battre, seulement du bien-être à cultiver.'
            ],
            [
                'title' => 'En cas de crise',
                'content' => 'Si vous ressentez une anxiété intense, concentrez-vous sur la respiration guidée et les bulles.'
            ]
        ];

        return $this->render('stresse/pag5.html.twig', [
            'gameStats' => $gameStats,
            'psychiatristTips' => $psychiatristTips,
            'pageTitle' => 'Espace Détente - Jeux Anti-Stress',
            'pageSubtitle' => 'Des jeux thérapeutiques conçus par des spécialistes pour vous aider à gérer le stress étudiant'
        ]);
    }

    #[Route('/stresse/codabar', name: 'app_stresse_codabar')]
    public function codabar(Request $request): Response
    {
        $showBarcode = false;
        $qrCodeUrl = '';

        if ($request->isMethod('POST')) {
            $showBarcode = true;
            // Generate URL for PDF download
            $qrCodeUrl = $this->generateUrl('app_stresse_download_pdf', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $this->render('stresse/codabar.html.twig', [
            'showBarcode' => $showBarcode,
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }

    #[Route('/stresse/download-pdf', name: 'app_stresse_download_pdf')]
    public function downloadPdf(): Response
    {
        // Medical contact data
        $medicalData = [
            'psychiatrists' => [
                ['name' => 'Dr. Amel Ben Ali', 'phone' => '+216 71 234 567', 'address' => 'Centre Urbain Nord, Tunis', 'specialty' => 'Psychiatrie Générale'],
                ['name' => 'Dr. Karim Trabelsi', 'phone' => '+216 71 345 678', 'address' => 'Avenue Habib Bourguiba, Tunis', 'specialty' => 'Psychiatrie de l\'Enfant'],
                ['name' => 'Dr. Fatma M\'rad', 'phone' => '+216 71 456 789', 'address' => 'La Marsa, Tunis', 'specialty' => 'Psychothérapie'],
                ['name' => 'Dr. Mohamed Salah', 'phone' => '+216 71 567 890', 'address' => 'Sidi Bou Said, Tunis', 'specialty' => 'Addictologie'],
            ],
            'pharmacies' => [
                ['name' => 'Pharmacie Centrale', 'phone' => '+216 71 111 222', 'address' => 'Avenue Mohamed V, Tunis', 'hours' => '24h/24'],
                ['name' => 'Pharmacie de Nuit', 'phone' => '+216 71 222 333', 'address' => 'Bab Bhar, Tunis', 'hours' => '24h/24'],
                ['name' => 'Pharmacie El Menzah', 'phone' => '+216 71 333 444', 'address' => 'El Menzah, Tunis', 'hours' => '8h-22h'],
                ['name' => 'Pharmacie Lafayette', 'phone' => '+216 71 444 555', 'address' => 'Lafayette, Tunis', 'hours' => '8h-22h'],
            ],
            'hospitals' => [
                ['name' => 'Hôpital Razi', 'phone' => '+216 71 555 666', 'address' => 'Route de la Manouba, Tunis', 'type' => 'Psychiatrique'],
                ['name' => 'CHU Charles Nicolle', 'phone' => '+216 71 666 777', 'address' => 'Boulevard 9 Avril, Tunis', 'type' => 'Public'],
                ['name' => 'Clinique La Corniche', 'phone' => '+216 71 777 888', 'address' => 'La Marsa, Tunis', 'type' => 'Privée'],
                ['name' => 'Hôpital Aziza Othmana', 'phone' => '+216 71 888 999', 'address' => 'Place de la Kasbah, Tunis', 'type' => 'Public'],
            ],
        ];

        // Generate HTML content for PDF
        $html = $this->renderView('stresse/medical_contacts_pdf.html.twig', [
            'medicalData' => $medicalData,
            'date' => new \DateTime(),
        ]);

        // Create PDF using DomPDF if available, otherwise use simple HTML response
        if (class_exists('Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $response = new Response($dompdf->output());
        } else {
            // Fallback: return HTML with print-friendly styles
            $response = new Response($html);
        }
        
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'StudyFlow_Contacts_Medicaux.pdf'
        ));

        return $response;
    }


    




































    #[Route('/stresse/emploie/avance', name: 'app_stresse_emploie_avance')]
public function generateEmploiTempsAvance(Request $request, ManagerRegistry $doctrine): Response
{
    // Paramètres par défaut optimisés
    $defaultMatiereParJour = 4;
    $defaultHeureParMatiere = 1.5;
    $defaultPause = 0.5;
    $defaultHeureDebut = 8;
    $defaultPeriode = 'semaine';
    
    // Récupération et validation des paramètres
    $matiereParJour = $this->validateInt($request->query->get('matiere_par_jour', $defaultMatiereParJour), 1, 8);
    $heureParMatiere = $this->validateFloat($request->query->get('heure_par_matiere', $defaultHeureParMatiere), 0.5, 4);
    $pause = $this->validateFloat($request->query->get('pause', $defaultPause), 0.25, 2);
    $heureDebut = $this->validateInt($request->query->get('heure_debut', $defaultHeureDebut), 6, 22);
    $periode = $request->query->get('periode', $defaultPeriode);
    $inclureWeekend = $request->query->getBoolean('inclure_weekend', false);
    $niveauStress = $request->query->getInt('niveau_stress', 5);
    
    // ✅ CORRECTION : Récupération sécurisée des matières
    $matieresSelectionnees = [];
    
    // Méthode 1 : Utiliser getAll() pour récupérer tous les paramètres 'matieres'
    $allParams = $request->query->all();
    
    if (isset($allParams['matieres'])) {
        $matieresParam = $allParams['matieres'];
        
        // Si c'est déjà un tableau
        if (is_array($matieresParam)) {
            $matieresSelectionnees = $matieresParam;
        } 
        // Si c'est une chaîne JSON
        elseif (is_string($matieresParam) && !empty($matieresParam)) {
            // Vérifier si c'est du JSON
            if (strpos($matieresParam, '[') === 0 || strpos($matieresParam, '{') === 0) {
                $decoded = json_decode($matieresParam, true);
                if (is_array($decoded)) {
                    $matieresSelectionnees = $decoded;
                }
            } else {
                // Si c'est une chaîne simple avec des virgules
                $matieresSelectionnees = array_map('trim', explode(',', $matieresParam));
            }
        }
    }
    
    // Méthode 2 : Alternative avec get() pour les formulaires avec name="matieres[]"
    if (empty($matieresSelectionnees)) {
        $matieresFromGet = $request->query->get('matieres');
        
        if (is_array($matieresFromGet)) {
            $matieresSelectionnees = $matieresFromGet;
        } elseif (is_string($matieresFromGet) && !empty($matieresFromGet)) {
            $matieresSelectionnees = [$matieresFromGet];
        }
    }
    
    // Si toujours vide, utiliser les valeurs par défaut
    if (empty($matieresSelectionnees)) {
        $matieresSelectionnees = [
            'Mathématiques', 'Physique', 'Chimie', 'SVT', 'Français', 
            'Anglais', 'Histoire', 'Géographie', 'Philosophie', 'Informatique'
        ];
    }
    
    // Filtrer les valeurs vides et nettoyer
    $matieresSelectionnees = array_values(array_filter(array_map('trim', $matieresSelectionnees), function($value) {
        return !empty($value);
    }));
    
    // Génération intelligente de l'emploi du temps
    $emploiTemps = $this->genererEmploiTempsAvance(
        $matieresSelectionnees,
        $matiereParJour,
        $heureParMatiere,
        $pause,
        $heureDebut,
        $periode,
        $inclureWeekend,
        $niveauStress
    );
    
    // Calcul des métriques
    $metriques = $this->calculerMetriquesEmploiTemps($emploiTemps);
    
    // Suggestions d'optimisation
    $suggestions = $this->genererSuggestionsOptimisation($metriques, $niveauStress);
    
    // Récupération des données StressSurvey pour analyse
    $stressSurveyRepo = $doctrine->getRepository(\App\Entity\StressSurvey::class);
    $recentSurveys = $stressSurveyRepo->findBy([], ['date' => 'DESC'], 5);
    
    return $this->render('stresse/emploie_avance.html.twig', [
        'emploiTemps' => $emploiTemps,
        'metriques' => $metriques,
        'suggestions' => $suggestions,
        'matieres' => $matieresSelectionnees,
        'jours' => $this->getJoursSemaine($inclureWeekend, $periode),
        'parametres' => [
            'matiereParJour' => $matiereParJour,
            'heureParMatiere' => $heureParMatiere,
            'pause' => $pause,
            'heureDebut' => $heureDebut,
            'periode' => $periode,
            'inclureWeekend' => $inclureWeekend,
            'niveauStress' => $niveauStress
        ],
        'recentSurveys' => $recentSurveys
    ]);
}

    /**
     * Agenda des cours avec notifications
     * Fonction liée à l'emploi du temps (generateEmploiTempsAvance)
     * Utilise les mêmes paramètres que la route /stresse/emploie/avance
     */
    #[Route('/stresse/agenda', name: 'app_stresse_agenda')]
    public function agenda(Request $request, ManagerRegistry $doctrine): Response
    {
        // Paramètres par défaut (identiques à generateEmploiTempsAvance)
        $defaultMatiereParJour = 4;
        $defaultHeureParMatiere = 1.5;
        $defaultPause = 0.5;
        $defaultHeureDebut = 8;
        $defaultPeriode = 'semaine';
        
        // Récupération et validation des paramètres (même logique que generateEmploiTempsAvance)
        $matiereParJour = $this->validateInt($request->query->get('matiere_par_jour', $defaultMatiereParJour), 1, 8);
        $heureParMatiere = $this->validateFloat($request->query->get('heure_par_matiere', $defaultHeureParMatiere), 0.5, 4);
        $pause = $this->validateFloat($request->query->get('pause', $defaultPause), 0.25, 2);
        $heureDebut = $this->validateInt($request->query->get('heure_debut', $defaultHeureDebut), 6, 22);
        $periode = $request->query->get('periode', $defaultPeriode);
        $inclureWeekend = $request->query->getBoolean('inclure_weekend', false);
        $niveauStress = $request->query->getInt('niveau_stress', 5);
        
        // Récupérer les matières (même logique que generateEmploiTempsAvance)
        $matieresSelectionnees = $this->getMatieresFromRequest($request);
        
        // Générer l'emploi du temps en utilisant la même fonction que generateEmploiTempsAvance
        $emploiTemps = $this->genererEmploiTempsAvance(
            $matieresSelectionnees,
            $matiereParJour,
            $heureParMatiere,
            $pause,
            $heureDebut,
            $periode,
            $inclureWeekend,
            $niveauStress
        );
        
        // Générer les notifications pour chaque cours
        $notifications = $this->genererNotificationsCours($emploiTemps);
        
        // Calculer les cours à venir
        $coursAVoir = $this->getCoursAVoir($emploiTemps);
        
        return $this->render('stresse/agenda.html.twig', [
            'emploiTemps' => $emploiTemps,
            'notifications' => $notifications,
            'coursAVoir' => $coursAVoir,
            'matieres' => $matieresSelectionnees,
            'jours' => $this->getJoursSemaine($inclureWeekend, $periode),
            'parametres' => [
                'matiereParJour' => $matiereParJour,
                'heureParMatiere' => $heureParMatiere,
                'pause' => $pause,
                'heureDebut' => $heureDebut,
                'periode' => $periode,
                'inclureWeekend' => $inclureWeekend,
                'niveauStress' => $niveauStress
            ]
        ]);
    }

    /**
     * Génère les notifications pour chaque cours
     */
    private function genererNotificationsCours(array $emploiTemps): array
    {
        $notifications = [];
        $aujourdhui = new \DateTime();
        $jourActuel = strftime('%A', $aujourdhui->getTimestamp());
        
        // Mapper les jours en français
        $joursMap = [
            'Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi',
            'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi', 'Sunday' => 'Dimanche'
        ];
        $jourActuel = $joursMap[$jourActuel] ?? $jourActuel;
        
        $numeroJour = (int)$aujourdhui->format('N'); // 1 (Lundi) à 7 (Dimanche)
        
        foreach ($emploiTemps as $jour => $cours) {
            foreach ($cours as $index => $coursDetail) {
                $notification = [
                    'id' => uniqid('notif_'),
                    'matiere' => $coursDetail['matiere'],
                    'jour' => $jour,
                    'heureDebut' => $coursDetail['heureDebut'],
                    'heureFin' => $coursDetail['heureFin'],
                    'plage' => $coursDetail['plage'] ?? 'matin',
                    'efficacite' => $coursDetail['efficacite'] ?? 'Normale',
                    'type' => 'info',
                    'message' => '',
                    'priorite' => 'normale'
                ];
                
                // Déterminer si c'est aujourd'hui
                $estAujourdhui = ($jour === $jourActuel);
                
                // Calculer l'urgence de la notification
                $heureCours = explode(':', $coursDetail['heureDebut']);
                $heureActuelle = (int)date('H');
                $minuteActuelle = (int)date('i');
                $heureDebutCours = (int)$heureCours[0];
                $minuteDebutCours = isset($heureCours[1]) ? (int)$heureCours[1] : 0;
                
                $minutesAvantCours = ($heureDebutCours * 60 + $minuteDebutCours) - ($heureActuelle * 60 + $minuteActuelle);
                
                // Définir le type de notification
                if ($estAujourdhui) {
                    if ($minutesAvantCours <= 0 && $minutesAvantCours >= -60) {
                        // Cours en cours
                        $notification['type'] = 'en_cours';
                        $notification['message'] = 'Cours de ' . $coursDetail['matiere'] . ' en cours maintenant!';
                        $notification['priorite'] = 'haute';
                    } elseif ($minutesAvantCours > 0 && $minutesAvantCours <= 15) {
                        // Cours commence dans moins de 15 minutes
                        $notification['type'] = 'imminent';
                        $notification['message'] = 'Cours de ' . $coursDetail['matiere'] . ' commence dans ' . $minutesAvantCours . ' minutes!';
                        $notification['priorite'] = 'haute';
                    } elseif ($minutesAvantCours > 15 && $minutesAvantCours <= 60) {
                        // Cours commence dans moins d'une heure
                        $notification['type'] = 'proche';
                        $notification['message'] = 'Cours de ' . $coursDetail['matiere'] . ' dans environ ' . $minutesAvantCours . ' minutes';
                        $notification['priorite'] = 'moyenne';
                    } else {
                        // Cours à venir aujourd'hui
                        $notification['type'] = 'avenir';
                        $notification['message'] = 'Cours de ' . $coursDetail['matiere'] . ' prévu à ' . $coursDetail['heureDebut'];
                        $notification['priorite'] = 'basse';
                    }
                } else {
                    // Cours d'un autre jour
                    $notification['type'] = 'avenir';
                    $notification['message'] = 'Cours de ' . $coursDetail['matiere'] . ' prévu ' . $jour . ' à ' . $coursDetail['heureDebut'];
                    $notification['priorite'] = 'basse';
                }
                
                // Ajouter des conseils selon la matière et le niveau d'efficacité
                $notification['conseils'] = $this->getConseilsMatiere($coursDetail['matiere'], $coursDetail['efficacite'] ?? 'Normale');
                
                $notifications[] = $notification;
            }
        }
        
        // Trier les notifications par priorité
        usort($notifications, function($a, $b) {
            $priorites = ['haute' => 0, 'moyenne' => 1, 'basse' => 2];
            return $priorites[$a['priorite']] - $priorites[$b['priorite']];
        });
        
        return $notifications;
    }

    /**
     * Récupère les matières depuis la requête
     */
    private function getMatieresFromRequest(Request $request): array
    {
        $matieresSelectionnees = [];
        $allParams = $request->query->all();
        
        if (isset($allParams['matieres'])) {
            $matieresParam = $allParams['matieres'];
            
            if (is_array($matieresParam)) {
                $matieresSelectionnees = $matieresParam;
            } elseif (is_string($matieresParam) && !empty($matieresParam)) {
                if (strpos($matieresParam, '[') === 0 || strpos($matieresParam, '{') === 0) {
                    $decoded = json_decode($matieresParam, true);
                    if (is_array($decoded)) {
                        $matieresSelectionnees = $decoded;
                    }
                } else {
                    $matieresSelectionnees = array_map('trim', explode(',', $matieresParam));
                }
            }
        }
        
        if (empty($matieresSelectionnees)) {
            $matieresFromGet = $request->query->get('matieres');
            if (is_array($matieresFromGet)) {
                $matieresSelectionnees = $matieresFromGet;
            } elseif (is_string($matieresFromGet) && !empty($matieresFromGet)) {
                $matieresSelectionnees = [$matieresFromGet];
            }
        }
        
        if (empty($matieresSelectionnees)) {
            $matieresSelectionnees = [
                'Mathématiques', 'Physique', 'Chimie', 'SVT', 'Français', 
                'Anglais', 'Histoire', 'Géographie', 'Philosophie', 'Informatique'
            ];
        }
        
        return array_values(array_filter(array_map('trim', $matieresSelectionnees), function($value) {
            return !empty($value);
        }));
    }

    /**
     * Retourne les cours à venir (les 10 prochains)
     */
    private function getCoursAVoir(array $emploiTemps): array
    {
        $coursAVoir = [];
        $aujourdhui = new \DateTime();
        $jourActuel = strftime('%A', $aujourdhui->getTimestamp());
        
        $joursMap = [
            'Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi',
            'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi', 'Sunday' => 'Dimanche'
        ];
        $jourActuel = $joursMap[$jourActuel] ?? $jourActuel;
        
        // Ordre des jours pour le tri
        $ordreJours = ['Lundi' => 1, 'Mardi' => 2, 'Mercredi' => 3, 'Jeudi' => 4, 'Vendredi' => 5, 'Samedi' => 6, 'Dimanche' => 7];
        
        $aujourdhuiNumero = (int)$aujourdhui->format('N');
        
        foreach ($emploiTemps as $jour => $cours) {
            foreach ($cours as $coursDetail) {
                $jourNumero = $ordreJours[$jour] ?? 8;
                $estAujourdhui = ($jour === $jourActuel);
                
                $coursAVoir[] = [
                    'jour' => $jour,
                    'jourNumero' => $jourNumero,
                    'estAujourdhui' => $estAujourdhui,
                    'matiere' => $coursDetail['matiere'],
                    'heureDebut' => $coursDetail['heureDebut'],
                    'heureFin' => $coursDetail['heureFin'],
                    'plage' => $coursDetail['plage'] ?? 'matin',
                    'efficacite' => $coursDetail['efficacite'] ?? 'Normale'
                ];
            }
        }
        
        // Trier par jour et heure
        usort($coursAVoir, function($a, $b) use ($aujourdhuiNumero) {
            // Si un cours est aujourd'hui, il passe en premier
            if ($a['estAujourdhui'] && !$b['estAujourdhui']) return -1;
            if (!$a['estAujourdhui'] && $b['estAujourdhui']) return 1;
            
            // Trier par jour
            if ($a['jourNumero'] !== $b['jourNumero']) {
                return $a['jourNumero'] - $b['jourNumero'];
            }
            
            // Trier par heure
            return strcmp($a['heureDebut'], $b['heureDebut']);
        });
        
        return array_slice($coursAVoir, 0, 10); // Retourner les 10 premiers
    }

    /**
     * Retourne des conseils pour une matière
     */
    private function getConseilsMatiere(string $matiere, string $efficacite): array
    {
        $conseilsParMatiere = [
            'Mathématiques' => [
                'Pratiquez régulièrement avec des exercices variés',
                'Ne négligez pas les bases avant d\'attaquer les problèmes complexes',
                'Faites des fiches de formules et théorèmes'
            ],
            'Physique' => [
                'Comprenez les concepts avant de mémoriser les formules',
                'Faites des schémas pour visualer les problèmes',
                'Reliez la théorie aux applications pratiques'
            ],
            'Chimie' => [
                'Apprenez le tableau périodique par cœur',
                'Pratiquez les équations chimiques régulièrement',
                'Faites des fiches de réactifs et produits'
            ],
            'SVT' => [
                'Utilisez des schémas pour comprendre les processus',
                'Reliez les concepts à des exemples concrets',
                'Faites des fiches de définitions'
            ],
            'Français' => [
                'Lisez régulièrement pour enrichir votre vocabulaire',
                'Pratiquez l\'écriture avec des exercices variés',
                'Analysez des textes de différents genres'
            ],
            'Anglais' => [
                'Écoutez des podcasts ou regarde des vidéos en anglais',
                'Pratiquez régulièrement la conversation',
                'Apprenez le vocabulaire en contexte'
            ],
            'Histoire' => [
                'Créez une frise chronologique pour situer les événements',
                'Comprenez les causes et conséquences des événements',
                'Faites des résumés de chaque période'
            ],
            'Géographie' => [
                'Utilisez des cartes pour visualiser les espaces',
                'Apprenez avec des exemples concrets',
                'Faites des fiches de vocabulaires géographiques'
            ],
            'Philosophie' => [
                'Lisez les textes des philosophes avec attention',
                'Entraînez-vous à la dissertation régulièrement',
                'Construisez des arguments logiques'
            ],
            'Informatique' => [
                'Pratiquez le code régulièrement',
                'Comprenez les concepts avant de coder',
                'Faites des projets pratiques'
            ]
        ];
        
        return $conseilsParMatiere[$matiere] ?? [
            'Révision régulière est clé',
            'Faites des résumé de cours',
            'Pratiquez avec des exercices'
        ];
    }

/**
 * Génère un emploi du temps avancé avec optimisation anti-stress
 */
private function genererEmploiTempsAvance(array $matieres, int $matiereParJour, float $heureParMatiere, float $pause, int $heureDebut, string $periode, bool $inclureWeekend, int $niveauStress): array
{
    $jours = $this->getJoursSemaine($inclureWeekend, $periode);
    $emploiTemps = [];
    $indexMatiere = 0;
    
    // Adapter les paramètres en fonction du niveau de stress
    $facteurStress = $this->calculerFacteurStress($niveauStress);
    $heureParMatiereAdaptee = $heureParMatiere * $facteurStress['duree'];
    $pauseAdaptee = $pause * $facteurStress['pause'];
    $matiereParJourAdapte = max(1, min(8, round($matiereParJour * $facteurStress['matieres'])));
    
    // Plages horaires optimales par moment de la journée
    $plagesOptimales = [
        'matin' => ['debut' => 8, 'fin' => 12, 'coefficient' => 1.2], // Meilleure concentration
        'apresMidi' => ['debut' => 14, 'fin' => 18, 'coefficient' => 1.0],
        'soir' => ['debut' => 18, 'fin' => 21, 'coefficient' => 0.7] // Moins efficace
    ];
    
    foreach ($jours as $jour) {
        $emploiTemps[$jour] = [];
        $heureCourante = $heureDebut;
        $matieresUtilisees = [];
        
        for ($i = 0; $i < $matiereParJourAdapte; $i++) {
            // Vérifier qu'on a des matières disponibles
            if (empty($matieres)) {
                break;
            }
            
            // Sélection intelligente de la matière
            $matiere = $this->selectionnerMatiereOptimale($matieres, $matieresUtilisees, $indexMatiere);
            $matieresUtilisees[] = $matiere;
            $indexMatiere++;
            
            // Adapter la durée selon la plage horaire
            $plage = $this->determinerPlageHoraire($heureCourante, $plagesOptimales);
            $dureeAdaptee = $heureParMatiereAdaptee * $plage['coefficient'];
            
            // Calcul des heures
            $heureDebutFormatted = $this->formatHeure($heureCourante);
            $heureCourante += $dureeAdaptee;
            $heureFinFormatted = $this->formatHeure($heureCourante);
            
            // Ajouter une pause sauf pour le dernier cours
            $pauseAvant = ($i < $matiereParJourAdapte - 1) ? $pauseAdaptee : 0;
            
            // Vérifier si on ne dépasse pas les limites raisonnables
            if ($heureCourante > 22) {
                break;
            }
            
            $emploiTemps[$jour][] = [
                'matiere' => $matiere,
                'heureDebut' => $heureDebutFormatted,
                'heureFin' => $heureFinFormatted,
                'duree' => round($dureeAdaptee, 1),
                'plage' => $plage['nom'],
                'efficacite' => round($plage['coefficient'] * 100) . '%'
            ];
            
            // Ajouter la pause
            if ($pauseAvant > 0) {
                $heureCourante += $pauseAvant;
            }
        }
    }
    
    return $emploiTemps;
}

/**
 * Sélectionne une matière optimale en évitant la répétition excessive
 */
private function selectionnerMatiereOptimale(array $matieres, array $utilisees, int $index): string
{
    // Si toutes les matières ont été utilisées récemment, on prend la suivante
    if (count($utilisees) >= count($matieres) / 2) {
        // On vide partiellement l'historique pour éviter la répétition
        $utilisees = array_slice($utilisees, -3);
    }
    
    // Filtrer les matières récemment utilisées
    $disponibles = array_diff($matieres, $utilisees);
    
    if (empty($disponibles)) {
        // Si toutes ont été utilisées, prendre la prochaine dans l'ordre
        return $matieres[$index % count($matieres)];
    }
    
    // Convertir en tableau indexé
    $disponibles = array_values($disponibles);
    
    // Prendre une matière aléatoire parmi les disponibles
    return $disponibles[array_rand($disponibles)];
}

/**
 * Détermine la plage horaire pour adapter la durée
 */
private function determinerPlageHoraire(float $heure, array $plages): array
{
    if ($heure >= $plages['matin']['debut'] && $heure < $plages['matin']['fin']) {
        return ['nom' => 'Matin', 'coefficient' => $plages['matin']['coefficient']];
    } elseif ($heure >= $plages['apresMidi']['debut'] && $heure < $plages['apresMidi']['fin']) {
        return ['nom' => 'Après-midi', 'coefficient' => $plages['apresMidi']['coefficient']];
    } else {
        return ['nom' => 'Soir', 'coefficient' => $plages['soir']['coefficient']];
    }
}

/**
 * Calcule le facteur d'adaptation basé sur le niveau de stress
 */
private function calculerFacteurStress(int $niveauStress): array
{
    // Facteurs par défaut (stress modéré)
    $facteurs = [
        'duree' => 1.0,
        'pause' => 1.0,
        'matieres' => 1.0
    ];
    
    if ($niveauStress <= 3) {
        // Stress faible: on peut travailler plus longtemps
        $facteurs['duree'] = 1.2;
        $facteurs['pause'] = 0.8;
        $facteurs['matieres'] = 1.1;
    } elseif ($niveauStress <= 6) {
        // Stress modéré: équilibre
        $facteurs['duree'] = 1.0;
        $facteurs['pause'] = 1.0;
        $facteurs['matieres'] = 1.0;
    } else {
        // Stress élevé: séances plus courtes, pauses plus longues
        $facteurs['duree'] = 0.7;
        $facteurs['pause'] = 1.5;
        $facteurs['matieres'] = 0.8;
    }
    
    return $facteurs;
}

/**
 * Calcule les métriques de l'emploi du temps
 */
private function calculerMetriquesEmploiTemps(array $emploiTemps): array
{
    $totalHeures = 0;
    $heuresParJour = [];
    $matieresCount = [];
    
    foreach ($emploiTemps as $jour => $seances) {
        $heuresJour = 0;
        
        foreach ($seances as $seance) {
            $duree = $seance['duree'] ?? 0;
            $heuresJour += $duree;
            $totalHeures += $duree;
            
            $matiere = $seance['matiere'];
            if (!isset($matieresCount[$matiere])) {
                $matieresCount[$matiere] = 0;
            }
            $matieresCount[$matiere] += $duree;
        }
        
        $heuresParJour[$jour] = round($heuresJour, 1);
    }
    
    // Calcul des moyennes
    $nbJours = count($heuresParJour);
    $moyenneParJour = $nbJours > 0 ? round($totalHeures / $nbJours, 1) : 0;
    
    // Trouver le jour le plus chargé et le moins chargé
    $jourPlusCharge = array_keys($heuresParJour, max($heuresParJour))[0] ?? 'N/A';
    $jourMoinsCharge = array_keys($heuresParJour, min($heuresParJour))[0] ?? 'N/A';
    
    // Trier les matières par temps passé
    arsort($matieresCount);
    $topMatieres = array_slice($matieresCount, 0, 3, true);
    
    // Évaluation de l'équilibre
    $equilibre = $this->evaluerEquilibreEmploiTemps($heuresParJour, $moyenneParJour);
    
    return [
        'total_heures' => round($totalHeures, 1),
        'moyenne_par_jour' => $moyenneParJour,
        'heures_par_jour' => $heuresParJour,
        'nb_jours' => $nbJours,
        'jour_plus_charge' => $jourPlusCharge,
        'jour_moins_charge' => $jourMoinsCharge,
        'top_matieres' => $topMatieres,
        'equilibre' => $equilibre,
        'score_sante' => $this->calculerScoreSante($heuresParJour, $moyenneParJour)
    ];
}

/**
 * Évalue l'équilibre de l'emploi du temps
 */
private function evaluerEquilibreEmploiTemps(array $heuresParJour, float $moyenne): string
{
    $ecarts = [];
    foreach ($heuresParJour as $heures) {
        $ecarts[] = abs($heures - $moyenne);
    }
    
    $ecartMoyen = count($ecarts) > 0 ? array_sum($ecarts) / count($ecarts) : 0;
    
    if ($ecartMoyen <= 1) {
        return 'Excellent équilibre';
    } elseif ($ecartMoyen <= 2) {
        return 'Bon équilibre';
    } elseif ($ecartMoyen <= 3) {
        return 'Équilibre moyen';
    } else {
        return 'Déséquilibré';
    }
}

/**
 * Calcule un score de santé de 0-100 pour l'emploi du temps
 */
private function calculerScoreSante(array $heuresParJour, float $moyenne): int
{
    $score = 100;
    
    // Pénalité pour les journées trop chargées (>8h)
    foreach ($heuresParJour as $heures) {
        if ($heures > 8) {
            $score -= ($heures - 8) * 5;
        }
    }
    
    // Pénalité pour moyenne trop élevée (>6h)
    if ($moyenne > 6) {
        $score -= ($moyenne - 6) * 10;
    }
    
    // Pénalité pour trop de variabilité
    $ecartType = $this->calculerEcartType($heuresParJour);
    if ($ecartType > 2) {
        $score -= ($ecartType - 2) * 5;
    }
    
    return max(0, min(100, round($score)));
}

/**
 * Calcule l'écart type des heures par jour
 */
private function calculerEcartType(array $heuresParJour): float
{
    $moyenne = array_sum($heuresParJour) / count($heuresParJour);
    $variance = 0;
    
    foreach ($heuresParJour as $heures) {
        $variance += pow($heures - $moyenne, 2);
    }
    
    $variance /= count($heuresParJour);
    return sqrt($variance);
}

/**
 * Génère des suggestions d'optimisation
 */
private function genererSuggestionsOptimisation(array $metriques, int $niveauStress): array
{
    $suggestions = [];
    
    // Suggestions basées sur la charge totale
    if ($metriques['total_heures'] > 40) {
        $suggestions[] = [
            'type' => 'warning',
            'titre' => 'Charge hebdomadaire élevée',
            'message' => 'Vous dépassez 40h de travail par semaine. Pensez à réduire pour éviter l\'épuisement.',
            'action' => 'Réduire de ' . round($metriques['total_heures'] - 35) . 'h'
        ];
    } elseif ($metriques['total_heures'] < 20) {
        $suggestions[] = [
            'type' => 'info',
            'titre' => 'Charge légère',
            'message' => 'Vous pourriez augmenter légèrement votre temps d\'étude pour de meilleurs résultats.',
            'action' => 'Ajouter 5-10h par semaine'
        ];
    }
    
    // Suggestions basées sur l'équilibre
    if ($metriques['equilibre'] === 'Déséquilibré') {
        $suggestions[] = [
            'type' => 'warning',
            'titre' => 'Répartition inégale',
            'message' => 'Votre charge est très variable selon les jours. Essayez de mieux répartir.',
            'action' => 'Uniformiser les journées'
        ];
    }
    
    // Suggestions basées sur le stress
    if ($niveauStress > 7) {
        $suggestions[] = [
            'type' => 'danger',
            'titre' => 'Stress élevé détecté',
            'message' => 'Votre niveau de stress est élevé. Intégrez plus de pauses et réduisez la charge.',
            'action' => 'Ajouter des pauses de 15min'
        ];
    }
    
    // Suggestion de pauses actives
    $suggestions[] = [
        'type' => 'success',
        'titre' => 'Pauses actives',
        'message' => 'Profitez des pauses pour faire des exercices de respiration ou une courte marche.',
        'action' => 'Voir les exercices'
    ];
    
    // Suggestion de révision
    $suggestions[] = [
        'type' => 'info',
        'titre' => 'Optimisation des révisions',
        'message' => 'Alternez les matières difficiles et faciles pour maintenir la motivation.',
        'action' => 'Réorganiser'
    ];
    
    return $suggestions;
}

/**
 * Récupère la liste des jours selon les paramètres
 */
private function getJoursSemaine(bool $inclureWeekend, string $periode): array
{
    $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
    
    if ($inclureWeekend) {
        $jours = array_merge($jours, ['Samedi', 'Dimanche']);
    }
    
    if ($periode === 'weekend') {
        $jours = ['Samedi', 'Dimanche'];
    } elseif ($periode === 'jour_unique') {
        $jours = ['Aujourd\'hui'];
    }
    
    return $jours;
}

/**
 * Formate une heure décimale en format HH:MM
 */
private function formatHeure(float $heure): string
{
    $heures = floor($heure);
    $minutes = round(($heure - $heures) * 60);
    
    return sprintf('%02d:%02d', $heures, $minutes);
}

/**
 * Valide un entier dans une plage donnée
 */
private function validateInt($value, int $min, int $max): int
{
    $int = intval($value);
    return max($min, min($max, $int));
}

/**
 * Valide un float dans une plage donnée
 */
private function validateFloat($value, float $min, float $max): float
{
    $float = floatval($value);
    return max($min, min($max, $float));
}
}