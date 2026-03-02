<?php

namespace App\Controller; 

use App\Service\NaturalLanguageInterpreter;
use App\Service\UserSearchQueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;  // ← Changé de JsonResponse à Response
use Symfony\Component\Routing\Annotation\Route;

class UserNaturalSearchController extends AbstractController
{
    #[Route('/admin/users/natural-search', name: 'admin_users_natural_search', methods: ['POST'])]
    public function naturalSearch(
        Request $request,
        NaturalLanguageInterpreter $interpreter,
        UserSearchQueryBuilder $queryBuilder
    ): Response {  // ← Changé de JsonResponse à Response
        
        // Lire la requête
        $query = '';
        
        // Si c'est un formulaire standard
        if ($request->request->has('query')) {
            $query = $request->request->get('query', '');
        } 
        // Si c'est du JSON
        else {
            $data = json_decode($request->getContent(), true);
            $query = $data['query'] ?? '';
        }

        if (empty($query)) {
            $this->addFlash('error', 'Veuillez saisir une requête');
            return $this->redirectToRoute('admin_natural_search');
        }

        // 1. Interpréter
        $dto = $interpreter->interpret($query);
        
        // 2. Chercher
        $users = $queryBuilder->execute($dto);

        // 3. Retourner la vue avec les résultats
        return $this->render('user/results.html.twig', [
            'query' => $query,
            'interpretation' => [
                'role' => $dto->getRole(),
                'statut_compte' => $dto->getStatutCompte(),
                'email_verified' => $dto->getEmailVerified(),
                'created_from' => $dto->getCreatedAtFrom()?->format('Y-m-d'),
                'created_to' => $dto->getCreatedAtTo()?->format('Y-m-d'),
                'never_logged_in' => $dto->getNeverLoggedIn(),
            ],
            'count' => count($users),
            'users' => $users
        ]);
    }
}