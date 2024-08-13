<?php

declare(strict_types = 1);

namespace App\Controller\Admin;


use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminUserController extends AbstractController {


    #[Route('/admin/user/insert', name: 'admin_user_insert')]
    public function insertAdmin(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, Request $request): Response
    {

       $user = new User();

       $userCreateForm = $this->createForm(UserType::class, $user);

       $userCreateForm->handleRequest($request);



        if ($userCreateForm->isSubmitted() && $userCreateForm->isValid())

            // $password = $request->get('password');

        try {

                // on hashe le mot de passe et c'est le mdp hashé qu'on enregistre en bdd
                // $hashedPassword = $passwordHasher->hashPassword($user, $password);

                // $user->setPassword($hashedPassword);
                $user->setRoles(['ROLE_ADMIN']);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Nouvel utilisateur ajouté');

            } catch(\Exception $exception) {
                //
                $this->addFlash('error', $exception->getMessage());
            }


        $userCreateFormView = $userCreateForm->createView();

        return $this->render('admin/page/user/admin_insert_user.html.twig', [
            'userForm' => $userCreateFormView
        ]);

    }


}