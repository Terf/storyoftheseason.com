<?php

namespace App\Repository;

use App\Entity\Buyer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Buyer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Buyer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Buyer[]    findAll()
 * @method Buyer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BuyerRepository extends EntityRepository
{
    // public function __construct(RegistryInterface $registry)
    // {
    //     parent::__construct($registry, Buyer::class);
    // }

    // /**
    //  * @return Buyer[] Returns an array of Buyer objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Buyer
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
