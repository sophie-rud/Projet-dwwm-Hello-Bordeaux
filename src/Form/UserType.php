<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', null, [
                'attr' => [
                    'class' => 'custom-input',
                    'type' => 'email',
                ],
                'label_attr' => ['class' => 'custom-label'],
                'label' => 'Email*',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'L\'email ne peut pas être vide.',
                    ]),
                    new Assert\Email([
                        'message' => 'Veuillez entrer une adresse email valide.',
                    ]),
                ],
            ])
            /* ->add('roles') */
            ->add('password', PasswordType::class , [
                /*'hash_property_path' => 'password', // autre manière de hasher le mdp, nous l'avons fait dans le controller
                'mapped' => false,*/
                'attr' => ['class' => 'custom-input'],
                'label_attr' => ['class' => 'custom-label'],
                'label' => 'Mot de passe*',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le mot de passe ne peut pas être vide.',
                    ]),
                    new Assert\Length([
                        'min' => 8,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^(?=.*[A-Z])(?=.*\d).{8,}$/',
                        'message' => 'Le mot de passe doit contenir au moins une majuscule et un chiffre.',
                    ]),
                ],
            ])
            ->add('username', null, [
                'attr' => ['class' => 'custom-input'],
                'label_attr' => ['class' => 'custom-label'],
                'label' => 'Pseudo*',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom d\'utilisateur ne peut pas être vide.',
                    ]),
                    new Assert\Length([
                        'min' => 3,
                        'minMessage' => 'Le nom d\'utilisateur doit contenir au moins {{ limit }} caractères.',
                        'max' => 20,
                        'maxMessage' => 'Le nom d\'utilisateur ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z0-9_]+$/',
                        'message' => 'Le nom d\'utilisateur ne peut contenir que des lettres, des chiffres et des underscores.',
                    ]),
                ],
            ])
            ->add('firstName', null, [
                'attr' => ['class' => 'custom-input'],
                'label_attr' => ['class' => 'custom-label'],
                'label' => 'Prénom',
                'constraints' => [
                    new Assert\Length([
                        'min' => 2,
                        'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères.',
                        'max' => 50,
                        'maxMessage' => 'Le prénom ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\- ]+$/',
                        'message' => 'Le prénom ne peut contenir que des lettres, des espaces et des tirets.',
                    ]),
                ],
            ])
            ->add('birthDate', null, [
                'widget' => 'single_text', //input de type "date"
                'attr' => [
                    'class' => 'custom-input',
                    'min' => '1920-01-01', // définit une date maximum
                    'max' => (new \DateTime())->format('Y-m-d'), // définit une date minimum (jour actuel)
                ],
                'label_attr' => ['class' => 'custom-label'],
                'label' => 'Date de naissance*',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La date de naissance ne peut pas être vide.',
                    ]),
                    new Assert\Date([
                        'message' => 'Veuillez entrer une date valide.',
                    ]),
                    new Assert\LessThan([
                        'value' => (new \DateTime())->format('Y-m-d'),
                        'message' => 'La date de naissance doit être dans le passé.',
                    ]),
                ],
            ])
            ->add('phone', null, [
                'attr' => [
                    'class' => 'custom-input',
                    'type' => 'tel',  // type: numéro de téléphone
                    'pattern' => '[0-9]{10}',  // Exige un format de 10 chiffres
                    'placeholder' => '06XXXXXXXX', //Exemple
                ],
                'label_attr' => ['class' => 'custom-label'],
                'label' => 'Numéro de téléphone',
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^(\d{10})$/',
                        'message' => 'Le numéro de téléphone doit comporter 10 chiffres.',
                    ]),
                ],
            ])
            ->add('presentation', null, [
                'attr' => ['class' => 'custom-input'],
                'label_attr' => ['class' => 'custom-label'],
                'label' => 'Présentation',
                'constraints' => [
                        new Assert\Length([
                        'max' => 1000, // Limite de caractères
                        'maxMessage' => 'La présentation ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            /* ->add('activitiesParticipate', EntityType::class, [
                'class' => Activity::class,
                'choice_label' => 'id',
                'multiple' => true,
            ]) */
            ->add('profilePicture', FileType::class, [
            'mapped' => false,
            'attr' => ['class' => 'custom-input'],
            'label_attr' => ['class' => 'custom-label'],
            'label' => 'Choisir une image de profil*'
            ])
            ->add('enregistrer', SubmitType::class,[
                'attr' => ['class' => 'submit-btn'],
                'label' => 'Valider'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
