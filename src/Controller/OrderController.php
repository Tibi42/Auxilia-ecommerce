<?php

namespace App\Controller;

use App\Service\CartService;
use App\Repository\OrderRepository;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur gérant les commandes côté client
 * 
 * Ce contrôleur permet à l'utilisateur de consulter l'historique de ses commandes,
 * de voir le détail d'une commande spécifique, de passer par le processus de checkout
 * et de valider sa commande après vérification de ses coordonnées.
 */
class OrderController extends AbstractController
{
    /**
     * Liste l'historique des commandes de l'utilisateur connecté
     * 
     * @param OrderRepository $orderRepository Le repository pour récupérer les commandes
     * @return Response Une instance de Response contenant la vue de l'historique
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
     * 
     * @param int $id L'identifiant de la commande
     * @param OrderRepository $orderRepository Le repository pour récupérer la commande
     * @return Response Une instance de Response contenant la vue des détails de la commande
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
     * 
     * Vérifie si l'utilisateur est connecté, si le panier n'est pas vide et si les
     * coordonnées de livraison (adresse, ville, code postal, téléphone, nom, prénom) sont complètes.
     * 
     * @param CartService $cartService Le service gérant le panier
     * @return Response Une instance de Response vers la vue de confirmation ou une redirection
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

    /**
     * Valide la commande, crée l'entité Order et vide le panier
     * 
     * @param CartService $cartService Le service gérant le panier
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités de Doctrine
     * @return Response Une instance de Response redirigeant vers la page de succès
     */
    #[Route('/checkout/validate', name: 'app_order_validate', methods: ['POST'])]
    public function validate(CartService $cartService, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cart = $cartService->getFullCart();
        if (empty($cart)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('cart_index');
        }

        // Création de la commande
        $order = new Order();
        $order->setUser($user);
        $order->setDateat(new \DateTime());
        $order->setStatus('paid');
        $order->setTotal($cartService->getTotal());

        $entityManager->persist($order);

        foreach ($cart as $item) {
            $orderItem = new OrderItem();
            $orderItem->setOrderRef($order);
            $orderItem->setProduct($item['product']);
            $orderItem->setProductName($item['product']->getName());
            $orderItem->setQuantity($item['quantity']);
            $orderItem->setPrice($item['product']->getPrice());
            $entityManager->persist($orderItem);
        }

        $entityManager->flush();

        // Vidage du panier
        $cartService->clear();

        return $this->redirectToRoute('app_order_success');
    }

    /**
     * Affiche la page de succès après une commande
     * 
     * @return Response Une instance de Response contenant la vue de succès
     */
    #[Route('/checkout/success', name: 'app_order_success')]
    public function success(): Response
    {
        return $this->render('order/success.html.twig');
    }
}
