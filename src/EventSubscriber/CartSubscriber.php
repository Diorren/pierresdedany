<?php

namespace App\EventSubscriber;

use App\Entity\Cart;
use App\Repository\CartRepository;
use Twig\Environment;
use App\Service\Cart\CartService;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CartSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $cartService;
    private $security;
    private $cartRepo;

    public function __construct(Environment $twig, CartService $cartService, Security $security, CartRepository $cartRepo)
    {
        $this->twig = $twig;
        $this->cartService = $cartService;
        $this->security = $security;
        $this->cartRepo = $cartRepo;
    }
    
    /**
     * Subscriber qui permet d'afficher le changement du montant total du panier
     *
     * @param ControllerEvent $event
     * @return void
     */
    public function onKernelController(ControllerEvent $event)
    {
        $items = $this->cartService->getFullCart();
       
        if($this->security->getUser()){
            if($this->cartRepo->getLastCart($this->security->getUser())){
                $cart = $this->cartRepo->getLastCart($this->security->getUser());
                $cartlines = $cart->getCartlines();
                $array = [];
                foreach($cartlines as $cartline){
                    $array[] = $cartline;
                }
                $items = array_merge($items, $array);
            }
        }
        $this->twig->addGlobal('cart_items', $items);
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}