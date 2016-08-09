<?php

namespace nacholibre\RichUploaderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Doctrine\Common\Annotations\AnnotationReader;

class RichUploaderType extends AbstractType {
    public function __construct($em, $container) {
        $this->em = $em;
        $this->container = $container;

        $this->options = [
            'multiple' => true,
            'entity_class' => null,
            'size' => 'md',
            'required' => false,
        ];

        $this->multiple = false;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults($this->options);
    }

    public function finishView(FormView $view, FormInterface $form, array $options) {
        parent::finishView($view, $form, $options);

        $name = $form->getName();
        $method = 'get' . ucwords($name);

        $entity = $form->getParent()->getData();
        $type = get_class($entity->$method());

        //$multiple = false;

        //if ($type != 'nacholibre\RichImageBundle\Form\Type\RichImageType') {
        //    $multiple = true;
        //}

        $em = $this->em;
        $repo = $em->getRepository($options['entity_class']);

        $images = [];
        $ids = explode(',', $view->vars['data']);
        foreach($ids as $id) {
            $img = $repo->findOneById($id);
            if ($img) {
                $images[] = $img;
            }
        }

        $view->vars['images_data'] = $images;
        $view->vars['nacholibre_multiple'] = $options['multiple'];
        $view->vars['nacholibre_size'] = $options['size'];
        $view->vars['nacholibre_entity_class'] = $options['entity_class'];

        $reader = new AnnotationReader();
        $data = $reader->getClassAnnotation(new \ReflectionClass(new $options['entity_class']), 'nacholibre\RichUploaderBundle\Annotation\RichUploader');

        $configName = $data->config;

        $richUploaderConfig = $this->container->getParameter('nacholibre_rich_uploader');

        $config = $richUploaderConfig['mappings'][$configName];

        $view->vars['nacholibre_mime_types'] = $config['mime_types'];
        $view->vars['nacholibre_max_size'] = $config['max_size'];
        $view->vars['nacholibre_config_name'] = $configName;

        //foreach($view->children as $child) {
        //    var_dump($child);
        //}
    }

    public function setMultiple($multiple) {
        $this->multiple = $multiple;
    }

    public function getMultiple() {
        return $this->multiple;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        //$builder->resetViewTransformers();
        $em = $this->em;
        $repo = $em->getRepository($options['entity_class']);

        //$options = $this->options;
        //if ($options['multiple'] == false) {
        //    unset($options['choices']);
        //}

        //var_dump($builder);

        $builder->addModelTransformer(new CallbackTransformer(
            function ($filesAsText) use ($options) {
                if (!$filesAsText) {
                    return null;
                }

                if ($options['multiple'] == false) {
                    $file = $filesAsText;
                    return $file->getID();
                }

                $newFiles = [];
                foreach($filesAsText as $file) {
                    $newFiles[] = $file->getID();
                }

                return implode(',', $newFiles);
            },
            function ($textAsFiles) use ($repo, $options) {
                $files = [];

                foreach(explode(',', $textAsFiles) as $fileID) {
                    $file = $repo->findOneById($fileID);

                    if ($file) {
                        $files[] = $file;
                    }
                }

                if ($options['multiple'] == false) {
                    if (count($files) > 0) {
                        return $files[0];
                    } else {
                        return null;
                    }
                }

                return $files;
            }
        ));

        //$builder->add('images', 'nacholibre\RichImageBundle\Form\Type\RichImageType', $options);

        //$name = $builder->getName();
        //$method = 'get' . ucwords($name);

        //$builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use($em, $method) {
        //    $entity = $event->getForm()->getParent()->getData();
        //    $type = get_class($entity->$method());

        //    //if ($type == 'Doctrine\Common\Collections\ArrayCollection' || $type == 'Doctrine\ORM\PersistentCollection') {
        //    //    $this->setMultiple(true);
        //    //}

        //    //var_dump($event->getForm()->getParent()->getData());
        //    //$form = $event->getForm();
        //    //$data = $form->getData();
        //    //exit;
        //});

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use($em, $options) {
            $position = 0;

            $files = $event->getForm()->getData();

            //remove not used images
            $richImageService = $this->container->get('nacholibre.rich_image.service');
            $richImageService->removeNotUsed($options['entity_class']);

            if ($options['multiple'] == false) {
                return;
            }

            foreach($files as $file) {
                $file->setPosition($position);
                $file->setHooked(true);

                $em->persist($file);

                $position++;
            }

            $em->flush();
        });
    }

    public function getParent() {
        return HiddenType::class;
    }

    public function getBlockPrefix() {
        return 'rich_image';
    }
}
