<?php

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Doctrine\Common\Persistence\ObjectManager;
use CoreBundle\Entity\User;


class SecurityController extends Controller
{
    
    /*******************************
     *          ATTRIBUTS
     ******************************/

    /**
     * Undocumented variable
     *
     * @var ObjectManager
     */
    private $manager;
    
    /**
     * Undocumented function
     *
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager) 
    {
        
        $this->manager = $manager;
    
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
        
        return $this->render('User/Security/login.html.twig',  [
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
     * Réinitialisation du mot de passe
     */
    public function resetPassword (Request $request)
    {
        
        if ( !$this->isGranted('ROLE_USER') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {
            throw $this->createAccessDeniedException('Veuillez vous connecter !');
        }
        
        $user    = $this->get('security.token_storage')->getToken()->getUser();
        
        if ( 'POST' === $request->getMethod() ) {
        
            $password        = $user->getPassword();
            $confirmPassword = $request->request->get('password');
            
            if ( !empty($request->request->get('confirmPassword1')) && !empty($request->request->get('confirmPassword2')) && !empty($confirmPassword )) {
                
                if ( password_verify($confirmPassword, $password) ) {
                    
                    if ( $request->request->get('confirmPassword1') === $request->request->get('confirmPassword2') ) {
                        
                        $hash = $this->get('security.password_encoder')->encodePassword($user, $request->request->get('confirmPassword1'));
                        $user->setPassword($hash);
                        $this->manager->persist($user);
                        $this->manager->flush();
                        
                        return $this->redirectToRoute('user_edit');

                    }

                    $message[] = 'Les mots de passe ne sont pas identique !';
                    return $this->render('User/Security/resetPassword.html.twig', [
                        'message' => $message
                    ]);
                    
                }

                $message[] = "Le mot de passe saisi n'est pas correct";
                return $this->render('User/Security/resetPassword.html.twig', [
                    'message' => $message
                ]);
            
            }
            
            $message[] = "Veuillez renseigner tous les champs !";
            return $this->render('User/Security/resetPassword.html.twig', [
                'message' => $message
            ]);
            
        }
        
        return $this->render('User/Security/resetPassword.html.twig');
        
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
    
}
