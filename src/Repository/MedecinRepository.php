<?php

namespace App\Repository;

use App\Entity\Medecin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Medecin>
 */
class MedecinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Medecin::class);
    }

//    /**
//     * @return Medecin[] Returns an array of Medecin objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Medecin
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * @return Medecin[] Returns an array of Medecin objects
     */
    public function searchByNomOrPrenom(string $search, string $sort = 'nom', string $order = 'ASC'): array
    {
        $qb = $this->createQueryBuilder('m');
        
        $qb->where(
            $qb->expr()->orX(
                $qb->expr()->like('m.nom', ':search'),
                $qb->expr()->like('m.prenom', ':search')
            )
        )
        ->setParameter('search', '%' . $search . '%')
        ->orderBy('m.' . $sort, $order);
        
        return $qb->getQuery()->getResult();
    }
}
