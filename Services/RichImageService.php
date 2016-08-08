<?php

namespace nacholibre\RichUploaderBundle\Services;

class RichImageService {
    function __construct($em) {
        $this->em = $em;
    }

    public function removeNotUsed() {
        exit('work here...');
        $em = $this->em;
        $repo = $em->getRepository('nacholibre\RichImageBundle\Entity\RichImage');

        $now = new \Datetime();
        $yesterday = $now->sub(new \DateInterval('P1D'));

        $query = $repo->createQueryBuilder('image')
            ->andWhere('image.hooked = 0')
            ->andWhere('image.updatedAt <= :yesterday')
            ->setParameter('yesterday', $yesterday)
            ->getQuery();

        $images = $query->getResult();

        foreach($images as $image) {
            $em->remove($image);
        }

        $em->flush();
    }
}
