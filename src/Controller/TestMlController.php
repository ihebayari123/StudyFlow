<?php
namespace App\Controller;

use App\Repository\UtilisateurRepository;
use App\Service\UserRiskCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestMlController extends AbstractController
{
    #[Route('/test-ml', name: 'test_ml')]
    public function testMl(
        UtilisateurRepository $userRepo,
        UserRiskCalculator $riskCalculator
    ): Response {
        // Pick a user to test
        $user = $userRepo->findOneBy([]); // first user in DB

        if (!$user) {
            return new Response("No user found in database.");
        }

        $riskScore = $riskCalculator->calculateRisk($user);

        return $this->render('test_ml/index.html.twig', [
            'user' => $user,
            'riskScore' => $riskScore
        ]);
    }

    #[Route('/test-ml/{id}', name: 'test_ml_user')]
public function testMlUser(
    int $id,
    UtilisateurRepository $userRepo,
    UserRiskCalculator $riskCalculator
): Response {
    $user = $userRepo->find($id);
    
    if (!$user) {
        return new Response("Utilisateur ID $id non trouvé");
    }
    
    $riskScore = $riskCalculator->calculateRisk($user);
    
    return $this->render('test_ml/index.html.twig', [
        'user' => $user,
        'riskScore' => $riskScore
    ]);
}
}