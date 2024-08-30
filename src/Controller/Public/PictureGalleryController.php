<?php

declare(strict_types=1);

namespace App\Controller\Public;


use App\Repository\PictureGalleryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/picture-gallery')]
class PictureGalleryController extends AbstractController
{

    #[Route('/', name: 'list-picture_gallery')]
    public function pictureGallery(PictureGalleryRepository $pictureGalleryRepository): Response
    {

        $pictures = $pictureGalleryRepository->findAll();

        return $this->render('public/page/picture-gallery/picture_gallery.html.twig', [
            'pictures' => $pictures
        ]);
    }
}

