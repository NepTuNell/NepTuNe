<?php

/**
 * author: CHU VAN Jimmy
 */
namespace BackendBundle\Entity;

use CoreBundle\Entity\Sujet;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Section
 *
 * @ORM\Table(name="section")
 * @ORM\Entity(repositoryClass="BackendBundle\Repository\SectionRepository")
 */
class Section
{
    /**
     * ID de la section
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * Sujets associés à la section
     * 
     * @ORM\OneToMany(targetEntity=Sujet::class, mappedBy="section", cascade={"persist", "remove"})
     */
    private $sujets;

    /**
     * Thème auquel la section est associée
     * 
     * @ORM\ManyToOne(targetEntity=Theme::class, inversedBy="sections")
     */
    private $theme;

    /**
     * Libellé de la section
     *
     * @ORM\Column(name="libelle", type="string", length=255, unique=false)
     * @Assert\Length(
     *      min=1,
     *      max=25,
     *      minMessage = "Le libelle est trop court !",
     *      maxMessage = "Le libelle est trop long !"
     * )
     * @Assert\NotBlank(message="Veuillez saisir le libellé !")
     */
    private $libelle;

    /**
     * Constructeur de la classe.
     */
    public function __construct()
    {
        $this->sujets = new ArrayCollection();
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
     * @param string 
     * @return string
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
     * 
     * @param Theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Get theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /****************************************************
     *      Traitement des collections de Sujet
     ****************************************************/

    /**
     * Ajout d'un sujet (méthode relative au traitement d'une collection)
     *
     * @param \CoreBundle\Entity\Sujet 
     */
    public function addSujet(\CoreBundle\Entity\Sujet $sujet)
    {
        $this->sujets[] = $sujet;

        return $this;
    }

    /**
     * Suppression d'un sujet (méthode relative au traitement d'une collection)
     *
     * @param \CoreBundle\Entity\Sujet  
     */
    public function removeSujet(\CoreBundle\Entity\Sujet $sujet)
    {
        $this->sujets->removeElement($sujet);
    }

    /**
     * Get sujets
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSujets()
    {
        return $this->sujets;
    }

}

