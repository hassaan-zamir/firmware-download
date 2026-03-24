<?php

namespace App\Repository;

use App\Entity\SoftwareVersion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SoftwareVersion>
 */
class SoftwareVersionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SoftwareVersion::class);
    }

    /**
     * Find software versions by system_version_alt (case-insensitive).
     *
     * @return SoftwareVersion[]
     */
    public function findByVersionAlt(string $versionAlt): array
    {
        return $this->createQueryBuilder('sv')
            ->where('LOWER(sv.systemVersionAlt) = LOWER(:version)')
            ->setParameter('version', $versionAlt)
            ->getQuery()
            ->getResult();
    }
}
