<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Repository\ActivityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController  extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(ActivityRepository $activityRepository): Response {

        $activities = $activityRepository->findAll();

        return $this->render('public/page/home.html.twig', [
            'activities' => $activities,
        ]);
    }


}
