<?php

namespace App\Repository;

use App\Entity\ProspectiveBuyer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ProspectiveBuyer|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProspectiveBuyer|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProspectiveBuyer[]    findAll()
 * @method ProspectiveBuyer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProspectiveBuyerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProspectiveBuyer::class);
    }

    // /**
    //  * @return ProspectiveBuyer[] Returns an array of ProspectiveBuyer objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProspectiveBuyer
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
