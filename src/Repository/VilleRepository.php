<?php

namespace App\Repository;

use App\Entity\Ville;
use App\Services\SearchVille;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @method Ville|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ville|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ville[]    findAll()
 * @method Ville[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VilleRepository extends ServiceEntityRepository
{
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Ville::class);
        $this->paginator = $paginator;
    }

    public function getVillesByCodePostal(string $value): QueryBuilder
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.codePostal LIKE :value')
            ->setParameter('value', "%{$value}%")
            ->orderBy('v.nom', 'ASC')
            ;
    }

    public function findSearchVillePaginate(SearchVille $searchVille, int $nbreResultat): PaginationInterface
    {
        $query = $this->getSearchQueryVille($searchVille, false)->getQuery();
        return $this->paginator->paginate($query, $searchVille->page, $nbreResultat);
    }

    public function countResultSearchVille(SearchVille $searchVille): int
    {
        return $this->getSearchQueryVille($searchVille, true)->getQuery()->getSingleScalarResult();
    }

    private function getSearchQueryVille(SearchVille $searchVille, bool $count): QueryBuilder
    {
        $query = $this
            ->createQueryBuilder('v')
            ->leftJoin('v.sorties', 's');

        if ($count) {
            $query = $query
                ->select('COUNT(DISTINCT v)');
        }
        else {
            $query = $query
                ->select('v', 's');
        }

        if (!empty($searchVille->keyword)) {
            $query = $query
                ->where($query->expr()->orX(
                    $query->expr()->like('v.id', ':keyword'),
                    $query->expr()->like('v.nom', ':keyword'),
                    $query->expr()->like('v.codePostal', ':keyword')
                ))
                ->setParameter('keyword', "%{$searchVille->keyword}%");
        }

        return $query;
    }
}
