<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Form\SearchFormType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homePage')]
    public function index(CategoryRepository $categoryRepository, ArticleRepository $articleRepository, Request $request): Response
    {
        $data = new SearchData();

        $data->setPage($request->get('page', 1));
        $data->setCat($request->get('cat', ''));
        
        $categories = $categoryRepository->findAll();
        foreach ($categories as $category) {
            if (count($category->getArticles()) > 0) {
                $activeCategories[] = $category;
            }
        }
        $articles = $articleRepository->findSearch($data);
        return $this->render('home/index.html.twig', [
            'articles' => $articles,
            'activeCategories' => $activeCategories
        ]);
    }

    /**
     * Géstion du formulaire de recherche des articles
     *
     * @param ArticleRepository $articleRepository
     * @param Request $request
     * @return Response
     */
    #[Route('/search-form', name: 'search_form')]
    public function searchForm(CategoryRepository $categoryRepository, ArticleRepository $articleRepository, Request $request): Response
    {
        $data = new SearchData();

        $form = $this->createForm(SearchFormType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $articles = $articleRepository->findSearch($data);
            $categories = $categoryRepository->findAll();
        foreach ($categories as $category) {
            if (count($category->getArticles()) > 0) {
                $activeCategories[] = $category;
            }
        }
            return $this->render('home/index.html.twig', [
                'articles' => $articles,
                'activeCategories' => $activeCategories
            ]);
        }
        return $this->render('_partials/_searchForm.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
