<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures pour générer des commandes de test associées aux utilisateurs
 */
class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Récupérer les utilisateurs
        $users = $manager->getRepository(User::class)->findAll();

        if (empty($users)) {
            return; // Pas d'utilisateurs, on ne crée pas de commandes
        }

        $statuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
        $totals = ['29.99', '49.99', '79.99', '119.99', '149.99', '179.99', '249.99', '299.99', '349.99', '449.99'];

        // Créer des commandes pour chaque utilisateur
        foreach ($users as $user) {
            // 1 à 3 commandes par utilisateur
            $numberOfOrders = rand(1, 3);

            for ($i = 0; $i < $numberOfOrders; $i++) {
                $order = new Order();
                $order->setUser($user);
                $order->setStatus($statuses[array_rand($statuses)]);
                $order->setTotal($totals[array_rand($totals)]);

                // Date aléatoire dans les 30 derniers jours
                $date = new \DateTime();
                $date->modify('-' . rand(0, 30) . ' days');
                $date->modify('-' . rand(0, 23) . ' hours');
                $date->modify('-' . rand(0, 59) . ' minutes');
                $order->setDateat($date);

                $manager->persist($order);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
