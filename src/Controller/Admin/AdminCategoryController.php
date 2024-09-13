<?php

declare(strict_types=1);

namespace App\Controller\Admin;



use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('admin/categories')]
class AdminCategoryController extends AbstractController {

    #[Route('/', name: 'admin_list_categories')]
    public function adminListCategories(CategoryRepository $categoryRepository): Response {
        $categories = $categoryRepository->findAll();

        // On retourne une réponse http en html
        return $this->render('admin/page/category/admin_list_categories.html.twig', [
            'categories' => $categories
        ]);
    }


    #[Route('/insert', name: 'admin_insert_category')]
    public function insertCategory(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, ParameterBagInterface $params): Response {

        $category = new Category();

        $categoryCreateForm = $this->createForm(CategoryType::class, $category);

        $categoryCreateForm->handleRequest($request);

        if ($categoryCreateForm->isSubmitted() && $categoryCreateForm->isValid()) {


            // On récupère le fichier depuis le formulaire
            $pictoFile = $categoryCreateForm->get('pictogram')->getData();

            // Si un fichier photo est bien soumis
            if ($pictoFile) {
                // On récupère le nom du fichier
                $originalFilename = pathinfo($pictoFile->getClientOriginalName(), PATHINFO_FILENAME);
                // On "nettoie" le nom du fichier avec $slugger->slug() (retire les caractères spéciaux...)
                $safeFilename = $slugger->slug($originalFilename);
                // On ajoute un identifiant unique au nom
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $pictoFile->guessExtension();


                try {
                    // On récupère le chemin de la racine du projet
                    $rootPath = $params->get('kernel.project_dir');
                    // On déplace le fichier dans le dossier indiqué dans le chemin d'accès. On renomme
                    $pictoFile->move($rootPath . '/public/uploads', $newFilename);
                } catch (FileException $e) {
                    dd($e->getMessage());
                }

                // On stocke le nom du fichier dans la propriété image de l'entité activity
                $category->setPictogram($newFilename);
            }




            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Nouvelle catégorie ajoutée !');

            return $this->redirectToRoute('admin_list_categories');
        }

        $categoryCreateFormView = $categoryCreateForm->createView();
        return $this->render('admin/page/category/admin_insert_category.html.twig', [
            'categoryForm' => $categoryCreateFormView,
        ]);
    }


    #[Route('/delete/{id}', name: 'admin_delete_category')]
    public function deleteCategory(int $id, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager): Response {

        $category = $categoryRepository->find($id);

        if (!$category) {
            $html404 = $this->renderView('admin/page/page404.html.twig');
            return new Response($html404, 404);
        }

        try {
        $entityManager->remove($category);
        $entityManager->flush();

        $this->addFlash('success', 'La catégorie a été supprimée');

        } catch(\Exception $exception){
        return $this->renderView('admin/page/error.html.twig', [
            'errorMessage' => $exception->getMessage()
        ]);
    }

        return $this->redirectToRoute('admin_list_categories');
    }



    #[Route('/update/{id}', name: 'admin_update_category')]
    public function updateCategory(int $id, Request $request, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager, SluggerInterface $slugger, ParameterBagInterface $params, LoggerInterface $logger): Response {

        $category = $categoryRepository->find($id);
        // On récupère le picto actuel avant de traiter le formulaire
        $currentPicto = $category->getPictogram();

        $categoryUpdateForm = $this->createForm(CategoryType::class, $category);

        $categoryUpdateForm->handleRequest($request);

        if ($categoryUpdateForm->isSubmitted() && $categoryUpdateForm->isValid()) {


            // On récupère le fichier depuis le formulaire
            $pictoFile = $categoryUpdateForm->get('pictogram')->getData();

            // Si un fichier photo est bien soumis
            if ($pictoFile) {
                // On récupère le nom du fichier
                $originalFilename = pathinfo($pictoFile->getClientOriginalName(), PATHINFO_FILENAME);
                // On "nettoie" le nom du fichier avec $slugger->slug() (retire les caractères spéciaux...)
                $safeFilename = $slugger->slug($originalFilename);
                // On ajoute un identifiant unique au nom
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $pictoFile->guessExtension();

                // bloc try/catch -> permet de gérer les erreurs lors de l'exécution du code, avec un code alternatif pour traiter les exceptions
                try {
                    // On récupère le chemin de la racine du projet
                    $rootPath = $params->get('kernel.project_dir');
                    // On déplace le fichier dans le dossier indiqué dans le chemin d'accès. On renomme
                    $pictoFile->move($rootPath . '/public/uploads', $newFilename);
                } catch (FileException $e) {
                    // Enregistrer l'erreur dans les logs (garde une trace des évènements, permettent de régler les erreurs)
                    $logger->error('File upload failed: ' . $e->getMessage());
                    // On affiche un message flash d'erreur
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement du fichier.');
                    // On fait un rechargement de la page d'édition de l'activité
                    return $this->redirectToRoute('admin_update_category');

                }


                // On stocke le nom du fichier dans la propriété image de l'entité activity
                $category->setPictogram($newFilename);

                // Si aucun fichier n'est uploadé, conserver l'image existante
                //$category->setPictogram($currentPicto);
            }



            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'La catégorie a été modifiée');

        }

        $categoryUpdateFormView = $categoryUpdateForm->createView();
        return $this->render('admin/page/category/admin_update_category.html.twig', [
            'categoryUpdateForm' => $categoryUpdateFormView
        ]);
    }


/*
    #[Route('/show/{id}', name: 'admin_show_category')]
    public function showCategory(int $id, CategoryRepository $categoryRepository): Response {

        // Dans $activity, on stocke le résultat de notre recherche par id dans les données de la table Activity
        $category = $categoryRepository->find($id);

        // Si aucune catégorie n'est trouvée, on retourne une page et code d'erreur 404
        if (!$category) {
            $html404 = $this->renderView('admin/page/page404.html.twig');
            return new Response($html404, 404);
        }

        // On retourne une réponse http en html
        return $this->render('admin/page/category/admin_show_category.html.twig', [
            'category' => $category
        ]);
    }
*/


}
