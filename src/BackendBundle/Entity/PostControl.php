<?php

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
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="postsControl")
     */
    private $user;

    /**
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
     * @param string $user
     *
     * @return Activity
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set Post
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

