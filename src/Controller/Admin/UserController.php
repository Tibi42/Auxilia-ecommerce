<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    /**
     * Liste tous les utilisateurs de la plateforme
     */
    #[Route('/admin/users', name: 'app_admin_users')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * Affiche le profil détaillé d'un utilisateur
     */
    #[Route('/admin/users/{id}', name: 'app_admin_user_show')]
    public function show(int $id, UserRepository $userRepository, \Doctrine\ORM\EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_admin_users');
        }

        // Récupère les 5 dernières commandes de l'utilisateur pour l'activité récente
        $recentOrders = $entityManager->getRepository(\App\Entity\Order::class)->findBy(
            ['user' => $user],
            ['dateat' => 'DESC'],
            5
        );

        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
            'recentOrders' => $recentOrders,
        ]);
    }

    /**
     * Modifie les informations d'un utilisateur (rôles, email, profil)
     */
    #[Route('/admin/users/edit/{id}', name: 'app_admin_user_edit')]
    public function edit(int $id, \Symfony\Component\HttpFoundation\Request $request, UserRepository $userRepository, \Doctrine\ORM\EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_admin_users');
        }

        $form = $this->createForm(\App\Form\Admin\UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur mis à jour avec succès.');
            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Supprime un compte utilisateur
     * Sécurisé par CSRF et empêche la suppression des administrateurs
     */
    #[Route('/admin/users/delete/{id}', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(int $id, \Symfony\Component\HttpFoundation\Request $request, UserRepository $userRepository, \Doctrine\ORM\EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_admin_users');
        }

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            // Sécurité : Empêcher la suppression d'un administrateur
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                $this->addFlash('error', 'Les comptes administrateurs ne peuvent pas être supprimés.');
                return $this->redirectToRoute('app_admin_users');
            }
            // Sécurité supplémentaire : Empêcher la suppression de soi-même
            if ($user === $this->getUser()) {
                $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
                return $this->redirectToRoute('app_admin_users');
            }

            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }

        return $this->redirectToRoute('app_admin_users');
    }
}
