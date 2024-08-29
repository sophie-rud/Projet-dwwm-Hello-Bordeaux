<?php

declare(strict_types=1);

namespace App\Controller\Admin;



use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function insertCategory(Request $request, EntityManagerInterface $entityManager): Response {

        $category = new Category();

        $categoryCreateForm = $this->createForm(CategoryType::class, $category);

        $categoryCreateForm->handleRequest($request);

        if ($categoryCreateForm->isSubmitted() && $categoryCreateForm->isValid()) {
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
    public function updateCategory(int $id, Request $request, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager): Response {

        $category = $categoryRepository->find($id);

        $categoryUpdateForm = $this->createForm(CategoryType::class, $category);

        $categoryUpdateForm->handleRequest($request);

        if ($categoryUpdateForm->isSubmitted() && $categoryUpdateForm->isValid()) {
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
