<?php

namespace CoreBundle\Services;

use Symfony\Bundle\FrameworkBundle\Controller\TemplateController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\RouterInterface;
use Doctrine\Common\Persistence\ObjectManager;
use CoreBundle\Entity\User;


class Mailer extends Controller
{
    
     /*******************************
     *          ATTRIBUTS
     ******************************/

    /**
     * Undocumented variable
     *
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * Undocumented variable
     *
     * @var ObjectManager
     */
    private $manager;

    /**
     * Undocumented variable
     *
     * @var RouterInterface
     */
    private $router;

     /**
     * @var Twig_Environment
     */
    private $templating;


    /*******************************
     *          METHODES
     ******************************/

    /**
     * Undocumented function
     *
     * @param \Swift_Mailer $mailer
     * @param \Twig_Environment $templating
     * @param ObjectManager $manager
     * @param RouterInterface $router
     */
    public function __construct (\Swift_Mailer $mailer, \Twig_Environment $templating, ObjectManager $manager, RouterInterface $router) 
    {

        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->manager = $manager;
        $this->router  = $router;

    }

    /**
     * Undocumented function
     *
     * @param User $user
     * @return void
     */
    public function accountCreated(User $user)
    {

        $message =  \Swift_Message::newInstance()
                    ->setSubject('Hello Email')
                    ->setFrom('sauronlemaudit@gmail.com')
                    ->setTo(''.$user->getEmail())
                    ->setBody($this->templating->render('Templates\Email\confirm.html.twig', [
                        'user'  => $user
                    ]), 
                    'text/html'
                );
                         
        $this->mailer->send($message); 

    }
    

    /**
     * Undocumented function
     *
     * @param User $user
     * @return void
     */
    public function accountActivated(User $user)
    {

        $message =  \Swift_Message::newInstance()
                    ->setSubject('Hello Email')
                    ->setFrom('sauronlemaudit@gmail.com')
                    ->setTo(''.$user->getEmail())
                    ->setBody('Votre compte a été activé par un administrateur!');
                         
        $this->mailer->send($message); 

    }


    /**
     * Undocumented function
     *
     * @param User $user
     * @return void
     */
    public function accountDeactivated(User $user)
    {

        $message =  \Swift_Message::newInstance()
                    ->setSubject('Hello Email')
                    ->setFrom('sauronlemaudit@gmail.com')
                    ->setTo(''.$user->getEmail())
                    ->setBody('Votre compte a été désactivé par un administrateur!');
                         
        $this->mailer->send($message); 

    }


    /**
     * Undocumented function
     *
     * @param User $user
     * @return void
     */
    public function situationDetailsActivated(User $user)
    {

        $message =  \Swift_Message::newInstance()
                    ->setSubject('Hello Email')
                    ->setFrom('sauronlemaudit@gmail.com')
                    ->setTo(''.$user->getEmail())
                    ->setBody('Votre situation a bien été mise à jour !');
                         
        $this->mailer->send($message); 

    }

    
    /**
     * Undocumented function
     *
     * @param User $user
     * @return void
     */
    public function situationDetailsDeactivated(User $user)
    {

        $message =  \Swift_Message::newInstance()
                    ->setSubject('Hello Email')
                    ->setFrom('sauronlemaudit@gmail.com')
                    ->setTo(''.$user->getEmail())
                    ->setBody('Votre situation a bien été désactivé.');
                         
        $this->mailer->send($message); 

    }

}