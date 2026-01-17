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

        // Récupère toutes les commandes de l'utilisateur pour la section "Commandes associées"
        $allOrders = $entityManager->getRepository(\App\Entity\Order::class)->findBy(
            ['user' => $user],
            ['dateat' => 'DESC']
        );

        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
            'recentOrders' => $recentOrders,
            'allOrders' => $allOrders,
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

    /**
     * Réinitialise le mot de passe d'un utilisateur
     * Génère un nouveau mot de passe temporaire et le hash
     */
    #[Route('/admin/users/reset-password/{id}', name: 'app_admin_user_reset_password', methods: ['POST'])]
    public function resetPassword(int $id, \Symfony\Component\HttpFoundation\Request $request, UserRepository $userRepository, \Doctrine\ORM\EntityManagerInterface $entityManager, \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_admin_users');
        }

        if ($this->isCsrfTokenValid('reset-password' . $user->getId(), $request->request->get('_token'))) {
            // Génération d'un mot de passe temporaire aléatoire
            $temporaryPassword = bin2hex(random_bytes(8)); // Génère un mot de passe de 16 caractères
            
            // Hash du nouveau mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $temporaryPassword);
            $user->setPassword($hashedPassword);
            
            $entityManager->flush();
            
            // Message de succès avec le mot de passe temporaire
            $this->addFlash('success', sprintf(
                'Le mot de passe de l\'utilisateur %s a été réinitialisé. Nouveau mot de passe temporaire : <strong>%s</strong> (À communiquer à l\'utilisateur)',
                $user->getEmail(),
                $temporaryPassword
            ));
        }

        return $this->redirectToRoute('app_admin_user_show', ['id' => $id]);
    }

    /**
     * Active ou désactive un compte utilisateur
     * Empêche la désactivation des administrateurs et de soi-même
     */
    #[Route('/admin/users/toggle-active/{id}', name: 'app_admin_user_toggle_active', methods: ['POST'])]
    public function toggleActive(int $id, \Symfony\Component\HttpFoundation\Request $request, UserRepository $userRepository, \Doctrine\ORM\EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_admin_users');
        }

        if ($this->isCsrfTokenValid('toggle-active' . $user->getId(), $request->request->get('_token'))) {
            // Sécurité : Empêcher la désactivation d'un administrateur
            if (in_array('ROLE_ADMIN', $user->getRoles()) && $user->isActive()) {
                $this->addFlash('error', 'Les comptes administrateurs ne peuvent pas être désactivés.');
                return $this->redirectToRoute('app_admin_user_show', ['id' => $id]);
            }
            
            // Sécurité supplémentaire : Empêcher la désactivation de soi-même
            if ($user === $this->getUser() && $user->isActive()) {
                $this->addFlash('error', 'Vous ne pouvez pas désactiver votre propre compte.');
                return $this->redirectToRoute('app_admin_user_show', ['id' => $id]);
            }

            // Bascule le statut du compte
            $user->setIsActive(!$user->isActive());
            $entityManager->flush();

            $status = $user->isActive() ? 'activé' : 'désactivé';
            $this->addFlash('success', sprintf('Le compte de %s a été %s avec succès.', $user->getEmail(), $status));
        }

        return $this->redirectToRoute('app_admin_user_show', ['id' => $id]);
    }
}
