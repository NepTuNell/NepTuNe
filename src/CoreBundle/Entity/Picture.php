<?php

/**
 * author: CHU VAN Jimmy
 */

namespace CoreBundle\Entity;

use CoreBundle\Entity\Post;
use CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Logo
 *
 * @ORM\Table(name="picture")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\PictureRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Picture 
{
    
    /**
     * ID de l'image
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Commentaire de l'image
     * 
     * @ORM\ManyToOne(targetEntity="post", inversedBy="pictures")
     */
    private $post;

    /**
     * Nom de l'image
     *
     * @ORM\Column(name="pictureName", type="string", length=255)
     */
    private $pictureName;

    /**
     * Extension de l'image
     *
     * @ORM\Column(name="pictureExtension", type="string", length=255)
     */
    private $pictureExtension;

    /**
     * Fichier uploadé
     */
    private $file;

    /**
     * Nom temporaire du fichier
     */
    private $tmpFile;

    /**
     * Constructeur de la classe
     * 
     * @param File
     * @param Post
     */
    public function __construct( $file, Post $post )
    {
        $this->setFile($file);
        $this->setPost($post);
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
     * Set pictureName
     *
     * @param string
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
     * @param string
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
     * Set post
     * 
     * @param Post
     */
    public function setPost(Post $post)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Get PictureFullName
     * 
     * @return string
     */
    public function getPictureFullName()
    {
        return $this->getPictureName()."_".$this->getId().$this->getPictureExtension();
    }
 
    /**
     * Get Path
     * 
     * @return string
     */
    public function getPath()
    {
        return '/home/jimmy/html/FORUM/web/upload/images';
    }

    /**
     * Set file
     * 
     * @param File
     */
    public function setFile($file)
    {
        $this->file = $file;
       
    }

    /**
     * Get file
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set tempfile
     * 
     * @param string
     */
    public function setTmpFile($fullName)
    {
        $this->tmpFile = $fullName;

        return $this;
    }

    /**
     * Get tempfile
     */
    public function getTmpFile()
    {
        return $this->tmpFile;
    }

    /**
     * Fonction exécutée avant le persist en base de données
     * 
     * @param File
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preupload($file)
    {
        
        $pos = strripos($this->file['name'], '.');
        $this->setPictureName(substr($this->file['name'], 0, $pos));
        $this->setPictureExtension(substr($this->file['name'], $pos));

    }

    /**
     * Fonction exécutée après le persist en base de données
     * 
     * @param File
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload($file)
    {

        move_uploaded_file( $this->file['tmp_name'], $this->getPath().'/'.$this->getPictureFullName() );
    
    }

    /** 
     * Sauvegarde du nom dans une variable temporaire avant suppression dans la base
     * de données pour suppression du fichier physique via la fonction remove
     * 
     * @ORM\PreRemove()
     */
    public function preRemove()
    {

        $this->setTmpFile( $this->getPictureFullName() );
    }
   
    /**
     * Suppression du fichier physique
     * 
     * @ORM\PostRemove()
     */
    public function remove()
    {

        if( file_exists( $this->getPath()."/".$this->getTmpFile() ) ) {

            unlink( $this->getPath()."/".$this->getTmpFile() );

        }
    }

}

