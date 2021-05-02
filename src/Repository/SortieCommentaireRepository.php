<?php

namespace App\Repository;

use App\Entity\SortieCommentaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SortieCommentaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method SortieCommentaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method SortieCommentaire[]    findAll()
 * @method SortieCommentaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieCommentaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SortieCommentaire::class);
    }

    // /**
    //  * @return SortieCommentaire[] Returns an array of SortieCommentaire objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SortieCommentaire
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
