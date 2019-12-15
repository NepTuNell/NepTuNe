<?php

namespace BackendBundle\Entity;

use BackendBundle\Entity\Theme;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Univers
 *
 * @ORM\Table(name="univers")
 * @ORM\Entity(repositoryClass="BackendBundle\Repository\UniversRepository")
 * @UniqueEntity(fields={"libelle"}, message="Cet univers existe déjà !")
 */
class Univers
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
     * @var Theme
     * 
     * @ORM\OneToMany(targetEntity=Theme::class, mappedBy="univers", cascade={"persist","remove"})
     */
    private $themes;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255, unique=true)
     * @Assert\Length(
     *      min=4,
     *      max=25,
     *      minMessage = "Le libelle est trop court !",
     *      maxMessage = "Le libelle est trop long !"
     * )
     * @Assert\NotBlank(message="Veuillez saisir le libellé !")
     */
    private $libelle;

    /**
     * Magic's methods
     */
    public function __construct()
    {
        $this->themes = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getLibelle();
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
     * @return Univers
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
     * Add theme
     *
     * @param \BackendBundle\Entity\Theme $theme
     *
     * @return Univers
     */
    public function addTheme(\BackendBundle\Entity\Theme $theme)
    {
        $this->themes[] = $theme;

        return $this;
    }

    /**
     * Remove theme
     *
     * @param \BackendBundle\Entity\Theme $theme
     */
    public function removeTheme(\BackendBundle\Entity\Theme $theme)
    {
        $this->themes->removeElement($theme);
    }

    /**
     * Get themes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getThemes()
    {
        return $this->themes;
    }
}
