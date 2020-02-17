<?php

namespace BackendBundle\Entity;

use CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use BackendBundle\Entity\Univers;

/**
 * Activity
 *
 * @ORM\Table(name="activity")
 * @ORM\Entity(repositoryClass="BackendBundle\Repository\ActivityRepository")
 */
class Activity
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="activities")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Univers::class, inversedBy="activities")
     */
    private $univers;

    /**
     * @ORM\ManyToOne(targetEntity=Theme::class, inversedBy="activities")
     */
    private $theme;

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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Activity
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
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
     * Get univers
     * 
     * @return Univers
     */
    public function getUnivers()
    {
        return $this->univers;
    }

    /**
     * Set univers
     * 
     * @return Univers
     */
    public function setUnivers(Univers $univers)
    {
        $this->univers = $univers;
    }

    /**
     * Get Theme
     * 
     * @return Theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Set Theme
     * 
     * @return Theme
     */
    public function setTheme(Theme $theme)
    {
        $this->theme = $theme;

        return $this;
    }

}

