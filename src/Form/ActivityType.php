<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\Category;
use App\Entity\PictureGallery;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'label' => 'Titre de l\'activité*'
            ])
            ->add('place', null, [
                'label' => 'Lieu de rendez-vous*'
            ])
            ->add('date', null, [
                'widget' => 'single_text',
                'label' => 'Date*'
            ])
            ->add('time', null, [
                'widget' => 'single_text',
                'label' => 'Heure de rendez-vous*'
            ])
            ->add('description', null, [
                'label' => 'Présentation de l\'activité*'
            ])
            ->add('nbParticipantsMax', null, [
                'label' => 'Un nombre de participants maximum ?'
            ])
            ->add('photo', FileType::class, [
                'mapped' => false, // demande à Symfony de ne pas gérer automatiquement les photos
                'required' => false,
                'label' => 'Photographie*'
            ])
            /* ->add('createdAt', null, [
                'widget' => 'single_text'
            ])
            ->add('updatedAt', null, [
                'widget' => 'single_text'
            ]) */
            ->add('isPublished', null, [
                'label' => 'Publié ?*'
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'nameCategory',
                'label' => 'Catégorie*'
            ])
            ->add('galleries', EntityType::class, [
                'class' => PictureGallery::class,
                'choice_label' => 'pictureName',
                'label' => 'Galerie photo',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                // 'mapped' => false, // demande à Symfony de ne pas gérer automatiquement les photos
            ])
            /* ->add('galleries', CollectionType::class, [
                'entry_type' => PictureGalleryType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
            ]) */
            /* ->add('userAdminOrganizer', EntityType::class, [
                'class' => User::class,
                'label' => 'Organisateur :',
                'data' => $options['userAdminOrganizer'], // Définit la valeur par défaut du champ
                'mapped' => false,
            ]) */

            /* ->add('userParticipant', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
                'multiple' => true,
            ]) */
            ->add('valider', SubmitType::class, [
            'label' => 'Publier l\'activité',
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
            /* 'userAdminOrganizer' => null, */
        ]);
    }
}
