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
use Symfony\Component\Form\FormError;
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
            'error_bubbling' => false,
        ];
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults($this->options);
    }

    public function finishView(FormView $view, FormInterface $form, array $options) {
        parent::finishView($view, $form, $options);

        $helper = $this->container->get('nacholibre.rich_uploader.helper');
        $em = $this->em;

        $name = $form->getName();
        $method = 'get' . ucwords($name);

        $entity = $form->getParent()->getData();
        $type = get_class($entity->$method());

        $repo = $em->getRepository($options['entity_class']);

        $ids = explode(',', $view->vars['data']);
        $images = $repo->findById($ids);

        $config = $helper->getEntityClassConfiguration($options['entity_class']);
        $configName = $helper->getEntityConfigName($options['entity_class']);

        $view->vars['images_data'] = $images;
        $view->vars['nacholibre_multiple'] = $options['multiple'];
        $view->vars['nacholibre_size'] = $options['size'];
        $view->vars['nacholibre_entity_class'] = $options['entity_class'];
        $view->vars['nacholibre_mime_types'] = $config['mime_types'];
        $view->vars['nacholibre_max_size'] = $config['max_size'];
        $view->vars['nacholibre_config_name'] = $configName;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $em = $this->em;
        $repo = $em->getRepository($options['entity_class']);
        $translator = $this->container->get('translator');

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
                if ($textAsFiles == '') {
                    if ($options['multiple']) {
                        return [];
                    } else {
                        return null;
                    }
                }

                $ids = explode(',', $textAsFiles);

                $files = $repo->findById($ids);

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

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use($em, $options, $translator) {
            $form = $event->getForm();

            $position = 0;

            $files = $event->getForm()->getData();

            //remove not used images
            $richImageService = $this->container->get('nacholibre.rich_uploader.service');
            $richImageService->removeNotUsed($options['entity_class']);

            if ($options['multiple'] && $options['required'] && count($files) == 0) {
                $event->getForm()->addError(new FormError($translator->trans('field_required')));
                return;
            }

            if ($options['multiple'] == false && $options['required'] && !$files) {
                $event->getForm()->addError(new FormError($translator->trans('field_required')));
                return;
            }

            // if multiple is false, $files is actually single RichFile, not an
            // array, so we don't need to iterate over it.
            if ($options['multiple'] === true) {
                foreach($files as $file) {
                    $file->setPosition($position);
                    $file->setHooked(true);

                    $em->persist($file);

                    $position++;
                }
            } else {
                $files->setHooked(true);
                $em->persist($files);
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
