<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Cartline;
use App\Service\Cart\CartService;
use App\Repository\CartRepository;
use App\Repository\UsersRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart_index")
     */
    public function index(CartRepository $cartRepo, CartService $cartService)
    {
        $items = $cartService->getFullCart();
        $amount = $cartService->getTotal();
        if($this->getUser()){
            if($cartRepo->getLastCart($this->getUser())){
                $cart = $cartRepo->getLastCart($this->getUser());
                $cartlines = $cart->getCartlines();
                $array = [];
                foreach($cartlines as $cartline){
                    $array[] = $cartline;
                }
                $items = array_merge($items, $array);
                $amount = $cart->getAmountTtc();
            }
        }
        return $this->render('cart/index.html.twig', [
            'items' => $items, 
            'total' => $amount            
            ]);
    }

    /**
     * Permet de récupérer le panier de la session en cours et ajouter un produit dans le panier
     * @Route("/cart/add/{id}", name="cart_add")
     */
    public function add($id, CartService $cartService, CartRepository $cartRepo, ProductsRepository $productRepo, EntityManagerInterface $manager)
    {        
        // AJOUT PANIER + CREATION CART + AJOUT EN BDD
        // Si un utilisateur est connecté on récupère l'id du produit et on crée une ligne produit sinon on ajoute le produit
        if ($this->getUser()){
            $product = $productRepo->find($id);
            $cartlines = new Cartline();
            $cartlines->setProduct($product)
            ->setQuantity(1)
            ->setAmountHt(number_format($product->getPrice() / 1.2, 2))
            ->setAmountTtc($product->getPrice())
            ;
           // On récupère le dernier panier de l'utilisateur connecté sinon on le crée     
            if($cartRepo->getLastCart($this->getUser())){
                $cart = $cartRepo->getLastCart($this->getUser());
            }else{
                $cart = new Cart();
                $cart->setUser($this->getUser());
            }
            // Si le dernier panier récupéré de l'utilisateur en session est différent de vide
            if(!empty($cartService->getFullCart())){
                // On boucle sur les lignes produits du panier et on crée les lignes
                foreach($cartService->getFullCart() as $lines){

                    $line = new Cartline();
                    $line->setProduct($lines["product"])
                            ->setQuantity($lines["quantity"])
                            ->setAmountHt(number_format($lines["product"]->getPrice() / 1.2, 2))
                            ->setAmountTtc($lines["product"]->getPrice())
                    ;
                    $manager->persist($line);
                }
                $cartService->removeSession();                
            }
            $cart->setAmountHt($cart->getAmountHt() + $cartlines->getAmountHt())
            ->setAmountTtc($cart->getAmountTtc() + $cartlines->getAmountTtc())
            ->setShippingHt(2)
            ->setShippingTtc(4)
            ;
            $cartlines->setCart($cart);
            $manager->persist($cart);
            $manager->persist($cartlines);
            $manager->flush();
        }else{
            $cartService->add($id);
        }        
        return $this->redirectToRoute('cart_index');
    }

    /**
     * Permet de supprimer un produit du panier
     * @Route("/cart/remove/{id}", name="cart_remove")
     */
    public function removeQuantity($id, CartService $cartService)
    {
        $cartService->remove($id);

        return $this->redirectToRoute('cart_index');
    }
}