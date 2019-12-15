<?php

namespace BackendBundle\Entity;

use CoreBundle\Entity\Sujet;
use Doctrine\ORM\Mapping as ORM;
use BackendBundle\Entity\Univers;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Theme
 *
 * @ORM\Table(name="theme")
 * @ORM\Entity(repositoryClass="BackendBundle\Repository\ThemeRepository")
 * @UniqueEntity(fields={"libelle"}, message="Ce thème existe déjà !")
 */
class Theme
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
     * @var Univers
     *
     * @ORM\ManyToOne(targetEntity=Univers::class, inversedBy="themes")
     */
    private $univers;

    /**
     * @ORM\OneToMany(targetEntity=Section::class, mappedBy="theme")
     */
    private $sections;

    /**
     * @ORM\OneToMany(targetEntity=Sujet::class, mappedBy="theme")
     */
    private $sujets;

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
     * @ORM\Column(name="subdivise", type="boolean")
     */
    private $subdivise;

    /**
     * 
     */
    public function __construct()
    {
        $this->sujets   = new ArrayCollection();
        $this->sections = new ArrayCollection();
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
     * Set univers
     *
     * @param \stdClass $univers
     *
     * @return Theme
     */
    public function setUnivers($univers)
    {
        $this->univers = $univers;

        return $this;
    }

    /**
     * Get univers
     *
     * @return \stdClass
     */
    public function getUnivers()
    {
        return $this->univers;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     *
     * @return Theme
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
    public function setSubdivise($subdivise)
    {
        $this->subdivise = $subdivise;

        return $this;
    }

    /**
     * 
     */
    public function getSubdivise()
    {
        return $this->subdivise;
    }

    /****************************************************
     *      Traitement des collections de Section
     ****************************************************/

    /**
     * Add section
     *
     * @param \BackendBundle\Entity\Section $section
     *
     * @return Section
     */
    public function addSection(\BackendBundle\Entity\Section $section)
    {
        $this->sections[] = $section;

        return $this;
    }

    /**
     * Remove section
     *
     * @param \BackendBundle\Entity\Section $section
     */
    public function removeSection(\BackendBundle\Entity\Section $section)
    {
        $this->sections->removeElement($section);
    }

    /**
     * Get sections
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSections()
    {
        return $this->sections;
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