<?php

namespace nacholibre\RichImageBundle\Form\Type;

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

class RichImageType extends AbstractType {
    public function __construct($em, $container) {
        $this->em = $em;
        $this->container = $container;

        $this->options = [
            //'class' => 'nacholibre\RichImageBundle\Entity\RichImage',
            //'choice_label' => 'id',
            'multiple' => true,
            'size' => 'md',
        ];
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults($this->options);
    }

    public function finishView(FormView $view, FormInterface $form, array $options) {
        parent::finishView($view, $form, $options);

        $em = $this->em;
        $repo = $em->getRepository('nacholibre\RichImageBundle\Entity\RichImage');

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

        //foreach($view->children as $child) {
        //    var_dump($child);
        //}
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        //$builder->resetViewTransformers();
        $em = $this->em;
        $repo = $em->getRepository('nacholibre\RichImageBundle\Entity\RichImage');

        //$options = $this->options;
        //if ($options['multiple'] == false) {
        //    unset($options['choices']);
        //}

        $builder->addModelTransformer(new CallbackTransformer(
            function ($imagesAsText) {
                if (!$imagesAsText) {
                    return null;
                }

                $newImages = [];
                foreach($imagesAsText as $img) {
                    $newImages[] = $img->getID();
                }

                return implode(',', $newImages);
            },
            function ($textAsImages) use ($repo) {
                $images = [];
                foreach(explode(',', $textAsImages) as $imgID) {
                    $img = $repo->findOneById($imgID);

                    if ($img) {
                        $images[] = $img;
                    }
                }

                return $images;
            }
        ));

        //$builder->add('images', 'nacholibre\RichImageBundle\Form\Type\RichImageType', $options);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use($em) {
            $position = 0;

            $images = $event->getForm()->getData();

            //remove not used images
            $richImageService = $this->container->get('nacholibre.rich_image.service');
            $richImageService->removeNotUsed();

            foreach($images as $image) {
                $image->setPosition($position);
                $image->setHooked(true);

                $em->persist($image);

                $position++;
            }

            $em->flush();
        });

        //$builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use($em) {
        //    $form = $event->getForm();

        //    $form->getParent()->add($form->getName() . '_data', EntityType::class, [
        //        'mapped' => false,
        //        'class' => 'nacholibre\RichImageBundle\Entity\RichImage',
        //        'choice_label' => 'id',
        //        'multiple' => true,
        //        'required' => false,
        //        'choices' => $event->getData(),
        //    ]);

        //    //var_dump($form);
        //    //var_dump($form->getData());
        //    //var_dump(count($event->getData()));

        //    //exit;
        //});
        //    $form = $event->getForm();

        //    $name = $form->getName();

        //    $parent = $form->getParent()->getData();

        //    $submittedImages = $event->getData();

        //    $repo = $em->getRepository('nacholibre\RichImageBundle\Entity\RichImage');

        //    $method = 'get' . ucwords($name);
        //    $images = $parent->$method();
        //    $images->clear();

        //    $choices = [];

        //    $richImageService = $this->container->get('nacholibre.rich_image.service');
        //    $richImageService->removeNotUsed();

        //    if ($submittedImages) {
        //        $position = 0;

        //        if (is_array($submittedImages)) {
        //            foreach($submittedImages as $imgID) {
        //                $img = $repo->findOneById($imgID);

        //                $choices[] = $img;

        //                if ($img) {
        //                    $img->setPosition($position);
        //                    $img->setHooked(true);
        //                    $images[] = $img;
        //                    //$parent->addImage();

        //                    $em->persist($parent);
        //                    $em->persist($img);
        //                    $position++;
        //                }
        //            }
        //        } else {
        //            $img = $repo->findOneById($submittedImages);
        //            $img->setPosition($position);
        //            $img->setHooked(true);

        //            $images[] = $img;

        //            $em->persist($parent);
        //            $em->persist($img);
        //        }
        //    }

        //    $em->flush();

        //    $options = $this->options;
        //    $options['choices'] = $choices;

        //    $form->getParent()->add($name, 'nacholibre\RichImageBundle\Form\Type\RichImageType', $options);
        //});
    }

    public function getParent() {
        return TextType::class;
    }

    public function getBlockPrefix() {
        return 'rich_image';
    }
}
