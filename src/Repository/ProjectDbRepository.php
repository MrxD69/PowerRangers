<?php
namespace App\Repository;

use App\Entity\ProjectDb;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectDb>
 */
class ProjectDbRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectDb::class);
    }
    /**
     * Search projects by domain or description.
     *
     * @param string $searchTerm
     * @return ProjectDb[]
     */
    public function searchProjects(string $searchTerm): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.domaine LIKE :search')
            ->orWhere('p.description LIKE :search')
            ->setParameter('search', '%' . $searchTerm . '%')
            ->getQuery()
            ->getResult();
    }



    // Optional: If you want to keep a method for a specific example field,
    // you can add that later if required, but it is currently unused.
    // public function findByExampleField($value): array
    // {
    //     return $this->createQueryBuilder('p')
    //         ->andWhere('p.exampleField = :val')
    //         ->setParameter('val', $value)
    //         ->orderBy('p.id', 'ASC')
    //         ->setMaxResults(10)
    //         ->getQuery()
    //         ->getResult();
    // }
}