<?php

declare(strict_types=1);

namespace App\Controller\Public;



use App\Entity\Activity;
use App\Repository\ActivityRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ActivityController extends AbstractController {

    #[Route('/activities', name: 'list_activities')]
    public function listActivities(ActivityRepository $activityRepository, CategoryRepository $categoryRepository): Response {

        $activities = $activityRepository->findUpcomingActivities();
        $categories = $categoryRepository->findAll();

        return $this->render('public/page/activity/list_activities.html.twig', [
            'activities' => $activities,
            'categories' => $categories,
        ]);
    }


    #[Route('/activities/show/{id}', name: 'show_activity')]
    public function showActivity(int $id, ActivityRepository $activityRepository): Response {

        $activity = $activityRepository->find($id);

        if (!$activity || !$activity->getisPublished()) {
            $html404 = $this->renderView('public/page/page404.html.twig');
            return new Response($html404, 404);
        }

        $participants = $activity->getUserParticipant();
        $organizer = $activity->getUserAdminOrganizer();
        $galleries = $activity->getGalleries();

        return $this->render('public/page/activity/show_activity.html.twig', [
            'activity' => $activity,
            'participants' => $participants,
            'organizer' => $organizer,
            'galleries' => $galleries,
        ]);
    }

    #[Route('/activities/inscription/{id}', name: 'inscription_activity')]
    #[IsGranted('IS_AUTHENTICATED_FULLY', message: 'Vous devez être connecté pour vous inscrire à une activité.')]
    public function inscriptionActivity(Activity $activity, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->getUser();

        // Si l'utilisateur n'est pas connecté, on le redirige vers la page d'inscription ou de connexion
        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }


        if (!$activity->getisPublished()) {
            $html404 = $this->renderView('public/page/page404.html.twig');
            return new Response($html404, 404);
        }

        if ($currentUser->getActivitiesParticipate()->contains($activity)) {
            // L'utilisateur est déjà inscrit à cette activité
            $this->addFlash('error', 'Vous êtes déjà inscrit à cette activité.');
            return $this->redirectToRoute('list_activities');
        }

        $currentUser->addActivitiesParticipate($activity);
        $entityManager->persist($currentUser);
        $entityManager->flush();

        $this->addFlash('success', 'Vous vous êtes inscrit avec succès à l\'activité.');

        return $this->redirectToRoute('list_activities');
    }


}