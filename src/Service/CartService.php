<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Service gérant la logique du panier d'achat
 */
class CartService
{
    private $requestStack;
    private $productRepository;
    private $security;
    private $entityManager;

    public function __construct(
        RequestStack $requestStack,
        ProductRepository $productRepository,
        Security $security,
        EntityManagerInterface $entityManager
    ) {
        $this->requestStack = $requestStack;
        $this->productRepository = $productRepository;
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    /**
     * Ajoute un produit au panier ou incrémente sa quantité
     */
    public function add(int $id): void
    {
        $cart = $this->getSession()->get('cart', []);

        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $this->getSession()->set('cart', $cart);
        $this->saveToUser($cart);
    }

    /**
     * Retire un produit du panier ou décrémente sa quantité
     */
    public function remove(int $id): void
    {
        $cart = $this->getSession()->get('cart', []);

        if (!empty($cart[$id])) {
            if ($cart[$id] > 1) {
                $cart[$id]--;
            } else {
                unset($cart[$id]);
            }
        }

        $this->getSession()->set('cart', $cart);
        $this->saveToUser($cart);
    }

    /**
     * Supprime un produit du panier (toutes les quantités)
     */
    public function deleteAll(int $id) // Changed return type from void to implicit as per provided code
    {
        $cart = $this->getSession()->get('cart', []); // Uses new getSession() method

        if (!empty($cart[$id])) { // Condition changed from isset to !empty as per provided code
            unset($cart[$id]);
        }

        $this->getSession()->set('cart', $cart); // Uses new getSession() method
        // $this->saveToUser($cart); // Removed as per provided code
    }

    /**
     * Supprime une sélection de produits du panier
     */
    public function deleteSelection(array $ids) // Changed return type from void to implicit as per provided code
    {
        $cart = $this->getSession()->get('cart', []); // Uses new getSession() method

        foreach ($ids as $id) {
            if (isset($cart[$id])) {
                unset($cart[$id]);
            }
        }

        $this->getSession()->set('cart', $cart); // Uses new getSession() method
        // $this->saveToUser($cart); // Removed as per provided code
    }

    /**
     * Récupère le contenu détaillé du panier avec les entités Product
     */
    public function getFullCart(): array
    {
        $cart = $this->getSession()->get('cart', []); // Uses new getSession() method
        $cartData = []; // Changed variable name from $cartWithData to $cartData as per provided code

        foreach ($cart as $id => $quantity) {
            $product = $this->productRepository->find($id);

            if ($product) {
                $cartData[] = [
                    'product' => $product,
                    'quantity' => $quantity
                ];
            }
        }

        return $cartData;
    }

    /**
     * Calcule le montant total du panier
     */
    public function getTotal(): float
    {
        $total = 0; // Initialized $total directly, removed $fullCart variable as per provided code

        foreach ($this->getFullCart() as $item) { // Directly calls getFullCart() as per provided code
            $total += $item['product']->getPrice() * $item['quantity'];
        }

        return $total;
    }

    /**
     * Calcule le nombre total d'articles dans le panier
     */
    public function getQuantitySum(): int
    {
        $cart = $this->getSession()->get('cart', []); // Uses new getSession() method
        $sum = 0;

        foreach ($cart as $quantity) {
            $sum += $quantity;
        }

        return $sum;
    }

    /**
     * Récupère la session courante
     */
    private function getSession(): SessionInterface // New method added as per provided code
    {
        return $this->requestStack->getSession();
    }

    private function saveToUser(array $cart): void
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $user->setCart($cart);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }
}
