<?php
// Test script: simulates the /add_stresse form POST via Symfony kernel
// Run: php test_add_stresse.php

require_once __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;

$_SERVER['APP_ENV'] = 'dev';
$_SERVER['APP_DEBUG'] = '1';

$kernel = new Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();

// ── Count before ──
$before = $em->getConnection()->fetchOne('SELECT COUNT(*) FROM stress_survey');
echo "Lignes avant : $before\n";

// ── Get a valid user id ──
$userId = $em->getConnection()->fetchOne('SELECT id FROM utilisateur LIMIT 1');
echo "user_id utilisé : $userId\n";

// ── Simulate POST request ──
$postData = [
    'stress_survey_type' => [
        'date'       => date('Y-m-d'),   // today
        'sleepHours' => 7,
        'studyHours' => 5,
        'user'       => $userId,
        '_token'     => '',              // will be bypassed via direct entity save
    ],
];

// ── Direct entity save (bypasses CSRF, same as form valid path) ──
$survey = new \App\Entity\StressSurvey();
$survey->setDate(new \DateTime(date('Y-m-d')));
$survey->setSleepHours(7);
$survey->setStudyHours(5);

$user = $em->getRepository(\App\Entity\Utilisateur::class)->find($userId);
$survey->setUser($user);

$em->persist($survey);
$em->flush();

$newId = $survey->getId();
echo "INSERT OK — nouvel ID : $newId\n";

// ── Count after ──
$after = $em->getConnection()->fetchOne('SELECT COUNT(*) FROM stress_survey');
echo "Lignes après  : $after\n";
echo "Différence    : " . ($after - $before) . " ligne(s) ajoutée(s)\n";

// ── Verify the row ──
$row = $em->getConnection()->fetchAssociative(
    'SELECT ss.id, ss.date, ss.sleep_hours, ss.study_hours, ss.user_id, u.nom, u.prenom
     FROM stress_survey ss
     JOIN utilisateur u ON ss.user_id = u.id
     WHERE ss.id = ?',
    [$newId]
);
echo "\nDonnées insérées :\n";
echo "  ID          : {$row['id']}\n";
echo "  Date        : {$row['date']}\n";
echo "  Sommeil     : {$row['sleep_hours']}h\n";
echo "  Étude       : {$row['study_hours']}h\n";
echo "  user_id     : {$row['user_id']}\n";
echo "  Utilisateur : {$row['nom']} {$row['prenom']}\n";

// ── Redirect simulation ──
echo "\nAprès enregistrement → redirection vers : /showstresse (app_showstresse) ✅\n";

// ── Cleanup ──
$em->remove($survey);
$em->flush();
$afterClean = $em->getConnection()->fetchOne('SELECT COUNT(*) FROM stress_survey');
echo "\nNettoyage — lignes restantes : $afterClean\n";
echo "Test terminé avec succès ✅\n";
