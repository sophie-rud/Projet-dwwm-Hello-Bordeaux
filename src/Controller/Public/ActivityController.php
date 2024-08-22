<?php

declare(strict_types=1);

namespace App\Controller\Public;



use App\Repository\ActivityRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActivityController extends AbstractController {

    #[Route('/activities', name: 'list_activities')]
    public function listActivities(ActivityRepository $activityRepository): Response {

        $activities = $activityRepository->findAll();

        return $this->render('public/page/activity/list_activities.html.twig', [
            'activities' => $activities
        ]);
    }

    #[Route('/activities-by-category', name: 'activities_by_category')]
    public function activitiesByCategory(CategoryRepository $categoryRepository): Response
    {
        // Récupère toutes les catégories avec leurs activités associées
        $categories = $categoryRepository->findAll();

        return $this->render('public/page/activity/activities_by_category.html.twig', [
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

        return $this->render('public/page/activity/show_activity.html.twig', [
            'activity' => $activity
        ]);
    }


}