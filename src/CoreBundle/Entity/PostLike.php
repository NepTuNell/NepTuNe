<?php

/**
 * author: CHU VAN Jimmy
 */

namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PostLike
 *
 * @ORM\Table(name="post_like")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\PostLikeRepository")
 */
class PostLike
{
    /**
     * ID
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Post concerné
     * 
     * @ORM\ManyToOne(targetEntity=Post::class, inversedBy="postLikes")
     */
    private $post;

    /**
     * Like
     * 
     * @ORM\Column(name="post_like", type="boolean", nullable=true)
     */
    private $like;

    /**
     * Utilisateur dont le commentaire est aimé
     * 
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $userConcerned;

    /**
     * Utilisateur qui a aimé
     * 
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="postLiked")
     */
    private $userWhoLiked;

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
     * Get post
     * 
     * @return Post
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set post
     * 
     * @param Post
     */
    public function setPost(Post $post)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get like
     */
    public function getLike()
    {
        return $this->like;
    }

    /**
     * Set like
     * 
     * @param like
     */
    public function setLike($like)
    {
        $this->like = $like;

        return $this;
    }

    /**
     * Get userWhoLiked
     */
    public function getUserWhoLiked()
    {
        return $this->userWhoLiked;
    }

    /**
     * Set userWhoLiked
     * 
     * @param User
     */
    public function setUserWhoLiked(User $user)
    {
        $this->userWhoLiked = $user;

        return $this;
    }

    /**
     * Get userConcerned
     */
    public function getUserConcerned()
    {
        return $this->userConcerned;
    }

    /**
     * Set userConcerned
     * 
     * @param User
     */
    public function setUserConcerned(User $user)
    {
        $this->userConcerned = $user;

        return $this;
    }
    
}

