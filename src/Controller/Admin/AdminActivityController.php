<?php

// lié au typage
declare(strict_types=1);

namespace App\Controller\Admin;


use App\Entity\Activity;
use App\Form\ActivityType;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


// la route donnée avant la classe vient en préfixe de toutes les routes des méthodes appelées dans la classe
#[Route('admin/activities')]
class AdminActivityController extends AbstractController {

    // Annotation qui permet de créer une route dès que la fonction adminListActivities est appelée
    #[Route('/', name: 'admin_list_activities')]
    public function adminListActivities(ActivityRepository $activityRepository): Response {

        // On stocke le résultat de notre select, findAll(), sur la table Activity
        $activities = $activityRepository->findAll();

        // On retourne une réponse http en html
        return $this->render('admin/page/activity/admin_list_activities.html.twig', [
            'activities' => $activities
        ]);
    }


    // Annotation qui permet de créer une route dès que la fonction showActivity est appelée
    #[Route('/show/{id}', name: 'admin_show_activity')]
    public function showActivity(int $id, ActivityRepository $activityRepository): Response {

        // Dans $activity, on stocke le résultat de notre recherche par id dans les données de la table Activity
        $activity = $activityRepository->find($id);

        // Si aucune activité n'est trouvée avec l'id recherché, on retourne une page et code d'erreur 404
        if (!$activity || !$activity->getisPublished()) {
            $html404 = $this->renderView('admin/page/page404.html.twig');
            return new Response($html404, 404);
        }

        // On retourne une réponse http en html
        return $this->render('admin/page/activity/admin_show_activity.html.twig', [
            'activity' => $activity
        ]);
    }


    // Annotation qui permet de créer une route dès que la fonction insertActivity est appelée
    #[Route('/insert', name: 'admin_insert_activity')]
    public function insertActivity(EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger, ParameterBagInterface $params): Response {
        // On crée une nouvelle instance de la classe Activity (de l'entité)
        $activity = new Activity();

        // On génère une instance de la classe de gabarit de formulaire, et on la lie avec l'entité Activity
        $activityCreateForm = $this->createForm(ActivityType::class, $activity);

        // On lie le formulaire à la requête
        $activityCreateForm->handleRequest($request);


        // Si le formulaire est soumis (posté) et complété avec des données valides (qui respectent les contraintes de champs)
        if ($activityCreateForm->isSubmitted() && $activityCreateForm->isValid()) {
            // On récupère le fichier depuis le formulaire
            $photoFile = $activityCreateForm->get('photo')->getData();

            // Si un fichier photo est bien soumis
            if ($photoFile) {
                // On récupère le nom du fichier
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                // On "nettoie" le nom du fichier avec $slugger->slug() (retire les caractères spéciaux...)
                $safeFilename = $slugger->slug($originalFilename);
                // On ajoute un identifiant unique au nom
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();


                try {
                    // On récupère le chemin de la racine du projet
                    $rootPath = $params->get('kernel.project_dir');
                    // On déplace le fichier dans le dossier indiqué dans le chemin d'accès. On renomme
                    $photoFile->move($rootPath . '/public/uploads', $newFilename);
                } catch (FileException $e) {
                    dd($e->getMessage());
                }

                // On stocke le nom du fichier dans la propriété image de l'entité article
                $activity->setPhoto($newFilename);
            }

            // On prépare la requête sql,
            $entityManager->persist($activity);
            // puis on l'exécute.
            $entityManager->flush();

            // On affiche un message pour informer l'utilisateur du succès de la requête
            $this->addFlash('success', 'Activité enregistrée !');

            // On fait une redirection sur la page du formulaire d'insertion
            // plus logique de retomber sur la liste des articles ou page de mise à jour des articles
            return $this->redirectToRoute('admin_list_activities');
        }

        // Avec la méthode createView(), on génère une instance de 'vue' du formulaire, pour le render
        $activityCreateFormView = $activityCreateForm->createView();

        // On retourne une réponse http (le fichier html du formulaire)
        return $this->render('admin/page/activity/admin_insert_activity.html.twig', [
            'activityForm' => $activityCreateFormView
        ]);
    }


    #[Route('/delete/{id}', name: 'admin_delete_activity')]
    public function deleteActivity(int $id, ActivityRepository $activityRepository, EntityManagerInterface $entityManager): Response {

        // Dans $activity, on stocke le résultat de notre recherche par id dans les données de la table Activity
        $activity = $activityRepository->find($id);

        // Si aucune activité n'est trouvée avec l'id recherché, on retourne une page et code d'erreur 404
        if (!$activity || !$activity->getisPublished()) {
            $html404 = $this->renderView('admin/page/page404.html.twig');
            return new Response($html404, 404);
        }


        // Le try catch permet d'éxecuter du code tout en récupérant les erreurs potentielles afin de les gérer correctement
        try {
            //on prépare la requête
            $entityManager->remove($activity);
            // on exécute la requête
            $entityManager->flush();
            // permet d'enregistrer un message dans la session de PHP, qui sera affiché grâce à twig sur la prochaine page
            $this->addFlash('success', 'L\'activité a bien été supprimée !');

        // Si l'exécution du try a échoué, catch est exécuté et on renvoie une réponse http avec un message d'erreur
        } catch (\Exception $exception) {
            return $this->renderView('admin/page/error.html.twig', [
                'errorMessage' => $exception->getMessage()
                ]);
        }


        // On fait une redirection sur la page d'affichage des activités (liste) de l'administrateur
        return $this->redirectToRoute('admin_list_activities');
    }


    #[Route('/update/{id}', name: 'admin_update_activity')]
    public function updateActivity(int $id, ActivityRepository $activityRepository, EntityManagerInterface $entityManager, Request $request): Response {

        // Dans $activity, on stocke le résultat du select par id dans l'entité Activity
        $activity = $activityRepository->find($id);

        // On génère une instance de la classe de gabarit de formulaire, et on la lie avec l'entité Activity
        $activityUpdateForm = $this->createForm(ActivityType::class, $activity);

        // On lie le formulaire à la requête sql (Doctrine gère la récupération des données et les stocke dans l'entité)
        $activityUpdateForm->handleRequest($request);

        // Sile formulaire est soumis et contient des données valides (qui respectent les contraintes)
        if($activityUpdateForm->isSubmitted() && $activityUpdateForm->isValid()) {
            // On actualise la date de mise à jour de l'activité
            $activity->setUpdatedAt(new \DateTime('NOW'));
            // On prépare la requête sql
            $entityManager->persist($activity);
            // puis on l'exécute
            $entityManager->flush();

            // Et on affiche un message flash pour informer l'utilisateur de la bonne exécution de sa requête
            $this->addFlash('success', 'Activité enregistrée !');
        }

        // Avec la méthode createView(), on génère une instance de 'vue' du formulaire, pour le render
        $activityUpdateFormView = $activityUpdateForm->createView();
        // On retourne une réponse http (le fichier html du formulaire)
        return $this->render('admin/page/activity/admin_update_activity.html.twig', [
            'activityForm' => $activityUpdateFormView
        ]);
    }


}