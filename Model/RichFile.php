<?php

namespace nacholibre\RichUploaderBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
class RichFile implements RichFileInterface {
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
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
    * @ORM\Column(name="hooked", type="boolean")
    */
    protected $hooked;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $mimeType;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    */
    protected $originalFilename;

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

    public function setFileName($name) {
        $this->fileName = $name;

        $this->setUpdatedAt(new \Datetime());

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

    public function setHooked($hooked) {
        $this->hooked = $hooked;
    }

    public function getHooked() {
        return $this->hooked;
    }

    public function setUpdatedAt($time) {
        $this->updatedAt = $time;
    }

    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    public function setMimeType($type) {
        $this->mimeType = $type;
    }

    public function getMimeType() {
        return $this->mimeType;
    }


    public function setOriginalFilename($name) {
        $this->originalFilename = $name;
    }

    public function getOriginalFilename() {
        return $this->originalFilename;
    }
}
