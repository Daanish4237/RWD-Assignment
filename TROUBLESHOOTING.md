# TaskFlow Troubleshooting Guide

## 🚨 **Common Installation Issues & Solutions**

### **Issue 1: "This site can't be reached" or "Connection refused"**

**Possible Causes:**
- Web server not running
- Wrong URL/port
- Firewall blocking access

**Solutions:**
```bash
# For XAMPP/WAMP/MAMP
# 1. Start Apache service from control panel
# 2. Check if Apache is running on port 80
# 3. Try: http://localhost/taskflow/
# 4. For MAMP: http://localhost:8888/taskflow/

# For manual installation
sudo systemctl start apache2
sudo systemctl status apache2
```

---

### **Issue 2: "Database connection failed" Error**

**Error Messages:**
- "Access denied for user 'root'@'localhost'"
- "Unknown database 'taskflow'"
- "Connection refused"

**Solutions:**

#### **Step 1: Check MySQL Service**
```bash
# Windows (XAMPP/WAMP)
# Start MySQL from control panel

# Linux/macOS
sudo systemctl start mysql
# or
brew services start mysql
```

#### **Step 2: Verify Database Exists**
```sql
-- Login to MySQL
mysql -u root -p

-- Check if database exists
SHOW DATABASES;

-- Create database if missing
CREATE DATABASE taskflow;
```

#### **Step 3: Check Credentials in db_connect.php**
```php
// Update these values in db_connect.php
$db_host = "localhost";
$db_username = "root";
$db_password = ""; // Your MySQL password
$db_name = "taskflow";
```

#### **Step 4: Import Database Schema**
```bash
# Import the database
mysql -u root -p taskflow < tasks.sql
```

---

### **Issue 3: "Fatal error: Uncaught Error" or White Screen**

**Common Causes:**
- PHP version incompatibility
- Missing PHP extensions
- File permissions
- Syntax errors

**Solutions:**

#### **Step 1: Check PHP Version**
```bash
php -v
# Should be 7.4 or higher
```

#### **Step 2: Enable Error Reporting**
Add this to the top of your PHP files:
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

#### **Step 3: Check Required Extensions**
```bash
php -m | grep -E "(mysqli|session|json)"
```

#### **Step 4: Fix File Permissions**
```bash
# Linux/macOS
chmod -R 755 /path/to/taskflow/
chown -R www-data:www-data /path/to/taskflow/

# Windows
# Right-click folder → Properties → Security → Full Control
```

---

### **Issue 4: "Session not working" or Login Issues**

**Symptoms:**
- Can't stay logged in
- Redirected to login page repeatedly
- Session errors in logs

**Solutions:**

#### **Step 1: Check Session Directory**
```php
<?php
echo "Session save path: " . session_save_path();
echo "Session status: " . session_status();
?>
```

#### **Step 2: Fix Session Permissions**
```bash
# Create session directory if needed
sudo mkdir -p /var/lib/php/sessions
sudo chown -R www-data:www-data /var/lib/php/sessions
sudo chmod 755 /var/lib/php/sessions
```

#### **Step 3: Check PHP Session Configuration**
```ini
# In php.ini
session.save_path = "/var/lib/php/sessions"
session.cookie_httponly = 1
session.use_strict_mode = 1
```

---

### **Issue 5: "Permission denied" Errors**

**Solutions:**

#### **For Linux/macOS:**
```bash
# Set correct ownership
sudo chown -R www-data:www-data /var/www/html/taskflow/
sudo chmod -R 755 /var/www/html/taskflow/

# For uploads directory (if exists)
sudo chmod -R 777 /var/www/html/taskflow/uploads/
```

#### **For Windows:**
1. Right-click on taskflow folder
2. Properties → Security → Advanced
3. Change owner to "Everyone"
4. Give "Full Control" permissions

---

### **Issue 6: Mobile/Tablet Access Issues**

**Problem:** Can't access from mobile devices

**Solutions:**

#### **Step 1: Find Your Computer's IP Address**
```bash
# Windows
ipconfig

# Mac/Linux
ifconfig
# or
ip addr show
```

#### **Step 2: Access from Mobile**
- Use: `http://[YOUR_IP_ADDRESS]/taskflow/`
- Example: `http://192.168.1.100/taskflow/`

#### **Step 3: Check Firewall**
```bash
# Windows
# Allow Apache through Windows Firewall

# Linux
sudo ufw allow 80
sudo ufw allow 443
```

---

### **Issue 7: "404 Not Found" for CSS/JS Files**

**Solutions:**

#### **Step 1: Check .htaccess File**
Create `.htaccess` in taskflow root:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### **Step 2: Enable mod_rewrite**
```bash
# Ubuntu/Debian
sudo a2enmod rewrite
sudo systemctl restart apache2

# CentOS/RHEL
# Edit /etc/httpd/conf/httpd.conf
# Uncomment: LoadModule rewrite_module modules/mod_rewrite.so
```

---

### **Issue 8: Database Import Fails**

**Error:** "SQL syntax error" or "Table already exists"

**Solutions:**

#### **Step 1: Check MySQL Version**
```sql
SELECT VERSION();
```

#### **Step 2: Clear Existing Data**
```sql
-- Drop existing tables
DROP DATABASE IF EXISTS taskflow;
CREATE DATABASE taskflow;
```

#### **Step 3: Import with Error Handling**
```bash
mysql -u root -p taskflow < tasks.sql 2>&1 | tee import.log
```

---

### **Issue 9: Performance Issues**

**Symptoms:** Slow loading, timeouts

**Solutions:**

#### **Step 1: Check PHP Memory Limit**
```php
<?php
echo "Memory limit: " . ini_get('memory_limit');
echo "Max execution time: " . ini_get('max_execution_time');
?>
```

#### **Step 2: Optimize PHP Settings**
```ini
# In php.ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 64M
post_max_size = 64M
```

#### **Step 3: Database Optimization**
```sql
-- Optimize tables
OPTIMIZE TABLE users, tasks, goals, time_logs;
```

---

### **Issue 10: SSL/HTTPS Issues**

**For Production Deployment:**

#### **Step 1: Get SSL Certificate**
```bash
# Using Let's Encrypt
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com
```

#### **Step 2: Force HTTPS**
Add to `.htaccess`:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 🔧 **Quick Diagnostic Commands**

### **Check System Status:**
```bash
# Check web server
sudo systemctl status apache2
sudo systemctl status nginx

# Check database
sudo systemctl status mysql

# Check PHP
php -v
php -m

# Check disk space
df -h

# Check memory
free -h
```

### **Test Database Connection:**
```php
<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'taskflow';

$connection = new mysqli($host, $username, $password, $database);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
} else {
    echo "Database connection successful!";
}
$connection->close();
?>
```

---

## 📞 **Getting Help**

### **If Issues Persist:**

1. **Check Error Logs:**
   - Apache: `/var/log/apache2/error.log`
   - Nginx: `/var/log/nginx/error.log`
   - PHP: Check `php.ini` error_log setting

2. **Run Setup Script:**
   - Access: `http://localhost/taskflow/setup.php`
   - This will verify your installation

3. **Common Solutions:**
   - Restart all services
   - Clear browser cache
   - Check firewall settings
   - Verify file permissions

4. **System Requirements:**
   - PHP 7.4+ with mysqli, session, json extensions
   - MySQL 5.7+ or MariaDB 10.3+
   - Apache 2.4+ or Nginx 1.18+
   - 512MB+ RAM, 100MB+ storage

---

**🎯 Most issues can be resolved by following the solutions above. If you continue to have problems, check the error logs and ensure all system requirements are met.**
