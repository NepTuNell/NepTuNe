<?php

/**
 * author: CHU VAN Jimmy
 */
namespace CoreBundle\Entity;

use Serializable;
use CoreBundle\Entity\Post;
use CoreBundle\Entity\Sujet;
use CoreBundle\Entity\PostLike;
use Doctrine\ORM\Mapping as ORM;
use BackendBundle\Entity\Activity;
use BackendBundle\Entity\PostControl;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * User
 *
 * @UniqueEntity( 
 *      fields = {"username"},
 *      errorPath="username",
 *      message="Ce pseudo est déjà utilisé !"
 * )
 * 
 * @UniqueEntity( 
 *      fields = {"email"},
 *      errorPath="email",
 *      message = "Cette adresse email est déjà utilisée !",
 * )
 * 
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\UserRepository")
 */
class User implements UserInterface  
{
    /**
     * ID de l'utilisateur
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Image de profil
     * 
     * @ORM\OneToOne(targetEntity="PictureProfile", cascade={"persist", "remove"}, mappedBy="user")
     * @Assert\Valid()
     * One profile's picture has one user
     */
    private $pictureProfile;

    /**
     * Nom de famille
     *
     * @ORM\Column(name="firstname", type="string", length=255)
     * @Assert\Length(
     *      min=2,
     *      minMessage = "Votre nom est trop court!",
     * )
     * @Assert\NotBlank(message="Veuillez saisir votre nom !")
     */
    private $firstname;

    /**
     * Prénom
     *
     * @ORM\Column(name="lastname", type="string", length=255)
     * @Assert\Length(
     *      min=2,
     *      minMessage = "Votre prénom est trop court!",
     * )
     * @Assert\NotBlank(message="Veuillez saisir votre prénom !")
     */
    private $lastname;

    /**
     * Pseudonyme
     *
     * @ORM\Column(name="username", type="string", length=255, nullable=true, unique=true)
     * @Assert\Length(
     *      min=2,
     *      minMessage = "Votre pseudo est trop court!",
     * )
     * @Assert\NotBlank(message="Veuillez saisir votre pseudo !")
     */
    private $username;

    /**
     * Email
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\Length(
     *      min=2,
     *      minMessage = "Votre adresse email est invalide!",
     * )
     * @Assert\NotBlank(message="Veuillez saisir votre adresse email !")
     */
    private $email;

    /**
     * Mot de passe
     *
     * @ORM\Column(name="password", type="string", length=255)
     * @Assert\Length(
     *      min=2,
     *      minMessage = "Votre mot de passe est trop court!",
     * )
     * @Assert\NotBlank(message="Veuillez saisir votre mot de passe !")
     */
    private $password;

    /**
     * Confirmation du mot de passe
     */
    private $confirm_password;

    /**
     * Compte actif
     *
     * @ORM\Column(name="isActive", type="boolean")
     */
    private $isActive;

    /**
     * Dernière activité
     *
     * @ORM\Column(name="lastActivity", type="datetime", nullable=true)
     */
    private $lastActivity;

    /**
     * Rôle de l'utilisateur
     *
     * @ORM\Column(name="roles", type="array")
     */
    private $roles;

    /**
     * Clé d'activation du compte
     *
     * @ORM\Column(name="registerKey", type="string", length=255, nullable=true)
     */
    private $registerKey;

    /**
     * Sujets que l'utilisateur a créé
     * 
     * @ORM\OneToMany(targetEntity=Sujet::class, mappedBy="user")
     */
    private $sujets;

    /**
     * Commentaires que l'utilisateur a créé
     * 
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="user")
     */
    private $posts;
    
    /**
     * Commentaire que l'utilisateur a aimé
     * 
     * @ORM\OneToMany(targetEntity=PostLike::class, mappedBy="userWhoLiked")
     */
    private $postLiked;

    /**
     * Activités de l'utilisateur
     * 
     * @ORM\OneToMany(targetEntity=Activity::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private $activities;

    /**
     * Activité de l'utilisateur par univers
     * 
     * @ORM\Column(name="activityUnivers", type="boolean", nullable=true)
     */
    private $activityUnivers;

    /**
     * Activité de l'utilisateur par thème
     * 
     * @ORM\Column(name="activityTheme", type="boolean", nullable=true)
     */
    private $activityTheme;

    /**
     * Nombre de j'aime pour les commentaires que l'utilisateur a posté
     * 
     * @ORM\Column(name="activityLike", type="boolean", nullable=true)
     */
    private $activityLike;

    /**
     * Commentaires signalés par l'utilisateur
     * 
     * @ORM\OneToMany(targetEntity=PostControl::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private $postsControl;

    /**
     * Constructeur de la classe
     */
    public function __construct()
    {
        $this->setIsActive(false);
        $this->sujets       = new ArrayCollection();
        $this->activities   = new ArrayCollection();
        $this->postsControl = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get pictureProfile
     */
    public function getPictureProfile()
    {
        return $this->pictureProfile;
    }

    /**
     * Set pictureProfile
     * 
     * @param PictureProfile
     */
    public function setPictureProfile($pictureProfile = null)
    {
        $this->pictureProfile = $pictureProfile;

        return $this;
    }    

    /**
     * Set firstname
     *
     * @param string 
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string  
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set email
     *
     * @param string 
     * @return string
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string  
     * @return string
     */
    public function setconfirmPassword($password)
    {
        $this->confirm_password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getconfirmPassword()
    {
        return $this->confirm_password;
    }

    /**
     * Set isActive
     *
     * @param boolean  
     * @return boolean
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set lastActivity
     *
     * @param \DateTime  
     * @return \DateTime  
     */
    public function setLastActivity($lastActivity)
    {
        $this->lastActivity = $lastActivity;

        return $this;
    }

    /**
     * Get lastActivity
     *
     * @return \DateTime
     */
    public function getLastActivity()
    {
        return $this->lastActivity;
    }

    /**
     * Set registerKey
     *
     * @param string   
     * @return string
     */
    public function setRegisterKey($registerKey)
    {
        $this->registerKey = $registerKey;

        return $this;
    }

    /**
     * Get registerKey
     *
     * @return string
     */
    public function getRegisterKey()
    {
        return $this->registerKey;
    }

    /**
     * Retourne les activités de l'utilisateur
     */
    public function getActivities()
    {
        return $this->activities;
    }

    /**
     * Set activityUnivers
     * 
     * @param boolean
     * @return boolean
     */
    public function setActivityUnivers($activityUnivers)
    {
        $this->activityUnivers = $activityUnivers;

        return $this;
    }

    /**
     * Get activityUnivers
     */
    public function getActivityUnivers()
    {
        return $this->activityUnivers;
    }

    /**
     * Set activityTheme
     * 
     * @param boolean
     * @return boolean
     */
    public function setActivityTheme($activityTheme)
    {
        $this->activityTheme = $activityTheme;

        return $this;
    }
    
    /**
     * Get activityTheme
     */
    public function getActivityTheme()
    {
        return $this->activityTheme;
    }

    /**
     * set activityLike
     * 
     * @param boolean
     * @return boolean
     */
    public function setActivityLike($activityLike)
    {
        $this->activityLike = $activityLike;

        return $this;
    }
    
    /**
     * Get activityLike
     */
    public function getActivityLike()
    {
        return $this->activityLike;
    }

    /**
     * Get postsControl
     *
     * @return ArrayCollection
     */
    public function getPostsControl()
    {
        return $this->postsControl;
    }
    
    /*****************************************
     *      USER INTERFACE'S METHODS
     ****************************************/

    /**
     * Set username
     *
     * @param string 
     * @return string
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string 
     * @return string
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set roles
     *
     * @param array  
     * @return array
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

     /**
     * Undocumented function
     *
     * @return void
     */
    public function eraseCredentials()
    {
    }
    
    /**
     * Undocumented function
     *
     * @return void
     */
    public function getSalt()
    {
    }

}
