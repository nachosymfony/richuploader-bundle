<?php

namespace nacholibre\RichUploaderBundle\Services;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Imagine {
    const IMAGINE_MODE = \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;

    function __construct($container) {
        $this->container = $container;

        if (extension_loaded('gd') && function_exists('gd_info')) {
            $imagine = new \Imagine\Gd\Imagine();
        } else if (extension_loaded('imagick')) {
            $imagine = new Imagine\Imagick\Imagine();
        } else if (extension_loaded('gmagick')) {
            $imagine = new Imagine\Gmagick\Imagine();
        } else {
            throw new AccessDeniedException('There is no available image manipulation library for the thumbnail generation. You should have gd, imagick or gmagick installed.');
        }

        $this->imagine = $imagine;
    }

    public function createThumbnail($fileLocation, $thumbnailLocation) {
        $imagineSize = new \Imagine\Image\Box(160, 160);

        return $this->imagine->open($fileLocation)
            ->thumbnail($imagineSize, self::IMAGINE_MODE)
            ->save($thumbnailLocation)
        ;
    }
}
