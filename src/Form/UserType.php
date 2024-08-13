<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            /* ->add('roles') */
            ->add('password', PasswordType::class, [
                'hash_property_path' => 'password',
                'mapped' => false,
                ])
            ->add('username')
            ->add('firstName')
            ->add('birthDate', null, [
                'widget' => 'single_text'
            ])
            ->add('phone')
            ->add('presentation')
            /* ->add('activitiesParticipate', EntityType::class, [
                'class' => Activity::class,
                'choice_label' => 'id',
                'multiple' => true,
            ]) */
            ->add('enregistrer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
