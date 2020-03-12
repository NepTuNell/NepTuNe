<?php

namespace BackendBundle\Repository;

use BackendBundle\Entity\Backup;

/**
 * BackupRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BackupRepository extends \Doctrine\ORM\EntityRepository
{

    public function fetchAllBackup() 
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $result = $queryBuilder->select('b')
                               ->from(Backup::class, 'b')
                               ->orderBy('b.libelle', 'DESC');
                                
        return $result; 
    }

    public function fetchBackup($libelle) 
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $result = $queryBuilder->select('COUNT(b)')
                               ->from(Backup::class, 'b')
                               ->where('b.libelle = :libelle')
                               ->setParameter('libelle', $libelle)
                               ->getQuery();

        return (int) $result->getSingleScalarResult();
    }
}
