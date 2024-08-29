<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\PictureGallery;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PictureGalleryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pictureName')
            ->add('filePath', FileType::class, [
                'mapped' => false, // demande à Symfony de ne pas gérer automatiquement les photos
            ])
            /* ->add('activities', EntityType::class, [
                'class' => Activity::class,
                'choice_label' => 'id',
                'multiple' => true,
            ]) */
            ->add('ajouter', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PictureGallery::class,
        ]);
    }
}
