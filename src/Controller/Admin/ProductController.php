<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\Admin\ProductType;
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
     * Liste tous les produits pour l'administration
     * 
     * @param ProductRepository $productRepository Le repository pour récupérer tous les produits
     * @return Response Une instance de Response vers la liste des produits admin
     */
    #[Route('/admin/products', name: 'app_admin_products')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('admin/product/index.html.twig', [
            'products' => $products,
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
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère le fichier image du formulaire
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                // Génère un nom de fichier unique et sécurisé
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

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
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('products_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la nouvelle image.');
                }

                // Supprime l'ancienne image du serveur si elle existe
                if ($product->getImageName()) {
                    $oldImagePath = $this->getParameter('products_directory') . '/' . $product->getImageName();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
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
            // Supprime le fichier image du serveur avant de supprimer le produit
            if ($product->getImageName()) {
                $imagePath = $this->getParameter('products_directory') . '/' . $product->getImageName();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('success', 'Produit supprimé avec succès.');
        }

        return $this->redirectToRoute('app_admin_products');
    }
}
