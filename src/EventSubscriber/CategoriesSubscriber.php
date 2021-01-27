<?php

namespace App\EventSubscriber;

use Twig\Environment;
use App\Repository\CategoriesRepository;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CategoriesSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $categoriesRepository;
    
    public function __construct(Environment $twig, CategoriesRepository $categoriesRepository)
    {
        $this->twig = $twig;
        $this->categoriesRepository = $categoriesRepository;
    }

    /**
     * Fonction Subscriber qui permet d' afficher le changement des catégories 
     *
     * @param ControllerEvent $event
     * @return void
     */
    public function onKernelController(ControllerEvent $event)
    {
        $this->twig->addGlobal('categories', $this->categoriesRepository->findAll());
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}