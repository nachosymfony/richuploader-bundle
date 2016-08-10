<?php

namespace nacholibre\RichUploaderBundle\Twig;

use Doctrine\Common\Annotations\AnnotationReader;

class HelperExtension extends \Twig_Extension {
    public function __construct($container) {
        $this->container = $container;
    }

    public function getFunctions() {
        return [
            new \Twig_SimpleFunction('nacholibre_rich_uploader_src', [$this, 'src']),
            new \Twig_SimpleFunction('nacholibre_rich_uploader_thumb', [$this, 'thumb']),
        ];
    }

    public function thumb($obj, $configName) {
        $src = $this->src($obj, $configName);
        $filename = $obj->getFileName();

        $parts = explode('/', $src);
        $lastPartIndex = count($parts)-1;
        $parts[$lastPartIndex] = 'thumb_' . $parts[$lastPartIndex];


        return implode('/', $parts);
    }

    public function src($obj, $configName) {
        $helper = $this->container->get('nacholibre.rich_uploader.helper');

        $config = $helper->getParameterConfig($configName);

        $uriPrefix = $config['uri_prefix'];

        $uriPrefix = rtrim($uriPrefix, '/');

        return sprintf('%s/%s', $uriPrefix, $obj->getFileName());
    }

    public function getName()
    {
        return 'nacholibre_rich_uploader';
    }
}
