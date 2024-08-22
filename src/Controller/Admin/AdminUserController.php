<?php

declare(strict_types = 1);

namespace App\Controller\Admin;


use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/admin')]
class AdminUserController extends AbstractController {

    #[Route('/', name: 'admin_list_users')]
    public function adminListUsers(UserRepository $userRepository): Response {

        $users = $userRepository->findAll();

        // On retourne une réponse http en html
        return $this->render('admin/page/user/admin_list_users.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/insert', name: 'admin_user_insert')]
    public function insertAdmin(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, Request $request): Response
    {

       $user = new User();

       $userCreateForm = $this->createForm(UserType::class, $user);
       $userCreateForm->handleRequest($request);


        if ($userCreateForm->isSubmitted() && $userCreateForm->isValid()) {

            // On récupère la valeur entrée par l'admin dans le champ password
            $password = $userCreateForm->get('password')->getData();


            try {
                // on hashe le mot de passe...
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $password
                );

                // et c'est le mdp hashé qu'on enregistre en bdd (on définit le mdp hashé comme mdp avec le setter)
                $user->setPassword($hashedPassword);
                $user->setRoles(['ROLE_ADMIN']);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Nouvel administrateur ajouté');

            } catch(\Exception $exception) {
                //
                $this->addFlash('error', $exception->getMessage());
            }
        }


        $userCreateFormView = $userCreateForm->createView();

        return $this->render('admin/page/user/admin_insert_user.html.twig', [
            'userForm' => $userCreateFormView
        ]);

    }



    #[Route('/delete/{id}', name: 'admin_delete_user')]
    public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response {

        $user = $userRepository->find($id);

        if (!$user) {
            $html404 = $this->renderView('admin/page/page404.html.twig');
            return new Response($html404, 404);
        }

        try {
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', 'L\'utilisateur a été supprimé !');

        } catch (\Exception $exception) {
            return $this->renderView('admin/page/error.html.twig', [
                'errorMessage' => $exception->getMessage()
            ]);
        }

        return $this->redirectToRoute('admin_list_users');
    }



    #[Route('/update/{id}', name: 'admin_update_admin')]
    public function updateAdmin(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager, Request $request, User $user, UserPasswordHasherInterface $passwordHasher): Response
    {
        /* $user = $userRepository->find($id); */
        $currentUser = $this->getUser();


        if ($currentUser->getId() !== $user->getId()) {
            /*throw $this->createAccessDeniedException('Vous ne pouvez pas modifier le profil d\'un autre utilisateur.');*/
            $html403 = $this->renderView('admin/page/page403.html.twig');
            return new Response($html403, 403);
        }

        $adminUpdateForm = $this->createForm(UserType::class, $user);
        $adminUpdateForm->handleRequest($request);


        if ($adminUpdateForm->isSubmitted() && $adminUpdateForm->isValid()) {

            // On récupère la valeur entrée par l'utilisateur dans le champ password
            $password = $adminUpdateForm->get('password')->getData();

            try {
                // on hashe le mot de passe
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $password
                );

                // et c'est le mdp hashé qu'on enregistre en bdd (on définit le mdp hashé comme mdp avec le setter)
                $user->setPassword($hashedPassword);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Votre profil a été mis à jour');

            } catch(\Exception $exception) {

                $this->addFlash('error', $exception->getMessage());
            }
        }

        $adminUpdateFormView = $adminUpdateForm->createView();

        return $this->render('admin/page/user/admin_update_admin.html.twig', [
            'adminUpdateForm' => $adminUpdateFormView,
            'user' => $user,
        ]);

    }


}