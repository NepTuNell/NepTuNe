<?php

/**
 * author: CHU VAN Jimmy
 */
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
use Symfony\Component\Validator\Constraints\Valid;
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
 * Controller des utilisateurs
 * 
 * @Route("home")
 */
class UserController extends Controller
{

    /**
     * Objet utilisé pour stocker l'ObjectManager de Doctrine.
     * Sert à administrer la base de données.
     * 
     * @var ObjectManager
     */
    private $manager;

    /**
     * Objet symfony, utilisé principalement pour la récupération de l'utilisateur courant
     * 
     * @var TokenStorageInterface
     */
    private $token;

    /**
     * Object utilisé pour l'envoi des emails.
     * 
     * @var Mailer
     */
    private $mailer;
    
    /**
     * Object utilisé pour la création des graphiques affichés sur la page d'accueil.
     * 
     * @var GoogleGraph
     */
    private $googleGraph;

    /**
     * Constructeur de la classe
     *
     * @param ObjectManager
     * @param TokenStorageInterface
     * @param Mailer
     * @param GoogleGraph
     */
    public function __construct(ObjectManager $em, TokenStorageInterface $token, Mailer $mailer, GoogleGraph $googleGraph)
    {

        $this->manager      = $em;
        $this->mailer       = $mailer;
        $this->googleGraph  = $googleGraph;
        $this->token        = $token;

    }


    /**
     * Page d'accueil de l'utilisateur
     * 
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
     * Création d'un utilisateur
     * 
     * @param Request
     * @Route("/account/register", name="user_new", methods={"GET", "POST"})
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
                    $user->setRoles(['ROLE_USER']);
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
                    'errors'        => $errors,
                    'message'       => $message,
                    'universList'   => $univers
                )
        );
        
    }


    /**
     * Modification d'un utilisateur
     * 
     * @param Request
     * @Route("/myaccount/profile", name="user_edit", methods={"GET", "POST"})
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
                         ->add('password', PasswordType::class, [
                            'mapped' => false,
                        ])
                        ->add('pictureProfile', PictureProfileType::class, [
                            'required' => false,
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
                    $this->manager->refresh($user);

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
     * Modification de la page d'accueil de l'utilisateur.
     * Sélection des graphiques à afficher.
     * 
     * @param Request
     * @Route("/myaccount/accueil", name="user_edit_accueil", methods={"GET", "POST"})
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
                         ->add('activityUnivers', CheckboxType::class, [
                            'label' => "Voir mon nombre de consultations par univers",
                            'attr'  => [ 
                                'style' => 'margin-right: 5%;'
                            ],
                            'label_attr' => [
                                'class' => "col-12 textColor"
                            ]
                         ])
                         ->add('activityTheme', CheckboxType::class, [
                            'label' => "Voir mon nombre de consultations par thème",
                            'attr'  => [ 
                                'style' => 'margin-right: 5%;'
                            ],
                            'label_attr' => [
                                'class' => "col-12 textColor"
                            ]
                        ])
                         ->add('activityLike', CheckboxType::class, [
                            'label' => "Voir le nombre de mes commentaires aimés",
                            'attr'  => [ 
                                'style' => 'margin-right: 5%;'
                            ],
                            'label_attr' => [
                                'class' => "col-12 textColor"
                            ]
                        ])
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
     * Suppression d'un utilisateur
     * 
     * @param Request
     * @Route("/myaccount/profile/delete", name="user_delete", methods={"GET", "POST"})
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
     * Suppression de l'image de profil de l'utilisateur
     * 
     * @param PictureProfile
     * @Route("/myaccount/profile/delete/Picture/{picture}", name="user_delete_picture", methods={"GET", "POST"})
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
     * Enregistrement de l'activité de l'utilisateur 
     * 
     * @param User
     * @param Sujet
     * @Route("/activity/{user}/{sujet}", name="register_activity", options = {"expose" = true}, methods={"GET"})
     */
    public function registerActivity(User $user, Sujet $sujet)
    {

        if ( $this->isGranted('ROLE_USER') && $this->isGranted('IS_AUTHENTICATED_FULLY') ) {

            if ( $sujet->getUser() !== $user ) {

                $theme    = $sujet->getTheme();
                $univers  = $sujet->getTheme()->getUnivers();
                $activity = new Activity();
                $activity->setDate(new \DateTime);
                $activity->setUser($user);
                $activity->setTheme($theme);
                $activity->setUnivers($univers);
                $this->manager->persist($activity);
                $this->manager->flush();

            }

        }

        $response = new Response(Response::HTTP_OK);

        return $response;

    }

}
