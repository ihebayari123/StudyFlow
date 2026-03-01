<?php

namespace App\Controller; 

use App\Service\NaturalLanguageInterpreter;
use App\Service\UserSearchQueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserNaturalSearchController extends AbstractController
{
    #[Route('/admin/users/natural-search', name: 'admin_users_natural_search', methods: ['POST'])]
    public function naturalSearch(
        Request $request,
        NaturalLanguageInterpreter $interpreter,
        UserSearchQueryBuilder $queryBuilder
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $query = $data['query'] ?? '';

        if (empty($query)) {
            return $this->json(['error' => 'Query is required'], 400);
        }

        // 1. Interpréter
        $dto = $interpreter->interpret($query);

        dump([
        'role' => $dto->getRole(),
        'emailVerified' => $dto->getEmailVerified(),
    ]);
    
        
        // 2. Chercher
        $results = $queryBuilder->execute($dto);

        // 3. Formater avec TOUS tes champs
        $formattedResults = array_map(function($user) {
            return [
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'statut_compte' => $user->getStatutCompte(),
                'email_verified' => $user->isEmailVerified(),
                'last_login' => $user->getLastLogin()?->format('Y-m-d H:i:s'),
                'created_at' => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
                'failed_attempts' => $user->getFailedLoginAttempts(),
            ];
        }, $results);

        return $this->json([
            'success' => true,
            'query' => $query,
            'interpretation' => [
                'role' => $dto->getRole(),
                'statut_compte' => $dto->getStatutCompte(),
                'email_verified' => $dto->getEmailVerified(),
                'created_from' => $dto->getCreatedAtFrom()?->format('Y-m-d'),
                'created_to' => $dto->getCreatedAtTo()?->format('Y-m-d'),
                'never_logged_in' => $dto->getNeverLoggedIn(),
            ],
            'count' => count($results),
            'results' => $formattedResults
        ]);
    }
}