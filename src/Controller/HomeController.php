<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur gérant la page d'accueil du site
 */
final class HomeController extends AbstractController
{
    /**
     * Affiche la page d'accueil avec les derniers produits ajoutés
     */
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository): Response
    {
        // Récupère tous les produits mis en vedette pour la section "Produits à la une"
        $featuredProducts = $productRepository->findFeatured();

        // Si aucun produit n'est mis en vedette, on prend les derniers
        if (empty($featuredProducts)) {
            $featuredProducts = $productRepository->findBy([], ['id' => 'DESC'], 5);
        }

        // Liste de commentaires clients mockés pour la démonstration
        $testimonials = [
            [
                'author' => 'Marie Laurent',
                'rating' => 5,
                'content' => 'Une expérience d\'achat incroyable ! Les produits sont de très haute qualité et la livraison a été ultra rapide. Je repasserai commande sans hésiter.',
                'avatar' => 'https://i.pravatar.cc/150?u=marie',
                'date' => 'Il y a 2 jours'
            ],
            [
                'author' => 'Jean-Pierre Martin',
                'rating' => 4,
                'content' => 'Très satisfait de mon nouvel ordinateur. Le service client a été de bon conseil pour m\'aider à choisir le modèle adapté à mes besoins.',
                'avatar' => 'https://i.pravatar.cc/150?u=jeanpierre',
                'date' => 'Il y a 1 semaine'
            ],
            [
                'author' => 'Sophie Durand',
                'rating' => 5,
                'content' => 'Le meilleur site e-commerce que j\'ai utilisé récemment. L\'interface est fluide et le suivi de commande est très précis. Bravo !',
                'avatar' => 'https://i.pravatar.cc/150?u=sophie',
                'date' => 'Il y a 2 semaines'
            ]
        ];

        // Liste des partenaires/sociétés (GAFAM)
        $partners = [
            ['name' => 'Google', 'logo' => 'https://www.vectorlogo.zone/logos/google/google-ar21.svg'],
            ['name' => 'Apple', 'logo' => 'https://www.vectorlogo.zone/logos/apple/apple-ar21.svg'],
            ['name' => 'Facebook', 'logo' => 'https://www.vectorlogo.zone/logos/facebook/facebook-ar21.svg'],
            ['name' => 'Amazon', 'logo' => 'https://www.vectorlogo.zone/logos/amazon/amazon-ar21.svg'],
            ['name' => 'Microsoft', 'logo' => 'https://www.vectorlogo.zone/logos/microsoft/microsoft-ar21.svg'],
        ];

        return $this->render('home/index.html.twig', [
            'featuredProducts' => $featuredProducts,
            'testimonials' => $testimonials,
            'partners' => $partners,
        ]);
    }
}
