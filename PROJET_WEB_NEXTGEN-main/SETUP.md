# Guide d'Installation - Module Matchmaking NextGen

## 📋 Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou MariaDB 10.2 ou supérieur
- Serveur web (Apache/Nginx)
- Extension PDO MySQL activée

## 🚀 Installation Rapide

### Étape 1: Base de Données

1. Créer la base de données :
```sql
mysql -u root -p
CREATE DATABASE nextgen_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Importer le script SQL :
```bash
mysql -u root -p nextgen_db < config/database.sql
```

Ou via phpMyAdmin :
- Importer le fichier `config/database.sql`

### Étape 2: Configuration

1. Modifier `config/database.php` avec vos paramètres :
```php
private $host = 'localhost';
private $dbname = 'nextgen_db';
private $username = 'votre_utilisateur';
private $password = 'votre_mot_de_passe';
```

### Étape 3: Configuration du Serveur Web

#### Apache
1. Assurez-vous que `mod_rewrite` est activé
2. Le fichier `.htaccess` est déjà configuré
3. Point de départ : `index.html`

#### Nginx
Ajouter dans la configuration :
```nginx
location / {
    try_files $uri $uri/ /index.html;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
```

### Étape 4: Permissions

```bash
chmod 755 config/
chmod 644 config/*.php
chmod 755 models/
chmod 644 models/*.php
chmod 755 controllers/
chmod 644 controllers/*.php
chmod 755 services/
chmod 644 services/*.php
chmod 755 api/
chmod 644 api/*.php
```

### Étape 5: Cron Job (Optionnel)

Pour la vérification automatique des matchs :

```bash
# Éditer le crontab
crontab -e

# Ajouter cette ligne (vérifier toutes les 5 minutes)
*/5 * * * * php /chemin/vers/projet/cron/check_matches.php >> /var/log/nextgen_matchmaking.log 2>&1
```

## 🧪 Test de l'Installation

### Test 1: Base de Données

```bash
php -r "
require_once 'config/database.php';
\$db = Database::getInstance()->getConnection();
echo 'Connexion réussie !';
"
```

### Test 2: API

Ouvrir dans le navigateur :
```
http://localhost/api/matchmaking.php?action=jeux_achetes&id_utilisateur=1
```

### Test 3: FrontOffice

1. Aller sur `frontoffice/account.html`
2. Cliquer sur l'onglet "🎮 Matchmaking"
3. Vérifier que les jeux achetés s'affichent

### Test 4: BackOffice

1. Aller sur `backoffice/matchmaking.html`
2. Vérifier que la page se charge
3. Tester les fonctionnalités admin

## 🔧 Configuration des Emails

Par défaut, le système utilise `mail()` de PHP. Pour une configuration SMTP :

1. Installer PHPMailer :
```bash
composer require phpmailer/phpmailer
```

2. Modifier `services/EmailService.php` pour utiliser PHPMailer

## 🐛 Dépannage

### Erreur: "Class not found"
- Vérifier que tous les `require_once` sont corrects
- Vérifier les chemins relatifs

### Erreur: "Connection refused"
- Vérifier que MySQL est démarré
- Vérifier les paramètres de connexion

### Erreur: "Table doesn't exist"
- Vérifier que le script SQL a été exécuté
- Vérifier le nom de la base de données

### Les matchs ne se créent pas
- Vérifier les logs PHP
- Vérifier que le cron job fonctionne
- Vérifier manuellement depuis l'admin

## 📝 Notes

- L'ID utilisateur est actuellement hardcodé à 1 dans `matchmaking.js`
- En production, récupérer l'ID depuis la session PHP
- Les emails peuvent ne pas fonctionner en local (configurer SMTP)
- Le système nécessite au moins 2 joueurs pour créer un match

## 🔐 Sécurité en Production

1. Désactiver l'affichage des erreurs PHP
2. Configurer les permissions de fichiers
3. Utiliser HTTPS
4. Configurer l'authentification admin
5. Valider toutes les entrées utilisateur
6. Utiliser des requêtes préparées (déjà fait avec PDO)

## 📞 Support

Pour toute question, consulter `README_MATCHMAKING.md` ou les commentaires dans le code.


