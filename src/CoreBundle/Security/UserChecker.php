<?php

/**
 * author: CHU VAN Jimmy 
 */

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

/**
 * Vérification utilisateur avant login et avant logout
 */
class UserChecker implements UserCheckerInterface
{
    
    /**
     * Objet utilisé pour stocker l'ObjectManager de Doctrine.
     * Sert à administrer la base de données.
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
     * Constructeur de la classe
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
     * Vérification de l'utilisateur avec sa connexion (une fois que le formulaire de connexion est soumis)
     *
     * @param UserInterface
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
     * Vérification de l'utilisateur juste après sa déconnexion (avant logout)
     *
     * @param UserInterface
     * @return void
     */
    public function checkPostAuth(UserInterface $user)
    {
        
        if (!$user instanceof AppUser) {
            return;
        }
  
    }
    
}