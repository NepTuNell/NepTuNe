<?php

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
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\OneToMany(targetEntity=Sujet::class, mappedBy="section", cascade={"persist", "remove"})
     */
    private $sujets;

    /**
     * @ORM\ManyToOne(targetEntity=Theme::class, inversedBy="sections")
     */
    private $theme;

    /**
     * @var string
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
     * 
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
     * @param string $libelle
     *
     * @return Section
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
     * 
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * 
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /****************************************************
     *      Traitement des collections de Sujet
     ****************************************************/

    /**
     * Add sujet
     *
     * @param \CoreBundle\Entity\Sujet $sujet
     *
     * @return Sujet
     */
    public function addSujet(\CoreBundle\Entity\Sujet $sujet)
    {
        $this->sujets[] = $sujet;

        return $this;
    }

    /**
     * Remove sujet
     *
     * @param \CoreBundle\Entity\Sujet $sujet
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

