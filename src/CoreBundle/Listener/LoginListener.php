<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CoreBundle\Listener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\RouterInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoginListener implements AuthenticationSuccessHandlerInterface 
{
 
    private $manager;
    private $token;
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
     * @param Request $request
     * @param TokenInterface $token
     * @return void
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        
        return new RedirectResponse($this->router->generate('user_index'));
    
    }
    
}