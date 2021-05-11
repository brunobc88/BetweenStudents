<?php

namespace App\Repository;

use App\Entity\Campus;
use App\Services\SearchCampus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @method Campus|null find($id, $lockMode = null, $lockVersion = null)
 * @method Campus|null findOneBy(array $criteria, array $orderBy = null)
 * @method Campus[]    findAll()
 * @method Campus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CampusRepository extends ServiceEntityRepository
{
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Campus::class);
        $this->paginator = $paginator;
    }

    public function findSearchCampusPaginate(SearchCampus $searchCampus, int $nbreResultat): PaginationInterface
    {
        $query = $this->getSearchQueryCampus($searchCampus, false)->getQuery();
        return $this->paginator->paginate($query, $searchCampus->page, $nbreResultat);
    }

    public function countResultSearchCampus(SearchCampus $searchCampus): int
    {
        return $this->getSearchQueryCampus($searchCampus, true)->getQuery()->getSingleScalarResult();
    }

    private function getSearchQueryCampus(SearchCampus $searchCampus, bool $count): QueryBuilder
    {
        $query = $this
            ->createQueryBuilder('c')
            ->leftJoin('c.ville', 'v');

        if ($count) {
            $query = $query
                ->select('COUNT(DISTINCT c)');
        }
        else {
            $query = $query
                ->select('c', 'v');
        }

        if (!empty($searchCampus->keyword)) {
            $query = $query
                ->where($query->expr()->orX(
                    $query->expr()->like('c.id', ':keyword'),
                    $query->expr()->like('c.nom', ':keyword'),
                    $query->expr()->like('v.nom', ':keyword'),
                    $query->expr()->like('v.codePostal', ':keyword')
                ))
                ->setParameter('keyword', "%$searchCampus->keyword%");
        }

        return $query;
    }
}
