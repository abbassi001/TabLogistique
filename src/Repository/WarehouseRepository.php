<?php

namespace App\Repository;

use App\Entity\Warehouse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Warehouse>
 *
 * @method Warehouse|null find($id, $lockMode = null, $lockVersion = null)
 * @method Warehouse|null findOneBy(array $criteria, array $orderBy = null)
 * @method Warehouse[]    findAll()
 * @method Warehouse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WarehouseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Warehouse::class);
    }

    /**
     * Enregistre un entrepôt en base de données
     */
    public function save(Warehouse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime un entrepôt de la base de données
     */
    public function remove(Warehouse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve tous les entrepôts actifs
     * 
     * @return Warehouse[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.actif = :val')
            ->setParameter('val', true)
            ->orderBy('w.nomEntreprise', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche d'entrepôts par terme de recherche
     */
    public function findBySearchTerm(string $term): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.actif = :actif')
            ->andWhere('
                w.codeUt LIKE :term OR 
                w.nomEntreprise LIKE :term OR 
                w.adresseWarehouse LIKE :term OR 
                w.ville LIKE :term
            ')
            ->setParameter('actif', true)
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('w.nomEntreprise', 'ASC')
            ->getQuery()
            ->getResult();
    }
}