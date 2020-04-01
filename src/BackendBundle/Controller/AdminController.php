<?php

/**
 * author: CHU VAN Jimmy
 */

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
use CoreBundle\Entity\PictureProfile;
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
 * Theme controller.
 *
 * @Route("admin")
 */
class AdminController extends Controller
{

    /**
     * Objet utilisé pour stocker l'EntityManagerInterface de Doctrine.
     * Sert à administrer la base de données.
     *
     * @var EntityManagerInterface
     */
    private $manager;
    
    /**
     * Objet symfony, utilisé principalement pour la récupération de l'utilisateur courant
     *
     * @var TokenStorageInterface
     */
    private $token;                                                                    
    
    /**
     * Object symfony (cryptage et décryptage)
     *
     * @var UserPasswordEncoderInterface
     */
    private $security;
    
    /**
     * Object utilisé pour l'envoi des emails.
     *
     * @var Mailer@var Mailer
     */
    private $mailer;

    /**
     * Constructeur de la classe
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
     * Affiche le tableau de bord de l'administrateur.
     * 
     * @Route("/dashboard", name="admin_dashboard")
     * @Method("GET")
     */
    public function dashboard()
    {

        if ( !$this->isGranted('ROLE_MODERATOR') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette section !');

        }

        $universList = $this->manager->getRepository(Univers::class)->findAll();

        return $this->render('Admin/dashboard.html.twig', array(

            'universList' => $universList,
            
        ));

    }

    /**
     * Renvoie la page de contrôle global du forum.
     * 
     * @Route("/dashboard/forum", name="admin_control_forum", methods={"GET"})
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
    
    /**
     * Liste des comptes utilisateurs accessible via la partie administration du site.
     * 
     * @Route("/dashboard/accounts", name="admin_view_accounts", methods={"GET"})
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
     * Activation ou désactivation d'un compte utilisateur.
     * 
     * @param User
     * @param int
     * @Route("/dashboard/account/{user}/{param}", name="admin_control_account", methods={"GET"})
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
     * Cette fonction retourne la page d'un utilisateur sélectionner via la section administration du site.
     * 
     * @param User
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
     * Fonction utilisée pour définir le rôle d'un utilisateur.
     * 
     * @param User
     * @param int
     * @Route("/dashboard/roles/{user}/{param}", name="admin_control_role", methods={"GET"})
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
     * Cette fonction permet d'obtenir les autorisations d'un utilisateur (si l'utilisateur est connecté ou non ainsi que son rôle).
     * Si l'utilisateur est connecté et si il est administrateur ou modérateur alors la variable $authorised sera à true.
     * Elle retourne l'id de l'utilisateur ainsi que ses droits.
     * 
     * @Route("/user/authorised", name="user_authorised", options = {"expose" = true}, methods={"GET"})
     */
    public function userAuthorised()
    {

        $authorised = false;

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

    /**
     * Fonction permettant de signaler ou de retirer un signalement sur un commentaire.
     * 
     * @param User
     * @param Post
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

    /**
     * Cette fonction affiche la page qui listera les commentaires signalés.
     * Les commentaires signalés seront retournés avec la fonction ("listPostReclamation").
     * 
     * @param Request
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
     * Ce code renvoi la liste des commentaires signalés pour traitement via Javascript.
     * 
     * @param Request
     * @param Sujet
     * @param Date
     * @param int
     * @Route("/signalement/list/{sujet}/{date}/{reclamation}", name="admin_post_list", options = {"expose" = true}, requirements={"sujet"="\d+"})
     * @Method({"GET", "POST"})
     */
    public function listPostReclamation(Request $request, $sujet = null, $date = null, $reclamation = null)
    {

        if ( !$this->isGranted('ROLE_MODERATOR') ) {
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

        $response = new Response($commentaires);

        return $response;
    
    }

    /**
     * Fonction permettant d'envoyer le formulaire de restauration de la base de données et de traiter la restauration lors de la soumission du formulaire.
     * Lors de la soumission du formulaire en "POST", la fonction créer une restauration des images.
     * 
     * @param Request
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
        $message = null;
       
        $form->handleRequest($request);

        if ( "POST" === $request->getMethod() ) {

            if ( $form->isSubmitted() && $form->isValid() ) {

                if ( null !== $form->get('backups')->getData() ) {

                    $libelle = $form->get('backups')->getData()->getLibelle();
                
                    $directoryPath = "/home/jimmy/html/FORUM/web/backup/BDD/";
                    $files = scandir($directoryPath);

                    $picturesPath = "/home/jimmy/html/FORUM/web/backup/images/";
                    $picturesList = scandir($picturesPath);
                    
                    $picturesProfilPath = "/home/jimmy/html/FORUM/web/backup/imgProfil/";
                    $picturesProfilList = scandir($picturesProfilPath);

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

                    foreach ( $picturesList as $picture ) {

                        if ( '.' !== $picture && '..' !== $picture ) {

                            $pos1 = strripos($picture, '.');
                            $name = substr($picture, 0, $pos1);
                            $ext  = substr($picture, $pos1);
                            
                            $pos2  = strripos($name, '_')+1;
                            $id    = substr($name, $pos2);
                         
                            $imgExist = $this->manager->getRepository(Picture::class)->find($id);
                            
                            if ( null !== $imgExist ) {
                                exec('rsync /home/jimmy/html/FORUM/web/backup/images/"'.$name.$ext.'" /home/jimmy/html/FORUM/web/upload/images/"'.$name.$ext.'"');
                            }
                        
                        }

                    }

                    foreach ( $picturesProfilList as $picture ) {

                        if ( '.' !== $picture && '..' !== $picture ) {

                            $pos1 = strripos($picture, '.');
                            $name = substr($picture, 0, $pos1);
                            $ext  = substr($picture, $pos1);

                            $pos2  = strripos($name, '_')+1;
                            $id    = substr($name, $pos2);
                             
                            $imgExist = $this->manager->getRepository(PictureProfile::class)->find($id);
                            
                            if ( null !== $imgExist ) {
                                exec('rsync /home/jimmy/html/FORUM/web/backup/imgProfil/"'.$name.$ext.'" /home/jimmy/html/FORUM/web/upload/imgProfil/"'.$name.$ext.'"');
                            }
                        
                        }

                    }

                    $form = $this->createForm('BackendBundle\Form\BackupType', $backupUseByForm);

                } else {

                    $message = ['Veuillez sélectionner une sauvegarde à restaurer !'];

                }

            }

        }

        return $this->render('Admin/controlBackup.html.twig', [
            'form'   =>  $form->createView(),
            'universList' => $universList, 
            'message'   => $message
        ]);

    }

    /**
     * Créer un point de restauration du site internet.
     * 
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
        
        exec('mysqldump -ujimmy -p2018 --databases jimmy_forum > /home/jimmy/html/FORUM/web/backup/BDD/'.$libelle);
        exec('rsync -r /home/jimmy/html/FORUM/web/upload/images/ /home/jimmy/html/FORUM/web/backup/images/');
        exec('rsync -r /home/jimmy/html/FORUM/web/upload/imgProfil/ /home/jimmy/html/FORUM/web/backup/imgProfil/');

        $this->manager->persist($backup);
        $this->manager->flush();

        return $this->redirectToRoute('admin_backup_restaure');

    }

}
