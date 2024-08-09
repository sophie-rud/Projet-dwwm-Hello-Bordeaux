<?php

namespace App\Controller\Public;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController  extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home() {
        return $this->render('public/page/home.html.twig');
    }

}
