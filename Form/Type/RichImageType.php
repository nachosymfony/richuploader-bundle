<?php

namespace nacholibre\RichImageBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class RichImageType extends AbstractType {
    public function __construct($em, $container) {
        $this->em = $em;
        $this->container = $container;

        $this->options = [
            'class' => 'nacholibre\RichImageBundle\Entity\RichImage',
            'choice_label' => 'id',
            'multiple' => true,
            'size' => 'md',
            'choices' => null,
        ];
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults($this->options);
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        //$builder->resetViewTransformers();
        $em = $this->em;

        //$options = $this->options;
        //if ($options['multiple'] == false) {
        //    unset($options['choices']);
        //}

        //$builder->add('images', 'nacholibre\RichImageBundle\Form\Type\RichImageType', $options);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use($em) {
            $form = $event->getForm();

            $name = $form->getName();

            $parent = $form->getParent()->getData();

            $submittedImages = $event->getData();

            $repo = $em->getRepository('nacholibre\RichImageBundle\Entity\RichImage');

            $method = 'get' . ucwords($name);
            $images = $parent->$method();
            $images->clear();

            $choices = [];

            $richImageService = $this->container->get('nacholibre.rich_image.service');
            $richImageService->removeNotUsed();

            if ($submittedImages) {
                $position = 0;

                if (is_array($submittedImages)) {
                    foreach($submittedImages as $imgID) {
                        $img = $repo->findOneById($imgID);

                        $choices[] = $img;

                        if ($img) {
                            $img->setPosition($position);
                            $img->setHooked(true);
                            $images[] = $img;
                            //$parent->addImage();

                            $em->persist($parent);
                            $em->persist($img);
                            $position++;
                        }
                    }
                } else {
                    $img = $repo->findOneById($submittedImages);
                    $img->setPosition($position);
                    $img->setHooked(true);

                    $images[] = $img;

                    $em->persist($parent);
                    $em->persist($img);
                }
            }

            $em->flush();

            $options = $this->options;
            $options['choices'] = $choices;

            $form->getParent()->add($name, 'nacholibre\RichImageBundle\Form\Type\RichImageType', $options);
        });
    }

    public function getParent() {
        return EntityType::class;
    }

    public function getBlockPrefix() {
        return 'rich_image';
    }
}
