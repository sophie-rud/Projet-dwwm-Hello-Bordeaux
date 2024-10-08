# Hello Bordeaux

Hello Bordeaux est une application web Symfony réalisée dans le cadre de ma soutenance du titre DWWM. 
C'est un site pensé pour la gestion d'activités à Bordeaux, avec des fonctionnalités d'inscription, de gestion des utilisateurs et d'administration des événements.



## Fonctionnalités

- **Gestion des utilisateurs** : Inscription, connexion, modification du profil.
- **Gestion des activités** : Création, modification, suppression et affichage d'activités à venir.
- **Interface administrateur** : Gestion des utilisateurs, gestion des activités et de la galerie photo.
- **Responsive design**  : Interface adaptée aux mobiles et tablettes.

## Installation

### Prérequis :

- Langages et frameworks : PHP 8.3.1, Symfony 7.1
- Base de données : MySQL (ou une autre base de données compatible)


### Installation 

1. Cloner le dépôt :
    ```bash
    git clone https://github.com/sophie-rud/Projet-dwwm-Hello-Bordeaux.git
    ```
2. Installer les dépendances :
    ```bash
    composer install
    ```

Voir [la documentation officielle](https://symfony.com/doc/current/index.html).


## Utilisation

##### Créer un compte utilisateur
- Enregistrez-vous via le formulaire d'inscription disponible sur l'application.

##### S'inscrire à une activité
- Connectez-vous sur la page de login
- Vous pouvez consulter toutes les activités proposées sur la page "voir les activités", ou directement via votre profil sur "s'inscrire à une activité".
- Cliquez sur l'activité qui vous intéresse,
- Et inscrivez-vous pour y participer !




##### Créer un compte administrateur
Un administrateur, qui possède le ROLE_ADMIN, peut accéder à la :

- Gestion des activités

    Pour ajouter une activité, connectez-vous avec un compte administrateur, puis accédez à la page "Gestion des activités".
    Vous pouvez ajouter une activité, consulter et modifier ou supprimer vos activités, publiées ou non, consulter la liste des utilisateurs inscrits.

- Gestion des utilisateurs

  Vous pouvez consulter la liste des utilisateurs, bloquer ou débloquer un utilisateur et gérer les inscriptions via l'interface d'administration.

- Gestion des photos de la galerie

   Vous pouvez ajouter des images à la galerie, les modifier et les supprimer.