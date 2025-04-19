<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ColisRepository;
use App\Repository\UserRepository;
use App\Repository\EmployeRepository;
use App\Repository\ExpediteurRepository;
use App\Repository\DestinataireRepository;
use App\Repository\StatutRepository;
use App\Repository\TransportRepository;
use App\Repository\WarehouseRepository;
use App\Entity\Statut;
use App\Enum\StatusType;

#[Route('/admin/dashboard')]
#[IsGranted('ROLE_ADMIN')]
class AdminDashboardController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_admin_dashboard')]
    public function index(
        ColisRepository $colisRepository,
        UserRepository $userRepository,
        EmployeRepository $employeRepository,
        ExpediteurRepository $expediteurRepository,
        DestinataireRepository $destinataireRepository,
        StatutRepository $statutRepository,
        TransportRepository $transportRepository,
        WarehouseRepository $warehouseRepository
    ): Response {
        // Statistiques générales
        $stats = [
            'colis' => [
                'total' => $colisRepository->count([]),
                'recent' => $colisRepository->countRecentColis(30), // Colis des 30 derniers jours
                'trend' => $this->calculateTrend($colisRepository->countMonthlyForYear()),
            ],
            'users' => [
                'total' => $userRepository->count([]),
                'active' => $userRepository->count(['isActive' => true]),
            ],
            'employes' => [
                'total' => $employeRepository->count([]),
            ],
            'expediteurs' => [
                'total' => $expediteurRepository->count([]),
                'recent' => $expediteurRepository->countRecentExpediteurs(30), // 30 derniers jours
            ],
            'destinataires' => [
                'total' => $destinataireRepository->count([]),
                'recent' => $destinataireRepository->countRecentDestinataires(30), // 30 derniers jours
            ],
            'warehouses' => [
                'total' => $warehouseRepository->count([]),
            ],
        ];

        // Données pour les graphiques
        $statusChartData = $this->getStatusChartData($statutRepository);
        $monthlyData = $this->getColisMonthlyData($colisRepository);
        $transportChartData = $this->getTransportChartData($transportRepository);

        // Récupération des top warehouses
        $warehouses = $warehouseRepository->findBy([], ['id' => 'DESC'], 5);
        $topWarehouses = [];
        
        foreach ($warehouses as $warehouse) {
            $topWarehouses[] = [
                'code' => $warehouse->getCodeUt(),
                'nom' => $warehouse->getNomEntreprise() ?: 'Entrepôt ' . $warehouse->getCodeUt(),
                'activity' => rand(10, 100), // À remplacer par des données réelles
                'performance' => rand(30, 95) // À remplacer par des données réelles
            ];
        }
        
        // Récupérer les activités récentes
        $activities = $this->getRecentActivities();
        
        // Simuler des alertes pour l'exemple
        $alerts = [
            [
                'message' => 'Colis TAB-000125-2025 bloqué en douane depuis 5 jours',
                'date' => new \DateTime('-2 days'),
            ],
            [
                'message' => 'Stock faible à l\'entrepôt Paris-Nord',
                'date' => new \DateTime('-1 day'),
            ]
        ];
        
        // Simuler des tâches pour l'exemple
        $tasks = [
            [
                'description' => 'Valider les expéditions du jour',
                'deadline' => new \DateTime('+1 day'),
            ],
            [
                'description' => 'Faire le point avec le transporteur XYZ',
                'deadline' => new \DateTime('+3 days'),
            ]
        ];
        
        // Données pour le graphique mensuel des colis
        $monthlyDeliveredData = array_map(function($val) { 
            return max(0, round($val * 0.85)); // Approximation pour l'exemple: 85% des colis sont livrés
        }, $monthlyData['data']);

        // Liste des colis récents
        $recentColis = $colisRepository->findBy([], ['id' => 'DESC'], 5);

        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => $stats,
            'status_data' => $statusChartData['data'],
            'monthly_data' => [
                'sent' => $monthlyData['data'],
                'delivered' => $monthlyDeliveredData,
            ],
            'activities' => $activities,
            'top_warehouses' => $topWarehouses,
            'alerts' => $alerts,
            'tasks' => $tasks,
            'recentColis' => $recentColis,
        ]);
    }

    /**
     * Calcule la tendance (pourcentage d'augmentation/diminution)
     */
    private function calculateTrend(array $data): float
    {
        if (count($data) < 2) {
            return 0;
        }

        // Extraire les deux derniers mois
        $values = array_values($data);
        $currentMonth = end($values);
        $previousMonth = prev($values);

        if ($previousMonth == 0) {
            return 100; // Si le mois précédent était à zéro, on considère une augmentation de 100%
        }

        return round((($currentMonth - $previousMonth) / $previousMonth) * 100, 2);
    }

    /**
     * Récupère les données pour le graphique des statuts
     */
    private function getStatusChartData(StatutRepository $statutRepository): array
    {
        $statusCounts = [];
        
        // Comptage manuel pour chaque type de statut
        foreach (StatusType::cases() as $statusType) {
            $count = $statutRepository->countByType($statusType);
            $statusCounts[$statusType->getLabel()] = $count;
        }

        return [
            'labels' => array_keys($statusCounts),
            'data' => array_values($statusCounts),
            'colors' => [
                '#ffc107', // En attente - warning
                '#20c997', // Reçu - teal
                '#0dcaf0', // En transit - info
                '#0d6efd', // En livraison - primary
                '#198754', // Livré - success
                '#dc3545', // Retourné - danger
                '#fd7e14', // Bloqué douane - orange
            ],
        ];
    }

    /**
     * Récupère les données mensuelles pour les colis
     */
    private function getColisMonthlyData(ColisRepository $colisRepository): array
    {
        $monthlyData = $colisRepository->countMonthlyForYear();
        $frenchMonths = [
            1 => 'Jan', 2 => 'Fév', 3 => 'Mar', 4 => 'Avr', 5 => 'Mai', 6 => 'Juin',
            7 => 'Juil', 8 => 'Août', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'
        ];

        // Formater les données pour les graphiques
        $labels = [];
        $data = [];

        for ($i = 1; $i <= 12; $i++) {
            $labels[] = $frenchMonths[$i];
            $data[] = $monthlyData[$i] ?? 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Récupère les données pour le graphique des transports
     */
    private function getTransportChartData(TransportRepository $transportRepository): array
    {
        $types = $transportRepository->countByType();
        
        return [
            'labels' => array_keys($types),
            'data' => array_values($types),
            'colors' => [
                '#4e73df', // Aérien
                '#1cc88a', // Maritime
                '#36b9cc', // Ferroviaire
                '#f6c23e', // Routier
                '#e74a3b', // Autre
            ],
        ];
    }

    /**
     * Récupère les activités récentes (simulées pour l'exemple)
     */
    private function getRecentActivities(): array
    {
        // Dans une implémentation réelle, vous pourriez avoir une table
        // d'audit_logs ou d'activités pour stocker ces informations
        
        return [
            [
                'type' => 'colis',
                'action' => 'create',
                'description' => 'Nouveau colis TAB-000123-2025 créé',
                'date' => new \DateTime('-1 hour'),
                'user' => 'Jean Dupont'
            ],
            [
                'type' => 'statut',
                'action' => 'update',
                'description' => 'Statut du colis TAB-000122-2025 mis à jour vers "En transit"',
                'date' => new \DateTime('-3 hours'),
                'user' => 'Marie Martin'
            ],
            [
                'type' => 'user',
                'action' => 'create',
                'description' => 'Nouvel utilisateur créé : Pierre Durand',
                'date' => new \DateTime('-1 day'),
                'user' => 'Admin Système'
            ],
            [
                'type' => 'warehouse',
                'action' => 'update',
                'description' => 'Entrepôt Paris-Nord mis à jour',
                'date' => new \DateTime('-2 days'),
                'user' => 'Sophie Lefebvre'
            ],
            [
                'type' => 'colis',
                'action' => 'delete',
                'description' => 'Colis TAB-000120-2025 supprimé',
                'date' => new \DateTime('-3 days'),
                'user' => 'Admin Système'
            ],
        ];
    }
}