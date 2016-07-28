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
    public function __construct($em) {
        $this->em = $em;

        $this->options = [
            'class' => 'nacholibre\RichImageBundle\Entity\RichImage',
            'choice_label' => 'id',
            'multiple' => true,
            'choices' => [],
        ];
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults($this->options);
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        //$builder->resetViewTransformers();
        $em = $this->em;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use($em) {
            $form = $event->getForm();
            $parent = $form->getParent()->getData();

            $submittedImages = $event->getData();

            $repo = $em->getRepository('nacholibre\RichImageBundle\Entity\RichImage');

            $images = $parent->getImages();
            $images->clear();

            $choices = [];

            $position = 0;
            foreach($submittedImages as $imgID) {
                $img = $repo->findOneById($imgID);

                $choices[] = $img;

                if ($img) {
                    $img->setPosition($position);
                    $parent->addImage($img);

                    $em->persist($parent);
                    $em->persist($img);
                    $position++;
                }
            }

            $em->flush();

            $options = $this->options;
            $options['choices'] = $choices;

            $form->getParent()->add('images', EntityType::class, $options);
        });
    }

    public function getParent() {
        return EntityType::class;
    }

    public function getName() {
        return 'rich_image';
    }
}
