<?php

namespace App\Repository;

use App\Entity\Microsite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Microsite|null find($id, $lockMode = null, $lockVersion = null)
 * @method Microsite|null findOneBy(array $criteria, array $orderBy = null)
 * @method Microsite[]    findAll()
 * @method Microsite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MicrositeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Microsite::class);
    }

    // /**
    //  * @return Microsite[] Returns an array of Microsite objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Microsite
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
