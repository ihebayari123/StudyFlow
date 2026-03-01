<?php

namespace App\Service;

use App\DTO\UserNaturalSearchDTO;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

class UserSearchQueryBuilder
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function execute(UserNaturalSearchDTO $criteria): array
    {
        $qb = $this->entityManager
            ->createQueryBuilder()
            ->select('u')
            ->from(Utilisateur::class, 'u');

        // ✅ CORRECT - Noms des PROPRIÉTÉS (camelCase)
        if ($criteria->getRole()) {
            $qb->andWhere('u.role LIKE :role')
               ->setParameter('role', '%' . $criteria->getRole() . '%');
        }

        if ($criteria->getStatutCompte()) {
            $qb->andWhere('u.statutCompte = :statut')
               ->setParameter('statut', $criteria->getStatutCompte());
        }

        if ($criteria->getEmailVerified() !== null) {
            $qb->andWhere('u.emailVerified = :emailVerified')
               ->setParameter('emailVerified', $criteria->getEmailVerified());
        }

        dump($qb->getDQL());
        dump($qb->getParameters());

        if ($criteria->getCreatedAtFrom()) {
            $qb->andWhere('u.createdAt >= :from')  // ✅ createdAt (pas created_at)
               ->setParameter('from', $criteria->getCreatedAtFrom());
        }

        if ($criteria->getCreatedAtTo()) {
            $qb->andWhere('u.createdAt <= :to')    // ✅ createdAt (pas created_at)
               ->setParameter('to', $criteria->getCreatedAtTo());
        }

        if ($criteria->getNeverLoggedIn() === true) {
            $qb->andWhere('u.lastLogin IS NULL');   // ✅ lastLogin (pas last_login)
        }

        if ($criteria->getMinFailedAttempts()) {
            $qb->andWhere('u.failedLoginAttempts >= :min')  // ✅ failedLoginAttempts
               ->setParameter('min', $criteria->getMinFailedAttempts());
        }

        return $qb->getQuery()->getResult();
    }
}