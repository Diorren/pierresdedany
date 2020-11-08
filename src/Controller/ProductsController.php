<?php

namespace App\Controller;

use App\Entity\Products;
use App\Entity\Categories;
use App\Repository\ProductsRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductsController extends AbstractController
{
    /**
     * Permet d'afficher les produits
     * @Route("/products", name="products_list")
     */
    public function index(ProductsRepository $productRepo, PaginatorInterface $paginator, Request $request)
    {
        // $data = $this->getDoctrine()->getRepository(Products::class)->findAll();
        $data = $productRepo->findAll();
        
        $products = $paginator->paginate(
            $data,   // Requête contenant les données à paginer (les produits)
            $request->query->getInt('page', 1), // N° de la page en cours passé dans l'URL, 1 par défaut si aucune page
            4  // Nombre de résultats par page
        );
        return $this->render('products/index.html.twig', [
            'products' => $products
        ]);
    }

    /**
     * Permet d'afficher un seul produit avant paiement
     * @Route("/product/{slug}", name="product_single")
     */
    public function show(Products $product)
    {
        return $this->render('products/show.html.twig', [
            'product' => $product
        ]);
    }

    /**
     * Permet de voir les produits par catégories
     * @Route("/categories/{id}", name="categories_show")
     * @return Response
     */
    public function showProductsByCategory(Categories $categories, ProductsRepository $productRepo,PaginatorInterface $paginator, Request $request)
    {
        $data = $productRepo->getProductsByCategory($categories->getId());
        //dd($request->attributes->all());
        //dd($data);
        $productsByCat = $paginator->paginate(
            $data,   // Requête contenant les données à paginer (les produits)
            $request->query->getInt('page', 1), // N° de la page en cours passé dans l'URL, 1 par défaut si aucune page
            4  // Nombre de résultats par page
        );
        return $this->render('categories/show.html.twig', [
            'productsByCat' => $productsByCat,
            'category' => $categories            
        ]);
    }
}