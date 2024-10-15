<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Task::class);
    }

    public function findTotalEstimates(bool $isAdmin, string $title = '', int $minEstimate = 0, int $maxEstimate = 10000): int
    {
        $qb = $this->createQueryBuilder('t')
            ->select('SUM(t.estimates)');

        $query = $this->createQueryWithFilters($qb, $isAdmin, $title, $minEstimate, $maxEstimate);

        $total = $query->getSingleScalarResult();

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
    public function findByFilters(bool $isAdmin, string $title = '', int $minEstimate, int $maxEstimate): array
    {
        $qb = $this->createQueryBuilder('t');

        $query = $this->createQueryWithFilters($qb, $isAdmin, $title, $minEstimate, $maxEstimate);

        return $query->getResult();
    }

    private function createQueryWithFilters(QueryBuilder $qb, bool $isAdmin = false, string $title = '', int $minEstimate = 0, int $maxEstimate = 10000): Query
    {
        if ($isAdmin) {
            $qb->andWhere('t.deletedAt IS NULL');
        }

        // Rechercher par titre (si renseigné)
        if ($title) {
            $qb->andWhere('t.title LIKE :title')
                ->setParameter('title', '%' . $title . '%');
        }

        // Filtrer par estimates (min/max)
        $qb->andWhere('t.estimates BETWEEN :min AND :max')
            ->setParameter('min', $minEstimate)
            ->setParameter('max', $maxEstimate);

        return $qb->getQuery();
    }

    // Use Paginator
    public function paginateWithPaginatorTask(int $page = 1, int $limit = 2): Paginator
    {
        $sql = $this->createQueryBuilder('t')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->setHint(Paginator::HINT_ENABLE_DISTINCT, false); // Désactive l'ajout automatique de distinct dans la requete
        return new Paginator($sql, false); // second params (false) desactive left join
    }

    // Use KnpPaginatorBundle
    public function paginateTask(int $page = 1, int $limit = 2): PaginationInterface
    {
        $sql = $this->createQueryBuilder('t');
        return $this->paginator->paginate($sql, $page, $limit, ['distinct' => true, 'sortFieldAllowList' => ['t.id']]);
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
