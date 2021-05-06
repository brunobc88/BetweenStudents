<?php

namespace App\Repository;

use App\Entity\Sortie;
use App\Services\SearchSortie;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Sortie::class);
        $this->paginator = $paginator;
    }

    public function findSearchSortiePaginate(SearchSortie $searchSortie): PaginationInterface
    {
        $query = $this->getSearchQuerySortie($searchSortie)->getQuery();
        return $this->paginator->paginate($query, $searchSortie->page, 12);
    }

    public function findSearchSortie(SearchSortie $searchSortie): array
    {
        return $this->getSearchQuerySortie($searchSortie)->getQuery()->getResult();
    }

    public function findSortie(int $id): Sortie
    {
        return $this->createQueryBuilder('s')
            ->select('s', 'o', 'p', 'e', 'v', 'i', 'c')
            ->leftJoin('s.organisateur', 'o')
            ->leftJoin('s.participants', 'p')
            ->leftJoin('s.etat', 'e')
            ->leftJoin('s.ville', 'v')
            ->leftJoin('s.images', 'i')
            ->leftJoin('s.campus', 'c')
            ->andWhere('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function getSearchQuerySortie(SearchSortie $searchSortie): QueryBuilder
    {
        $query = $this
            ->createQueryBuilder('s')
            ->select('s', 'o', 'p', 'e', 'v', 'i', 'c')
            ->leftJoin('s.organisateur', 'o')
            ->leftJoin('s.participants', 'p')
            ->leftJoin('s.etat', 'e')
            ->leftJoin('s.ville', 'v')
            ->leftJoin('s.images', 'i')
            ->leftJoin('s.campus', 'c');

        if (!empty($searchSortie->keyword)) {
            $query = $query
                ->where($query->expr()->orX(
                    $query->expr()->like('s.nom', ':keyword'),
                    $query->expr()->like('s.description', ':keyword')
                ))
                ->setParameter('keyword', "%{$searchSortie->keyword}%");
        }

        if (empty($searchSortie->archive)) {
            $query = $query
                ->andWhere('e = 2');
        }

        if (!empty($searchSortie->archive)) {
            $query = $query
                ->andWhere('e = 5')
                ->andWhere('s.dateDebut < :today')
                ->andWhere('s.dateDebut > :filtre1MonthArchive')
                ->setParameter('today', new DateTime())
                ->setParameter('filtre1MonthArchive', date_modify(new DateTime(), '-1 month'));
        }

        if (!empty($searchSortie->campus)) {
            $query = $query
                ->andWhere('c = :campus')
                ->setParameter('campus', $searchSortie->campus);
        }

        if (!empty($searchSortie->dateMin)) {
            $query = $query
                ->andWhere('s.dateDebut > :dateMin')
                ->setParameter('dateMin', $searchSortie->dateMin);
        }

        if (!empty($searchSortie->dateMax)) {
            $query = $query
                ->andWhere('s.dateDebut < :dateMax')
                ->setParameter('dateMax', $searchSortie->dateMax);
        }

        if (!empty($searchSortie->organisateur)) {
            $query = $query
                ->orWhere('e <= 4')
                ->andWhere('o = :organisateur')
                ->setParameter('organisateur', $searchSortie->organisateur);
        }

        if (!empty($searchSortie->participant)) {
            $query = $query
                ->orWhere('e = 3 OR e = 4')
                ->andWhere('p = :participant')
                ->andWhere('o != :participant')
                ->setParameter('participant', $searchSortie->participant);
        }

        if (!empty($searchSortie->both)) {
            $query = $query
                ->orWhere('e = 3 OR e = 4')
                ->andWhere('p = :user')
                ->orWhere('o = :user AND e <= 4')
                ->setParameter('user', $searchSortie->both);
        }

        dump($query->getQuery()->getSQL());

        return $query;
    }
}
