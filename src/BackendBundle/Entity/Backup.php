<?php

/**
 * author: Jimmy 
 */

namespace BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Backup
 *
 * @ORM\Table(name="backup")
 * @ORM\Entity(repositoryClass="BackendBundle\Repository\BackupRepository")
 */
class Backup
{

    /**
     * ID du backup
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Libellé du backup
     *
     * @ORM\Column(name="libelle", type="string", length=255, unique=true)
     */
    private $libelle;

    /**
     * Date à laquelle le backup a été créé
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

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
     * Méthode magique utilisé pour définir l'affichage par défaut d'un objet.
     */
    public function __toString()
    {
        return $this->getDate()->format('d-m-Y')." ".$this->getLibelle();
    }

    /**
     * Set libelle
     *
     * @param string
     *
     * @return Backup
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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Backup
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
    
}

