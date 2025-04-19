<?php

namespace App\Repository;

use App\Entity\Statut;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Statut>
 */
class StatutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Statut::class);
    }

//    /**
//     * @return Statut[] Returns an array of Statut objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Statut
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


// Ajouter ces méthodes à votre StatutRepository.php existant

/**
 * Compte le nombre de statuts par type
 */
public function countByType(\App\Enum\StatusType $statusType): int
{
    return $this->createQueryBuilder('s')
        ->select('COUNT(s.id)')
        ->where('s.type_statut = :type')
        ->setParameter('type', $statusType->value)
        ->getQuery()
        ->getSingleScalarResult();
}

/**
 * Récupère la distribution des statuts actuels
 */
public function getStatusDistribution(): array
{
    $qb = $this->createQueryBuilder('s');
    $qb->select('s.type_statut, COUNT(s.id) as count')
       ->groupBy('s.type_statut');
    
    $results = $qb->getQuery()->getResult();
    
    $distribution = [];
    foreach ($results as $result) {
        $distribution[$result['type_statut']] = $result['count'];
    }
    
    return $distribution;
}
}
