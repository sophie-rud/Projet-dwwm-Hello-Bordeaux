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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/picture-gallery')]
#[IsGranted('ROLE_ADMIN')]
class AdminPictureGalleryController extends AbstractController {

    #[Route('/', name: 'admin_picture_gallery')]
    public function listPictureGallery(PictureGalleryRepository $pictureGalleryRepository) : Response {

        $pictures = $pictureGalleryRepository->findAll();

        return $this->render('admin/page/picture-gallery/admin_picture_gallery.html.twig', [
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

            // On fait une redirection sur la galerie
            return $this->redirectToRoute('admin_picture_gallery');
        }

        $pictureCreateFormView = $pictureCreateForm->createView();

        return $this->render('admin/page/picture-gallery/admin_add_picture_in_gallery.html.twig', [
            'pictureForm' => $pictureCreateFormView,
            'picture' => $picture,
        ]);
    }


    #[Route('/delete/{id}', name: 'admin_delete_picture_in_gallery')]
    public function deletePictureInGallery(int $id, PictureGalleryRepository $pictureGalleryRepository, EntityManagerInterface $entityManager): Response {

        // Dans $activity, on stocke le résultat de notre recherche par id dans les données de la table Activity
        $picture = $pictureGalleryRepository->find($id);

        // Si aucune activité n'est trouvée avec l'id recherché, on retourne une page et code d'erreur 404
        if (!$picture) { // || !$picture->getIsPublished()
            $html404 = $this->renderView('admin/page/page404.html.twig');
            return new Response($html404, 404);
        }


        // Le try catch permet d'éxecuter du code tout en récupérant les erreurs potentielles afin de les gérer correctement
        try {
            //on prépare la requête
            $entityManager->remove($picture);
            // on exécute la requête
            $entityManager->flush();
            // permet d'enregistrer un message dans la session de PHP, qui sera affiché grâce à twig sur la prochaine page
            $this->addFlash('success', 'L\'image a été supprimée !');

            // Si l'exécution du try a échoué, catch est exécuté et on renvoie une réponse http avec un message d'erreur
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
        }


        // On fait une redirection sur la page d'affichage des images de la gallerie
        return $this->redirectToRoute('admin_picture_gallery');
    }


    #[Route('/update/{id}', name: 'admin_update_picture_in_gallery')]
    public function updatePictureInGallery(int $id, PictureGalleryRepository $pictureGalleryRepository, EntityManagerInterface $entityManager, Request $request): Response
    {

        // Dans $activity, on stocke le résultat de notre recherche par id dans les données de la table Activity
        $picture = $pictureGalleryRepository->find($id);

        $pictureUpdateForm = $this->createForm(PictureGalleryType::class, $picture);
        $pictureUpdateForm->handleRequest($request);

        if ($pictureUpdateForm->isSubmitted() && $pictureUpdateForm->isValid()) {

            $entityManager->persist($picture);
            $entityManager->flush();

            $this->addFlash('success', 'Image mise à jour !');

            return $this->redirectToRoute('admin_picture_gallery');
        }

        $pictureUpdateFormView = $pictureUpdateForm->createView();

        return $this->render('admin/page/picture-gallery/admin_update_picture_in_gallery.html.twig', [
            'pictureUpdateForm' => $pictureUpdateFormView,
            'picture' => $picture,
        ]);
    }

}