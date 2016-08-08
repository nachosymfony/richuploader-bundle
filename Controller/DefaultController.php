<?php

namespace nacholibre\RichUploaderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\Common\Annotations\AnnotationReader;

class DefaultController extends Controller {
    /**
     * @Route("/rich_image/upload", name="nacholibre.rich_image.upload")
     */
    public function uploadAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $images = [];

        $entityClass = $request->get('entityClass');

		$reader = new AnnotationReader();
		$metaData = $reader->getClassAnnotation(new \ReflectionClass(new $entityClass), 'nacholibre\RichUploaderBundle\Annotation\RichUploader');

        $config = 123;

        foreach($request->files as $file) {
            $image = new $entityClass;
            $image->setImageFile($file);
            $image->setPosition(0);
            $image->setHooked(false);

            $images[] = $image;

            $em->persist($image);
            $em->flush();

            //$fileName = md5(uniqid()).'.'.$file->guessExtension();
            //var_dump($fileName);
            //print_r($file);
        }
        //print_R($_FILES);
        //$data = $request->request->get('file');

        return $this->render('nacholibreRichUploaderBundle::show_image.html.twig', [
            'images' => $images,
        ]);
    }
}
