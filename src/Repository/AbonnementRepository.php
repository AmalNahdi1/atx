<?php

namespace App\Repository;

use App\Entity\Abonnement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Abonnement>
 *
 * @method Abonnement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Abonnement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Abonnement[]    findAll()
 * @method Abonnement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AbonnementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Abonnement::class);
    }

    public function save(Abonnement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Abonnement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

// QueryBuilder pour les requetes personnalisés
public function findByKeywordAndSort($keyword, $sortBy)
{
    $queryBuilder = $this->createQueryBuilder('a');
    
    if (!empty($keyword)) {
        // Add the WHERE clause to filter by keyword
        $queryBuilder->andWhere('a.typeAb LIKE :keyword OR a.modePaiementAb LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%');
    }
    
    // Add the ORDER BY clause to sort the results
    if ($sortBy === 'type') {
        $queryBuilder->orderBy('a.typeAb', 'ASC');
    } elseif ($sortBy === 'prix') {
        $queryBuilder->orderBy('a.prixAb', 'ASC');
    } elseif ($sortBy === 'modepaiement') {
        $queryBuilder->orderBy('a.modePaiementAb', 'ASC');
    } else {
        $queryBuilder->orderBy('a.id', 'ASC'); // Default sorting by 'id'
    }
    
    return $queryBuilder->getQuery()->getResult();
}


//    /**
//     * @return Abonnement[] Returns an array of Abonnement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Abonnement
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
