<?php

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\Users;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Cart|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cart|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cart[]    findAll()
 * @method Cart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    public function getLastCart(Users $users)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'user')
            ->addSelect('user')
            ->orderBy('c.id','DESC')
            ->andWhere('user.id = :id')
            ->setParameter('id', $users->getId())
            ->andWhere('c.statut = :statut')
            ->setParameter('statut', false)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Cart
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}