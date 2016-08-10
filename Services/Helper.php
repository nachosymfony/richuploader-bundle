<?php

namespace nacholibre\RichUploaderBundle\Services;

use Doctrine\Common\Annotations\AnnotationReader;

class Helper {
    function __construct($container) {
        $this->container = $container;
    }

    public function getEntityClassConfiguration($entityClass) {
        $configName = $this->getEntityConfigName($entityClass);

        return $this->getParameterConfig($configName);;
    }

    public function getEntityConfigName($entityClass) {
		$reader = new AnnotationReader();
		$metaData = $reader->getClassAnnotation(new \ReflectionClass(new $entityClass), 'nacholibre\RichUploaderBundle\Annotation\RichUploader');

        return $metaData->config;
    }

    public function getParameterConfig($configName) {
        $richUploaderConfig = $this->container->getParameter('nacholibre_rich_uploader');

        return $richUploaderConfig['mappings'][$configName];
    }
}
