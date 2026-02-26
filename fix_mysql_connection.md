# Fix MySQL Connection Issues for phpMyAdmin

## Problem
- Access denied for user 'pma'@'localhost'
- Access denied for user 'root'@'localhost' (using password: NO)
- MySQL is running on port 3307 instead of default 3306

## Solutions

### Option 1: Reset MySQL Root Password (Recommended)

1. **Open MySQL command line** (as Administrator):
   ```bash
   mysql -u root -p --port=3307
   ```
   If it asks for password and you don't have one, try without `-p`:
   ```bash
   mysql -u root --port=3307
   ```

2. **Set a password for root user**:
   ```sql
   ALTER USER 'root'@'localhost' IDENTIFIED BY 'your_password';
   FLUSH PRIVILEGES;
   ```

3. **Update your Symfony .env file** with the new password:
   ```
   DATABASE_URL="mysql://root:your_password@127.0.0.1:3307/studyflow"
   ```

### Option 2: Configure phpMyAdmin for Port 3307

1. **Locate phpMyAdmin config file**:
   - Usually at: `C:\xampp\phpMyAdmin\config.inc.php` (XAMPP)
   - Or: `C:\wamp64\apps\phpmyadmin\config.inc.php` (WAMP)
   - Or: `C:\laragon\etc\apps\phpMyAdmin\config.inc.php` (Laragon)

2. **Edit the config file** and change:
   ```php
   $cfg['Servers'][$i]['host'] = '127.0.0.1';
   $cfg['Servers'][$i]['port'] = '3307';  // Change from 3306 to 3307
   $cfg['Servers'][$i]['auth_type'] = 'cookie';
   $cfg['Servers'][$i]['user'] = 'root';
   $cfg['Servers'][$i]['password'] = '';  // Add your password if you set one
   ```

3. **Comment out or fix the controluser** (pma user):
   ```php
   // $cfg['Servers'][$i]['controluser'] = 'pma';
   // $cfg['Servers'][$i]['controlpass'] = '';
   ```

### Option 3: Create PMA Control User (Advanced)

If you want to use phpMyAdmin's advanced features:

1. **Connect to MySQL**:
   ```bash
   mysql -u root --port=3307
   ```

2. **Create pma user**:
   ```sql
   CREATE USER 'pma'@'localhost' IDENTIFIED BY 'pmapass';
   GRANT ALL PRIVILEGES ON phpmyadmin.* TO 'pma'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. **Update phpMyAdmin config**:
   ```php
   $cfg['Servers'][$i]['controluser'] = 'pma';
   $cfg['Servers'][$i]['controlpass'] = 'pmapass';
   ```

### Option 4: Use MySQL Workbench Instead

If phpMyAdmin continues to have issues, use MySQL Workbench:
- Download from: https://dev.mysql.com/downloads/workbench/
- Connect with:
  - Host: 127.0.0.1
  - Port: 3307
  - User: root
  - Password: (empty or your password)

## Quick Test

After making changes, test your connection:

```bash
mysql -u root --port=3307 -e "SELECT 'Connection successful!' as Status;"
```

## For Your Symfony Application

Your current configuration should work if MySQL root user has no password:
```
DATABASE_URL="mysql://root:@127.0.0.1:3307/studyflow"
```

If you set a password, update it to:
```
DATABASE_URL="mysql://root:your_password@127.0.0.1:3307/studyflow"
```

Then run:
```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate
```
