<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Products;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Products|null find($id, $lockMode = null, $lockVersion = null)
 * @method Products|null findOneBy(array $criteria, array $orderBy = null)
 * @method Products[]    findAll()
 * @method Products[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductsRepository extends ServiceEntityRepository
{
    /**
     * @var PaginatorInterface
     */
    private $paginator;
    
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator )
    {
        parent::__construct($registry, Products::class);
        $this->paginator = $paginator;
    }

    public function findNewsProducts($limit)
    {
        return $this->createQueryBuilder('p')
            ->select('p as products')
            ->orderBy('p.stock','DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findPromoProducts($limit)
    {
        return $this->createQueryBuilder('p')
            ->select('p as products')
            ->orderBy('p.price', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Récupère les produits en lien avec une recherche avec la fonction (getSearchQuery)
     * @return PaginationInterface
     */
    public function findSearch(SearchData $search)
    {
        $query = $this->getSearchQuery($search);
        return $this->paginator->paginate(
            $query,
            $search->page,
            9
        );                
    }

    /**
     * Récupère le prix minimum et maximum correspondant à une recherche avec la fonction (getSearchQuery)
     *
     * @param SearchData $search
     * @return integer[]
     */
    public function findMinMax(SearchData $search)
    {
        $results = $this->getSearchQuery($search, true)
                    ->select('MIN(p.price) as min', 'MAX(p.price) as max')
                    ->getQuery()
                    ->getScalarResult();
                    
        return [(int)$results[0]['min'],(int)$results[0]['max']];
    }

    /**
     * Fonction recherches multiples
     * 
     * @param SearchData $search
     * @return QueryBuilder
     */
    private function getSearchQuery(SearchData $search, $ignorePrice = false)
    {
        $query = $this
            ->createQueryBuilder('p')
            ->select('c', 'p')
            ->join('p.categories', 'c');

        if(!empty($search->q)){
            $query = $query
                ->andWhere('p.name LIKE :q')
                ->setParameter('q', "%{$search->q}%");
        }

        if(!empty($search->min) && $ignorePrice === false){
            $query = $query
                ->andWhere('p.price >= :min')
                ->setParameter('min', $search->min);
        }

        if(!empty($search->max) && $ignorePrice === false){
            $query = $query
                ->andWhere('p.price <= :max')
                ->setParameter('max', $search->max);
        }

        if(!empty($search->promo)){
            $query = $query
                ->andWhere('p.promo = 1');
        }

        if(!empty($search->categories)){
            $query = $query
                ->andWhere('c.id IN (:categories)')
                ->setParameter('categories', $search->categories);
        }
        
        return $query;
    }
    
    /**
     * Affiche les produits par catégorie
     * @return void
     */
    public function getProductsByCategory($id)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.categories', 'c')
            ->addSelect('c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()                    
            ->getResult()
        ; 
    }
}