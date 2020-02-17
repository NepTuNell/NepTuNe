<?php

namespace BackendBundle\Repository;

use BackendBundle\Entity\Theme;

/**
 * ThemeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ThemeRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Nombre de theme pour un univers
     */
    public function getThemeByUnivers(Univers $univers)
    {

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();  
        
        $result = $queryBuilder ->select('COUNT(t)')
                                ->from(Theme::class, 't')
                                ->where('t.univers = :univers')
                                ->setParameter('univers', $univers)
                                ->getQuery();

        return $result->getSingleScalarResult();

    }

}
