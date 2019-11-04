<?php

namespace CoreBundle\Controller;

use CoreBundle\Entity\User;
use CoreBundle\Services\Mailer;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;


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

        $this->manager = $em;
        $this->mailer  = $mailer;

    }
    
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index()
    {
        return $this->render('User/index.html.twig');
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
        
        if("POST" === $request->getMethod()) {
            
            $form->handleRequest($request);
            
            if ( $form->isSubmitted() && $form->isValid() ) {
                
                if (  $form["password"]->getData() === $form["confirm_password"]->getData() ) {
                    
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
                    return $this->render('User/Registration/register.html.twig', 
                        array('form' => $form->createview())
                    );

                }
                
                $message[] = "Les mots de passe saisies ne sont pas identiques !";
                return $this->render('User/Registration/register.html.twig', [
                    'form'    => $form->createview(),
                    'message' => $message
                ]);
                
            } else  {
                
                $errors = $this->get('validator')->validate($user);
                
                return $this->render('User/Registration/register.html.twig', [
                    'form' => $form->createview(),
                    'errors' => $errors
                ]);
            
            }
                
        } else {
                
            return $this->render('User/Registration/register.html.twig', 
                array('form' => $form->createview())
            );
                
        } 
        
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
        
        $user   = $this->get('security.token_storage')->getToken()->getUser();
        $form   = $this->createFormBuilder($user)
                         ->add('firstname', TextType::class)
                         ->add('lastname', TextType::class)
                         ->add('username', TextType::class)
                         ->add('email', EmailType::class)
                         ->add('password', PasswordType::class, [
                            'mapped' => false
                        ])
                        ->getForm();
        
        $form->handleRequest($request);
        
        if ( "POST" === $request->getMethod()) {
            
            if ($form->isSubmitted() && $form->isValid()) {
                            
                if ( password_verify($form["password"]->getData(), $user->getPassword()) ) {

                    $this->manager->persist($user);
                    $this->manager->flush();

                    return $this->redirectToRoute('user_index');
                } 
                    
                $this->manager->refresh($user);  
                
                $message[] = 'Mot de passe incorrect !';
                return $this->render('User/Profile/edit.html.twig', [
                    'form' => $form->createview(),
                    'message' =>  $message
                ]);
                
            } else {
                
                $errors = $this->get('validator')->validate($user);

                return $this->render('User/Profile/edit.html.twig', [
                    'form' => $form->createview(),
                    'errors' => $errors
                ]);
                
            }      
            
        } else {
            
            return $this->render('User/Profile/edit.html.twig', [
                'form' => $form->createview()
            ]);
            
        }
        
    }

}
