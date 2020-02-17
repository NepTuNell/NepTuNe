<?php

namespace CoreBundle\Repository;

use CoreBundle\Entity\Post;
use CoreBundle\Entity\Picture;

/**
 * PictureRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PictureRepository extends \Doctrine\ORM\EntityRepository
{

    public function fetchByPost( $id ) 
    {
        $queryBuilder  = $this->getEntityManager()->createQueryBuilder();  
        
        if ( null !== $id ) {
            
            $result = $queryBuilder ->select('p.id, p.pictureName, p.pictureExtension')
                                    ->from(Picture::class, 'p')
                                    ->where('p.post = :id')
                                    ->setParameters([
                                        'id' => $id
                                    ])
                                    ->getQuery();

        }

        return $result->getResult();
    }
    
}
