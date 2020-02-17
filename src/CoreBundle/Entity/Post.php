<?php

namespace CoreBundle\Entity;

use CoreBundle\Entity\User;
use CoreBundle\Entity\Sujet;
use CoreBundle\Entity\Picture;
use Doctrine\ORM\Mapping as ORM;
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
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Sujet::class, inversedBy="posts")
     */
    private $sujet;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="text")
     */
    private $commentaire;

    /**
     * @var string
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity=Picture::class, mappedBy="post", cascade={"persist", "remove"})
     */
    private $pictures;

    /**
     * @ORM\OneToMany(targetEntity=PostLike::class, mappedBy="post", cascade={"persist", "remove"})
     */
    private $postLikes;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pictures  = new ArrayCollection();
        $this->postLikes = new ArrayCollection();
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
     * @param Sujet $sujet
     *
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
     * @param string $commentaire
     *
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
     * add picture
     */
    public function addPicture(Picture $picture)
    {
        $this->pictures[] = $picture;
    }

    /**
     * remove picture
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

}

