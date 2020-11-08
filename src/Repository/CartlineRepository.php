<?php

namespace App\Repository;

use App\Entity\Cartline;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Cartline|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cartline|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cartline[]    findAll()
 * @method Cartline[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CartlineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cartline::class);
    }

    // /**
    //  * @return Cartline[] Returns an array of Cartline objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Cartline
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
