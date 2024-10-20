<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Project::class);
    }

   /**
    * @return ProjectWithTaskCountDTO[] Returns an array of Project objects
    */
    public function findAllWithTaskCount() : array {
        dump(
            $this->getEntityManager()->createQuery(<<<DQL
                SELECT NEW App\DTO\ProjectWithTaskCountDTO(p.id, p.name, COUNT(t.id))
                FROM APP\ENTITY\PROJECT p
                LEFT JOIN p.tasks t
                GROUP BY p.id
            DQL)->getResult()
        );

        dump(
            $this->createQueryBuilder('p')
                ->select('NEW App\DTO\ProjectWithTaskCountDTO(p.id, p.name, COUNT(t.id))')
                ->leftJoin('p.tasks', 't')
                ->groupBy('p.id')
                ->getQuery()
                ->getResult()
        );
        
        return $this->createQueryBuilder('p')
            ->select('p as project', 'COUNT(t.id) as taskCount')
            ->leftJoin('p.tasks', 't')
            ->groupBy('p.id')
            ->getQuery()
            ->getResult();
    }

    public function getQueryBuilderFindAllWithTaskCount() : QueryBuilder {
        return (
            $this->createQueryBuilder('p')
                ->select('NEW App\DTO\ProjectWithTaskCountDTO(p.id, p.name, COUNT(t.id))')
                ->leftJoin('p.tasks', 't')
                ->groupBy('p.id')
        );
    }

    public function paginateProjectsWithPaginator(int $page, int $limit) : Paginator {
        return new Paginator($this
            ->getQueryBuilderFindAllWithTaskCount()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery(),
            false
        );
    }

    private function createQueryWithFilters(QueryBuilder $qb, bool $isAdmin = false, string $title = ''): Query
    {
        if ($isAdmin) {
            $qb->andWhere('t.deletedAt IS NULL');
        }

        // Rechercher par titre (si renseigné)
        if ($title) {
            $qb->andWhere('t.title LIKE :title')
                ->setParameter('title', '%' . $title . '%');
        }
        return $qb->getQuery();
    }

    // Use KnpPaginatorBundle
    public function paginateProjects(bool $isAdmin, string $title = '', int $page = 1, int $limit = 2): PaginationInterface
    {
        $qb = $this->createQueryBuilder('p');
        $query = $this->createQueryWithFilters($qb, $isAdmin, $title);
        return $this->paginator->paginate($query, $page, $limit, ['distinct' => true, 'sortFieldAllowList' => ['p.id']]);
    }

//    /**
//     * @return Project[] Returns an array of Project objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Project
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
