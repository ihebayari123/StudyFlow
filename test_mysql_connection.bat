@echo off
echo ========================================
echo MySQL Connection Test for StudyFlow
echo ========================================
echo.

echo Testing MySQL connection on port 3307...
echo.

REM Test connection without password
mysql -u root --port=3307 -e "SELECT 'Connection successful!' as Status, VERSION() as MySQL_Version;"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo SUCCESS: MySQL is accessible!
    echo.
    echo Now testing database 'studyflow'...
    mysql -u root --port=3307 -e "SHOW DATABASES LIKE 'studyflow';"
    echo.
    echo To fix phpMyAdmin, you need to:
    echo 1. Find your phpMyAdmin config.inc.php file
    echo 2. Change the port from 3306 to 3307
    echo 3. Set auth_type to 'cookie'
    echo.
    echo See fix_mysql_connection.md for detailed instructions.
) else (
    echo.
    echo ERROR: Cannot connect to MySQL on port 3307
    echo.
    echo Possible solutions:
    echo 1. Make sure MySQL/MariaDB is running
    echo 2. Check if it's running on a different port
    echo 3. Try with password: mysql -u root -p --port=3307
    echo.
    echo Common MySQL locations:
    echo - XAMPP: C:\xampp\mysql\bin\mysql.exe
    echo - WAMP: C:\wamp64\bin\mysql\mysql[version]\bin\mysql.exe
    echo - Laragon: C:\laragon\bin\mysql\mysql[version]\bin\mysql.exe
)

echo.
pause
