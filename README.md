# Symfony Article API

Ce projet Symfony est une API permettant de gérer des articles.
## Prérequis

Assurez-vous d'avoir les éléments suivants installés sur votre système :

- PHP 8.1
- Composer 2.6
- Symphony CLI 6.4
- MySQL ou tout autre système de gestion de base de données pris en charge par Symfony

## Installation et configuration

1. Clonez ce dépôt sur votre machine locale :
```
git clone https://github.com/bmisarts/symfony-blog-api.git
```

2. Accédez au répertoire du projet :

3. Installez les dépendances PHP à l'aide de Composer :
```
composer install
```

4. Créez une copie du fichier `.env` et configurez vos variables d'environnement, notamment la connexion à votre base de données.

5. Créez la base de données :
```
php bin/console doctrine:database:create
```

6. exécutez les migrations :
```
php bin/console doctrine:migrations:migrate
```

7. Une fois le projet installé et configuré, vous pouvez démarrer le serveur de développement Symfony en exécutant la commande suivante :
```
symfony server:start
```

8. L'API sera accessible à l'adresse http://127.0.0.1:8000.

## Documentation de l'API

La documentation de l'API sera disponible via [un page web](http://127.0.0.1:8000/api/doc) ou [au format de données json](http://127.0.0.1:8000/api/doc.json)