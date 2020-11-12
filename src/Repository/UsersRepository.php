<?php

namespace App\Repository;

use App\Entity\Users;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

/**
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof Users) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * Requête pour listUsers et Cart
     * @return Users[]
     */
    public function getListUsers()
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.carts', 'cart')
            ->addSelect('cart')
            ->orderBy('u.id','ASC')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function getLastCart(Users $users)
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.carts', 'cart')
            ->addSelect('cart')
            ->orderBy('cart.id','DESC')
            ->where('u.id = :id')
            ->setParameter('id', $users->getId())
            // ->andWhere('u.status = 0')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Users
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}