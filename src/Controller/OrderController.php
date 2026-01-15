<?php

namespace App\Controller;

use App\Service\CartService;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur gérant les commandes côté client
 */
class OrderController extends AbstractController
{
    /**
     * Liste l'historique des commandes de l'utilisateur connecté
     */
    #[Route('/profile/orders', name: 'app_orders')]
    public function index(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();

        // Redirige vers la connexion si l'utilisateur n'est pas authentifié
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupère les commandes de l'utilisateur, de la plus récente à la plus ancienne
        $orders = $orderRepository->findBy(
            ['user' => $user],
            ['dateat' => 'DESC']
        );

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    /**
     * Affiche les détails d'une commande spécifique de l'utilisateur
     */
    #[Route('/profile/orders/{id}', name: 'app_order_show')]
    public function show(int $id, OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupère la commande par son ID
        $order = $orderRepository->find($id);

        // Vérifie que la commande existe et appartient bien à l'utilisateur connecté
        if (!$order || $order->getUser() !== $user) {
            $this->addFlash('error', 'Commande introuvable.');
            return $this->redirectToRoute('app_orders');
        }

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * Vérifie les informations de l'utilisateur avant de valider la commande
     */
    #[Route('/checkout', name: 'app_order_checkout')]
    public function checkout(CartService $cartService): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour passer une commande.');
            return $this->redirectToRoute('app_login');
        }

        // Vérification si le panier est vide
        if (empty($cartService->getFullCart())) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('cart_index');
        }

        // Vérification des coordonnées de livraison et contact
        if (
            empty($user->getAddress()) ||
            empty($user->getCity()) ||
            empty($user->getPostalCode()) ||
            empty($user->getPhone()) ||
            empty($user->getFirstName()) ||
            empty($user->getLastName())
        ) {
            $this->addFlash('warning', 'Veuillez compléter vos coordonnées de livraison et votre profil avant de passer commande.');
            return $this->redirectToRoute('app_profile');
        }

        // Si les coordonnées sont complètes, on pourra procéder à la création de la commande
        // Pour l'instant, on redirige vers une page de confirmation ou un récapitulatif
        return $this->render('order/checkout_confirm.html.twig');
    }
}
