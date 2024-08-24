<?php


declare(strict_types=1);

namespace App\Controller\Admin;



use App\Entity\PictureGallery;
use App\Form\PictureGalleryType;
use App\Repository\PictureGalleryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/picture-gallery')]
class AdminPictureGalleryController extends AbstractController {

    #[Route('/', name: 'admin_picture_gallery')]
    public function listPictureGallery(PictureGalleryRepository $pictureGalleryRepository) : Response {

        $pictures = $pictureGalleryRepository->findAll();

        return $this->render('admin/page/picture-gallery/admin_add_picture_in_gallery.html.twig', [
            'pictures' => $pictures
        ]);
    }


    #[Route('/add/', name: 'admin_add_picture_in_gallery')]
    public function addPictureInGallery(EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger, ParameterBagInterface $params) : Response {
        $picture = new PictureGallery();

        $pictureCreateForm = $this->createForm(PictureGalleryType::class, $picture);

        $pictureCreateForm->handleRequest($request);

        if ($pictureCreateForm->isSubmitted() && $pictureCreateForm->isValid()) {

            // On récupère le fichier depuis le formulaire
            $pictureFile = $pictureCreateForm->get('filePath')->getData();

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
                $picture->setFilePath($newFilename);
            }

            /* $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); */
            // $currentUser = $this->getUser();
            // $picture->setUserAdminOrganizer($currentUser);

            // On prépare la requête sql,
            $entityManager->persist($picture);
            // puis on l'exécute.
            $entityManager->flush();

            // On affiche un message pour informer l'admin du succès de la requête
            $this->addFlash('success', 'Photo enregistrée dans la gallerie !');

            // On fait une redirection sur la liste des activités
            return $this->redirectToRoute('admin_picture_gallery');
        }


        $pictureCreateFormView = $pictureCreateForm->createView();

        return $this->render('admin/page/picture-gallery/admin_add_picture_in_gallery.html.twig', [
            'pictureForm' => $pictureCreateFormView,
            'picture' => $picture,
        ]);
    }

}