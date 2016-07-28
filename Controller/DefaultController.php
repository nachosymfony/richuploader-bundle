<?php

namespace nacholibre\RichImageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

use nacholibre\RichImageBundle\Entity\RichImage;

class DefaultController extends Controller {
    /**
     * @Route("/rich_image/upload", name="nacholibre.rich_image.upload")
     */
    public function uploadAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $images = [];

        foreach($request->files as $file) {
            $image = new RichImage();
            $image->setImageFile($file);
            $image->setPosition(0);

            $images[] = $image;

            $em->persist($image);
            $em->flush();

            //$fileName = md5(uniqid()).'.'.$file->guessExtension();
            //var_dump($fileName);
            //print_r($file);
        }
        //print_R($_FILES);
        //$data = $request->request->get('file');

        return $this->render('nacholibreRichImageBundle::show_image.html.twig', [
            'images' => $images,
        ]);
    }
}
