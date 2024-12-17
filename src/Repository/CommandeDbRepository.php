<?php

namespace App\Repository;

use App\Entity\CommandeDb;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommandeDb>
 */
class CommandeDbRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandeDb::class);
    }



    public function findByExampleField($value): array
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

      public function findOneBySomeField($value): ?CommandeDb
   {
          return $this->createQueryBuilder('c')
             ->andWhere('c.exampleField = :val')
           ->setParameter('val', $value)
           ->getQuery()
             ->getOneOrNullResult()
        ;
        }
}
