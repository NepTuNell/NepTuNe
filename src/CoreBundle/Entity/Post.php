<?php

/**
 * author: CHU VAN Jimmy
 */
namespace CoreBundle\Entity;

use CoreBundle\Entity\User;
use CoreBundle\Entity\Sujet;
use CoreBundle\Entity\Picture;
use Doctrine\ORM\Mapping as ORM;
use BackendBundle\Entity\PostControl;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Post
 *
 * @ORM\Table(name="post")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\PostRepository")
 */
class Post
{

    /**
     * ID du commentaire
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Sujet du commentaire
     * 
     * @ORM\ManyToOne(targetEntity=Sujet::class, inversedBy="posts")
     */
    private $sujet;

    /**
     * Utilisateur qui a posté le commentaire
     * 
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
     */
    private $user;

    /**
     * Texte du commentaire
     *
     * @ORM\Column(name="commentaire", type="text")
     */
    private $commentaire;

    /**
     * Date à laquelle le commentaire est posté
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * Images associées au commentaire
     * 
     * @ORM\OneToMany(targetEntity=Picture::class, mappedBy="post", cascade={"persist", "remove"})
     */
    private $pictures;

    /**
     * Nombre de j'aime pour ce commentaire
     * 
     * @ORM\OneToMany(targetEntity=PostLike::class, mappedBy="post", cascade={"persist", "remove"})
     */
    private $postLikes;

    /**
     * Nombre de signalement pour ce commentaire
     * 
     * @ORM\OneToMany(targetEntity=PostControl::class, mappedBy="post", cascade={"persist", "remove"})
     */
    private $postsControl;
    
    /**
     * Constructeur de la classe
     */
    public function __construct()
    {
        $this->pictures     = new ArrayCollection();
        $this->postLikes    = new ArrayCollection();
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
     * Set Sujet
     *
     * @param Sujet
     * @return Sujet
     */
    public function setSujet(Sujet $sujet)
    {
        $this->sujet = $sujet;

        return $this;
    }

    /**
     * Get sujet
     *
     * @return Sujet
     */
    public function getSujet()
    {
        return $this->sujet;
    }

    /**
     * Set user
     * 
     * @param User
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     * 
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set commentaire
     *
     * @param string
     * @return Post
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }

    /**
     * Set date
     * 
     * @param Date
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     * 
     * @return date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Ajout d'une image (méthode de type Collection)
     * 
     * @param Picture
     */
    public function addPicture(Picture $picture)
    {
        $this->pictures[] = $picture;
    }

    /**
     * Suppression d'une image (méthode de type Collection)
     * 
     * @param Picture
     */
    public function removePicture(Picture $picture)
    {
        $this->pictures->removeElement($picture);
    }

    /**
     * get postLikes
     * 
     * @return ArrayCollection
     */
    public function getPostLikes()
    {
        return $this->postLikes;
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

}

