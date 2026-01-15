<?php

namespace App\Twig;

use App\Service\CartService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Extension Twig personnalisée pour exposer des fonctionnalités globales aux templates
 */
class AppExtension extends AbstractExtension
{
    private $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Définit les fonctions personnalisées utilisables dans Twig
     */
    public function getFunctions(): array
    {
        return [
            // Permet d'utiliser {{ cart_count() }} dans n'importe quel template
            new TwigFunction('cart_count', [$this, 'getCartCount']),
        ];
    }

    /**
     * Récupère le nombre total d'articles du panier via le CartService
     */
    public function getCartCount(): int
    {
        return $this->cartService->getQuantitySum();
    }
}
