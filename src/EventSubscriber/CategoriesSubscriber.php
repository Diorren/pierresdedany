<?php

namespace App\EventSubscriber;

use App\Repository\CategoriesRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

class CategoriesSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $categoriesRepository;
    
    public function __construct(Environment $twig, CategoriesRepository $categoriesRepository)
    {
        $this->twig = $twig;
        $this->categoriesRepository = $categoriesRepository;
    }
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