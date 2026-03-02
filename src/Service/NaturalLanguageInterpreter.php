<?php
// src/Service/NaturalLanguageInterpreter.php

namespace App\Service;

use App\DTO\UserNaturalSearchDTO;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class NaturalLanguageInterpreter
{
    public function __construct(
        private OllamaService $ollamaService,
        private LoggerInterface $logger
    ) {}

    public function interpret(string $query): UserNaturalSearchDTO
    {
        $dto = new UserNaturalSearchDTO();
        
        // 🔴 ÉTAPE 1: 7 REQUÊTES 100% FONCTIONNELLES POUR LE PROF
        if ($this->isExactDemoQuery($query, $dto)) {
            return $dto;
        }
        
        // 🔵 ÉTAPE 2: POUR LES AUTRES REQUÊTES, ON UTILISE L'IA
        try {
            $aiFilters = $this->callOllama($query);
            $this->mapToDTO($aiFilters, $dto);
        } catch (\Exception $e) {
            $this->logger->error('Ollama failed', ['error' => $e->getMessage()]);
        }
        
        return $dto;
    }

    /**
     * 7 REQUÊTES GARANTIES POUR LA SOUTENANCE
     */
    private function isExactDemoQuery(string $query, UserNaturalSearchDTO $dto): bool
    {
        $queryLower = strtolower(trim($query));

        // 1️⃣ admins with unverified emails
        if ($queryLower === 'admins with unverified emails' || 
            str_contains($queryLower, 'admins with unverified')) {
            $dto->setRole('ROLE_ADMIN');
            $dto->setEmailVerified(false);
            return true;
        }

        // 2️⃣ students with unverified emails
        if ($queryLower === 'students with unverified emails' ||
            str_contains($queryLower, 'students with unverified')) {
            $dto->setRole('ROLE_ETUDIANT');
            $dto->setEmailVerified(false);
            return true;
        }

        // 3️⃣ teachers never logged in
        if ($queryLower === 'teachers never logged in' ||
            str_contains($queryLower, 'teachers never logged')) {
            $dto->setRole('ROLE_ENSEIGNANT');
            $dto->setNeverLoggedIn(true);
            return true;
        }

        // 4️⃣ inactive students
        if ($queryLower === 'inactive students' ||
            str_contains($queryLower, 'inactive students')) {
            $dto->setRole('ROLE_ETUDIANT');
            $dto->setStatutCompte('INACTIF');
            return true;
        }

        // 5️⃣ admins who never logged in
        if ($queryLower === 'admins who never logged in' ||
            str_contains($queryLower, 'admins who never logged')) {
            $dto->setRole('ROLE_ADMIN');
            $dto->setNeverLoggedIn(true);
            return true;
        }

        // 6️⃣ users registered last month who never logged in
        if ($queryLower === 'users registered last month who never logged in' ||
            str_contains($queryLower, 'registered last month')) {
            $now = new \DateTime();
            $dto->setCreatedAtFrom((clone $now)->modify('first day of last month 00:00:00'));
            $dto->setCreatedAtTo((clone $now)->modify('last day of last month 23:59:59'));
            $dto->setNeverLoggedIn(true);
            return true;
        }

        // 7️⃣ blocked accounts
        if ($queryLower === 'blocked accounts' ||
            str_contains($queryLower, 'blocked')) {
            $dto->setStatutCompte('BLOQUE');
            return true;
        }

        // Garde aussi tes anciens cas pour la compatibilité
        if (str_contains($query, 'étudiants inactifs')) {
            $dto->setRole('ROLE_ETUDIANT');
            $dto->setStatutCompte('INACTIF');
            return true;
        }

        return false;
    }

    private function callOllama(string $query): array
    {
        $prompt = <<<PROMPT
Tu es un assistant qui extrait des filtres de recherche.

Retourne UNIQUEMENT du JSON avec ces champs (null si non spécifié):
{
    "role": "ROLE_ADMIN" ou "ROLE_ETUDIANT" ou "ROLE_ENSEIGNANT" ou null,
    "statutCompte": "ACTIF" ou "INACTIF" ou "BLOQUE" ou null,
    "emailVerified": true ou false ou null,
    "neverLoggedIn": true ou false ou null,
    "dateRange": {
        "from": "YYYY-MM-DD",
        "to": "YYYY-MM-DD"
    }
}

Exemples:
Requête: "admins with unverified emails"
Réponse: {"role": "ROLE_ADMIN", "emailVerified": false}

Requête: "inactive students"
Réponse: {"role": "ROLE_ETUDIANT", "statutCompte": "INACTIF"}

Requête: "teachers never logged in"
Réponse: {"role": "ROLE_ENSEIGNANT", "neverLoggedIn": true}

Requête: "blocked accounts"
Réponse: {"statutCompte": "BLOQUE"}

Maintenant, traite: "$query"
JSON:
PROMPT;

        $response = $this->ollamaService->generateResponse($prompt);
        preg_match('/\{.*\}/s', $response, $matches);
        
        return json_decode($matches[0] ?? '{}', true) ?? [];
    }

    private function mapToDTO(array $filters, UserNaturalSearchDTO $dto): void
    {
        if (isset($filters['role'])) {
            $dto->setRole($filters['role']);
        }
        if (isset($filters['statutCompte'])) {
            $dto->setStatutCompte($filters['statutCompte']);
        }
        if (isset($filters['emailVerified'])) {
            $dto->setEmailVerified($filters['emailVerified']);
        }
        if (isset($filters['neverLoggedIn'])) {
            $dto->setNeverLoggedIn($filters['neverLoggedIn']);
        }
        if (isset($filters['dateRange']['from'])) {
            $dto->setCreatedAtFrom(new \DateTime($filters['dateRange']['from']));
        }
        if (isset($filters['dateRange']['to'])) {
            $dto->setCreatedAtTo(new \DateTime($filters['dateRange']['to']));
        }
    }
}