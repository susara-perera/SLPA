# MySQL Shutdown Fix Guide

## Immediate Solutions to Try (in order)

### 1. **Check Port Conflicts**

Run these commands in Command Prompt (as Administrator):

```cmd
netstat -ano | findstr :3306
netstat -ano | findstr :80
```

### 2. **Stop Conflicting Services**

```cmd
net stop "World Wide Web Publishing Service"
net stop "SQL Server (MSSQLSERVER)" 
net stop "SQL Server Reporting Services (MSSQLSERVER)"
```

### 3. **Restart XAMPP Services**

- Open XAMPP Control Panel as Administrator
- Stop Apache and MySQL services
- Wait 10 seconds
- Start Apache first, then MySQL

### 4. **Change MySQL Port (if port conflict exists)**

Edit: `C:\xampp\mysql\bin\my.ini`

```ini
[mysqld]
port=3307
```

Then update your PHP connection files to use port 3307.

### 5. **Fix Common Windows Issues**

#### A. Add XAMPP to Antivirus Exclusions

Add these folders to your antivirus exclusions:

- C:\xampp\
- C:\xampp\apache\
- C:\xampp\mysql\
- C:\xampp\php\

#### B. Check Windows Event Viewer

1. Press Win+R, type `eventvwr.msc`
2. Navigate to Windows Logs > Application
3. Look for MySQL or XAMPP related errors

#### C. Run XAMPP as Administrator

- Right-click XAMPP Control Panel
- Select "Run as administrator"

### 6. **Database Recovery (if data corruption)**

#### A. Backup existing data

```cmd
xcopy "C:\xampp\mysql\data" "C:\xampp\mysql\data_backup" /E /I
```

#### B. Reset MySQL data (CAUTION: This will delete all databases)

```cmd
# Stop MySQL service first
# Delete contents of C:\xampp\mysql\data (except for mysql, performance_schema, sys folders)
# Restart MySQL
```

### 7. **Alternative: Use Different MySQL Port**

#### Update my.ini

```ini
[mysqld]
port=3307
bind-address=127.0.0.1
```

#### Update PHP connection

```php
$connect = mysqli_connect("localhost", "root", "", "slpa_db", 3307);
```

### 8. **Check System Requirements**

- Ensure Windows Defender/Antivirus isn't blocking XAMPP
- Verify sufficient disk space (at least 1GB free)
- Check if Visual C++ Redistributable is installed

### 9. **Reinstall MySQL (Last Resort)**

1. Backup your databases first
2. Uninstall XAMPP
3. Delete C:\xampp folder completely
4. Download fresh XAMPP installation
5. Install as Administrator
6. Restore databases

## Quick Diagnostic Commands

```cmd
# Check what's using port 3306
netstat -ano | findstr :3306

# Check MySQL service status
sc query mysql

# Check XAMPP processes
tasklist | findstr xampp

# Check Windows services
services.msc
```

## Error Log Locations

- `C:\xampp\mysql\data\mysql_error.log`
- `C:\xampp\apache\logs\error.log`
- `C:\xampp\mysql\data\[computername].err`

## Testing Your Fix

1. Run the diagnostic PHP file: `test_db_connection.php`
2. Check XAMPP Control Panel - MySQL should show green
3. Test your application connections

---
**Created:** July 29, 2025
**For:** SLPA Project MySQL Issues
