# 🛒 Alten E-commerce Backend

Ce projet est une API Symfony 6.4 permettant de gérer un site e-commerce (produits, utilisateurs, panier, wishlist) avec authentification JWT.

## 🚀 Installation

```bash
git clone https://github.com/codeurcasa/alten-ecommerce-backend.git
cd alten-ecommerce-backend
composer install
🔑 Clés JWT
Générer les clés :
mkdir -p config/jwt
openssl genrsa -out config/jwt/private.pem -aes256 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem

Ajouter dans .env :
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=ton_mot_de_passe_utilisé_lors_de_la_génération

📦 Base de données
Configurer .env :
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
Puis exécuter :
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
🔐 Authentification
Créer un compte utilisateur : POST /account

Récupérer un token : POST /token

Accès protégé : ajouter Authorization: Bearer <token> dans les headers.

Seul l'utilisateur avec l'email admin@admin.com peut ajouter, modifier ou supprimer un produit.

📬 Endpoints principaux

Endpoint	Méthode	Description
/account	POST	Créer un compte
/token	POST	Authentification (JWT)
/products	GET	Liste des produits
/products	POST	Créer un produit (admin)
/products/{id}	PATCH	Modifier un produit (admin)
/products/{id}	DELETE	Supprimer un produit (admin)
/cart	GET	Voir le panier
/cart	POST	Ajouter un produit au panier
/wishlist	GET	Voir la wishlist
/wishlist	POST	Ajouter un produit à la wishlist
🧪 Tests
Une collection Postman est fournie dans le dossier tests/Alten.postman_collection.json pour tester facilement tous les endpoints.