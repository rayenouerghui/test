# Module de Matchmaking - NextGen

## 📋 Description

Module de gestion des sessions de matchmaking pour la plateforme NextGen. Ce module permet aux utilisateurs ayant acheté un jeu de trouver des partenaires de jeu via un système de file d'attente et de matchmaking automatique.

## 🎯 Fonctionnalités

### FrontOffice (Utilisateurs)
- **Bouton "Find a Match"** : Après l'achat d'un jeu, l'utilisateur peut cliquer sur ce bouton pour être ajouté à la file d'attente
- **Statut d'attente** : Affichage en temps réel du statut (en attente, match trouvé)
- **Sessions actives** : Liste des sessions de match actives avec liens pour rejoindre
- **Notifications** : Envoi d'emails automatique quand un match est trouvé

### BackOffice (Administrateurs)
- **Gestion des files d'attente** : Visualisation des utilisateurs en attente par jeu
- **Gestion des sessions** : Liste de toutes les sessions actives/terminées
- **Vérification manuelle** : Bouton pour forcer la vérification de matchs
- **Nettoyage** : Suppression des anciennes attentes (matched)

## 🗄️ Structure de la Base de Données

### Entités (2 entités seulement)

#### 1. AttenteMatch
- `id_attente` (PK) : Identifiant unique
- `id_utilisateur` (FK) : Référence à l'utilisateur
- `id_jeu` (FK) : Référence au jeu
- `date_ajout` : Date d'ajout à la file d'attente
- `matched` : Booléen indiquant si un match a été trouvé

#### 2. SessionMatch
- `id_session` (PK) : Identifiant unique
- `id_jeu` (FK) : Référence au jeu
- `lien_session` : Lien unique pour rejoindre la session
- `date_creation` : Date de création de la session
- `participants` : Liste des IDs utilisateurs (JSON)
- `statut` : Statut de la session (active, terminee, expiree)

## 🏗️ Architecture MVC

### Modèles (Models)
- `AttenteMatchModel.php` : Gère les opérations CRUD sur les attentes
- `SessionMatchModel.php` : Gère les opérations CRUD sur les sessions

### Contrôleurs (Controllers)
- `MatchmakingController.php` : Gère les requêtes FrontOffice
- `AdminMatchmakingController.php` : Gère les requêtes BackOffice

### Services
- `MatchService.php` : Logique métier du matchmaking
- `EmailService.php` : Envoi d'emails pour les matchs

### Vues (Views)
- `frontoffice/matchmaking.html` : **Page indépendante** pour le matchmaking (ne modifie pas account.html)
- `backoffice/matchmaking.html` : Page admin de gestion du matchmaking

## 📁 Structure des Fichiers

```
PROJET_WEB_NEXTGEN-main/
├── config/
│   ├── database.php          # Configuration PDO
│   └── database.sql          # Script SQL de création
├── models/
│   ├── AttenteMatchModel.php
│   └── SessionMatchModel.php
├── controllers/
│   ├── MatchmakingController.php
│   └── AdminMatchmakingController.php
├── services/
│   ├── MatchService.php
│   └── EmailService.php
├── api/
│   ├── matchmaking.php       # API FrontOffice
│   └── admin/
│       └── matchmaking.php   # API BackOffice
├── cron/
│   └── check_matches.php     # Script cron pour vérification automatique
├── frontoffice/
│   ├── account.html          # Page compte avec matchmaking
│   └── js/
│       ├── matchmaking.js    # JS FrontOffice
│       └── account.js        # JS validation
└── backoffice/
    ├── matchmaking.html      # Page admin matchmaking
    └── js/
        └── admin-matchmaking.js  # JS BackOffice
```

## 🚀 Installation

### 1. Base de Données

```bash
# Importer le script SQL
mysql -u root -p < config/database.sql
```

Ou exécuter le fichier `config/database.sql` dans phpMyAdmin.

### 2. Configuration

Modifier le fichier `config/database.php` avec vos paramètres de base de données :

```php
private $host = 'localhost';
private $dbname = 'nextgen_db';
private $username = 'root';
private $password = '';
```

### 3. Configuration du Serveur Web

Assurez-vous que PHP est configuré et que le serveur web pointe vers le répertoire du projet.

### 4. Cron Job (Optionnel)

Pour la vérification automatique des matchs, ajouter un cron job :

```bash
# Vérifier les matchs toutes les 5 minutes
*/5 * * * * php /chemin/vers/projet/cron/check_matches.php
```

## 📝 Utilisation

### Pour les Utilisateurs

1. **Acheter un jeu** : L'utilisateur doit d'abord acheter un jeu
2. **Accéder au matchmaking** : 
   - Via la page d'accueil (`frontoffice/index.html`) : Section matchmaking + lien navigation
   - Directement : `frontoffice/matchmaking.html`
3. **Find a Match** : Cliquer sur "Find a Match" pour le jeu désiré
4. **Attendre** : L'utilisateur est ajouté à la file d'attente
5. **Match trouvé** : Quand un match est trouvé (2+ joueurs), un email est envoyé avec le lien de session

### Pour les Administrateurs

1. **Accéder à l'admin** : Aller sur `backoffice/matchmaking.html` (page indépendante)
2. **Visualiser les files d'attente** : Voir les utilisateurs en attente par jeu
3. **Vérifier les matchs** : Cliquer sur "🔄 Vérifier les Matchs" pour forcer une vérification
4. **Gérer les sessions** : Voir toutes les sessions actives et les supprimer si nécessaire
5. **Nettoyer** : Supprimer les anciennes attentes (matched)

## 🔒 Module Indépendant

**IMPORTANT** : Ce module est **complètement indépendant** et ne modifie **AUCUN fichier** des autres membres du groupe.

- ✅ **Intégration dans index.html** : Lien navigation + section promotionnelle
- ✅ Aucune modification de `frontoffice/account.html`
- ✅ Aucune modification des pages backoffice des autres modules
- ✅ Page principale : `frontoffice/matchmaking.html`
- ✅ Page admin séparée : `backoffice/matchmaking.html`
- ✅ Utilise les tables existantes en lecture seule
- ✅ Crée seulement ses propres tables (AttenteMatch, SessionMatch)

**Note** : L'intégration dans `index.html` ajoute seulement un lien dans la navigation et une section promotionnelle. La fonctionnalité complète reste dans `matchmaking.html`.

Voir `MODULE_INDEPENDANT.md` pour plus de détails.

## 🔒 Sécurité

- **Validation JavaScript** : Tous les formulaires utilisent la validation JS (pas HTML5)
- **PDO avec requêtes préparées** : Protection contre les injections SQL
- **Échappement HTML** : Protection XSS dans les affichages
- **Vérification des achats** : Seuls les utilisateurs ayant acheté un jeu peuvent utiliser le matchmaking

## ✅ Contraintes Respectées

- ✅ **CRUD fonctionnel** : FrontOffice et BackOffice
- ✅ **Templates intégrés** : FrontOffice et BackOffice
- ✅ **Validation JavaScript** : Pas de validation HTML5
- ✅ **MVC** : Architecture Model-View-Controller respectée
- ✅ **POO** : Programmation orientée objet
- ✅ **PDO** : Utilisation exclusive de PDO pour la base de données
- ✅ **2 entités seulement** : AttenteMatch et SessionMatch

## 🐛 Dépannage

### Erreur de connexion à la base de données
- Vérifier les paramètres dans `config/database.php`
- Vérifier que MySQL/MariaDB est démarré
- Vérifier que la base de données existe

### Les matchs ne sont pas créés
- Vérifier que le script cron fonctionne (ou vérifier manuellement depuis l'admin)
- Vérifier les logs PHP pour les erreurs
- Vérifier que les emails sont configurés correctement

### Les emails ne sont pas envoyés
- Vérifier la configuration PHP mail() ou configurer PHPMailer
- Vérifier les logs d'erreur
- En développement, les emails sont loggés dans les erreurs PHP

## 📧 Configuration des Emails

Pour une configuration email en production, modifier `services/EmailService.php` pour utiliser PHPMailer ou un service d'email SMTP.

## 🎓 Auteur

Module développé dans le cadre du projet NextGen - Gestion des sessions de matchmaking

## 📄 Licence

Ce projet fait partie du projet NextGen.

