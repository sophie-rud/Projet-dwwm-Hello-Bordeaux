<?php

declare(strict_types = 1);

namespace App\Controller\Admin;


use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/admin/user')]
#[IsGranted('ROLE_ADMIN')]
class AdminUserController extends AbstractController {


    #[Route('/', name: 'admin_list_users')]
    public function adminListUsers(UserRepository $userRepository): Response {

        $users = $userRepository->findAll();

        // On retourne une réponse http en html
        return $this->render('admin/page/user/admin_list_users.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/profile/{id}', name: 'admin_show_profile')]
    public function showAdminProfile(int $id, UserRepository $userRepository): Response {

        // Dans $user, on stocke le résultat de notre recherche par id dans les données de la table User
        $user = $userRepository->find($id);
        $currentUser = $this->getUser();

        // Si aucun user n'est trouvé avec l'id recherché, on retourne une page et code d'erreur 404
        if (!$user) {
            $html404 = $this->renderView('admin/page/page404.html.twig');
            return new Response($html404, 404);
        }

        if ($currentUser->getId() !== $id) {
            $html403 = $this->renderView('admin/page/page403.html.twig');
            return new Response($html403, 403);
        }

        // On retourne une réponse http en html
        return $this->render('admin/page/user/admin_show_profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/insert', name: 'admin_insert_user')]
    #[IsGranted('ROLE_ADMIN')]
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


                // Si l'utilisateur courant est SUPERADMIN, il peut créer un admin
                if ($this->isGranted('ROLE_SUPERADMIN')) {
                    $user->setRoles(['ROLE_ADMIN']);
                } else {
                    // Sinon, s'il est admin, il crée un utilisateur simple
                    $user->setRoles(['ROLE_USER']);
                }


                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Nouvel administrateur ajouté');

                return $this->redirectToRoute('admin_list_users');

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
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response {

        $user = $userRepository->find($id);
        $currentUser = $this->getUser();

        if (!$user) {
            $html404 = $this->renderView('admin/page/page404.html.twig');
            return new Response($html404, 404);
        }

        if ($currentUser->getId() !== $id) {
            $html403 = $this->renderView('admin/page/page403.html.twig');
            return new Response($html403, 403);
        }

        // On récupère le rôle de l'utilisateur de l'id recherché
        $roles = $user->getRoles();

        // On vérifie si l'utilisateur courant (qui essaie de supprimer) est un admin ou un super admin
        if (in_array('ROLE_ADMIN', $roles) && !$this->isGranted('ROLE_SUPERADMIN')) {

            $this->addFlash('error', 'Vous n\'avez pas le droit de supprimer un administrateur.');
            return $this->redirectToRoute('admin_list_users');
        }


        try {
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', 'L\'utilisateur a été supprimé !');

        } catch (\Exception $exception) {
            /*return $this->renderView('admin/page/error.html.twig', [
                'errorMessage' => $exception->getMessage()
            ]);*/
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_list_users');
    }


    // Route appelée au lancement de la méthode
    #[Route('/block/{id}', name: 'admin_block_user')]
    // Cette méthode est accessible unique aux utilisateurs ayant au moins le rôle ADMIN
    #[IsGranted('ROLE_ADMIN')]
    public function blockUser(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response {

        // On récupère l'id de l'utilisateur à bloquer
        $userToBlock = $userRepository->find($id);
        // On récupère l'utilisateur actuellement connecté
        $currentUser = $this->getUser();

        // Si l'utilisateur à bloquer n'existe pas, renvoie une page 404
        if (!$userToBlock) {
            $html404 = $this->renderView('admin/page/page404.html.twig');
            return new Response($html404, 404);
        }

        // On vérifie si l'utilisateur actuel a les rôles ADMIN ou SUPER_ADMIN
        if ($currentUser && ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN'))) {
            // Récupère les rôles de l'utilisateur à bloquer
            $roles = $userToBlock->getRoles();

            // Si l'utilisateur actuel a le rôle de SUPER_ADMIN
            if ($this->isGranted('ROLE_SUPER_ADMIN')) {
                // Les SUPER_ADMIN peuvent bloquer les ADMIN et les USER
                if (in_array('ROLE_ADMIN', $roles) || in_array('ROLE_USER', $roles)) {
                    // Si l'utilisateur à bloquer est actif (n'est pas déjà bloqué)
                    if ($userToBlock->isActive()) {
                        // On bloque l'utilisateur (en changeant son statut IsActive en false)
                        $userToBlock->setActive(false);
                        // On enregistre cette modification dans la base de données
                        $entityManager->flush();
                        // On affiche un message de succès
                        $this->addFlash('success', 'L\'utilisateur a été bloqué');

                        // Sinon (si l'utilisateur est déjà bloqué)
                    } else {
                        // On affiche un message flash indiquant que l'utilisateur est déjà bloqué
                        $this->addFlash('warning', 'L\'utilisateur est déjà bloqué');
                    }

                    // Sinon (si l'utilisateur ne peut pas être bloqué en raison de son rôle)
                } else {
                    $this->addFlash('error', 'Cet utilisateur ne peut pas être bloqué.');
                }

                // Vérifie si l'utilisateur a uniquement le rôle 'ROLE_USER' (car les ADMIN ne peuvent bloquer que les USER)
            } else if (in_array('ROLE_USER', $roles) && !in_array('ROLE_ADMIN', $roles) && !in_array('ROLE_SUPER_ADMIN', $roles)) {
                // Vérifie si l'utilisateur n'est pas déjà bloqué
                if ($userToBlock->isActive()) {
                    // On bloque l'utilisateur (en changeant son statut IsActive en false)
                    $userToBlock->setActive(false);
                    // On enregistre cette modification dans la base de données
                    $entityManager->flush();

                    // On affiche un message de succès
                    $this->addFlash('success', 'L\'utilisateur a été bloqué');
                } else {
                    // Sinon, (si l'utilisateur est déjà bloqué), on affiche un message flash pour l'indiquer
                    $this->addFlash('warning', 'L\'utilisateur est déjà bloqué');
                }
            } else {
                // Affiche un message flash d'erreur si les rôles ne permettent pas de bloquer l'utilisateur
                $this->addFlash('error', 'Cet utilisateur ne peut pas être bloqué.');
            }
        }
        // Redirige vers la liste des utilisateurs après avoir bloquer (ou tenté de bloquer) l'utilisateur
        return $this->redirectToRoute('admin_list_users');
    }


    #[Route('/unblock/{id}', name: 'admin_unblock_user')]
    #[IsGranted('ROLE_ADMIN')]
    public function unblockUser(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response {

        $userToUnblock = $userRepository->find($id);
        $currentUser = $this->getUser();

        if (!$userToUnblock) {
            $html404 = $this->renderView('admin/page/page404.html.twig');
            return new Response($html404, 404);
        }

        if ($currentUser && $this->isGranted('ROLE_ADMIN')) {
            $roles = $userToUnblock->getRoles();

            // Vérifie si l'utilisateur a uniquement le rôle 'ROLE_USER'
            if (in_array('ROLE_USER', $roles) && !in_array('ROLE_ADMIN', $roles) && !in_array('ROLE_SUPER_ADMIN', $roles)) {
                // Vérifie si l'utilisateur est déjà bloqué
                if (!$userToUnblock->isActive()) {
                    $userToUnblock->setActive(true);
                    $entityManager->flush();

                    $this->addFlash('success', 'L\'utilisateur a été débloqué');
                } else {
                    $this->addFlash('warning', 'L\'utilisateur est déjà bloqué');
                }
            } else {
                $this->addFlash('error', 'Cet utilisateur ne peut pas être débloqué.');
            }
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

                return $this->redirectToRoute('admin_list_users');

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