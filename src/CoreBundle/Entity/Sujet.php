<?php

namespace CoreBundle\Entity;

use CoreBundle\Entity\User;
use BackendBundle\Entity\Theme;
use Doctrine\ORM\Mapping as ORM;
use BackendBundle\Entity\Section;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Sujet
 *
 * @ORM\Table(name="sujet")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\SujetRepository")
 */
class Sujet
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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="sujets")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="sujet", cascade={"persist", "remove"})
     */
    private $posts;

    /**
     * @ORM\ManyToOne(targetEntity=Theme::class, inversedBy="sujets")
     */
    private $theme;

    /**
     * @ORM\ManyToOne(targetEntity=Section::class, inversedBy="sujets")
     */
    private $section;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255)
     * @Assert\Length(
     *      min=2,
     *      max=20,
     *      minMessage = "Le libelle est trop court !",
     *      maxMessage = "Le libelle est trop long !"
     * )
     * @Assert\NotBlank(message="Veuillez saisir le libellé !")
     * 
     */
    private $libelle;

     /**
     * @var string
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * CONSTRUCTOR
     */
    public function __construct()
    {
        $this->posts = new ArrayCollection();
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
     * Set libelle
     *
     * @param string $libelle
     *
     * @return Sujet
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set theme 
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Get theme
     * 
     * @return Theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Set section
     */
    public function setSection($section)
    {
        $this->section = $section;

        return $this;
    }

    /**
     * Get section
     * 
     * @return Section
     */
    public function getSection()
    {
        return $this->section;
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
     * Set User
     */
    public function setUser( User $user )
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get date
     * 
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

}

