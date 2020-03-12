<?php

namespace BackendBundle\Controller;

use DateTime;
use CoreBundle\Entity\Post;
use CoreBundle\Entity\User;
use CoreBundle\Entity\Sujet;
use CoreBundle\Entity\Picture;
use BackendBundle\Entity\Theme;
use CoreBundle\Services\Mailer;
use BackendBundle\Entity\Backup;
use BackendBundle\Entity\Univers;
use BackendBundle\Entity\PostControl;
use Symfony\Component\HttpFoundation\Request;
use BackendBundle\Repository\BackupRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Config\Definition\Exception\Exception;
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


    /*************************************************
     *               TABLEAU DE BORD
     *************************************************/

    /**
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

    /*************************************************
     *          SIGNALEMENT DES COMMENTAIRES
     *************************************************/

    /**
     * @Route("signalement/{post}/{user}", options = {"expose" = true}, name="post_user_reclamation", methods={"GET"})
     */
    public function reclamation(User $user, Post $post)
    {

        if ( !$this->isGranted('ROLE_USER') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {
            throw $this->createAccessDeniedException('Veuillez vous connecter !');
        }

        $postControlExist = $this->manager->getRepository(PostControl::class)->findOneBy([
            'post' =>  $post,
            'user'  =>  $user
        ]);

        if ( !$postControlExist ) {
            
            $postControl = new PostControl;
            $postControl->setUser($user);
            $postControl->setPost($post);
            $this->manager->persist($postControl);
            $this->manager->flush();

        } else {

            $this->manager->remove($postControlExist);
            $this->manager->flush();
        
        }

        $response = new Response(
            RESPONSE::HTTP_OK 
        );

        return $response;

    }

    /**************************************************************
     *      AFFICHAGE ET GESTION DES COMMENTAIRES SIGNALES
     *************************************************************/

    /**
     * @Route("/view/post", name="admin_post_view", options = {"expose" = true})
     * @Method({"GET", "POST"})
     */
    public function view(Request $request)
    {

        $universList = $this->manager->getRepository(Univers::class)->findAll();

        return $this->render('Admin/Post/list.html.twig', [
            'universList'   => $universList,
        ]);

    }

    /**
     * @Route("/signalement/list/{sujet}/{date}/{reclamation}", name="admin_post_list", options = {"expose" = true}, requirements={"sujet"="\d+"})
     * @Method({"GET", "POST"})
     */
    public function listPostReclamation(Request $request, $sujet = null, $date = null, $reclamation = null)
    {

        if ( !$this->isGranted('ROLE_ADMIN') ) {
            throw $this->createAccessDeniedException('Vous essayer d\'accéder à des ressources protégées !');
        }

        $data = array();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $result = $this->manager->getRepository(Post::class)->fetchAllAdmin([
            'sujet' => $sujet,
            'orderByDate' => $date,
            'orderBySignalement' => $reclamation
        ]);

        foreach ( $result as $post ) {

            $pictures = $this->manager->getRepository(Picture::class)->fetchByPost($post['id']);
            
            $elem = [
                
                'comment'  =>  $post,
                'pictures' =>  $pictures,
                 
            ]; 
            
            array_push($data, $elem);

        }

        $commentaires = json_encode($data);

        $response = new Response(
            $commentaires,
        );

        return $response;
    
    }

    /**
     * @Route("/backup/bdd/list", name="admin_backup_restaure")
     * @Method({"GET", "POST"})
     */
    function bddBackUpList(Request $request)
    {
        if ( !$this->isGranted('ROLE_ADMIN') ) {
            throw $this->createAccessDeniedException('Vous essayer d\'accéder à des ressources protégées !');
        }
        
        $universList = $this->manager->getRepository(Univers::class)->findAll();
        $backupUseByForm = new Backup;
        $backupList = $this->manager->getRepository(Backup::class)->fetchAllBackup();
        $form = $this->createForm('BackendBundle\Form\BackupType', $backupUseByForm);
       
        $form->handleRequest($request);

        if ( "POST" === $request->getMethod() ) {

            if ( $form->isSubmitted() && $form->isValid() ) {
                
                $libelle = $form->get('backups')->getData()->getLibelle();
           
                // Restauration du nom des backups dans la bdd
                $directoryPath = "/home/jimmy/html/FORUM/web/backup/BDD/";
                $files = scandir($directoryPath);
          
                if ( null !== $libelle && "" !== $libelle ) {

                    exec('mysql -ujimmy -p2018 jimmy_forum < /home/jimmy/html/FORUM/web/backup/BDD/'.$libelle);
                    $this->addFlash('success', 'La base de donnée a été restaurée !');

                    foreach ( $files as $file ) {

                        $result = $this->manager->getRepository(Backup::class)->fetchBackup($file);
                        
                        if ( 1 !== $result && "." !== $file && ".." !== $file ) {
                            $date = new DateTime(substr($file, 7, 10));
                            $backup = new Backup();
                            $backup->setLibelle($file);
                            $backup->setDate($date);
                            $this->manager->persist($backup);
                            $this->manager->flush();
                        }
                    
                    }

                    $form = $this->createForm('BackendBundle\Form\BackupType', $backupUseByForm);

                }

            }

            $errors = $this->get('validator')->validate($backupUseByForm);

        }

        return $this->render('Admin/controlBackup.html.twig', [
            'form'   =>  $form->createView(),
            'universList' => $universList 
        ]);

    }

    /**
     * @Route("/backup/bdd/create", name="admin_backup_bdd")
     * @Method({"GET", "POST"})
     */
    function dump_MySQL()
    {

        if ( !$this->isGranted('ROLE_ADMIN') ) {
            throw $this->createAccessDeniedException('Vous essayer d\'accéder à des ressources protégées !');
        }

        sleep(1);
        $date  = new DateTime();

        do {
            $libelle = "backup_".$date->format('d-m-Y').'_'.$date->getTimestamp();
            $backupExist = $this->manager->getRepository(Backup::class)->findOneBy([
                'libelle'   => $libelle.'.sql'
            ]);
        } while ( null !== $backupExist );

        $libelle .= '.sql';
        $backup = new Backup;
        $backup->setLibelle($libelle);
        $backup->setDate($date);
        
        $result = exec('mysqldump -ujimmy -p2018 --databases jimmy_forum > /home/jimmy/html/FORUM/web/backup/BDD/'.$libelle);
        $this->manager->persist($backup);
        $this->manager->flush();

        return $this->redirectToRoute('admin_backup_restaure');

    }

}
