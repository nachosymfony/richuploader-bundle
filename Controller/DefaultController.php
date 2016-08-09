<?php

namespace nacholibre\RichUploaderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\Common\Annotations\AnnotationReader;
//use Symfony\Component\HttpFoundation\File\File;
//use Symfony\Component\HttpFoundation\File\FileValidator;

class DefaultController extends Controller {
    /**
     * @Route("/rich_image/upload", name="nacholibre.rich_image.upload")
     */
    public function uploadAction(Request $request) {
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();
        $validator = $this->get('validator');

        $images = [];

        $entityClass = $request->get('entityClass');

		$reader = new AnnotationReader();
		$metaData = $reader->getClassAnnotation(new \ReflectionClass(new $entityClass), 'nacholibre\RichUploaderBundle\Annotation\RichUploader');

        $configName = $metaData->config;

        $richUploaderConfig = $this->container->getParameter('nacholibre_rich_uploader');

        $config = $richUploaderConfig['mappings'][$configName];

        $uploadDirectory = $config['upload_destination'];

        $fileOptions = [
            'maxSize' => $config['max_size'],
            'mimeTypes' => $config['mime_types'],
            'mimeTypesMessage' => $translator->trans('upload_valid_file_type'),
        ];

        if (count($config['mime_types']) == 1 && in_array('*', $config['mime_types'])) {
            unset($fileOptions['mimeTypes']);
            unset($fileOptions['mimeTypesMessage']);
        }

        $validatorConstraint = new \Symfony\Component\Validator\Constraints\File($fileOptions);

        if (extension_loaded('gd') && function_exists('gd_info')) {
            $imagine = new \Imagine\Gd\Imagine();
        } else if (extension_loaded('imagick')) {
            $imagine = new Imagine\Imagick\Imagine();
        } else if (extension_loaded('gmagick')) {
            $imagine = new Imagine\Gmagick\Imagine();
        } else {
            throw $this->createException('There is no available image manipulation library for the thumbnail generation. You should have gd, imagick or gmagick installed.');
        }

        $imagineMode = \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
        $imagineSize = new \Imagine\Image\Box(160, 160);

        $filesWithErrors = 0;
        $uploadErrors = [];
        foreach($request->files as $file) {
            $errors = $validator->validate($file, $validatorConstraint);

            if (count($errors)) {
                $filesWithErrors++;
                foreach($errors as $error) {
                    $uploadErrors[] = $error;
                }
            } else {
                $fileName = md5(uniqid()).'.'.$file->guessExtension();

                $mimeType = $file->getMimeType();

                $fileLocationAfterUpload = $uploadDirectory . '/'.  $fileName;

                $file->move($uploadDirectory, $fileName);

                $image = new $entityClass;

                $image->setFileName($fileName);

                $image->setPosition(0);
                $image->setHooked(false);
                $image->setMimeType($mimeType);
                $image->setOriginalFilename($file->getClientOriginalName());

                //check if file is image and generate thumbnail
                if ($mimeType && strpos($mimeType, 'image/') !== false) {
                    $imagine->open($fileLocationAfterUpload)
                        ->thumbnail($imagineSize, $imagineMode)
                        ->save($uploadDirectory . '/thumb_'. $fileName)
                    ;
                }


                $images[] = $image;

                $em->persist($image);
                $em->flush();
            }
        }

        return $this->render('nacholibreRichUploaderBundle::show_image.html.twig', [
            'images' => $images,
            'uploadErrors' => $uploadErrors,
            'filesWithErrors' => $filesWithErrors,
            'configName' => $configName,
        ]);
    }
}
