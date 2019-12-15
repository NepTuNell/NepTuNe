<?php

namespace CoreBundle\Repository;

use CoreBundle\Entity\Post;
use CoreBundle\Entity\User;

/**
 * PostRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PostRepository extends \Doctrine\ORM\EntityRepository
{

    public function fetchAll( $options = array() )
    {

        $queryBuilder  = $this->getEntityManager()->createQueryBuilder();  
        
        if (array_key_exists('sujet', $options)) {
            
            $result = $queryBuilder ->select('p.id, p.commentaire, p.date, u.username, u.id as id_user')
                                    ->from(Post::class, 'p')
                                    ->innerJoin(User::class, 'u')
                                    ->where('p.sujet = :sujet')
                                    ->setParameters([
                                        'sujet'   => $options['sujet']
                                    ])
                                    ->orderBy('p.date', 'DESC')
                                    ->getQuery();

        }

        return $result->getArrayResult();

    }

}