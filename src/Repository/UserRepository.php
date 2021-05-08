<?php

namespace App\Repository;

use App\Entity\User;
use App\Services\SearchUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use function get_class;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, User::class);
        $this->paginator = $paginator;
    }

    public function loadUserByEmailOrPseudo(string $emailOrPseudo)
    {
        $entityManager = $this->getEntityManager();

        return $entityManager->createQuery(
            'SELECT u
                FROM App\Entity\User u
                WHERE (u.pseudo = :query OR u.email = :query)'
        )
            ->setParameter('query', $emailOrPseudo)
            ->getOneOrNullResult();
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function findSearchUserPaginate(SearchUser $searchUser, int $nbreResultat): PaginationInterface
    {
        $query = $this->getSearchQueryUser($searchUser)->getQuery();
        return $this->paginator->paginate($query, $searchUser->page, $nbreResultat);
    }

    public function findSearchUser(SearchUser $searchUser): array
    {
        return $this->getSearchQueryUser($searchUser)->getQuery()->getResult();
    }

    private function getSearchQueryUser(SearchUser $searchUser): QueryBuilder
    {
        $query = $this
            ->createQueryBuilder('u')
            ->select('u', 'c', 'sO', 'sP', 'com')
            ->leftJoin('u.campus', 'c')
            ->leftJoin('u.sortiesAsOrganisateur', 'sO')
            ->leftJoin('u.sortiesAsParticipant', 'sP')
            ->leftJoin('u.commentaires', 'com');

        if (!empty($searchUser->keyword)) {
            $query = $query
                ->where($query->expr()->orX(
                    $query->expr()->like('u.id', ':keyword'),
                    $query->expr()->like('u.email', ':keyword'),
                    $query->expr()->like('u.pseudo', ':keyword'),
                    $query->expr()->like('u.nom', ':keyword'),
                    $query->expr()->like('u.prenom', ':keyword'),
                    $query->expr()->like('u.telephone', ':keyword')
                ))
                ->setParameter('keyword', "%{$searchUser->keyword}%");
        }

        if (!empty($searchUser->campus)) {
            $query = $query
                ->andWhere('c = :campus')
                ->setParameter('campus', $searchUser->campus);
        }

        if (!empty($searchUser->isAdmin)) {
            $query = $query
                ->andWhere('u.administrateur = 1');
        }
        else {
            $query = $query
                ->andWhere('u.administrateur = 0');
        }

        if (!empty($searchUser->isActif)) {
            $query = $query
                ->andWhere('u.actif = 1');
        }
        else {
            $query = $query
                ->andWhere('u.actif = 0');
        }

        return $query;
    }
}
