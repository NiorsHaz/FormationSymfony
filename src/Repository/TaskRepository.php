<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

//    /**
//     * @return Task[] Returns an array of Task objects
//     */
    public function findWithLowerEstimatesThan($estimates): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.estimates <= :estimates')
            ->setParameter('estimates', $estimates)
            ->orderBy('t.estimates', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findTotalEstimates() : int {
        $total = $this->createQueryBuilder('t')
        ->select('SUM(t.estimates)')
        ->getQuery()
        ->getSingleScalarResult();
        return $total;
    }

//    public function findOneBySomeField($value): ?Task
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}