@echo off
REM MySQL XAMPP Fix Script
REM Run this script as Administrator

echo ========================================
echo MySQL XAMPP Automatic Fix Script
echo ========================================
echo.

echo Step 1: Checking current MySQL processes...
tasklist | findstr mysql
echo.

echo Step 2: Checking port 3306 usage...
netstat -ano | findstr :3306
echo.

echo Step 3: Stopping potentially conflicting services...
echo Stopping IIS...
net stop "World Wide Web Publishing Service" 2>nul
echo Stopping SQL Server...
net stop "SQL Server (MSSQLSERVER)" 2>nul
net stop "SQL Server Reporting Services (MSSQLSERVER)" 2>nul
echo.

echo Step 4: Killing any existing MySQL processes...
taskkill /f /im mysqld.exe 2>nul
taskkill /f /im mysql.exe 2>nul
echo.

echo Step 5: Starting XAMPP MySQL...
echo Please manually start MySQL from XAMPP Control Panel
echo.

echo Step 6: Testing MySQL connection...
timeout /t 5 /nobreak >nul
netstat -ano | findstr :3306
echo.

echo ========================================
echo Fix script completed!
echo.
echo If MySQL still doesn't start:
echo 1. Check XAMPP Control Panel logs
echo 2. Run test_db_connection.php in browser
echo 3. Check MySQL error logs in C:\xampp\mysql\data\
echo ========================================

pause
