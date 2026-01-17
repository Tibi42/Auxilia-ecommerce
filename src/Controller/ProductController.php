<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Contrôleur gérant l'affichage du catalogue de produits côté client
 * 
 * Permet aux utilisateurs de naviguer dans le catalogue, d'appliquer des filtres de catégorie,
 * de rechercher des produits par nom ou description, et de trier les résultats.
 */
final class ProductController extends AbstractController
{
    /**
     * Liste les produits avec filtres, recherche, tri et pagination
     * 
     * @param ProductRepository $productRepository Le repository pour les produits
     * @param CategoryRepository $categoryRepository Le repository pour les catégories
     * @param PaginatorInterface $paginator Le service de pagination KNP
     * @param Request $request La requête HTTP entrante
     * @return Response Une instance de Response vers la vue du catalogue
     */
    #[Route('/product', name: 'app_product')]
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository, PaginatorInterface $paginator, Request $request): Response
    {
        // Récupération des paramètres de l'URL (limite, tri, recherche, catégorie)
        $limit = $request->query->getInt('limit', 9);
        $sort = $request->query->get('sort', 'p.price');
        $direction = $request->query->get('direction', 'asc');
        $q = $request->query->get('q');
        $categoryName = $request->query->get('category');

        // Sécurité : Liste blanche des champs de tri autorisés
        $allowedSorts = ['p.price', 'p.name', 'p.id'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'p.price';
        }

        // Construction de la requête dynamique avec QueryBuilder
        $queryBuilder = $productRepository->createQueryBuilder('p');

        // Filtre par catégorie
        if ($categoryName) {
            $queryBuilder
                ->andWhere('p.category = :category')
                ->setParameter('category', $categoryName);
        }

        // Filtre de recherche si un mot-clé est saisi
        if ($q) {
            $queryBuilder
                ->andWhere('p.name LIKE :q OR p.description LIKE :q')
                ->setParameter('q', '%' . $q . '%');
        }

        $queryBuilder->orderBy($sort, $direction);

        // Mise en place de la pagination via KnpPaginatorBundle
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            $limit
        );

        return $this->render('product/index.html.twig', [
            'pagination' => $pagination,
            'categories' => $categoryRepository->findAllOrderedByName(),
            'currentLimit' => $limit,
            'currentSort' => $sort,
            'currentDirection' => $direction,
            'currentCategory' => $categoryName,
            'q' => $q
        ]);
    }
}
