<?php

namespace BackendBundle\Controller;

use CoreBundle\Entity\User;
use BackendBundle\Entity\Theme;
use CoreBundle\Services\Mailer;
use BackendBundle\Entity\Univers;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * 
 * Theme controller.
 *
 * @Route("admin")
 */
class AdminController extends Controller
{

   /**
     * Undocumented variable
     *
     * @var EntityManagerInterface
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
     * @var UserPasswordEncoderInterface
     */
    private $security;
    
    /**
     * Undocumented variable
     *
     * @var Mailer
     */
    private $mailer;

    /**
     * Undocumented function
     *
     * @param EntityManagerInterface $manager
     * @param TokenStorageInterface $token
     * @param UserPasswordEncoderInterface $security
     * @param Mailer $mailer
     */
    public function __construct(ObjectManager $manager, TokenStorageInterface $token, UserPasswordEncoderInterface $security, Mailer $mailer)
    {
        $this->manager  = $manager;
        $this->token    = $token;
        $this->security = $security;
        $this->mailer   = $mailer;
    }

    /**
     * Lists all entities of BackendBundle.
     *
     * @Route("/dashboard", name="admin_dashboard")
     * @Method("GET")
     */
    public function dashboard()
    {

        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');

        }

        $universList = $this->manager->getRepository(Univers::class)->findAll();

        return $this->render('Admin/dashboard.html.twig', array(

            'universList' => $universList,
            
        ));

    }

    /*************************************************
     *              CONTROLE DU FORUM
     *************************************************/

    /**
     * Undocumented function
     * 
     * @Route("/dashboard/forum", name="admin_control_forum", methods={"GET"})
     * @return void
     */
    public function controlForum()
    {
        
        if ( !$this->isGranted('ROLE_ADMIN') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');

        }

        $univers = $this->manager->getRepository(Univers::class)->findAll();

        return $this->render('Admin/controlForum.html.twig', [
            'universList' => $univers
        ]);

    }

    /*************************************************
     *  AFFICHAGE ET GESTION DES COMPTES UTILISATEUR 
     *************************************************/
    
    /**
     * Undocumented function
     * 
     * @Route("/dashboard/accounts", name="admin_view_accounts", methods={"GET"})
     * @return void
     */
    public function viewAccounts ()
    {
       
        if ( !$this->isGranted('ROLE_ADMIN') ) {
            throw $this->createAccessDeniedException('Vous essayer d\'accéder à des ressources protégées !');
        }
         
        $user    = $this->token->getToken()->getUser();
        $univers = $this->manager->getRepository(Univers::class)->findAll();
        $users   = $this->manager->getRepository(User::class)->getUsersWithoutMe($user->getId());
         
        return $this->render('Admin/controlAccount.html.twig', [
            'userList'      => $users,
            'universList'   => $univers
        ]);
        
    }
    
    /**
     * Undocumented function
     * 
     * @Route("/dashboard/account/{user}/{param}", name="admin_control_account", methods={"GET"})
     * @param User $user
     * @param [type] $param
     * @return void
     */
    public function accountActivated (User $user, $param)
    {
        
        if ( !$this->isGranted('ROLE_ADMIN') ) {
            throw $this->createAccessDeniedException('Vous essayer d\'accéder à des ressources protégées !');
        }
        
        if ($param == 1) {
            
            $user->setIsActive(true);
            $this->manager->persist($user);
            $this->manager->flush();
            $this->mailer->accountActivated($user);
            
        } else {
            
            $user->setIsActive(false);
            $this->manager->persist($user);
            $this->manager->flush();
            $this->mailer->accountDeactivated($user);
            
        }     
        
        return $this->redirectToRoute('admin_show_user_account', ['id' => $user->getId()]);
        
    }
    
    /**
     * @Route("/show/account/{id}", name="admin_show_user_account", methods={"GET"})
     */
    public function showAccount(User $user)
    {

        if ( !$this->isGranted('ROLE_ADMIN') ) {
            throw $this->createAccessDeniedException('Vous essayer d\'accéder à des ressources protégées !');
        }
        
        $univers = $this->manager->getRepository(Univers::class)->findAll();

        return $this->render('Admin/User/account.html.twig', [
            'user'  =>  $user,
            'universList' => $univers,
        ]);

    }

    /**
     * Undocumented function
     * 
     * @Route("/dashboard/roles/{user}/{param}", name="admin_control_role", methods={"GET"})
     * @param User $user
     * @param [type] $param
     * @return void
     */
    public function accountRoles (User $user, $param)
    {
        
        if ( !$this->isGranted('ROLE_ADMIN') ) {
            throw $this->createAccessDeniedException('Vous essayer d\'accéder à des ressources protégées !');
        }
        
        switch ($param)
        {

            case 0:
                $user->setRoles(['ROLE_USER']);
            break;

            case 1:
                $user->setRoles(['ROLE_MODERATOR']);
            break;

            case 2:
                $user->setRoles(['ROLE_ADMIN']);
            break;

        }

        $this->manager->persist($user);
        $this->manager->flush();
        return $this->redirectToRoute('admin_show_user_account', ['id' => $user->getId()]);
        
    }
    
    /**
     * @Route("/user/authorised", name="user_authorised", options = {"expose" = true}, methods={"GET"})
     */
    public function userAuthorised()
    {

        $authorised = false;

        // Si pas d'instance de user alors pas d'ID
        if ( !$this->isGranted('ROLE_USER') && !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            $userId  = 0;

        } else {

            $user    = $this->get('security.token_storage')->getToken()->getUser();
            $userId  = $user->getId();

            if ( $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_MODERATOR') ) {
                $authorised = true;
            } 

        }

        $data = [
            'authorised' => $authorised,
            'userID'     => $userId
        ];
        
        $response = new Response(
            json_encode($data)
        );

        return $response;

    }

}
