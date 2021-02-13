<?php
namespace App\Twig;
use App\Repository\CartRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtensions extends AbstractExtension
{
    private $cartRepo;

    public function __construct(CartRepository $cartRepo)
    {
        $this->cartRepo = $cartRepo;
    }

    /**
     * Récupère le nombre de commandes effectuées par le user
     */
    public function totalCartPaidByUser($users)
    {
        $result = $this->cartRepo->getPaidCart($users);
        return count($result);
    }

    /**
     * Retourne de nouvelles fonctions dans Twig
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('totalCartUser',[$this, 'totalCartPaidByUser'])
        ];
    }
}