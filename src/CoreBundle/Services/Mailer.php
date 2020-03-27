<?php

/**
 * author: CHU VAN Jimmy 
 */

namespace CoreBundle\Services;

use Symfony\Bundle\FrameworkBundle\Controller\TemplateController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\RouterInterface;
use Doctrine\Common\Persistence\ObjectManager;
use CoreBundle\Entity\User;

/**
 * Class servant au mailing
 */
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
     * Objet utilisé pour stocker l'ObjectManager de Doctrine.
     * Sert à administrer la base de données.
     *
     * @var ObjectManager
     */
    private $manager;

    /**
     * Objet utilisé par Symfony pour Router
     *
     * @var RouterInterface
     */
    private $router;

    /**
     * Objet twig pour création de template 
     * 
     * @var Twig_Environment
     */
    private $templating;


    /*******************************
     *          METHODES
     ******************************/

    /**
     * Constructeur de la classe
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
     * Envoi d'un email lors de la création d'un compte
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
     * Envoi d'un email lorsque l'administrateur réactive un compte
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
     * Envoi d'un email lorsque l'administrateur désactive un compte
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
     * Envoi d'un email lorsque l'utilisateur vérifie son compte lors d'une demande de réinitialisation du mot de passe
     *
     * @param User $user
     * @return void
     */
    public function resetPasswordCheckAccount(User $user)
    {

        $message =  \Swift_Message::newInstance()
                        ->setSubject('Hello Email')
                        ->setFrom('sauronlemaudit@gmail.com')
                        ->setTo(''.$user->getEmail())
                        ->setBody($this->templating->render('Templates\Email\reset.html.twig', [
                            'user'  => $user
                        ]), 
                        'text/html'
                    );     

        $this->mailer->send($message); 

    }

    /**
     * Envoi d'un email qui contient le nouveau mot de passe
     * Après avoir vérifié son compte lors d'une demande de réinitialisation du mot de passe
     *
     * @param User
     * @param string
     * @return void
     */
    public function resetPassword(User $user, $password)
    {

        $message =  \Swift_Message::newInstance()
                    ->setSubject('Hello Email')
                    ->setFrom('sauronlemaudit@gmail.com')
                    ->setTo(''.$user->getEmail())
                    ->setBody('Votre mot de passe a été réinitialisé ! Votre nouveau mot de passe est : '.$password.' . Veuillez le modifier dès que possible.');
                         
        $this->mailer->send($message); 

    }

}