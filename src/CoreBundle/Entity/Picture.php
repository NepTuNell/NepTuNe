<?php

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
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="post", inversedBy="pictures")
     */
    private $post;

    /**
     * @var string
     *
     * @ORM\Column(name="pictureName", type="string", length=255)
     */
    private $pictureName;

    /**
     * @var string
     *
     * @ORM\Column(name="pictureExtension", type="string", length=255)
     */
    private $pictureExtension;

    /**
     * Fichier en lui même
     */
    private $file;

    /**
     * Nom temporaire du fichier
     */
    private $tmpFile;

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
     * @param string $pictureName
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

    public function setPost(Post $post)
    {
        $this->post = $post;

        return $this;
    }

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
     * GetPath
     * 
     * @return string
     */
    public function getPath()
    {
        return '/home/jimmy/html/FORUM/web/upload/images';
    }

    public function setFile($file)
    {
        $this->file = $file;
       
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setTmpFile($fullName)
    {
        $this->tmpFile = $fullName;

        return $this;
    }

    public function getTmpFile()
    {
        return $this->tmpFile;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     * 
     * Avant le persist en base de données
     */
    public function preupload($file)
    {
        
        $pos = strripos($this->file['name'], '.');
        $this->setPictureName(substr($this->file['name'], 0, $pos));
        $this->setPictureExtension(substr($this->file['name'], $pos));

    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     * 
     * Après le persist en base de données
     */
    public function upload($file)
    {

        move_uploaded_file( $this->file['tmp_name'], $this->getPath().'/'.$this->getPictureFullName() );
    
    }

    /**
     * @ORM\PreRemove()
     * 
     * Sauvegarde du nom dans une variable temporaire avant suppression dans la base
     * de données pour suppression du fichier physique via la fonction remove
     */
    public function preRemove()
    {

        $this->setTmpFile( $this->getPictureFullName() );
    }
   
    /**
     * @ORM\PostRemove()
     * 
     * Suppression du fichier physique
     */
    public function remove()
    {

        if( file_exists( $this->getPath()."/".$this->getTmpFile() ) ) {

            var_dump( $this->getPath()."/".$this->getTmpFile() );
            unlink( $this->getPath()."/".$this->getTmpFile() );

        }
    }

}

