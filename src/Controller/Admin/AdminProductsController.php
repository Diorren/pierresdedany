<?php

namespace App\Controller\Admin;

use App\Entity\Images;
use App\Entity\Products;
use App\Form\ProductsType;
use Gedmo\Sluggable\Util\Urlizer;
use App\Repository\ImagesRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/products", name="admin_products_")
 * @package App\Controller\Admin
 */
class AdminProductsController extends AbstractController
{
    /**
     * @Route("/", name="home", methods={"GET"})
     */
    public function index(ProductsRepository $repo): Response
    {
        return $this->render('admin/products/index.html.twig', [
            'products' => $repo->findAll()
        ]);
    }

    
    /**
     * Permet d'ajouter un produit
     * @Route("/add", name="add", methods={"GET","POST"})
     */
    public function addProducts(Request $request, EntityManagerInterface $manager): Response
    {
        $product = new Products();

        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            // On récupère les images transmises
            $images = $form->get('images')->getData();
            
            // On boucle sur les images
            foreach($images as $image){
                // On génère un nouveau nom de fichier
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = Urlizer::urlize($originalFilename).uniqid().'.'.$image->guessExtension();
                
                // On copie le fichier dans le dossier uploads
                $image->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
                // On stocke le nom de l'image dans la base de données
                $img = new Images();
                $img->setName($newFilename);
                $product->addImage($img);
            }
            $manager->persist($product);
            $manager->flush();

            return $this->redirectToRoute('admin_products_home');        
        }
            return $this->render('admin/products/add.html.twig', [
                'product' => $product,
                'productForm' => $form->createView()
        ]);
    }

    /**
     * Permet d'éditer et de modifier un produit
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     */
    public function editProducts(Products $product, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère les images transmises
            $images = $form->get('images')->getData();
            
            // On boucle sur les images
            foreach($images as $image){
                // On génère un nouveau nom de fichier
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = Urlizer::urlize($originalFilename).uniqid().'.'.$image->guessExtension();
                
                // On copie le fichier dans le dossier uploads
                $image->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
                // On stocke le nom de l'image dans la base de données
                $img = new Images();
                $img->setName($newFilename);
                $product->addImage($img);
            }            
            $manager->persist($product);
            $manager->flush();

            return $this->redirectToRoute('admin_products_home');
        }

        return $this->render('admin/products/edit.html.twig', [
            'product' => $product,
            'productForm' => $form->createView()
        ]);
    }

    /**
     * Permet de supprimer un produit
     * @Route("/delete/{id}", name="delete")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function delete($id, ProductsRepository $productRepo, EntityManagerInterface $manager)
    {
        // On récupère l' ID du produit de l'entité correspondante
        $product = $productRepo->find($id);
        if ($product === null) {
        throw $this->createNotFoundException('Product[id='.$id.'] inexistant.');
        }
        // On récupère toutes les images
        $images = $product->getImages();
        
        // On enlève toutes les images du produit
        foreach($images as $image){
            $product->removeImage($image);  // $image est une instance de l'entité Images
        }
        $manager->remove($product);
        $manager->flush();
        
        $this->addFlash("message", "L'annonce <em>{$product->getName()}</em> a bien été supprimée");
        return $this->redirectToRoute('admin_products_home');
    }

    /**
     * Permet de supprimer les images dans l'édition de produits
     * @Route("/delete/image/{id}", name="delete_image", methods={"DELETE"})
     */
    public function deleteImage(Request $request, Images $image, EntityManagerInterface $manager)
    {
        $data = json_decode($request->getContent(), true);
        
        // On vérifie si le token est valide
        if($this->isCsrfTokenValid('delete'.$image->getId(), $data['_token'])) {
            
            // On récupère le nom de l'image
            $name = $image->getName();
            
            // On supprime le fichier
            unlink($this->getParameter('images_directory').'/'.$name);
            
            // On supprime l'entrée de la base
            $manager->remove($image);
            $manager->flush();

            // On répond en json
            return new JsonResponse(['success' => 1]);
        }else{
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }
        
}