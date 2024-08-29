<?php

declare(strict_types = 1);

namespace App\Controller\Admin;


use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


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
    public function insertAdmin(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger, ParameterBagInterface $params): Response
    {

       $user = new User();

       $userCreateForm = $this->createForm(UserType::class, $user);
       $userCreateForm->handleRequest($request);


        if ($userCreateForm->isSubmitted() && $userCreateForm->isValid()) {

            // On récupère le fichier depuis le formulaire
            $pictureFile = $userCreateForm->get('profilePicture')->getData();

            // Si un fichier photo est bien soumis
            if ($pictureFile) {
                // On récupère le nom du fichier
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // On "nettoie" le nom du fichier avec $slugger->slug() (retire les caractères spéciaux...)
                $safeFilename = $slugger->slug($originalFilename);
                // On ajoute un identifiant unique au nom
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $pictureFile->guessExtension();


                try {
                    // On récupère le chemin de la racine du projet
                    $rootPath = $params->get('kernel.project_dir');
                    // On déplace le fichier dans le dossier indiqué dans le chemin d'accès. On renomme
                    $pictureFile->move($rootPath . '/public/uploads', $newFilename);
                } catch (FileException $e) {
                    dd($e->getMessage());
                }

                // On stocke le nom du fichier dans la propriété image de l'entité activity
                $user->setProfilePicture($newFilename);
            }



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



    #[Route('/update', name: 'admin_update_admin')]
    public function updateAdmin(EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $passwordHasher, SluggerInterface $slugger, ParameterBagInterface $params): Response
    {
        $user = $this->getUser();

        $adminUpdateForm = $this->createForm(UserType::class, $user);
        $adminUpdateForm->handleRequest($request);


        if ($adminUpdateForm->isSubmitted() && $adminUpdateForm->isValid()) {

            // On récupère le fichier depuis le formulaire
            $pictureFile = $adminUpdateForm->get('profilePicture')->getData();

            // Si un fichier photo est bien soumis
            if ($pictureFile) {
                // On récupère le nom du fichier
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // On "nettoie" le nom du fichier avec $slugger->slug() (retire les caractères spéciaux...)
                $safeFilename = $slugger->slug($originalFilename);
                // On ajoute un identifiant unique au nom
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $pictureFile->guessExtension();


                try {
                    // On récupère le chemin de la racine du projet
                    $rootPath = $params->get('kernel.project_dir');
                    // On déplace le fichier dans le dossier indiqué dans le chemin d'accès. On renomme
                    $pictureFile->move($rootPath . '/public/uploads', $newFilename);
                } catch (FileException $e) {
                    dd($e->getMessage());
                }

                // On stocke le nom du fichier dans la propriété image de l'entité activity
                $user->setProfilePicture($newFilename);
            }


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