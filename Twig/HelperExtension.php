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

    private function getClassConfig($configName) {
        //$reader = new AnnotationReader();
        //$data = $reader->getClassAnnotations(new \ReflectionClass($obj));

        //$configName = null;
        //foreach($data as $aObj) {
        //    if (get_class($aObj) == 'nacholibre\RichUploaderBundle\Annotation\RichUploader') {
        //        $configName = $aObj->config;
        //    }
        //}

        $richUploaderConfig = $this->container->getParameter('nacholibre_rich_uploader');

        $config = $richUploaderConfig['mappings'][$configName];

        return $config;
    }

    public function thumb($obj, $configName) {
        $src = $this->src($obj, $configName);
        $filename = $obj->getFileName();

        $parts = explode('/', $src);
        $lastPartIndex = count($parts)-1;
        $parts[$lastPartIndex] = 'thumb_' . $parts[$lastPartIndex];


        return implode('/', $parts);
    }

    public function src($obj, $configName=false) {
        $config = $this->getClassConfig($configName);

        $uriPrefix = $config['uri_prefix'];

        $uriPrefix = rtrim($uriPrefix, '/');

        return sprintf('%s/%s', $uriPrefix, $obj->getFileName());
    }

    public function getName()
    {
        return 'nacholibre_rich_uploader';
    }
}
