<?php

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
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Post::class, inversedBy="postLikes")
     */
    private $post;

    /**
     * @ORM\Column(name="post_like", type="boolean", nullable=true)
     */
    private $like;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $userConcerned;

    /**
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
     */
    public function setPost(Post $post)
    {
        $this->post = $post;

        return $this;
    }

    public function getLike()
    {
        return $this->like;
    }

    public function setLike($like)
    {
        $this->like = $like;

        return $this;
    }

    public function getUserWhoLiked()
    {
        return $this->userWhoLiked;
    }

    public function setUserWhoLiked(User $user)
    {
        $this->userWhoLiked = $user;

        return $this;
    }

    public function getUserConcerned()
    {
        return $this->userConcerned;
    }

    public function setUserConcerned(User $user)
    {
        $this->userConcerned = $user;

        return $this;
    }
    
}

