<?php

namespace App\Controller\Admin;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    /**
     * Liste toutes les commandes de la boutique pour l'administration
     * 
     * @param Request $request La requête HTTP pour récupérer le filtre de statut
     * @param OrderRepository $orderRepository Le repository pour récupérer toutes les commandes
     * @return Response Une instance de Response vers la liste des commandes admin
     */
    #[Route('/admin/orders', name: 'app_admin_orders')]
    public function index(Request $request, OrderRepository $orderRepository): Response
    {
        // Récupération du filtre de statut depuis la requête
        $statusFilter = $request->query->get('status');
        
        // Définition des critères de recherche
        $criteria = [];
        if ($statusFilter && $statusFilter !== 'all') {
            $criteria['status'] = $statusFilter;
        }
        
        // Récupération des commandes filtrées
        $orders = $orderRepository->findBy($criteria, ['id' => 'DESC']);
        
        // Liste des statuts disponibles
        $availableStatuses = [
            'all' => 'Tous les statuts',
            'paid' => 'Payée',
            'confirmed' => 'Confirmée',
            'pending' => 'En attente',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée',
            'shipped' => 'Expédiée'
        ];

        return $this->render('admin/order/index.html.twig', [
            'orders' => $orders,
            'currentStatus' => $statusFilter ?? 'all',
            'availableStatuses' => $availableStatuses,
        ]);
    }

    /**
     * Affiche les détails d'une commande spécifique pour l'administration
     * 
     * @param int $id L'identifiant de la commande
     * @param OrderRepository $orderRepository Le repository pour récupérer la commande
     * @return Response Une instance de Response vers la vue détaillée ou une redirection
     */
    #[Route('/admin/orders/{id}', name: 'app_admin_order_show')]
    public function show(int $id, OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->find($id);

        if (!$order) {
            $this->addFlash('error', 'Commande introuvable.');
            return $this->redirectToRoute('app_admin_orders');
        }

        return $this->render('admin/order/show.html.twig', [
            'order' => $order,
        ]);
    }
}
