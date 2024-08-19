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



    // Annotation qui permet de créer une route dès que la fonction est appelée
    #[Route('/user/profile/{id}', name: 'user_show_profile')]
    public function showProfile(int $id, UserRepository $userRepository): Response {

        // Dans $user, on stocke le résultat de notre recherche par id dans les données de la table User
        $user = $userRepository->find($id);

        // Si aucun user n'est trouvé avec l'id recherché, on retourne une page et code d'erreur 404
        if (!$user) {
            $html404 = $this->renderView('public/page/page404.html.twig');
            return new Response($html404, 404);
        }

        // On retourne une réponse http en html
        return $this->render('public/page/user/user_show_profile.html.twig', [
            'user' => $user
        ]);
    }


    #[Route('/user/delete/{id}', name: 'user_delete_user')]
    public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response {

        $user = $userRepository->find($id);

        if (!$user) {
            $html404 = $this->renderView('public/page/page404.html.twig');
            return new Response($html404, 404);
        }


        try {
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre compte a été supprimé !');

        } catch (\Exception $exception) {
            return $this->renderView('public/page/error.html.twig', [
                'errorMessage' => $exception->getMessage()
            ]);
        }

        return $this->redirectToRoute('home');
    }


    #[Route('/user/update/{id}', name: 'user_update_user')]
    public function updateUser(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager, Request $request): Response
    {

        $user = $userRepository->find($id);

        $userUpdateForm = $this->createForm(UserType::class, $user);

        $userUpdateForm->handleRequest($request);



        if ($userUpdateForm->isSubmitted() && $userUpdateForm->isValid())

            try {
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Votre compte a été mis à jour');

            } catch(\Exception $exception) {

                $this->addFlash('error', $exception->getMessage());
            }


        $userUpdateFormView = $userUpdateForm->createView();

        return $this->render('public/page/user/user_update_user.html.twig', [
            'userUpdateForm' => $userUpdateFormView
        ]);

    }


}
