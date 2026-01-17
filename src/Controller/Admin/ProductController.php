<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\Admin\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Contrôleur d'administration pour la gestion du catalogue de produits
 */
final class ProductController extends AbstractController
{
    /**
     * Liste tous les produits pour l'administration avec filtrage par catégorie
     * 
     * @param ProductRepository $productRepository Le repository pour récupérer tous les produits
     * @param Request $request La requête HTTP entrante pour récupérer le filtre
     * @return Response Une instance de Response vers la liste des produits admin
     */
    #[Route('/admin/products', name: 'app_admin_products')]
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        // Sécurité : Vérification explicite du rôle admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Récupère les paramètres de filtres depuis l'URL
        $selectedCategory = $request->query->get('category');
        $selectedStockFilter = $request->query->get('stock');
        
        // Récupère les produits selon les filtres
        $products = $productRepository->findAllByCategoryAndStock($selectedCategory, $selectedStockFilter);
        
        // Récupère toutes les catégories distinctes pour le filtre
        $categories = $productRepository->findDistinctCategories();

        return $this->render('admin/product/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'selectedStockFilter' => $selectedStockFilter,
        ]);
    }

    /**
     * Crée un nouveau produit avec gestion de l'upload d'image
     * 
     * @param Request $request La requête HTTP entrante
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités de Doctrine
     * @param SluggerInterface $slugger Le service pour générer des noms de fichiers sécurisés
     * @return Response Une instance de Response vers le formulaire ou la liste après succès
     */
    #[Route('/admin/product/new', name: 'app_admin_product_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, CategoryRepository $categoryRepository): Response
    {
        // Sécurité : Vérification explicite du rôle admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $categories = $categoryRepository->findAllOrderedByName();
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product, [
            'categories' => $categories,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère le fichier image du formulaire
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                // Sécurité : Validation du type MIME réel du fichier
                $mimeType = $imageFile->getMimeType();
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                
                if (!in_array($mimeType, $allowedMimeTypes)) {
                    $this->addFlash('error', 'Type de fichier non autorisé.');
                    return $this->redirectToRoute('app_admin_product_new');
                }
                
                // Sécurité : Déterminer l'extension à partir du MIME type
                $extensionMap = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                ];
                $extension = $extensionMap[$mimeType] ?? 'jpg';
                
                // Génère un nom de fichier unique et sécurisé
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $sluggedFilename = $slugger->slug($originalFilename);
                $newFilename = $sluggedFilename . '-' . uniqid() . '.' . $extension;
                
                // Sécurité : S'assurer que le nom de fichier ne contient pas de path traversal
                $newFilename = basename($newFilename);

                try {
                    // Déplace le fichier vers le répertoire configuré
                    $imageFile->move(
                        $this->getParameter('products_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // En cas d'erreur lors de l'upload
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }

                $product->setImageName($newFilename);
            }

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit créé avec succès.');

            return $this->redirectToRoute('app_admin_products');
        }

        return $this->render('admin/product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    /**
     * Modifie un produit existant et met à jour son image si nécessaire
     * 
     * @param Request $request La requête HTTP entrante
     * @param Product $product L'entité Product à modifier
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités de Doctrine
     * @param SluggerInterface $slugger Le service pour générer des noms de fichiers sécurisés
     * @return Response Une instance de Response vers le formulaire ou la liste après succès
     */
    #[Route('/admin/product/{id}/edit', name: 'app_admin_product_edit')]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, SluggerInterface $slugger, CategoryRepository $categoryRepository): Response
    {
        // Sécurité : Vérification explicite du rôle admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $categories = $categoryRepository->findAllOrderedByName();
        $form = $this->createForm(ProductType::class, $product, [
            'categories' => $categories,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                // Sécurité : Validation du type MIME réel du fichier
                $mimeType = $imageFile->getMimeType();
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                
                if (!in_array($mimeType, $allowedMimeTypes)) {
                    $this->addFlash('error', 'Type de fichier non autorisé.');
                    return $this->redirectToRoute('app_admin_product_edit', ['id' => $product->getId()]);
                }
                
                // Sécurité : Déterminer l'extension à partir du MIME type
                $extensionMap = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                ];
                $extension = $extensionMap[$mimeType] ?? 'jpg';
                
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $sluggedFilename = $slugger->slug($originalFilename);
                $newFilename = $sluggedFilename . '-' . uniqid() . '.' . $extension;
                
                // Sécurité : S'assurer que le nom de fichier ne contient pas de path traversal
                $newFilename = basename($newFilename);

                try {
                    $imageFile->move(
                        $this->getParameter('products_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la nouvelle image.');
                }

                // Sécurité : Protection contre path traversal lors de la suppression
                if ($product->getImageName()) {
                    $oldImagePath = $this->getParameter('products_directory') . '/' . basename($product->getImageName());
                    // S'assurer que le chemin reste dans le répertoire autorisé
                    $productsDir = realpath($this->getParameter('products_directory'));
                    $filePath = realpath($oldImagePath);
                    if ($filePath && strpos($filePath, $productsDir) === 0 && file_exists($filePath)) {
                        unlink($filePath);
                    }
                }

                $product->setImageName($newFilename);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Produit mis à jour avec succès.');

            return $this->redirectToRoute('app_admin_products');
        }

        return $this->render('admin/product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    /**
     * Supprime un produit et son image associée
     * 
     * @param Request $request La requête HTTP entrante (pour le jeton CSRF)
     * @param Product $product L'entité Product à supprimer
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités de Doctrine
     * @return Response Une redirection vers la liste des produits admin
     */
    #[Route('/admin/product/{id}/delete', name: 'app_admin_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        // Vérification du jeton CSRF pour la sécurité
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            // Sécurité : Protection contre path traversal lors de la suppression
            if ($product->getImageName()) {
                $imageName = basename($product->getImageName());
                $productsDir = realpath($this->getParameter('products_directory'));
                $imagePath = $productsDir . '/' . $imageName;
                // S'assurer que le chemin reste dans le répertoire autorisé
                $realPath = realpath($imagePath);
                if ($realPath && strpos($realPath, $productsDir) === 0 && file_exists($realPath)) {
                    unlink($realPath);
                }
            }
            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('success', 'Produit supprimé avec succès.');
        }

        return $this->redirectToRoute('app_admin_products');
    }
}
