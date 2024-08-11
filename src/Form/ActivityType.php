<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\Category;
use App\Entity\PictureGallery;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('place')
            ->add('date', null, [
                'widget' => 'single_text'
            ])
            ->add('description')
            ->add('nbParticipantsMax')
            ->add('photo', FileType::class, [
                // demande à Symfony de ne pas gérer automatiquement les photos
                'mapped' => false,
            ])
            ->add('createdAt', null, [
                'widget' => 'single_text'
            ])
            ->add('updatedAt', null, [
                'widget' => 'single_text'
            ])
            ->add('isPublished')
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'id',
            ])
            ->add('gallery', EntityType::class, [
                'class' => PictureGallery::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('userAdminOrganizer', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('userParticipant', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('valider', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
        ]);
    }
}
