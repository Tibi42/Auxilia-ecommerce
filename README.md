# Auxilia E-commerce

Application e-commerce développée avec Symfony 7.x permettant la gestion complète d'une boutique en ligne avec administration.

## 🚀 Fonctionnalités principales

### Côté Client
- **Catalogue de produits** : Affichage, recherche, filtrage par catégorie et tri
- **Gestion du panier** : Ajout, modification, suppression de produits avec persistance entre sessions
- **Gestion des commandes** : Visualisation de l'historique et détails des commandes
- **Authentification** : Inscription, connexion, réinitialisation de mot de passe
- **Profil utilisateur** : Gestion des informations personnelles et historique des commandes

### Administration
- **Tableau de bord** : Statistiques globales (produits, utilisateurs, commandes)
- **Gestion des produits** : CRUD complet avec gestion du stock et des catégories
- **Gestion des utilisateurs** : Visualisation, édition, réinitialisation de mot de passe, activation/désactivation de comptes
- **Gestion des commandes** : Liste complète avec filtrage par statut et détails

## 📋 Prérequis

- PHP 8.2 ou supérieur
- Composer 2.x
- MySQL 8.0 ou supérieur (ou MariaDB 10.3+)
- Symfony CLI (optionnel)

## 🛠️ Installation

1. **Cloner le projet**
```bash
git clone <url-du-repo>
cd Auxilia-Ecommerce
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Configurer la base de données**
```bash
# Créer le fichier .env.local et configurer la connexion
DATABASE_URL="mysql://user:password@127.0.0.1:3306/auxilia_ecommerce?serverVersion=8.0&charset=utf8mb4"
```

4. **Créer la base de données et exécuter les migrations**
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. **Charger les données de test (optionnel)**
```bash
php bin/console doctrine:fixtures:load
```

6. **Lancer le serveur de développement**
```bash
symfony server:start
# ou
php -S localhost:8000 -t public
```

## 📁 Structure du projet

```
src/
├── Controller/           # Contrôleurs de l'application
│   ├── Admin/           # Contrôleurs d'administration
│   ├── CartController   # Gestion du panier
│   ├── OrderController  # Gestion des commandes
│   ├── ProductController # Catalogue produits
│   └── ...
├── Entity/              # Entités Doctrine (modèles)
│   ├── User.php         # Utilisateur
│   ├── Product.php      # Produit
│   ├── Order.php        # Commande
│   └── OrderItem.php    # Article de commande
├── Repository/          # Repositories Doctrine
├── Service/             # Services métier
│   └── CartService.php  # Service de gestion du panier
├── Form/                # Formulaires Symfony
├── Security/            # Sécurité
│   └── UserChecker.php  # Vérification des comptes désactivés
└── EventSubscriber/     # Abonnés d'événements
    └── LoginCartSubscriber.php # Synchronisation du panier à la connexion

templates/
├── admin/               # Templates d'administration
├── order/               # Templates des commandes
├── product/             # Templates des produits
└── ...
```

## 🔐 Sécurité

### Authentification
- Authentification par email/mot de passe
- Réinitialisation de mot de passe via email
- Protection CSRF sur tous les formulaires
- Validation des comptes désactivés (UserChecker)

### Autorisations
- Routes publiques : Catalogue, panier, pages statiques
- Routes authentifiées : Profil, commandes
- Routes admin : Toutes les routes `/admin/*` nécessitent le rôle `ROLE_ADMIN`

### Statuts des comptes
- Les comptes peuvent être activés/désactivés par l'administrateur
- Les comptes désactivés ne peuvent pas se connecter
- Les administrateurs ne peuvent pas être désactivés

## 🗄️ Base de données

### Principales entités

**User** : Utilisateurs
- email, password, roles
- Informations personnelles (firstName, lastName, phone, address, etc.)
- Panier persistant (cart)
- Statut actif/inactif (isActive)

**Product** : Produits
- name, description, price, stock
- Catégorie associée
- Images (chemin)

**Order** : Commandes
- Utilisateur associé
- Statut (paid, confirmed, pending, shipped, delivered, cancelled)
- Total, date
- Relation OneToMany avec OrderItem

**OrderItem** : Articles de commande
- Produit associé
- Quantité, prix unitaire, total
- Nom du produit (snapshot pour historique)

## 🔧 Services

### CartService
Gère la logique métier du panier :
- Stockage en session pour les utilisateurs non connectés
- Persistance en base de données pour les utilisateurs connectés
- Synchronisation automatique lors de la connexion (LoginCartSubscriber)

### UserChecker
Vérifie l'état des comptes lors de l'authentification :
- Empêche la connexion des comptes désactivés
- Affiche un message d'erreur approprié

## 📝 Commandes utiles

```bash
# Créer une migration
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Charger les fixtures
php bin/console doctrine:fixtures:load

# Vider le cache
php bin/console cache:clear

# Voir les routes
php bin/console debug:router

# Voir les services
php bin/console debug:container
```

## 🧪 Tests

Les tests peuvent être exécutés avec PHPUnit :

```bash
php bin/phpunit
```

## 📚 Documentation additionnelle

- [Documentation Symfony](https://symfony.com/doc/current/index.html)
- [Doctrine ORM](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/index.html)
- [Twig](https://twig.symfony.com/doc/)

## 👥 Auteurs

Développé pour Auxilia E-commerce

## 📄 Licence

Propriétaire - Tous droits réservés
