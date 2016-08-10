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
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get('translator');
        $validator = $this->get('validator');
        $helper = $this->get('nacholibre.rich_uploader.helper');
        $imagine = $this->get('nacholibre.rich_uploader.imagine');

        $em = $this->getDoctrine()->getManager();

        $entityClass = $request->get('entityClass');
        $config = $helper->getEntityClassConfiguration($entityClass);
        $configName = $helper->getEntityConfigName($entityClass);

        $uploadDirectory = $config['upload_destination'];

        $fileOptions = [
            'maxSize' => $config['max_size'],
            'mimeTypes' => $config['mime_types'],
            'mimeTypesMessage' => $translator->trans('upload_valid_file_type'),
        ];

        // * means all files can be uplaoded, so remove mimeTypes and
        // mimeTypesMessage from the file contraint options
        if (count($config['mime_types']) == 1 && in_array('*', $config['mime_types'])) {
            unset($fileOptions['mimeTypes']);
            unset($fileOptions['mimeTypesMessage']);
        }

        $validatorConstraint = new \Symfony\Component\Validator\Constraints\File($fileOptions);

        $uploadErrors = [];
        $images = [];
        $filesWithErrors = 0;

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
                    $imagine->createThumbnail($fileLocationAfterUpload, $uploadDirectory . '/thumb_'. $fileName);
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
