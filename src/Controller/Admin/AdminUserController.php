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


#[Route('/admin/user')]
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


}