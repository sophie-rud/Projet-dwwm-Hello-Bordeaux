<?php

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

class AdminActivityController extends AbstractController {

    #[Route('admin/activities/', name: 'admin_list_activities')]
    public function adminListActivities(ActivityRepository $activityRepository): Response {

        $activities = $activityRepository->findAll();

        return $this->render('admin/page/activity/admin_list_activities.html.twig', [
            'activities' => $activities
        ]);
    }


    #[Route('/admin/activities/show/{id}', name: 'admin_show_activity')]
    public function showActivity(int $id, ActivityRepository $activityRepository): Response {

        $activity = $activityRepository->find($id);

        if (!$activity || !$activity->getisPublished()) {
            $html404 = $this->renderView('public/page/page404.html.twig');
            return new Response($html404, 404);
        }

        return $this->render('admin/page/activity/admin_show_activity.html.twig', [
            'activity' => $activity
        ]);
    }

    #[Route('admin/activities/insert', name: 'admin_activity_insert')]
    public function insertActivity(EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger, ParameterBagInterface $params): Response {
        $activity = new Activity();

        $activityCreateForm = $this->createForm(ActivityType::class, $activity);

        $activityCreateForm->handleRequest($request);

        if ($activityCreateForm->isSubmitted() && $activityCreateForm->isValid()) {

            $photoFile = $activityCreateForm->get('photo')->getData();

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
            return $this->redirectToRoute('admin_activity_insert');
        }

        // Avec la méthode createView(), on génère une instance de 'vue' du formulaire, pour le render
        $activityCreateFormView = $activityCreateForm->createView();

        // On retourne une réponse http (le fichier html du formulaire)
        return $this->render('admin/page/activity/admin_insert_activity.html.twig', [
            'activityForm' => $activityCreateFormView
        ]);
    }

}