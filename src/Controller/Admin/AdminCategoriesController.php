<?php

namespace App\Controller\Admin;

use App\Entity\Products;
use App\Entity\Categories;
use App\Form\CategoriesType;
use App\Form\ProductsType;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/categories", name="admin_categories_")
 * @package App\Controller\Admin
 */
class AdminCategoriesController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(CategoriesRepository $repo)
    {
        return $this->render('admin/categories/index.html.twig', [
            'categories' => $repo->findAll()
        ]);
    }

    /**
     * @Route("/add", name="add")
     */
    public function addCategories(Request $request, EntityManagerInterface $manager)
    {
        $categorie = new Categories();

        $form = $this->createForm(CategoriesType::class, $categorie);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($categorie);
            $manager->flush();

            return $this->redirectToRoute('admin_categories_home');
        }

        return $this->render('admin/categories/add.html.twig', [
            'categorie' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function editCategories(Categories $categorie, Request $request, EntityManagerInterface $manager)
    {
        $form = $this->createForm(CategoriesType::class, $categorie);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($categorie);
            $manager->flush();

            return $this->redirectToRoute('admin_categories_home');
        }

        return $this->render('admin/categories/add.html.twig', [
            'categorie' => $form->createView(),
        ]);
    }
}