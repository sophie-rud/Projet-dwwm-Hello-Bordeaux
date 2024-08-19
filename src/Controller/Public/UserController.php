<?php

declare(strict_types=1);

namespace App\Controller\Public;


use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;



class UserController extends AbstractController {

    #[Route('/inscription', name: 'inscription_user')]
    public function insertUser(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, Request $request): Response
    {

        $user = new User();

        $userCreateForm = $this->createForm(UserType::class, $user);

        $userCreateForm->handleRequest($request);



        if ($userCreateForm->isSubmitted() && $userCreateForm->isValid())

            try {

                $user->setRoles(['ROLE_USER']);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Votre compte a été créé !');

            } catch(\Exception $exception) {

                $this->addFlash('error', $exception->getMessage());
            }


        $userCreateFormView = $userCreateForm->createView();

        return $this->render('public/page/user/inscription_user.html.twig', [
            'userForm' => $userCreateFormView
        ]);

    }


}

