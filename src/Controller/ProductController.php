<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur gérant l'affichage du catalogue de produits côté client
 */
final class ProductController extends AbstractController
{
    /**
     * Liste les produits avec filtres, recherche, tri et pagination
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
            'categories' => $categoryRepository->findAll(),
            'currentLimit' => $limit,
            'currentSort' => $sort,
            'currentDirection' => $direction,
            'currentCategory' => $categoryName,
            'q' => $q
        ]);
    }
}
