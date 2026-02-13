<?php

namespace App\Repository;

use App\Entity\ChapitreVersion;
use App\Entity\Chapitre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChapitreVersion>
 */
class ChapitreVersionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChapitreVersion::class);
    }

    /**
     * Get all versions for a specific chapter, ordered by version number descending
     */
    public function findByChapitreOrderedByVersion(Chapitre $chapitre): array
    {
        return $this->createQueryBuilder('cv')
            ->andWhere('cv.chapitre = :chapitre')
            ->setParameter('chapitre', $chapitre)
            ->orderBy('cv.versionNumber', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get the latest version number for a chapter
     */
    public function getLatestVersionNumber(Chapitre $chapitre): int
    {
        $result = $this->createQueryBuilder('cv')
            ->select('MAX(cv.versionNumber)')
            ->andWhere('cv.chapitre = :chapitre')
            ->setParameter('chapitre', $chapitre)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (int)$result : 0;
    }

    /**
     * Get a specific version of a chapter
     */
    public function findVersionByNumber(Chapitre $chapitre, int $versionNumber): ?ChapitreVersion
    {
        return $this->createQueryBuilder('cv')
            ->andWhere('cv.chapitre = :chapitre')
            ->andWhere('cv.versionNumber = :versionNumber')
            ->setParameter('chapitre', $chapitre)
            ->setParameter('versionNumber', $versionNumber)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get version statistics for a chapter
     */
    public function getVersionStatistics(Chapitre $chapitre): array
    {
        $versions = $this->findByChapitreOrderedByVersion($chapitre);
        
        if (empty($versions)) {
            return [
                'total_versions' => 0,
                'average_modification' => 0,
                'major_changes' => 0,
                'minor_changes' => 0
            ];
        }

        $totalVersions = count($versions);
        $totalModification = 0;
        $majorChanges = 0;
        $minorChanges = 0;

        foreach ($versions as $version) {
            $modPercent = $version->getModificationPercentage() ?? 0;
            $totalModification += $modPercent;
            
            if ($modPercent >= 30) {
                $majorChanges++;
            } else {
                $minorChanges++;
            }
        }

        return [
            'total_versions' => $totalVersions,
            'average_modification' => $totalVersions > 0 ? round($totalModification / $totalVersions, 2) : 0,
            'major_changes' => $majorChanges,
            'minor_changes' => $minorChanges
        ];
    }

    /**
     * Get recent versions across all chapters (for dashboard)
     */
    public function findRecentVersions(int $limit = 10): array
    {
        return $this->createQueryBuilder('cv')
            ->orderBy('cv.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get versions with major changes (>30% modification)
     */
    public function findMajorChanges(Chapitre $chapitre): array
    {
        return $this->createQueryBuilder('cv')
            ->andWhere('cv.chapitre = :chapitre')
            ->andWhere('cv.modificationPercentage >= 30')
            ->setParameter('chapitre', $chapitre)
            ->orderBy('cv.versionNumber', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
