<?php

/** 
 * author: CHU VAN Jimmy
 * 
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

/**
 * Classe utilisée pour "écouter" lors de la déconnexion
 */
class LogoutListener implements LogoutHandlerInterface
{
    
    /**
     * Objet utilisé pour stocker l'ObjectManager de Doctrine.
     * Sert à administrer la base de données.
     */
    private $manager;

    /**
     * Objet symfony, utilisé principalement pour la récupération de l'utilisateur courant
     */
    private $token;
    
    /**
     * Constructeur de la classe
     * 
     * @param ObjectManager
     * @param TokenStorageInterface
     */
    public function __construct (ObjectManager $manager, TokenStorageInterface $token) 
    {
        $this->manager = $manager;
        $this->token   = $token;
    }
    
    /**
     * Fonction utilisé lors de la déconnexion
     * 
     * @param Request
     * @param Response
     * @param TokenInterface
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        
    }
    
}