<?php

namespace CoreBundle\Controller;

use CoreBundle\Entity\User;
use CoreBundle\Entity\Sujet;
use BackendBundle\Entity\Theme;
use CoreBundle\Services\Mailer;
use BackendBundle\Entity\Univers;
use BackendBundle\Entity\Activity;
use CoreBundle\Services\GoogleGraph;
use CoreBundle\Entity\PictureProfile;
use CoreBundle\Form\PictureProfileType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("home")
 */
class UserController extends Controller
{

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var
     */
    private $token;

    /**
     * @var Mailer
     */
    private $mailer;
    
    /**
     * @var GoogleGraph
     */
    private $googleGraph;

    /**
     * Undocumented variable
     *
     * @var ObjectManager
     * @var Mailer
     */
    public function __construct(ObjectManager $em, TokenStorageInterface $token, Mailer $mailer, GoogleGraph $googleGraph)
    {

        $this->manager      = $em;
        $this->mailer       = $mailer;
        $this->googleGraph  = $googleGraph;
        $this->token        = $token;

    }


    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index()
    {

        $columnChartUniv        = null;
        $columnChartTheme       = null;
        $pieChartLike           = null;
        $data                   = array();
        $user                   = $this->token->getToken()->getUser();
        $universList            = $this->manager->getRepository(Univers::class)->findAll();
        $subjectsLast           = $this->manager->getRepository(Sujet::class)->fetchLastSubjects();
        $subjectsView           = $this->manager->getRepository(Sujet::class)->fetchMostViewSubjects();
        
        if ( $this->isGranted('IS_AUTHENTICATED_FULLY') && $this->isGranted('ROLE_USER') ) {

            if ( $user->getActivityUnivers() ) {
                $columnChartUniv = $this->googleGraph->getActivityUnivers($user, $universList);
            }

            if ( $user->getActivityTheme() ) {
                $columnChartTheme = $this->googleGraph->getActivityTheme($user, $universList);
            }

            if ( $user->getActivityLike() ) {
                $pieChartLike = $this->googleGraph->getPieChartLike($user);
            }

        }
 
        
        
        return $this->render('User/home.html.twig', [

            'universList'           => $universList,
            'subjectsLast'          => $subjectsLast,
            'subjectsView'          => $subjectsView,
            'columnChartUniv'       => $columnChartUniv,
            'columnChartTheme'      => $columnChartTheme,
            'pieChartLike'          => $pieChartLike,
            
        ]);

    }

    /**
     * Undocumented function
     * 
     * @Route("/account/register", name="user_new", methods={"GET", "POST"})
     * @param Request $request
     * @return void
     */
    public function newUser(Request $request)
    {
        
        $user    = new User;
        $form    = $this->createForm('CoreBundle\Form\UserType', $user);
        $univers = $this->manager->getRepository(Univers::class)->findAll();
        $errors  = null;
        $message = null;
        
        /**
         * Vérifiaction type de requête
         */
        if("POST" === $request->getMethod()) {
            
            $form->handleRequest($request);
            
            if ( $form->isSubmitted() && $form->isValid() ) {
                
                /**
                 * Mots de passe saisies
                 */
                if ( $form["password"]->getData() === $form["confirm_password"]->getData() ) {
                    
                    /**
                     *  Création de la clé de confirmation
                     */
                    $registerKey = mt_rand(10, 255); 

                    /**
                     * Cryptage du mot de passe utilisateur
                     * Attribution du rôle utilisateur
                     * Enregistrement BDD
                     */
                    $hash = $this->get('security.password_encoder')->encodePassword($user, $user->getPassword());
                    $user->setPassword($hash);
                    $user->setRegisterKey($registerKey);
                    $user->setRoles(['ROLE_ADMIN']);
                    $this->manager->persist($user);
                    $this->manager->flush();

                    /**
                     * Envoi email confirmation de compte et vérification
                     */
                    $this->mailer->accountCreated($user);
                    $this->addFlash('success', 'Votre compte à bien été enregistré. Un email de confirmation vous a été envoyé !');

                } else { 
                
                    $message[] = "Les mots de passe saisies ne sont pas identiques !";

                }
                
            } else  {
                
                $errors = $this->get('validator')->validate($user);
            
            }
                
        }          

        return $this->render('User/Registration/register.html.twig', 
            array(
                    'form'          => $form->createview(),
                    'errors'         => $errors,
                    'message'       => $message,
                    'universList'   => $univers
                )
        );
        
    }


    /**
     * Undocumented function
     * 
     * @Route("/myaccount/profile", name="user_edit", methods={"GET", "POST"})
     * @param Request $request
     * @return void
     */
    public function editUser (Request $request) 
    {
        
        if ( !$this->isGranted('ROLE_USER') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {
            throw $this->createAccessDeniedException('Veuillez vous connecter !');
        }
        
        $picture = null;
        $errors  = null;
        $message = null;
        $univers = $this->manager->getRepository(Univers::class)->findAll();
        $user    = $this->token->getToken()->getUser();
        $form    = $this->createFormBuilder($user)
                         ->add('firstname', TextType::class)
                         ->add('lastname', TextType::class)
                         ->add('username', TextType::class)
                         ->add('email', EmailType::class)
                         ->add('pictureProfile', PictureProfileType::class, [
                             'required' => false
                         ])
                         ->add('password', PasswordType::class, [
                            'mapped' => false
                        ])
                        ->getForm();
        
        $form->handleRequest($request);
        
        if ( "POST" === $request->getMethod()) {
            
            $picture = $user->getPictureProfile();

            if ($form->isSubmitted() && $form->isValid()) {
                            
                if ( password_verify($form["password"]->getData(), $user->getPassword()) ) {

                    if ( null !== $picture ) {
                        $picture->setUser($user);
                    }
   
                    $this->manager->persist($user);
                    $this->manager->flush();
                    $this->addFlash('success', 'Votre profil a été modifié !');

                } else {
                
                    $message[] = 'Mot de passe incorrect !';

                }
                
            } else {
                
                $errors = $this->get('validator')->validate($user);
                $this->manager->refresh($user);
                
            }      
            
        }

        return $this->render('User/Profile/edit.html.twig', [
            'form'          =>  $form->createview(),
            'universList'   =>  $univers,
            'user'          =>  $user,
            'message'       =>  $message,
            'errors'        =>  $errors,
        ]);
        
    }

    /**
     * Undocumented function
     * 
     * @Route("/myaccount/accueil", name="user_edit_accueil", methods={"GET", "POST"})
     * @param Request $request
     * @return void
     */
    public function editUserAccueil (Request $request) 
    {
        
        if ( !$this->isGranted('ROLE_USER') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {
            throw $this->createAccessDeniedException('Veuillez vous connecter !');
        }
        
        $errors  = null;
        $message = null;
        $univers = $this->manager->getRepository(Univers::class)->findAll();
        $user    = $this->token->getToken()->getUser();
        $form    = $this->createFormBuilder($user)
                         ->add('activityUnivers', CheckboxType::class)
                         ->add('activityTheme', CheckboxType::class)
                         ->add('activityLike', CheckboxType::class)
                         ->getForm();
        
        $form->handleRequest($request);
        
        if ( "POST" === $request->getMethod()) {
            
            if ($form->isSubmitted() && $form->isValid()) {
                
                $this->addFlash('success', 'Votre profil a été modifié !');
                $this->manager->persist($user);
                $this->manager->flush();
 
            } else {
                
                $errors = $this->get('validator')->validate($user);
                
            }      
            
        }

        return $this->render('User/Profile/editAccueil.html.twig', [
            'form'          =>  $form->createview(),
            'universList'   =>  $univers,
            'message'       =>  $message,
            'errors'        =>  $errors,
        ]);
        
    }

    /**
     * Undocumented function
     * 
     * @Route("/myaccount/profile/delete", name="user_delete", methods={"GET", "POST"})
     * @param Request $request
     * @return void
     */
    public function deleteUser (Request $request) 
    {
        
        if ( !$this->isGranted('ROLE_USER') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {
            throw $this->createAccessDeniedException('Veuillez vous connecter !');
        }
        
        $univers =  $this->manager->getRepository(Univers::class)->findAll();
        $user    =  $this->token->getToken()->getUser();
        $message =  null;
       
        if ( 'POST' === $request->getMethod() ) {
            
            if ( null !== $request->request->get('password') && "" !== $request->request->get('password') ) {

                if ( password_verify($request->request->get('password'), $user->getPassword()) ) {
                        
                    $user->setIsActive(false);
                    $this->manager->persist($user);
                    $this->manager->flush();
                    
                    return $this->redirectToRoute('logout');
                    
                } 

                $message[] = 'Mot de passe incorrect !';

            } else {

                $message[] = 'Veuillez saisir votre mot de passe !';

            }
            
        }
        
        return $this->render('User/Security/deleteAccount.html.twig', [
            'universList' => $univers,
            'message'     => $message,
        ]);

    }

    /**
     * Undocumented function
     * 
     * @Route("/myaccount/profile/delete/Picture/{picture}", name="user_delete_picture", methods={"GET", "POST"})
     * @param Request $request
     * @return void
     */
    public function removePictureProfile(PictureProfile $picture)
    {

        $user = $this->token->getToken()->getUser();

        if ( !$this->isGranted('ROLE_USER') || !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {
            throw $this->createAccessDeniedException('Veuillez vous connecter !');
        }

        if ( $picture->getUser() === $user ) {

            $this->manager->remove($picture);
            $this->manager->flush();
            $this->manager->refresh($user);

        }

        return $this->redirectToRoute('user_edit');

    }

    /**
     * @Route("/activity/{user}/{sujet}", name="register_activity", options = {"expose" = true}, methods={"GET"})
     */
    public function registerActivity(User $user, Sujet $sujet)
    {

        if ( $this->isGranted('ROLE_USER') && $this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            //if ( $sujet->getUser !== $user ) {

                $theme    = $sujet->getTheme();
                $univers  = $sujet->getTheme()->getUnivers();
                $activity = new Activity();
                $activity->setDate(new \DateTime);
                $activity->setUser($user);
                $activity->setTheme($theme);
                $activity->setUnivers($univers);
                $this->manager->persist($activity);
                $this->manager->flush();

            //}

        }

        $response = new Response(
            Response::HTTP_OK,
        );

        return $response;

    }

}
