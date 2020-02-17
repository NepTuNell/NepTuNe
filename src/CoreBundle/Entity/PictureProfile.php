<?php

namespace CoreBundle\Entity;

use CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Logo
 *
 * @ORM\Table(name="pictureProfile")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\PictureProfileRepository")
 * @ORM\HasLifecycleCallbacks
 */
class PictureProfile implements \Serializable
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
     * @ORM\OneToOne(targetEntity="User", inversedBy="pictureProfile")
     * One profile's picture has one user
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="pictureName", type="string", length=255, nullable=true)
     */
    private $pictureName;

    /**
     * @var string
     *
     * @ORM\Column(name="pictureExtension", type="string", length=255, nullable=true)
     */
    private $pictureExtension;

    /**
     * 
     * @Assert\File(
     *   maxSize = "25M",
     *   mimeTypes = {
     *       "image/png",
     *       "image/jpeg",
     *       "image/jpg"
     *   },
     *   mimeTypesMessage = "Format de l'image non supportée, assurez vous d'utiliser ces formats : png, jpg, jpeg, gif.",
     *   maxSizeMessage = "Fichier trop volumineux, l'image ne doit pas excéder 25 mo."
     * )
     * @Assert\Valid()
     * 
     * @var UploadedFile
     */
    private $upload;

    /**
     * @var string
     * 
     * No use in the database
     */
    private $TempFile;

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
     * Set logoName
     *
     * @param string $logoName
     *
     * @return Logo
     */
    public function setPictureName($pictureName) 
    {
        $this->pictureName = $pictureName;
    }

    /**
     * Get pictureName
     *
     * @return string
     */
    public function getPictureName()
    {
        return $this->pictureName;
    }

    /**
     * Set pictureExtension
     *
     * @param string pictureExtension
     *
     * @return 
     */
    public function setPictureExtension($pictureExtension)  
    {
        $this->pictureExtension = $pictureExtension;
    }

    /**
     * Get pictureExtension
     *
     * @return string
     */
    public function getPictureExtension()
    {
        return $this->pictureExtension;
    }

    /**
     * Set TempFile
     * 
     * @param string TempFile
     * @return string
     */
    public function setTempFile($TempFile) 
    {
        $this->TempFile = $TempFile;

        return $this;
    }

    /**
     * Get TempFile
     * 
     * @return string
     */
    public function getTempFile()
    {
        return $this->TempFile;
    }

    public function setUser(User $user) 
    {
        $this->user = $user;

        return $this;
    }

    public function getUser() 
    {
        return $this->user;
    }

    /**
     * Get PictureFullName
     * 
     * @return string
     */
    public function getPictureFullName()
    {
        return $this->getPictureName()."_".$this->getId().".".$this->getPictureExtension();
    }
    

    /**
     * GetFullPath
     * 
     * @return string
     */
    public function getPath()
    {
        return '/home/jimmy/html/FORUM/web/upload/imgProfil';
    }

    public function getUpload()
    {
        return $this->upload;
    }

    /**
     * Set Logo
     * 
     * @param UploadedFile
     */
    public function setUpload(UploadedFile $upload = null)  
    {

        $this->upload = $upload;
        
        /**
         * Si un fichier existe déjà
         */
        if( null !== $this->getPictureName() ) {

            /**
             * Sauvegarde du fichier existant pour suppression après enregistrement 
             * du nouveau fichier dans la base de données
             */
            $this->setTempFile( $this->getPictureName()."_".$this->getId().".".$this->getPictureExtension() );

            /**
             * Passage des attributs à null
             */
            $this->setPictureName(null);
            $this->setPictureExtension(null);

        }

    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     * 
     * Avant le persist en base de données
     */
    public function preUpdate()
    {

        /**
         * Si pas de fichier on arrête
         */
        if( null === $this->upload ) {

            return;
 
        }

        /**
         * On prend la partie gauche après le point dans le nom puis on le concatène avec l'Id attribué (en prepersist si l'enregistrement n'existe pas)
         * Attribution du nom du fichier
         */
        $fileName = strstr($this->upload->getClientOriginalName(), '.', true);
        $this->setPictureName($fileName);

        /**
         * Attribution de l'extension du fichier
         * Si l'extension ne peut être supposée alors mise à .bin par défaut
         * (Tiré de la documentation symfony sur l'utilisation du type UploadedFile)
         */
        if(null === $this->upload->guessExtension()) {

            $this->setPictureExtension('.bin');
    
        } else {

            $this->setPictureExtension($this->upload->guessExtension());

        }

    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     * 
     * Après persist dans la base de données
     */
    public function update()
    {

        /**
         * Si un fichier existe alors suppression
         */
        if( null !== $this->getTempFile() && file_exists( $this->getPath()."/".$this->getTempFile() ) ) {

            unlink($this->getPath()."/".$this->getTempFile());

        }

        /**
         * Déplacement du fichier
         */
        $this->upload->move($this->getPath(), $this->getPictureFullName());

    }

    /**
     * @ORM\PreRemove()
     * 
     * Sauvegarde du nom dans une variable temporaire avant suppression dans la base
     * de données pour suppression du fichier physique via la fonction remove
     */
    public function preRemove()
    {
        $this->setTempFile( $this->getPictureFullName() );
    }
   
    /**
     * @ORM\PostRemove()
     * 
     * Suppression du fichier physique
     */
    public function remove()
    {
        if( file_exists( $this->getPath()."/".$this->getTempFile() ) ) {

            unlink( $this->getPath()."/".$this->getTempFile() );

        }
    }

    /*******************************
     *      Serializer interface
     *******************************/

    /**
     * Serialize
     */
    public function serialize()
    {
        return serialize($this->id);
    }
 
    /**
     * Unserialize
     */
    public function unserialize($serialized)
    {
        $this->id = unserialize($serialized);
 
    }

}

