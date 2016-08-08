<?php

namespace nacholibre\RichImageBundle\Annotation;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class RichUploader extends Annotation {
    /**
     * @var string
     */
    public $config = 'default';
}
