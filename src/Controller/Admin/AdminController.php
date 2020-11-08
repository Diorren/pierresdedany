<?php

namespace App\Controller\Admin;

use App\Entity\Cart;
use App\Entity\Users;
use App\Repository\UsersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin", name="admin_")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("/users",  name="users")
     */
    public function listUsers(UsersRepository $usersRepo)
    {   
        return $this->render('admin/users/list.html.twig', [
            'users' => $usersRepo->getListUsers()
        ]);
    }
}