<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ConsultationRepository;
use App\Entity\Consultation;
use App\Repository\MedecinRepository;
use App\Repository\StressSurveyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\ConsultationType;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Dompdf\Dompdf;
use Dompdf\Options as DompdfOptions;


// src/Controller/DefaultController.php




// src/Controller/DefaultController.php


final class ConsultationController extends AbstractController
{
    #[Route('/consultation', name: 'app_consultation')]
    public function index(): Response
    {
        return $this->render('consultation/index.html.twig', [
            'controller_name' => 'ConsultationController',
        ]);
    }

    #[Route('/showconsultation', name: 'app_showconsultation')]
    public function showstconsultation(Request $request, ConsultationRepository $consultationRepo): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'ASC');

        $queryBuilder = $consultationRepo->createQueryBuilder('c')
            ->leftJoin('c.medecin', 'm');

        // Recherche par nom de médecin
        if ($search) {
            $queryBuilder
                ->andWhere('m.nom LIKE :search OR m.prenom LIKE :search OR c.motif LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Tri
        if ($sort === 'medecin') {
            $queryBuilder->orderBy('m.nom', $order);
        } elseif ($sort === 'date') {
            $queryBuilder->orderBy('c.date_de_consultation', $order);
        } elseif ($sort === 'motif') {
            $queryBuilder->orderBy('c.motif', $order);
        } elseif ($sort === 'genre') {
            $queryBuilder->orderBy('c.genre', $order);
        } elseif ($sort === 'niveau') {
            $queryBuilder->orderBy('c.niveau', $order);
        } else {
            $queryBuilder->orderBy('c.id', $order);
        }

        $consultations = $queryBuilder->getQuery()->getResult();

        return $this->render('consultation/showconsultation.html.twig', [
            'consultations' => $consultations,
            'search' => $search,
            'sort' => $sort,
            'order' => $order,
        ]);
    }

    #[Route('/addconsultation', name: 'app_add_consultation')]
    public function addConsultation(ManagerRegistry $m, Request $request): Response
    {
        $em = $m->getManager();
        $stresse = new Consultation();
        $form = $this->createForm(ConsultationType::class, $stresse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($stresse);
            $em->flush();
            return $this->redirectToRoute('app_studyflow_affiche');
        }
        
        return $this->render('consultation/addconsultation.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/delete_consultation/{id}', name: 'app_delete_consultation')]
    public function delete_consultation($id, ManagerRegistry $m, ConsultationRepository $consultationRepo): Response
    {
        $em = $m->getManager();
        $del = $consultationRepo->find($id);
        $em->remove($del);
        $em->flush();
        return $this->redirectToRoute('app_showconsultation');
    }

    #[Route('/updateformconsultation/{id}', name: 'app_updateconsultation')]
    public function updateformconsultation($id, Request $req, ManagerRegistry $m, ConsultationRepository $consultationRepo): Response
    {
        $em = $m->getManager();
        $author = $consultationRepo->find($id);

        if (!$author) {
            throw $this->createNotFoundException('Consultation non trouvée');
        }

        $form = $this->createForm(ConsultationType::class, $author);
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_showconsultation');
        }
        return $this->render('consultation/updateformconsultation.html.twig', [
            'f' => $form,
        ]);
    }

    #[Route('/statistiques', name: 'app_statistiques_consultation')]
    public function statistiques(ConsultationRepository $consultationRepo): Response
    {
        // Statistiques par motif
        $motifStats = $consultationRepo->createQueryBuilder('c')
            ->select('c.motif, COUNT(c.id) as total')
            ->groupBy('c.motif')
            ->getQuery()
            ->getResult();

        // Statistiques par genre
        $genreStats = $consultationRepo->createQueryBuilder('c')
            ->select('c.genre, COUNT(c.id) as total')
            ->groupBy('c.genre')
            ->getQuery()
            ->getResult();

        // Statistiques par niveau
        $niveauStats = $consultationRepo->createQueryBuilder('c')
            ->select('c.niveau, COUNT(c.id) as total')
            ->groupBy('c.niveau')
            ->getQuery()
            ->getResult();

        // Total des consultations
        $totalConsultations = $consultationRepo->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Consultations par mois (pour graphique)
        $consultationsParMois = $consultationRepo->createQueryBuilder('c')
            ->select('SUBSTRING(c.date_de_consultation, 1, 7) as mois, COUNT(c.id) as total')
            ->groupBy('mois')
            ->orderBy('mois', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('consultation/statistiques.html.twig', [
            'motifStats' => $motifStats,
            'genreStats' => $genreStats,
            'niveauStats' => $niveauStats,
            'totalConsultations' => $totalConsultations,
            'consultationsParMois' => $consultationsParMois,
        ]);
    }

    #[Route('/bilan/{id}', name: 'app_bilan_consultation')]
    public function bilanAnalyse(int $id, ConsultationRepository $consultationRepo): Response
    {
        $consultation = $consultationRepo->find($id);

        if (!$consultation) {
            throw $this->createNotFoundException('Consultation non trouvée');
        }

        $stressSurvey = $consultation->getStressSurvey();
        
        if (!$stressSurvey) {
            throw $this->createNotFoundException('Aucun sondage de stress associé à cette consultation');
        }

        // Récupérer les données du WellBeingScore
        $wellBeingScore = $stressSurvey->getWellBeingScore();
        $score = $wellBeingScore ? $wellBeingScore->getScore() : null;
        $sleepHours = $stressSurvey->getSleepHours();
        $studyHours = $stressSurvey->getStudyHours();

        // Générer le bilan d'analyse basé sur le score
        $bilan = [];
        $messageMotivation = '';

        if ($score !== null) {
            if ($score < 30) {
                $bilan['niveau_stress'] = 'Élevé';
                $bilan['etat'] = 'critique';
                $bilan['recommandations'] = [
                    'Prendre des pauses régulières pendant l\'étude',
                    'Dormir au moins 7-8 heures par nuit',
                    'Pratiquer des exercices de respiration ou méditation',
                    'Consulter un professionnel de santé si nécessaire',
                    'Allouer du temps pour des activités relaxantes',
                ];
                $messageMotivation = 'Nous croyons en votre capacité à surmonter cette période difficile. Chaque petit pas compte et vous êtes plus fort(e) que vous ne le pensez. Prenez soin de vous !';
            } elseif ($score < 60) {
                $bilan['niveau_stress'] = 'Modéré';
                $bilan['etat'] = 'attention';
                $bilan['recommandations'] = [
                    'Équilibrer le temps d\'étude et de repos',
                    'Maintenir une routine de sommeil régulière',
                    'Faire de l\'exercice physique régulièrement',
                    'Partager vos préoccupations avec des proches',
                ];
                $messageMotivation = 'Vous êtes sur la bonne voie ! Avec quelques ajustements, vous pouvez améliorer votre bien-être. Continuez à avancer pas à pas.';
            } else {
                $bilan['niveau_stress'] = 'Faible';
                $bilan['etat'] = 'bon';
                $bilan['recommandations'] = [
                    'Continuez à maintenir vos bonnes habitudes',
                    'Partagez vos techniques de gestion du stress avec d\'autres',
                    'Restez actif(ve) physiquement et socialement',
                ];
                $messageMotivation = 'Félicitations ! Votre équilibre est exemplaire. Continuez à prendre soin de vous et à inspirer les autres par votre positivité !';
            }
        } else {
            $bilan['niveau_stress'] = 'Non évalué';
            $bilan['etat'] = 'inconnu';
            $bilan['recommandations'] = ['Aucune donnée de score disponible'];
            $messageMotivation = 'Nous vous encourageons à compléter votre évaluation pour mieux comprendre votre état de bien-être.';
        }

        // Analyse du sommeil
        if ($sleepHours < 6) {
            $bilan['sommeil_status'] = 'Insuffisant';
            $bilan['sommeil_conseil'] = 'Votre sommeil est insuffisant. Essayez d\'ajouter 1-2 heures de sommeil pour améliorer votre concentration et votre santé.';
        } elseif ($sleepHours < 7) {
            $bilan['sommeil_status'] = 'À améliorer';
            $bilan['sommeil_conseil'] = 'Votre sommeil est acceptable mais pourrait être amélioré. Visez 7-8 heures pour un repos optimal.';
        } else {
            $bilan['sommeil_status'] = 'Bon';
            $bilan['sommeil_conseil'] = 'Excellent ! Votre durée de sommeil est idéale pour la récupération et la performance.';
        }

        // Analyse des heures d'étude
        if ($studyHours > 10) {
            $bilan['etude_status'] = 'Excessif';
            $bilan['etude_conseil'] = 'Votre charge d\'étude est très élevée. Pensez à prendre des pauses régulières pour éviter le burnout.';
        } elseif ($studyHours > 8) {
            $bilan['etude_status'] = 'Élevé';
            $bilan['etude_conseil'] = 'Vous travaillez beaucoup. Assurez-vous de prendre suffisamment de temps pour vous détendre.';
        } else {
            $bilan['etude_status'] = 'Équilibré';
            $bilan['etude_conseil'] = 'Votre charge d\'étude semble bien équilibrée. Continuez ainsi !';
        }

        return $this->render('consultation/bilan.html.twig', [
            'consultation' => $consultation,
            'stressSurvey' => $stressSurvey,
            'wellBeingScore' => $wellBeingScore,
            'score' => $score,
            'sleepHours' => $sleepHours,
            'studyHours' => $studyHours,
            'bilan' => $bilan,
            'messageMotivation' => $messageMotivation,
        ]);
    }

    #[Route('/bilan/download/{id}', name: 'app_bilan_download')]
    public function downloadBilan(int $id, ConsultationRepository $consultationRepo): Response
    {
        $consultation = $consultationRepo->find($id);

        if (!$consultation) {
            throw $this->createNotFoundException('Consultation non trouvée');
        }

        $stressSurvey = $consultation->getStressSurvey();
        
        if (!$stressSurvey) {
            throw $this->createNotFoundException('Aucun sondage de stress associé à cette consultation');
        }

        $wellBeingScore = $stressSurvey->getWellBeingScore();
        $score = $wellBeingScore ? $wellBeingScore->getScore() : null;
        $sleepHours = $stressSurvey->getSleepHours();
        $studyHours = $stressSurvey->getStudyHours();

        // Générer le contenu du fichier texte
        $content = "=====================================\n";
        $content .= "       BILAN D'ANALYSE ÉTUDIANT      \n";
        $content .= "=====================================\n\n";
        
        $content .= "Date de consultation: " . $consultation->getDateDeConsultation()->format('d/m/Y H:i') . "\n";
        $content .= "Motif: " . $consultation->getMotif() . "\n";
        $content .= "Genre: " . $consultation->getGenre() . "\n\n";
        
        $content .= "-------------------------------------\n";
        $content .= "         DONNÉES DU SONDAGE         \n";
        $content .= "-------------------------------------\n";
        $content .= "Heures de sommeil: " . $sleepHours . " heures\n";
        $content .= "Heures d'étude: " . $studyHours . " heures\n";
        if ($score !== null) {
            $content .= "Score de bien-être: " . $score . "/100\n";
        }
        $content .= "\n";

        // Déterminer le niveau de stress
        if ($score !== null) {
            if ($score < 30) {
                $niveauStress = 'Élevé';
                $recommandations = [
                    'Prendre des pauses régulières pendant l\'étude',
                    'Dormir au moins 7-8 heures par nuit',
                    'Pratiquer des exercices de respiration ou méditation',
                    'Consulter un professionnel de santé si nécessaire',
                ];
                $messageMotivation = 'Nous croyons en votre capacité à surmonter cette période difficile. Chaque petit pas compte!';
            } elseif ($score < 60) {
                $niveauStress = 'Modéré';
                $recommandations = [
                    'Équilibrer le temps d\'étude et de repos',
                    'Maintenir une routine de sommeil régulière',
                    'Faire de l\'exercice physique régulièrement',
                ];
                $messageMotivation = 'Vous êtes sur la bonne voie! Continuez à avancer pas à pas.';
            } else {
                $niveauStress = 'Faible';
                $recommandations = [
                    'Continuez à maintenir vos bonnes habitudes',
                    'Restez actif(ve) physiquement et socialement',
                ];
                $messageMotivation = 'Félicitations! Votre équilibre est exemplaire.';
            }

            $content .= "-------------------------------------\n";
            $content .= "         ANALYSE DU STRESS          \n";
            $content .= "-------------------------------------\n";
            $content .= "Niveau de stress: " . $niveauStress . "\n\n";
            
            $content .= "Recommandations:\n";
            foreach ($recommandations as $rec) {
                $content .= "- " . $rec . "\n";
            }
            $content .= "\n";
        }

        // Analyse sommeil
        if ($sleepHours < 6) {
            $sommeilStatus = 'Insuffisant';
            $sommeilConseil = 'Votre sommeil est insuffisant. Essayez d\'ajouter 1-2 heures.';
        } elseif ($sleepHours < 7) {
            $sommeilStatus = 'À améliorer';
            $sommeilConseil = 'Votre sommeil est acceptable mais pourrait être amélioré.';
        } else {
            $sommeilStatus = 'Bon';
            $sommeilConseil = 'Excellent! Votre durée de sommeil est idéale.';
        }

        $content .= "-------------------------------------\n";
        $content .= "         ANALYSE DU SOMMEIL         \n";
        $content .= "-------------------------------------\n";
        $content .= "Statut: " . $sommeilStatus . "\n";
        $content .= "Conseil: " . $sommeilConseil . "\n\n";

        // Analyse étude
        if ($studyHours > 10) {
            $etudeStatus = 'Excessif';
            $etudeConseil = 'Votre charge d\'étude est très élevée. Pensez à prendre des pauses.';
        } elseif ($studyHours > 8) {
            $etudeStatus = 'Élevé';
            $etudeConseil = 'Vous travaillez beaucoup. Assurez-vous de vous détendre.';
        } else {
            $etudeStatus = 'Équilibré';
            $etudeConseil = 'Votre charge d\'étude semble bien équilibrée.';
        }

        $content .= "-------------------------------------\n";
        $content .= "         ANALYSE DES ÉTUDES         \n";
        $content .= "-------------------------------------\n";
        $content .= "Statut: " . $etudeStatus . "\n";
        $content .= "Conseil: " . $etudeConseil . "\n\n";

        $content .= "-------------------------------------\n";
        $content .= "         MESSAGE DE MOTIVATION      \n";
        $content .= "-------------------------------------\n";
        $content .= isset($messageMotivation) ? $messageMotivation : 'Prenez soin de vous!';
        $content .= "\n\n";
        
        $content .= "=====================================\n";
        $content .= "  Document généré le " . date('d/m/Y à H:i') . "\n";
        $content .= "=====================================\n";

        // Créer la réponse avec le fichier
        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/plain');
        $response->headers->set('Content-Disposition', ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'bilan_consultation_' . $id . '.txt');

        return $response;
    }

    #[Route('/bilan/pdf/{id}', name: 'app_bilan_pdf')]
    public function downloadBilanPdf(int $id, ConsultationRepository $consultationRepo): Response
    {
        $consultation = $consultationRepo->find($id);

        if (!$consultation) {
            throw $this->createNotFoundException('Consultation non trouvée');
        }

        $stressSurvey = $consultation->getStressSurvey();
        
        if (!$stressSurvey) {
            throw $this->createNotFoundException('Aucun sondage de stress associé à cette consultation');
        }

        $wellBeingScore = $stressSurvey->getWellBeingScore();
        $score = $wellBeingScore ? $wellBeingScore->getScore() : null;
        $sleepHours = $stressSurvey->getSleepHours();
        $studyHours = $stressSurvey->getStudyHours();

        // Générer le bilan
        $bilan = [];
        $messageMotivation = '';

        if ($score !== null) {
            if ($score < 30) {
                $bilan['niveau_stress'] = 'Élevé';
                $bilan['etat'] = 'critique';
                $bilan['recommandations'] = [
                    'Prendre des pauses régulières pendant l\'étude',
                    'Dormir au moins 7-8 heures par nuit',
                    'Pratiquer des exercices de respiration ou méditation',
                    'Consulter un professionnel de santé si nécessaire',
                ];
                $messageMotivation = 'Nous croyons en votre capacité à surmonter cette période difficile. Chaque petit pas compte!';
            } elseif ($score < 60) {
                $bilan['niveau_stress'] = 'Modéré';
                $bilan['etat'] = 'attention';
                $bilan['recommandations'] = [
                    'Équilibrer le temps d\'étude et de repos',
                    'Maintenir une routine de sommeil régulière',
                    'Faire de l\'exercice physique régulièrement',
                ];
                $messageMotivation = 'Vous êtes sur la bonne voie! Continuez à avancer pas à pas.';
            } else {
                $bilan['niveau_stress'] = 'Faible';
                $bilan['etat'] = 'bon';
                $bilan['recommandations'] = [
                    'Continuez à maintenir vos bonnes habitudes',
                    'Restez actif(ve) physiquement et socialement',
                ];
                $messageMotivation = 'Félicitations! Votre équilibre est exemplaire.';
            }
        }

        // Analyse du sommeil
        if ($sleepHours < 6) {
            $bilan['sommeil_status'] = 'Insuffisant';
            $bilan['sommeil_conseil'] = 'Votre sommeil est insuffisant. Essayez d\'ajouter 1-2 heures.';
        } elseif ($sleepHours < 7) {
            $bilan['sommeil_status'] = 'À améliorer';
            $bilan['sommeil_conseil'] = 'Votre sommeil est acceptable mais pourrait être amélioré.';
        } else {
            $bilan['sommeil_status'] = 'Bon';
            $bilan['sommeil_conseil'] = 'Excellent! Votre durée de sommeil est idéale.';
        }

        // Analyse des heures d'étude
        if ($studyHours > 10) {
            $bilan['etude_status'] = 'Excessif';
            $bilan['etude_conseil'] = 'Votre charge d\'étude est très élevée. Pensez à prendre des pauses.';
        } elseif ($studyHours > 8) {
            $bilan['etude_status'] = 'Élevé';
            $bilan['etude_conseil'] = 'Vous travaillez beaucoup. Assurez-vous de vous détendre.';
        } else {
            $bilan['etude_status'] = 'Équilibré';
            $bilan['etude_conseil'] = 'Votre charge d\'étude semble bien équilibrée.';
        }

        // Configurer Dompdf
        $pdfOptions = new DompdfOptions();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($pdfOptions);

        // Générer le HTML pour le PDF
        $html = $this->renderView('consultation/bilan_pdf.html.twig', [
            'consultation' => $consultation,
            'stressSurvey' => $stressSurvey,
            'wellBeingScore' => $wellBeingScore,
            'score' => $score,
            'sleepHours' => $sleepHours,
            'studyHours' => $studyHours,
            'bilan' => $bilan,
            'messageMotivation' => $messageMotivation,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Générer le nom du fichier
        $filename = 'bilan_consultation_' . $id . '_' . date('Y-m-d') . '.pdf';

        // Retourner le PDF en téléchargement
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    #[Route('/exercice-respiration', name: 'app_exercice_respiration')]
    public function exerciceRespiration(): Response
    {
        return $this->render('consultation/exercice_respiration.html.twig');
    }

    #[Route('/study_flow', name: 'app_studyflow')]
    public function studyflow(ConsultationRepository $consultationRepo): Response
    {
        $a=$consultationRepo->findAll();
        
        
        return $this->render('consultation/page1.html.twig', [
            'listcours' => $a,
        ]);
    }





    
    #[Route('/study_affiche', name: 'app_studyflow_affiche')]
    public function studyflowaffiche(ConsultationRepository $consultationRepo): Response
    {
        $a=$consultationRepo->findAll();
        
        
        return $this->render('consultation/affiche.html.twig', [
            'listcours' => $a,
        ]);
    }
   

     #[Route('/study_conclusion', name: 'app_studyflow_conclusion')]
    public function studyflowconclusion(ConsultationRepository $consultationRepo): Response
    {
        $a=$consultationRepo->findAll();
        
        
        return $this->render('consultation/conclusion.html.twig', [
            'listcours' => $a,
        ]);
    }

    #[Route('/study_Fitness', name: 'app_studyflow_fitness')]
    public function studyflowfitness(ConsultationRepository $consultationRepo): Response
    {
        $a=$consultationRepo->findAll();
        
        
        return $this->render('consultation/fitness.html.twig', [
            'listcours' => $a,
        ]);
    }

    #[Route('/study_Nutrition', name: 'app_studyflow_nutrition')]
    public function studyflownutrition(): Response
    {
        // Aliments qui augmentent la force et la vitesse
        $strengthSpeedFoods = [
            [
                'name' => 'Poulet (blanc)',
                'emoji' => '🍗',
                'calories' => 165,
                'benefits' => ['Protéines musculaires', 'Récupération rapide', 'Force'],
                'description' => 'Riche en protéines maigres, essentiel pour la construction musculaire et la récupération après effort.'
            ],
            [
                'name' => 'Steak de bœuf',
                'emoji' => '🥩',
                'calories' => 250,
                'benefits' => ['Fer', 'Créatine naturelle', 'Protéines'],
                'description' => 'Excellente source de créatine naturelle et de fer pour l\'endurance et la force explosive.'
            ],
            [
                'name' => 'Saumon',
                'emoji' => '🐟',
                'calories' => 208,
                'benefits' => ['Oméga-3', 'Anti-inflammatoire', 'Récupération'],
                'description' => 'Les oméga-3 réduisent l\'inflammation musculaire et accélèrent la récupération.'
            ],
            [
                'name' => 'Œufs',
                'emoji' => '🥚',
                'calories' => 155,
                'benefits' => ['Protéines complètes', 'Leucine', 'Énergie durable'],
                'description' => 'Protéines de haute qualité avec tous les acides aminés essentiels pour la synthèse musculaire.'
            ],
            [
                'name' => 'Banane',
                'emoji' => '🍌',
                'calories' => 89,
                'benefits' => ['Potassium', 'Glucides rapides', 'Énergie'],
                'description' => 'Apport rapide en énergie et potassium pour prévenir les crampes pendant l\'effort.'
            ],
            [
                'name' => 'Avoine',
                'emoji' => '🌾',
                'calories' => 389,
                'benefits' => ['Glucides complexes', 'Énergie prolongée', 'Fibres'],
                'description' => 'Libération lente d\'énergie idéale pour les entraînements de longue durée.'
            ],
            [
                'name' => 'Patate douce',
                'emoji' => '🍠',
                'calories' => 86,
                'benefits' => ['Glucides complexes', 'Vitamine A', 'Récupération'],
                'description' => 'Recharge les stocks de glycogène musculaire après l\'effort intense.'
            ],
            [
                'name' => 'Épinards',
                'emoji' => '🥬',
                'calories' => 23,
                'benefits' => ['Nitrates', 'Endurance', 'Fer'],
                'description' => 'Les nitrates améliorent l\'efficacité musculaire et l\'endurance.'
            ],
            [
                'name' => 'Quinoa',
                'emoji' => '🍚',
                'calories' => 368,
                'benefits' => ['Protéines végétales', 'Acides aminés', 'Énergie'],
                'description' => 'Céréale complète riche en protéines pour une récupération optimale.'
            ],
            [
                'name' => 'Amandes',
                'emoji' => '🥜',
                'calories' => 579,
                'benefits' => ['Vitamine E', 'Magnésium', 'Énergie'],
                'description' => 'Source d\'énergie concentrée et de magnésium pour la contraction musculaire.'
            ],
            [
                'name' => 'Yaourt grec',
                'emoji' => '🥛',
                'calories' => 97,
                'benefits' => ['Protéines', 'Probiotiques', 'Récupération'],
                'description' => 'Double apport en protéines comparé au yaourt standard, idéal post-entraînement.'
            ],
            [
                'name' => 'Brocoli',
                'emoji' => '🥦',
                'calories' => 34,
                'benefits' => ['Vitamine C', 'Antioxydants', 'Anti-inflammatoire'],
                'description' => 'Combat le stress oxydatif et soutient la récupération musculaire.'
            ]
        ];

        // Aliments qui diminuent la force et la vitesse
        $decreaseFoods = [
            [
                'name' => 'Boissons énergisantes',
                'emoji' => '🥤',
                'calories' => 45,
                'negativeEffects' => ['Crash énergétique', 'Déshydratation', 'Tachycardie'],
                'description' => 'Le sucre et la caféine en excès causent un crash énergétique et déshydratent.'
            ],
            [
                'name' => 'Fast Food',
                'emoji' => '🍔',
                'calories' => 500,
                'negativeEffects' => ['Digestion lourde', 'Inflammation', 'Fatigue'],
                'description' => 'Riche en graisses trans et sodium, ralentit la digestion et cause de la fatigue.'
            ],
            [
                'name' => 'Bonbons & Sucreries',
                'emoji' => '🍬',
                'calories' => 400,
                'negativeEffects' => ['Pic d\'insuline', 'Crash glycémique', 'Zéro nutriment'],
                'description' => 'Pic de sucre suivi d\'un crash, aucun apport nutritionnel pour les muscles.'
            ],
            [
                'name' => 'Sodas',
                'emoji' => '🧃',
                'calories' => 140,
                'negativeEffects' => ['Déshydratation', 'Sucre vide', 'Osteoporose'],
                'description' => 'Le phosphore inhibe l\'absorption du calcium, nécessaire aux contractions musculaires.'
            ],
            [
                'name' => 'Alcool',
                'emoji' => '🍺',
                'calories' => 150,
                'negativeEffects' => ['Déshydratation', 'Récupération ralentie', 'Coordination'],
                'description' => 'Ralentit la récupération musculaire, déshydrate et affecte la coordination.'
            ],
            [
                'name' => 'Chips & Snacks salés',
                'emoji' => '🥔',
                'calories' => 536,
                'negativeEffects' => ['Sodium excessif', 'Rétention d\'eau', 'Graisses saturées'],
                'description' => 'Trop de sodium cause la rétention d\'eau et gonflement des muscles.'
            ],
            [
                'name' => 'Pâtisseries industrielles',
                'emoji' => '🧁',
                'calories' => 450,
                'negativeEffects' => ['Graisses trans', 'Sucre raffiné', 'Inflammation'],
                'description' => 'Provoquent l\'inflammation et réduisent la capacité de récupération musculaire.'
            ],
            [
                'name' => 'Nouilles instantanées',
                'emoji' => '🍜',
                'calories' => 350,
                'negativeEffects' => ['Sodium extrême', 'Additifs', 'Pauvre en nutriments'],
                'description' => 'Très riches en sodium et pauvres en nutriments essentiels pour la performance.'
            ]
        ];

        // Aliments qui augmentent le niveau de joie
        $joyFoods = [
            [
                'name' => 'Chocolat noir (70%+)',
                'emoji' => '🍫',
                'calories' => 546,
                'benefits' => ['Sérotonine', 'Endorphines', 'Antioxydants'],
                'description' => 'Stimule la production de sérotonine et d\'endorphines, les hormones du bonheur.'
            ],
            [
                'name' => 'Baies (myrtilles, fraises)',
                'emoji' => '🫐',
                'calories' => 57,
                'benefits' => ['Antioxydants', 'Vitamine C', 'Humeur positive'],
                'description' => 'Réduisent le stress oxydatif associé à l\'anxiété et améliorent l\'humeur.'
            ],
            [
                'name' => 'Noix et graines',
                'emoji' => '🌰',
                'calories' => 607,
                'benefits' => ['Oméga-3', 'Tryptophane', 'Cerveau sain'],
                'description' => 'Le tryptophane est un précurseur de la sérotonine, hormone de la bonne humeur.'
            ],
            [
                'name' => 'Saumon gras',
                'emoji' => '🐟',
                'calories' => 208,
                'benefits' => ['Oméga-3', 'Dopamine', 'Fonction cognitive'],
                'description' => 'Les oméga-3 soutiennent la production de dopamine, hormone de la récompense.'
            ],
            [
                'name' => 'Avocat',
                'emoji' => '🥑',
                'calories' => 160,
                'benefits' => ['Graisses saines', 'Vitamine B', 'Énergie cérébrale'],
                'description' => 'Les graisses saines nourrissent le cerveau et stabilisent l\'humeur.'
            ],
            [
                'name' => 'Thé vert',
                'emoji' => '🍵',
                'calories' => 2,
                'benefits' => ['L-théanine', 'Calme', 'Focus'],
                'description' => 'La L-théanine promeut la relaxation sans somnolence et réduit l\'anxiété.'
            ],
            [
                'name' => 'Miel brut',
                'emoji' => '🍯',
                'calories' => 304,
                'benefits' => ['Énergie naturelle', 'Antioxydants', 'Bien-être'],
                'description' => 'Libération modérée d\'énergie qui stabilise l\'humeur et réduit l\'irritabilité.'
            ],
            [
                'name' => 'Orange & Agrumes',
                'emoji' => '🍊',
                'calories' => 47,
                'benefits' => ['Vitamine C', 'Folate', 'Réduction stress'],
                'description' => 'La vitamine C réduit les niveaux de cortisol, l\'hormone du stress.'
            ],
            [
                'name' => 'Lentilles',
                'emoji' => '🫘',
                'calories' => 116,
                'benefits' => ['Fer', 'Folate', 'Stabilité émotionnelle'],
                'description' => 'Le fer combat la fatigue et le folate soutient la santé mentale.'
            ],
            [
                'name' => 'Beurre de cacahuète',
                'emoji' => '🥜',
                'calories' => 588,
                'benefits' => ['Protéines', 'Tryptophane', 'Satiété'],
                'description' => 'Le tryptophane aide à la production de mélatonine et sérotonine.'
            ],
            [
                'name' => 'Kiwi',
                'emoji' => '🥝',
                'calories' => 61,
                'benefits' => ['Vitamine C', 'Sérine', 'Sommeil réparateur'],
                'description' => 'Améliore la qualité du sommeil, essentiel pour la régulation de l\'humeur.'
            ],
            [
                'name' => 'Curcuma',
                'emoji' => '🧡',
                'calories' => 354,
                'benefits' => ['Curcumine', 'Anti-inflammatoire', 'Santé mentale'],
                'description' => 'La curcumine peut augmenter les niveaux de BDNF, protéine de la santé cérébrale.'
            ]
        ];

        // Combiner tous les aliments pour le calculateur
        $allFoods = array_merge($strengthSpeedFoods, $decreaseFoods, $joyFoods);
        
        // Trier par nom
        usort($allFoods, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $this->render('consultation/nitrution.html.twig', [
            'strengthSpeedFoods' => $strengthSpeedFoods,
            'decreaseFoods' => $decreaseFoods,
            'joyFoods' => $joyFoods,
            'allFoods' => $allFoods,
        ]);
    }

   



}









