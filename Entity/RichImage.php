<?php

namespace nacholibre\RichImageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @Vich\Uploadable
 * @ORM\Table(name="nacholibre_rich_image")
 */
class RichImage {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
    * @ORM\Column(type="string", length=255)
    */
    protected $fileName;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     * @Assert\Image(
     *     maxSize="20M",
     * )
     * @Vich\UploadableField(mapping="default", fileNameProperty="fileName")
     *
     * @var Image
     */
    protected $imageFile;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * @ORM\Column(type="integer")
     */
    protected $position;

    function __construct() {
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    public function setUpdatedAt($time) {
        $this->updatedAt = $time;
    }

    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    public function setImageFile(File $image = null) {
        $this->imageFile = $image;
        $this->setUpdatedAt(new \Datetime());
    }

    public function getImageFile() {
        return $this->imageFile;
    }

    public function setFileName($name) {
        $this->fileName = $name;

        return $this;
    }

    public function getFileName() {
        return $this->fileName;
    }

    public function setPosition($position) {
        $this->position = $position;
    }

    public function getPosition() {
        return $this->position;
    }
}
