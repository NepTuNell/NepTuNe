<?php

namespace BackendBundle\Controller;

use CoreBundle\Entity\User;
use CoreBundle\Services\Mailer;
use BackendBundle\Entity\Univers;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class SecurityController extends Controller
{
    
    /*******************************
     *          ATTRIBUTS
     ******************************/

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var Mailer
     */
    private $mailer;
    
    /**
     * Undocumented variable
     *
     * @var ObjectManager
     * @var Mailer
     */
    public function __construct(ObjectManager $em, Mailer $mailer)
    {

        $this->manager      = $em;
        $this->mailer       = $mailer;

    }

    /**
     * Undocumented function
     * 
     * @Route("/login", name="login", methods={"GET", "POST"})
     * @return void
     */
    public function login ()
    {
        
        $error        = $this->get('security.authentication_utils')->getLastAuthenticationError();
        $lastUsername = $this->get('security.authentication_utils')->getLastUsername();
        $univers      = $this->manager->getRepository(Univers::class)->findAll();
        
        return $this->render('User/Security/login.html.twig',  [
            'universList'   => $univers,
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
        
    }


    /**
     * Undocumented function
     * 
     * @Route("/logout", name="logout", methods={"GET", "POST"})
     * @return void
     */
    public function logout ()
    {
        
    }
    
    /**
     * Undocumented function
     * 
     * @Route("/home/password", name="user_reset_password", methods={"GET", "POST"})
     * @param Request $request
     * @return void
     */
    public function resetPassword (Request $request)
    {
        
        if ( !$this->isGranted('ROLE_USER') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {
            throw $this->createAccessDeniedException('Veuillez vous connecter !');
        }
        
        $univers  = $this->manager->getRepository(Univers::class)->findAll();
        $user     = $this->get('security.token_storage')->getToken()->getUser();
        $message  = null;

        /**
         * Vérification du type de requête
         */
        if ( 'POST' === $request->getMethod() ) {
        
            $password        = $user->getPassword();
            $confirmPassword = $request->request->get('password');
            
            /**
             * Vérification du contenu des mots de passe saisies
             */
            if ( !empty($request->request->get('confirmPassword1')) && !empty($request->request->get('confirmPassword2')) && !empty($confirmPassword) ) {
                
                /**
                 * Vérification de la concordance des mots de passe
                 */
                if ( password_verify($confirmPassword, $password) ) {
                    
                    if ( $request->request->get('confirmPassword1') === $request->request->get('confirmPassword2') ) {
                        
                        $hash = $this->get('security.password_encoder')->encodePassword($user, $request->request->get('confirmPassword1'));
                        $user->setPassword($hash);
                        $this->manager->persist($user);
                        $this->manager->flush();
                        
                        return $this->redirectToRoute('user_edit');

                    }

                    $message[] = 'Les mots de passe ne sont pas identique !';
                    
                }

                $message[] = "Le mot de passe saisi n'est pas correct";
            
            } else {
            
                $message[] = "Veuillez renseigner tous les champs !";
            
            }
            
        }
        
        return $this->render('User/Security/resetPassword.html.twig', [
            'message'     =>  $message,
            'universList' =>  $univers
        ]);
        
    }
    
    /**
     * Undocumented function
     * 
     * @Route("/home/account/verify/{id}/{key}", name="user_confirm_account", methods={"GET"})
     * @param User $user
     * @param [type] $key
     * @return void
     */
    public function confirmAccount(User $user, $key)
    {

        if  ( !$user || $user->getRegisterKey() !== $key ) {

            $message = array();
            $message[] = 'Votre compte n\'est pas activé, une erreur est survenue!';
            $message[] = 'Veuillez contacter l\'administrateur du site!';
            return $this->render('User/Security/login.html.twig', [
                'message' => $message
            ]);

        } 

        $user->setIsActive(true);
        $user->setRegisterKey(null);
        $this->manager->persist($user);
        $this->manager->flush();

        $this->addFlash('success', 'Votre compte est activé, vous pouvez maintenant vous connecter!');
        return $this->render('User/Security/login.html.twig');

    }

    /**
     * Undocumented function
     * 
     * @Route("/home/check/account", name="user_check_account", methods={"POST","GET"})
     * @param User $user
     * @param [type] $key
     * @return void
     */
    public function checkAccount(Request $request)
    {

        $universList = $this->manager->getRepository(Univers::class)->findAll();
        $errors      = null;
        $messages    = null;

        if ( "POST" === $request->getMethod() ) {

            $user = $this->manager->getRepository(User::class)->findOneBy([
                'email' => $request->request->get('email')
            ]);

            if ( null !== $user ) {

                $this->mailer->resetPasswordCheckAccount($user);
                $this->addFlash('success', 'Un mail vous a été envoyé pour réinitialiser votre mot de passe !');

            } else {

                $messages[] = "Identifiants invalides !";

            }

        }
        
        return $this->render('User/Security/check.html.twig', [
            'universList'   =>  $universList,
            'message'       =>  $messages,
            'errors'        =>  $errors,
        ]);

    }

    /**
     * Undocumented function
     * 
     * @Route("/home/account/reset/password/{id}", name="user_check_account_reset_password", methods={"GET"})
     * @param User $user
     * @param [type] $key
     * @return void
     */
    public function checkAccountResetPassword(User $user)
    {

        $universList = $this->manager->getRepository(Univers::class)->findAll();
        $errors      = null;
        $messages    = null;
        $password    = null; 

        /**
         *  Création d'un mot de passe aléatoire
         */
        for ( $i = 0; $i < 14; $i++ ) {

            $lowerCase = mt_rand(0, 1);

            if ( $lowerCase === 0 ) {
                $rand = mt_rand(97, 122);
            } else {
                $rand = mt_rand(65, 90);
            }

            $password = $password.chr($rand);

        }
                    
        /**
         * Cryptage du mot de passe et envoie mail avec mot de pass décrypté 
         */
        $hash = $this->get('security.password_encoder')->encodePassword($user, $password);
        $this->mailer->resetPassword($user, $password);

        /**
         * Changement mot de passe
         */
        $user->setPassword($hash);
        $this->manager->persist($user);
        $this->manager->flush();
        $this->addFlash('success', 'Votre nouveau mot de passe vous a été envoyé par email !');
        
        return $this->render('User/Security/login.html.twig');

    }
    
}
