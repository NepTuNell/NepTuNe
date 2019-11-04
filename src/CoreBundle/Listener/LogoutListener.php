<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CoreBundle\Listener;
 
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LogoutListener implements LogoutHandlerInterface
{
 
    private $manager;
    private $token;
    
    public function __construct (ObjectManager $manager, TokenStorageInterface $token) 
    {
        $this->manager = $manager;
        $this->token   = $token;
    }
    
    /**
     * @{inheritDoc}
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        
    }
    
}