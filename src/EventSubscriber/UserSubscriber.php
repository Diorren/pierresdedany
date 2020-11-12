<?php

namespace App\EventSubscriber;

use App\Entity\Cart;
use Twig\Environment;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

// Subscriber qui vérifie s'il y a un user connecté avec un panier ou qui crée un panier s'il n y en a pas

class UserSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $usersRepository;
    private $security;
    private $manager;

    public function __construct(Security $security, Environment $twig, UsersRepository $usersRepository, EntityManagerInterface $manager)
    {
         $this->twig = $twig;
         $this->usersRepository = $usersRepository;
         $this->security = $security;
         $this->manager = $manager;
    }
    
    public function onKernelController(ControllerEvent $event)
    {
       if ($this->security->getUser()){
           $lastCart = $this->usersRepository->getLastCart($this->security->getUser());
           if($lastCart){
               $currentCart = $lastCart;
           }else{
               $currentCart = new Cart();
               $currentCart->setUser($this->security->getUser());
               $this->manager->persist($currentCart);
               $this->manager->flush();
           }
           $this->twig->addGlobal('currentCart', $currentCart);
       }
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}