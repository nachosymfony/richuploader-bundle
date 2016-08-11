<?php

namespace nacholibre\RichUploaderBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Filesystem\Filesystem;

use nacholibre\RichUploaderBundle\Model\RichFileInterface;

class RemoveSubscriber implements EventSubscriber {
    function __construct($container) {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return array(
            //'preUpdate',
            'preRemove',
        );
    }

    //public function postUpdate(LifecycleEventArgs $args)
    //{
    //    $this->index($args);
    //}

    //public function postPersist(LifecycleEventArgs $args)
    //{
    //    $this->index($args);
    //}

    //public function preUpdate(LifecycleEventArgs $args) {
    //    $this->index($args);
    //    //$post->setModifiedAt(new \Datetime());
    //    //$slug = $this->slugger->slugify($post->getTitle());
    //    //$post->setSlug($slug);
    //}

    public function preRemove(LifecycleEventArgs $args) {
        $this->index($args);
    }

    public function index(LifecycleEventArgs $args) {
        $file = $args->getEntity();

        if ($file instanceof RichFileInterface) {
            $helper = $this->container->get('nacholibre.rich_uploader.helper');

            $config = $helper->getEntityClassConfiguration(get_class($file));
            $uploadDestination = $config['upload_destination'];

            $fs = new Filesystem();

            $filename = $file->getFileName();
            $filenameFull = $uploadDestination . '/'. $filename;

            $thumbFilename = 'thumb_'.$filename;
            $thumbFull = $uploadDestination . '/'. $thumbFilename;

            if ($fs->exists($filenameFull)) {
                $fs->remove($filenameFull);
            }

            if ($fs->exists($thumbFull)) {
                $fs->remove($thumbFull);
            }
        }
    }
}
