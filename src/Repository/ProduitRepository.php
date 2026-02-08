<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }



    ///////////////////////////////////////////////////////
    public function findByCategorieName(?string $categorieName): array
    {
        $em = $this->getEntityManager();
        
        if ($categorieName) {
            $dql = "SELECT p FROM App\Entity\Produit p 
                    INNER JOIN p.typeCategorie c 
                    WHERE c.nomCategorie LIKE :nom";
            
            $query = $em->createQuery($dql);
            $query->setParameter('nom', '%' . $categorieName . '%');
        } else {
            $dql = "SELECT p FROM App\Entity\Produit p 
                    INNER JOIN p.typeCategorie c";
            
            $query = $em->createQuery($dql);
        }
        
        return $query->getResult();
    }






    //    /**
    //     * @return Produit[] Returns an array of Produit objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Produit
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
