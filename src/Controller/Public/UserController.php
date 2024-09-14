<?php

declare(strict_types=1);

namespace App\Controller\Public;


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


class UserController extends AbstractController {

    #[Route('/inscription', name: 'inscription_user')]
    public function insertUser(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger, ParameterBagInterface $params): Response
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




            // On récupère la valeur entrée par l'utilisateur dans le champ password
            $password = $userCreateForm->get('password')->getData();

            try {
                // on hashe le mot de passe
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $password
                );

                // et c'est le mdp hashé qu'on enregistre en bdd (on définit le mdp hashé comme mdp avec le setter)
                $user->setPassword($hashedPassword);

                $user->setRoles(['ROLE_USER']);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Votre profil a été créé !');

            } catch(\Exception $exception) {

                $this->addFlash('error', $exception->getMessage());
            }

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

        $myActivities = $user->getActivitiesParticipate();

        // On retourne une réponse http en html
        return $this->render('public/page/user/user_show_profile.html.twig', [
            'user' => $user,
            'myActivities' => $myActivities
        ]);
    }


    #[Route('/user/delete/{id}', name: 'user_delete_user')]
    public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response {

        $user = $userRepository->find($id);
        $currentUser = $this->getUser();

        if (!$user || $currentUser->getId() !== $user->getId()) {
            $html404 = $this->renderView('public/page/page404.html.twig');
            return new Response($html404, 404);
        }


        try {
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été supprimé !');

        } catch (\Exception $exception) {
            return $this->renderView('public/page/error.html.twig', [
                'errorMessage' => $exception->getMessage()
            ]);
        }

        return $this->redirectToRoute('home');
    }


    #[Route('/user/update/{id}', name: 'user_update_user')]
    public function updateUser(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager, Request $request, User $user, UserPasswordHasherInterface $passwordHasher, SluggerInterface $slugger, ParameterBagInterface $params): Response
    {
        $user = $userRepository->find($id);
        $currentUser = $this->getUser();

        if ($currentUser->getId() !== $user->getId()) {
            /*throw $this->createAccessDeniedException('Vous ne pouvez pas modifier le profil d\'un autre utilisateur.');*/
            $html403 = $this->renderView('admin/page/page403.html.twig');
            return new Response($html403, 403);
        }

        $userUpdateForm = $this->createForm(UserType::class, $user);
        $userUpdateForm->handleRequest($request);


        if ($userUpdateForm->isSubmitted() && $userUpdateForm->isValid()) {

            // On récupère le fichier depuis le formulaire
            $pictureFile = $userUpdateForm->get('profilePicture')->getData();

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
            $password = $userUpdateForm->get('password')->getData();

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

        $userUpdateFormView = $userUpdateForm->createView();

        return $this->render('public/page/user/user_update_user.html.twig', [
            'userUpdateForm' => $userUpdateFormView
        ]);

    }


}
