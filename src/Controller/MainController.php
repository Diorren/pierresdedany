<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Form\SearchType;
use App\Repository\ProductsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     * @return Response
     */
    public function index(ProductsRepository $newsProductRepo, ProductsRepository $promoProductRepo)
    {
        //dd($newsProductRepo->findNewsProducts(4));
        return $this->render('main/index.html.twig', [
            'news' => $newsProductRepo->findNewsProducts(4),
            'bests' => $promoProductRepo->findPromoProducts(4)
        ]);
    }   
    
    /**
     * Permet de faire une recherche
     * @Route("/search", name="app_search")
     * @param ProductsRepository $productRepo
     * @return void
     */
    public function search(ProductsRepository $productRepo, Request $request)
    {
        $data = new SearchData();
        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchType::class, $data);
        $form->handleRequest($request);

        [$min, $max] = $productRepo->findMinMax($data);
        
        $products = $productRepo->findSearch($data);

        return $this->render('main/search.html.twig', [
            'products' => $products,
            'form' => $form->createView(),
            'min' => $min,
            'max' => $max
        ]);
    }

    /**
     * Permet d'afficher un seul produit
     * @Route("/products/{slug}", name="products_single")
     * @return Response
     */
    public function show($slug, ProductsRepository $repo)
    {
        $product = $repo->findOneBySlug($slug);
        return $this->render('products/_product.html.twig', [
            'product' => $product
        ]);
    }
    
    /**
     * Permet d'afficher un seul produit
     * @Route("/products/{slug}", name="products_single")
     * @return Response
     */
    public function show2($slug, ProductsRepository $repo)
    {
        $product = $repo->findOneBySlug($slug);
        return $this->render('products/_bestsellers.html.twig', [
            'product' => $product
        ]);
    }
}