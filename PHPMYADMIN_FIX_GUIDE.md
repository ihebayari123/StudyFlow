# Guide de Correction des Erreurs MySQL/phpMyAdmin

## 🔴 Problèmes Identifiés

Vous rencontrez ces erreurs:
1. **Access denied for user 'pma'@'localhost'** - Utilisateur de contrôle phpMyAdmin
2. **Access denied for user 'root'@'localhost' (using password: NO)** - Utilisateur root sans mot de passe
3. **Port incorrect** - MySQL tourne sur le port 3307, phpMyAdmin essaie de se connecter au port 3306

## ✅ Solutions Rapides

### Solution 1: Configurer phpMyAdmin pour le Port 3307 (RECOMMANDÉ)

#### Étape 1: Localiser le fichier de configuration phpMyAdmin

Selon votre installation:
- **XAMPP**: `C:\xampp\phpMyAdmin\config.inc.php`
- **WAMP**: `C:\wamp64\apps\phpmyadmin[version]\config.inc.php`
- **Laragon**: `C:\laragon\etc\apps\phpMyAdmin\config.inc.php`

#### Étape 2: Modifier le fichier config.inc.php

Ouvrez le fichier et trouvez la section serveur. Modifiez comme suit:

```php
<?php
/* Server configuration */
$i = 0;

/* Server: localhost [1] */
$i++;
$cfg['Servers'][$i]['verbose'] = '';
$cfg['Servers'][$i]['host'] = '127.0.0.1';
$cfg['Servers'][$i]['port'] = '3307';  // ⚠️ CHANGEZ ICI: 3306 → 3307
$cfg['Servers'][$i]['socket'] = '';
$cfg['Servers'][$i]['auth_type'] = 'cookie';
$cfg['Servers'][$i]['user'] = 'root';
$cfg['Servers'][$i]['password'] = '';  // Laissez vide si pas de mot de passe
$cfg['Servers'][$i]['AllowNoPassword'] = true;  // ⚠️ AJOUTEZ CETTE LIGNE

/* Désactiver le controluser si vous avez des erreurs */
// $cfg['Servers'][$i]['controluser'] = 'pma';
// $cfg['Servers'][$i]['controlpass'] = '';

/* Autres configurations... */
?>
```

#### Étape 3: Redémarrer Apache

Après avoir modifié le fichier, redémarrez Apache depuis votre panneau de contrôle (XAMPP/WAMP/Laragon).

---

### Solution 2: Corriger les Permissions MySQL

#### Option A: Via la ligne de commande MySQL

1. **Ouvrir le terminal MySQL**:
   ```bash
   mysql -u root --port=3307
   ```

2. **Exécuter ces commandes**:
   ```sql
   -- Permettre à root de se connecter sans mot de passe
   ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
   FLUSH PRIVILEGES;
   
   -- Vérifier
   SELECT User, Host, plugin FROM mysql.user WHERE User = 'root';
   ```

#### Option B: Utiliser le script SQL fourni

1. Ouvrez le fichier [`fix_mysql_users.sql`](fix_mysql_users.sql)
2. Copiez le contenu
3. Exécutez-le dans phpMyAdmin (onglet SQL) ou via la ligne de commande:
   ```bash
   mysql -u root --port=3307 < fix_mysql_users.sql
   ```

---

### Solution 3: Créer l'Utilisateur PMA (Optionnel)

Si vous voulez utiliser les fonctionnalités avancées de phpMyAdmin:

```sql
-- Créer l'utilisateur pma
CREATE USER IF NOT EXISTS 'pma'@'localhost' IDENTIFIED BY '';
GRANT SELECT, INSERT, UPDATE, DELETE ON phpmyadmin.* TO 'pma'@'localhost';
FLUSH PRIVILEGES;
```

Puis dans [`config.inc.php`](config.inc.php):
```php
$cfg['Servers'][$i]['controluser'] = 'pma';
$cfg['Servers'][$i]['controlpass'] = '';
```

---

## 🧪 Tester la Connexion

### Méthode 1: Script Batch (Windows)

Exécutez le fichier [`test_mysql_connection.bat`](test_mysql_connection.bat):
```bash
test_mysql_connection.bat
```

### Méthode 2: Ligne de commande manuelle

```bash
# Test simple
mysql -u root --port=3307 -e "SELECT 'OK' as Status;"

# Test avec la base de données
mysql -u root --port=3307 -e "SHOW DATABASES;"

# Test de la base studyflow
mysql -u root --port=3307 studyflow -e "SHOW TABLES;"
```

---

## 🔧 Configuration Symfony

Votre fichier [`.env`](.env) est déjà correctement configuré:

```env
DATABASE_URL="mysql://root:@127.0.0.1:3307/studyflow"
```

Si vous définissez un mot de passe pour root, mettez-le à jour:
```env
DATABASE_URL="mysql://root:votre_mot_de_passe@127.0.0.1:3307/studyflow"
```

### Commandes Symfony à exécuter après correction

```bash
# Créer la base de données si elle n'existe pas
php bin/console doctrine:database:create --if-not-exists

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Vérifier la connexion
php bin/console doctrine:query:sql "SELECT 1"
```

---

## 🎯 Checklist de Dépannage

- [ ] MySQL/MariaDB est démarré
- [ ] MySQL tourne sur le port 3307 (vérifier dans le panneau de contrôle)
- [ ] Le fichier `config.inc.php` de phpMyAdmin est modifié avec le port 3307
- [ ] `AllowNoPassword` est défini à `true` dans `config.inc.php`
- [ ] Apache a été redémarré après les modifications
- [ ] L'utilisateur root peut se connecter sans mot de passe
- [ ] La base de données `studyflow` existe

---

## 🆘 Si Rien ne Fonctionne

### Alternative 1: Utiliser MySQL Workbench

Téléchargez MySQL Workbench: https://dev.mysql.com/downloads/workbench/

Paramètres de connexion:
- **Hostname**: 127.0.0.1
- **Port**: 3307
- **Username**: root
- **Password**: (vide)

### Alternative 2: Utiliser Adminer

Adminer est plus léger que phpMyAdmin:
1. Téléchargez: https://www.adminer.org/
2. Placez `adminer.php` dans votre dossier `public/`
3. Accédez à: `http://localhost/adminer.php`
4. Connectez-vous avec:
   - Système: MySQL
   - Serveur: 127.0.0.1:3307
   - Utilisateur: root
   - Mot de passe: (vide)
   - Base de données: studyflow

---

## 📝 Notes Importantes

⚠️ **Sécurité**: Utiliser root sans mot de passe est acceptable en développement local, mais **JAMAIS en production**.

💡 **Conseil**: Pour la production, créez toujours un utilisateur dédié avec des privilèges limités:

```sql
CREATE USER 'studyflow_user'@'localhost' IDENTIFIED BY 'mot_de_passe_fort';
GRANT ALL PRIVILEGES ON studyflow.* TO 'studyflow_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## 📞 Support

Si vous avez encore des problèmes:
1. Vérifiez les logs MySQL (généralement dans le dossier d'installation MySQL)
2. Vérifiez les logs Apache
3. Assurez-vous qu'aucun firewall ne bloque le port 3307
