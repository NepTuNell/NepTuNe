<?php

/**
 * author: CHU VAN Jimmy
 */
namespace BackendBundle\Entity;

use CoreBundle\Entity\User;
use CoreBundle\Entity\Post;
use BackendBundle\Entity\Univers;
use Doctrine\ORM\Mapping as ORM;

/**
 * PostControl
 *
 * @ORM\Table(name="postControl")
 * @ORM\Entity(repositoryClass="BackendBundle\Repository\PostControlRepository")
 */
class PostControl
{
    /**
     * ID du commentaire signalé
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Utilisateur qui a signalé le commentaire
     * 
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="postsControl")
     */
    private $user;

    /**
     * Commentaire signalé
     * 
     * @ORM\ManyToOne(targetEntity=Post::class, inversedBy="postsControl")
     */
    private $post;

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
     * Set user
     *
     * @param User  
     * @return User
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
     * Set Post
     * 
     * @param Post
     * @return Post
     */
    public function setPost(Post $post)
    {
        $this->post = $post;

        return $this;

    }
    
    /**
     * Get Post
     */
    public function getPost()
    {
        return $this->post;
    }

}

