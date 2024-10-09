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

    public function findTotalEstimates() : int {
        $total = $this->createQueryBuilder('t')
            ->select('SUM(t.estimates)')
            ->getQuery()
            ->getSingleScalarResult();
        return $total;
    }
    
    /**
     * Retourne un tableau de tâches filtrées par titre et estimation.
     * 
     * @param string $title Le titre à rechercher (optionnel)
     * @param int $minEstimate La valeur minimale pour estimates
     * @param int $maxEstimate La valeur maximale pour estimates
     * @return Task[] Un tableau d'objets Task
     */
    public function findByFilters(string $title = '', int $minEstimate = 0, int $maxEstimate = 10000) : array
    {
        $qb = $this->createQueryBuilder('t');

        // Rechercher par titre (si renseigné)
        if ($title) {
            $qb->andWhere('t.title LIKE :title')
               ->setParameter('title', '%' . $title . '%');
        }

        // Filtrer par estimates (min/max)
        $qb->andWhere('t.estimates BETWEEN :min AND :max')
           ->setParameter('min', $minEstimate)
           ->setParameter('max', $maxEstimate);

        return $qb->getQuery()->getResult();
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
