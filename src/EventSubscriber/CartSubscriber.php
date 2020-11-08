<?php

namespace App\EventSubscriber;

use App\Service\Cart\CartService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

class CartSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $cartService;

    public function __construct(Environment $twig, CartService $cartService)
    {
        $this->twig = $twig;
        $this->cartService = $cartService;
    }
    
    public function onKernelController(ControllerEvent $event)
    {
        $this->twig->addGlobal('cart_items', $this->cartService->getFullCart());
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}