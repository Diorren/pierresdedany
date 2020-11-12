<?php

namespace App\Service\Cart;

use App\Repository\ProductsRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    protected $session;
    protected $productRepo;

    public function __construct(SessionInterface $session, ProductsRepository $productRepo)
    {
        $this->session = $session;
        $this->productRepo = $productRepo;
    }
    public function add(int $id) {
        $cart = $this->session->get('cart', []);
        
        if(!empty($cart[$id])){
            $cart[$id]++;
        }else{            
            $cart[$id] = 1;
        }

        $this->session->set('cart', $cart);
    }
    
    public function remove(int $id) 
    {
         $cart = $this->session->get('cart', []);

        if(!empty($cart[$id]) && $cart[$id] >= 2){
            $cart[$id]--;
        }else{
            unset($cart[$id]);
        }
        $this->session->set('cart', $cart);
    }
    
    /**
     * Permet de récupérer le panier avec les produits et la quantités
     *
     * @return array
     */
    public function getFullCart() : array
    {
        $cart = $this->session->get('cart', []);

        $cartWithData = [];
        
        foreach($cart as $id => $quantity){
            $cartWithData[] = [
                'product' => $this->productRepo->find($id),
                'quantity' => $quantity
            ];
        }
        return $cartWithData;
        dd($cartWithData);
    }
    
    /**
     * Permet de récupérer le total du panier
     *
     * @return float
     */
    public function getTotal() : float
    {
        $total = 0;
        
        foreach($this->getFullCart() as $item){
            
            $total += $item['product']->getPrice() * $item['quantity'];
        }
        return $total;
    }
}