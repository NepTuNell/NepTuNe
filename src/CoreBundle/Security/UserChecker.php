<?php

namespace CoreBundle\Security;

use CoreBundle\Entity\User as AppUser;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Exception\AccountDeletedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\RouterInterface;


class UserChecker implements UserCheckerInterface
{
    
    /**
     * Undocumented variable
     *
     * @var ObjectManager
     */
    private $manager;

    /**
     * Undocumented variable
     *
     * @var TokenStorageInterface
     */
    private $token;

    /**
     * Undocumented variable
     *
     * @var RouterInterface
     */
    private $router;
    
    /**
     * Undocumented function
     *
     * @param ObjectManager $manager
     * @param TokenStorageInterface $token
     * @param RouterInterface $router
     */
    public function __construct (ObjectManager $manager, TokenStorageInterface $token, RouterInterface $router) 
    {

        $this->manager = $manager;
        $this->token   = $token;
        $this->router  = $router;

    }
    
    /**
     * Undocumented function
     *
     * @param UserInterface $user
     * @return void
     */
    public function checkPreAuth(UserInterface $user)
    {

        if (!$user instanceof AppUser) {
            return;
        }

        /**
         * User logged for the first time or account not activated
         */
        if ( !$user->getIsActive() || $user->getRegisterKey() !== null ) {

            throw new DisabledException();

        }

    }

    /**
     * Undocumented function
     *
     * @param UserInterface $user
     * @return void
     */
    public function checkPostAuth(UserInterface $user)
    {
        
        if (!$user instanceof AppUser) {
            return;
        }
  
    }
    
}