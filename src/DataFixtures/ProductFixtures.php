<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures pour générer un catalogue de produits de test
 */
class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Catégories
        $categoriesData = [
            ['name' => 'Électronique', 'slug' => 'electronique'],
            ['name' => 'Vêtements', 'slug' => 'vetements'],
            ['name' => 'Maison & Décoration', 'slug' => 'maison-decoration'],
            ['name' => 'Sport & Fitness', 'slug' => 'sport-fitness'],
            ['name' => 'Livres', 'slug' => 'livres'],
            ['name' => 'Jouets', 'slug' => 'jouets'],
        ];

        $categoryEntities = [];
        foreach ($categoriesData as $catData) {
            $category = new Category();
            $category->setName($catData['name']);
            $category->setSlug($catData['slug']);
            $manager->persist($category);
            $categoryEntities[$catData['slug']] = $category;
        }

        // Produits
        $products = [
            // Électronique
            [
                'name' => 'Smartphone Pro Max',
                'description' => 'Smartphone haut de gamme avec écran OLED 6.7 pouces, processeur puissant, 256 Go de stockage et triple caméra 48MP. Autonomie exceptionnelle et charge rapide.',
                'price' => '899.99',
                'stock' => 45,
                'category' => 'electronique',
            ],
            [
                'name' => 'Casque Audio Sans Fil',
                'description' => 'Casque Bluetooth avec réduction de bruit active, autonomie 30h, qualité sonore premium et confort optimal pour les longues sessions d\'écoute.',
                'price' => '179.99',
                'stock' => 32,
                'category' => 'electronique',
            ],
            [
                'name' => 'Tablette Android 10 pouces',
                'description' => 'Tablette performante avec écran Full HD, 128 Go de stockage, processeur octa-core et batterie longue durée. Parfaite pour le travail et les loisirs.',
                'price' => '299.99',
                'stock' => 28,
                'category' => 'electronique',
            ],
            [
                'name' => 'Montre Connectée Sport',
                'description' => 'Montre intelligente avec suivi GPS, moniteur de fréquence cardiaque, résistance à l\'eau et écran AMOLED. Idéale pour le sport et le quotidien.',
                'price' => '249.99',
                'stock' => 56,
                'category' => 'electronique',
            ],
            [
                'name' => 'Enceinte Bluetooth Portable',
                'description' => 'Enceinte compacte avec son stéréo puissant, résistance à l\'eau IPX7, autonomie 20h et design moderne. Parfaite pour les sorties.',
                'price' => '89.99',
                'stock' => 78,
                'category' => 'electronique',
            ],

            // Vêtements
            [
                'name' => 'T-shirt Coton Bio',
                'description' => 'T-shirt en coton biologique 100% naturel, doux et respirant. Coupe moderne et confortable, disponible en plusieurs coloris. Respectueux de l\'environnement.',
                'price' => '29.99',
                'stock' => 120,
                'category' => 'vetements',
            ],
            [
                'name' => 'Jean Slim Premium',
                'description' => 'Jean slim coupe moderne en denim stretch premium. Confortable et élégant, il s\'adapte parfaitement à votre morphologie. Qualité supérieure garantie.',
                'price' => '79.99',
                'stock' => 65,
                'category' => 'vetements',
            ],
            [
                'name' => 'Veste en Cuir Véritable',
                'description' => 'Veste en cuir véritable de qualité supérieure, doublure intérieure, coupe classique intemporelle. Un investissement durable pour votre garde-robe.',
                'price' => '349.99',
                'stock' => 18,
                'category' => 'vetements',
            ],
            [
                'name' => 'Sneakers Sport Style',
                'description' => 'Chaussures de sport élégantes avec semelle amortissante, tige en mesh respirant et design moderne. Confortables pour la ville et le sport.',
                'price' => '119.99',
                'stock' => 42,
                'category' => 'vetements',
            ],
            [
                'name' => 'Pull en Laine Mérinos',
                'description' => 'Pull chaud et doux en laine mérinos 100% naturelle. Parfait pour l\'hiver, il régule la température et évacue l\'humidité. Qualité premium.',
                'price' => '89.99',
                'stock' => 38,
                'category' => 'vetements',
            ],

            // Maison & Décoration
            [
                'name' => 'Lampe LED Design Moderne',
                'description' => 'Lampe LED avec variateur d\'intensité, design épuré et moderne. Éclairage chaleureux et économique. Parfaite pour salon ou bureau.',
                'price' => '59.99',
                'stock' => 55,
                'category' => 'maison-decoration',
            ],
            [
                'name' => 'Set de Coussins Décoratifs',
                'description' => 'Lot de 4 coussins décoratifs en coton, motifs géométriques modernes. Apportent couleur et confort à votre intérieur. Housse lavable.',
                'price' => '49.99',
                'stock' => 72,
                'category' => 'maison-decoration',
            ],
            [
                'name' => 'Tapis Moderne 200x300cm',
                'description' => 'Tapis moderne en fibres synthétiques, antidérapant et facile d\'entretien. Design contemporain qui s\'adapte à tous les intérieurs.',
                'price' => '129.99',
                'stock' => 25,
                'category' => 'maison-decoration',
            ],
            [
                'name' => 'Vase Céramique Artisanal',
                'description' => 'Vase en céramique artisanale, design unique et élégant. Parfait pour fleurs fraîches ou décoration seule. Pièce unique et authentique.',
                'price' => '39.99',
                'stock' => 48,
                'category' => 'maison-decoration',
            ],
            [
                'name' => 'Horloge Murale Design',
                'description' => 'Horloge murale moderne avec cadran minimaliste, mécanisme silencieux et pile longue durée. Design épuré pour tous les intérieurs.',
                'price' => '34.99',
                'stock' => 61,
                'category' => 'maison-decoration',
            ],

            // Sport & Fitness
            [
                'name' => 'Ballon de Football Pro',
                'description' => 'Ballon de football professionnel taille 5, cuir synthétique de qualité, cousu main. Conforme aux normes FIFA. Parfait pour l\'entraînement et les matchs.',
                'price' => '34.99',
                'stock' => 88,
                'category' => 'sport-fitness',
            ],
            [
                'name' => 'Tapis de Yoga Premium',
                'description' => 'Tapis de yoga antidérapant 6mm, matière écologique, dimensions 183x61cm. Confortable et durable pour toutes vos séances de yoga.',
                'price' => '44.99',
                'stock' => 95,
                'category' => 'sport-fitness',
            ],
            [
                'name' => 'Haltères Ajustables 2x10kg',
                'description' => 'Paire d\'haltères ajustables de 2x10kg, poignée ergonomique et système de verrouillage sécurisé. Idéal pour la musculation à domicile.',
                'price' => '89.99',
                'stock' => 34,
                'category' => 'sport-fitness',
            ],
            [
                'name' => 'Corde à Sauter Pro',
                'description' => 'Corde à sauter professionnelle avec roulements à billes, poignées ergonomiques et longueur ajustable. Parfaite pour le cardio-training.',
                'price' => '19.99',
                'stock' => 112,
                'category' => 'sport-fitness',
            ],
            [
                'name' => 'Raquette de Tennis Pro',
                'description' => 'Raquette de tennis professionnelle, cadre en graphite, cordage pré-monté. Équilibre parfait pour tous les niveaux de jeu.',
                'price' => '149.99',
                'stock' => 27,
                'category' => 'sport-fitness',
            ],

            // Livres
            [
                'name' => 'Roman Bestseller 2024',
                'description' => 'Roman à succès de l\'année 2024, édition brochée, 450 pages. Une histoire captivante qui vous tiendra en haleine jusqu\'à la dernière page.',
                'price' => '22.99',
                'stock' => 156,
                'category' => 'livres',
            ],
            [
                'name' => 'Guide de Cuisine Gastronomique',
                'description' => 'Livre de recettes gastronomiques avec photos, techniques professionnelles et 200 recettes. Un must-have pour les amateurs de cuisine.',
                'price' => '39.99',
                'stock' => 43,
                'category' => 'livres',
            ],
            [
                'name' => 'Biographie Inspirante',
                'description' => 'Biographie d\'une personnalité inspirante, récit authentique et émouvant. Découvrez le parcours exceptionnel d\'une vie hors du commun.',
                'price' => '18.99',
                'stock' => 67,
                'category' => 'livres',
            ],

            // Jouets
            [
                'name' => 'Lego Set Architecture',
                'description' => 'Set de construction LEGO Architecture, 1200 pièces, modèle détaillé d\'un monument célèbre. Pour les passionnés de construction de 12 ans et plus.',
                'price' => '89.99',
                'stock' => 29,
                'category' => 'jouets',
            ],
            [
                'name' => 'Poupée Interactive',
                'description' => 'Poupée interactive qui parle et chante, avec accessoires inclus. Développe l\'imagination et la créativité des enfants de 3 à 8 ans.',
                'price' => '49.99',
                'stock' => 54,
                'category' => 'jouets',
            ],
            [
                'name' => 'Voiture Télécommandée',
                'description' => 'Voiture télécommandée 4x4, contrôle à distance, batterie rechargeable, vitesse jusqu\'à 25 km/h. Pour enfants de 8 ans et plus.',
                'price' => '69.99',
                'stock' => 41,
                'category' => 'jouets',
            ],
        ];

        foreach ($products as $productData) {
            $product = new Product();
            $product->setName($productData['name']);
            $product->setDescription($productData['description']);
            $product->setPrice($productData['price']);
            $product->setStock($productData['stock']);
            $product->setCategory($categoryEntities[$productData['category']]->getName());
            $manager->persist($product);
        }

        $manager->flush();
    }
}
