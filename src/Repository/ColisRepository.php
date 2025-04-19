<?php

namespace App\Repository;

use App\Entity\Colis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Colis>
 */
class ColisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Colis::class);
    }
    
    /**
     * Trouve tous les colis avec pagination, filtres et tri
     *
     * @param int $page Le numéro de page
     * @param int $limit Nombre d'éléments par page
     * @param array $filters Filtres (codeTracking, dateMin, dateMax, expediteur, destinataire...)
     * @param array $order Ordre de tri (champ, direction)
     * @return array
     */
    public function findAllPaginated(int $page = 1, int $limit = 10, array $filters = [], array $order = ['id' => 'DESC']): array
    {
        $query = $this->createQueryBuilder('c')
            ->leftJoin('c.expediteur', 'e')
            ->leftJoin('c.destinataire', 'd')
            ->leftJoin('c.statuts', 's', 'WITH', 's.id = (
                SELECT MAX(s2.id) FROM App\Entity\Statut s2 WHERE s2.colis = c
            )')
            ->addSelect('e', 'd', 's') // Optimise les requêtes avec des jointures
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        
        // Appliquer les filtres
        if (!empty($filters['search'])) {
            $query->andWhere('c.codeTracking LIKE :search OR d.nomEntrepriseIndividu LIKE :search OR e.nomEntrepriseIndividu LIKE :search')
                ->setParameter('search', '%' . $filters['search'] . '%');
        }
        
        if (!empty($filters['dateMin'])) {
            $query->andWhere('c.date_creation >= :dateMin')
                ->setParameter('dateMin', new \DateTime($filters['dateMin']));
        }
        
        if (!empty($filters['dateMax'])) {
            $query->andWhere('c.date_creation <= :dateMax')
                ->setParameter('dateMax', new \DateTime($filters['dateMax']));
        }
        
        if (!empty($filters['expediteur'])) {
            $query->andWhere('e.id = :expediteur')
                ->setParameter('expediteur', $filters['expediteur']);
        }
        
        if (!empty($filters['destinataire'])) {
            $query->andWhere('d.id = :destinataire')
                ->setParameter('destinataire', $filters['destinataire']);
        }
        
        if (!empty($filters['status'])) {
            $query->andWhere('s.typeStatut = :status')
                ->setParameter('status', $filters['status']);
        }
        
        // Appliquer le tri
        foreach ($order as $field => $direction) {
            $query->addOrderBy("c.$field", $direction);
        }
        
        // Utiliser le Paginator de Doctrine pour une pagination efficace
        $paginator = new Paginator($query);
        
        return [
            'items' => iterator_to_array($paginator->getIterator()),
            'totalItems' => $paginator->count(),
            'totalPages' => ceil($paginator->count() / $limit),
            'currentPage' => $page
        ];
    }

    /**
     * Vérifie si un code tracking est unique
     */
    public function isCodeTrackingUnique(string $codeTracking): bool
    {
        return null === $this->findOneBy(['codeTracking' => $codeTracking]);
    }

    /**
     * Récupère les derniers colis ajoutés
     */
    public function findLatest(int $limit = 5): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.statuts', 's', 'WITH', 's.id = (
                SELECT MAX(s2.id) FROM App\Entity\Statut s2 WHERE s2.colis = c
            )')
            ->leftJoin('c.destinataire', 'd')
            ->leftJoin('c.expediteur', 'e')
            ->addSelect('s', 'd', 'e')
            ->orderBy('c.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
 * Compte le nombre de colis créés dans les X derniers jours
 */
public function countRecentColis(int $days): int
{
    $date = new \DateTime();
    $date->modify('-' . $days . ' days');

    return $this->createQueryBuilder('c')
        ->select('COUNT(c.id)')
        ->where('c.date_creation >= :date')
        ->setParameter('date', $date)
        ->getQuery()
        ->getSingleScalarResult();
}



/**
 * Compte le nombre de colis par mois pour l'année courante
 */
public function countMonthlyForYear(int $year = null): array
{
    $year = $year ?? (int) date('Y');
    
    $conn = $this->getEntityManager()->getConnection();
    $sql = '
        SELECT EXTRACT(MONTH FROM c.date_creation) as month, COUNT(c.id) as count
        FROM colis c
        WHERE EXTRACT(YEAR FROM c.date_creation) = :year
        GROUP BY EXTRACT(MONTH FROM c.date_creation)
        ORDER BY month ASC
    ';
    
    $stmt = $conn->prepare($sql);
    $resultSet = $stmt->executeQuery(['year' => $year]);
    $results = $resultSet->fetchAllAssociative();
    
    // Initialiser tous les mois avec 0
    $months = array_fill(1, 12, 0);
    
    // Remplir avec les données réelles
    foreach ($results as $result) {
        $months[(int)$result['month']] = (int)$result['count'];
    }
    
    return $months;
}
}