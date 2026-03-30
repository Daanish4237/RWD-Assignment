# TaskFlow Installation Guide

## 🚀 **Complete Setup Instructions for TaskFlow Productivity Enhancement Web Application**

### 📋 **System Requirements**

#### **Minimum Requirements:**
- **Operating System:** Windows 10/11, macOS 10.15+, or Linux (Ubuntu 18.04+)
- **Web Server:** Apache 2.4+ or Nginx 1.18+
- **PHP:** Version 7.4 or higher (PHP 8.0+ recommended)
- **Database:** MySQL 5.7+ or MariaDB 10.3+
- **Memory:** 512MB RAM minimum (1GB+ recommended)
- **Storage:** 100MB free space

#### **Recommended Requirements:**
- **PHP:** Version 8.1 or 8.2
- **MySQL:** Version 8.0+
- **Memory:** 2GB+ RAM
- **Storage:** 500MB+ free space

---

## 🔧 **Installation Methods**

### **Method 1: XAMPP (Easiest for Windows/Mac)**

#### **Step 1: Download and Install XAMPP**
1. Download XAMPP from: https://www.apachefriends.org/download.html
2. Install XAMPP following the installer instructions
3. Start Apache and MySQL services from XAMPP Control Panel

#### **Step 2: Download TaskFlow**
```bash
# Option A: Download ZIP from GitHub
# Go to: https://github.com/51xengineer/Rwd.git
# Click "Code" → "Download ZIP"

# Option B: Clone with Git
git clone https://github.com/51xengineer/Rwd.git
```

#### **Step 3: Setup Project**
1. Copy the downloaded files to `C:\xampp\htdocs\taskflow\` (Windows) or `/Applications/XAMPP/htdocs/taskflow/` (Mac)
2. Open XAMPP Control Panel
3. Start Apache and MySQL services
4. Click "Admin" next to MySQL to open phpMyAdmin

#### **Step 4: Database Setup**
1. In phpMyAdmin, create a new database called `taskflow`
2. Import the database schema:
   - Click on the `taskflow` database
   - Go to "Import" tab
   - Choose file: `tasks.sql`
   - Click "Go"

#### **Step 5: Configure Database Connection**
1. Open `db_connect.php` in a text editor
2. Update the database credentials if needed:
```php
$db_host = "localhost";
$db_username = "root";
$db_password = ""; // Leave empty for XAMPP default
$db_name = "taskflow";
```

#### **Step 6: Access the Application**
1. Open your web browser
2. Go to: `http://localhost/taskflow/`
3. You should see the TaskFlow login page

---

### **Method 2: WAMP (Windows Only)**

#### **Step 1: Download and Install WAMP**
1. Download WAMP from: https://www.wampserver.com/en/
2. Install WAMP following the installer instructions
3. Start WAMP services

#### **Step 2-6: Follow the same steps as XAMPP**
- Copy files to `C:\wamp64\www\taskflow\`
- Setup database in phpMyAdmin
- Configure database connection
- Access via `http://localhost/taskflow/`

---

### **Method 3: MAMP (Mac Only)**

#### **Step 1: Download and Install MAMP**
1. Download MAMP from: https://www.mamp.info/en/downloads/
2. Install MAMP following the installer instructions
3. Start MAMP services

#### **Step 2-6: Follow the same steps as XAMPP**
- Copy files to `/Applications/MAMP/htdocs/taskflow/`
- Setup database in phpMyAdmin
- Configure database connection
- Access via `http://localhost:8888/taskflow/`

---

### **Method 4: Manual Installation (Advanced)**

#### **Step 1: Install Prerequisites**
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install apache2 mysql-server php php-mysql php-curl php-json php-mbstring

# CentOS/RHEL
sudo yum install httpd mysql-server php php-mysql php-curl php-json php-mbstring

# macOS (using Homebrew)
brew install apache2 mysql php
```

#### **Step 2: Configure Services**
```bash
# Start services
sudo systemctl start apache2
sudo systemctl start mysql
sudo systemctl enable apache2
sudo systemctl enable mysql
```

#### **Step 3: Setup Database**
```bash
# Login to MySQL
mysql -u root -p

# Create database and user
CREATE DATABASE taskflow;
CREATE USER 'taskflow_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON taskflow.* TO 'taskflow_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### **Step 4: Deploy Application**
```bash
# Copy files to web directory
sudo cp -r taskflow/ /var/www/html/
sudo chown -R www-data:www-data /var/www/html/taskflow/
sudo chmod -R 755 /var/www/html/taskflow/
```

#### **Step 5: Import Database**
```bash
mysql -u taskflow_user -p taskflow < tasks.sql
```

---

## 🔐 **Default Login Credentials**

### **Admin Account:**
- **Username:** `admin`
- **Password:** `password`
- **Email:** `admin@taskflow.com`

### **User Account:**
- **Username:** `user1`
- **Password:** `password`
- **Email:** `user1@taskflow.com`

**⚠️ Important:** Change these passwords immediately after installation!

---

## 🛠️ **Troubleshooting Common Issues**

### **Issue 1: "Database Connection Failed"**
**Solution:**
1. Check if MySQL service is running
2. Verify database credentials in `db_connect.php`
3. Ensure database `taskflow` exists
4. Check MySQL user permissions

### **Issue 2: "Page Not Found" or "404 Error"**
**Solution:**
1. Verify files are in correct web directory
2. Check Apache/Nginx configuration
3. Ensure `.htaccess` file exists (if using Apache)
4. Check file permissions

### **Issue 3: "PHP Errors" or "White Screen"**
**Solution:**
1. Enable PHP error reporting
2. Check PHP version compatibility
3. Verify all PHP extensions are installed
4. Check file permissions

### **Issue 4: "Permission Denied"**
**Solution:**
```bash
# Set correct permissions
chmod -R 755 /path/to/taskflow/
chown -R www-data:www-data /path/to/taskflow/
```

### **Issue 5: "Session Errors"**
**Solution:**
1. Check PHP session configuration
2. Ensure session directory is writable
3. Verify session cookies are enabled

---

## 📱 **Mobile Access**

The application is fully responsive and can be accessed on mobile devices:
- **Local Network:** `http://[your-ip-address]/taskflow/`
- **Find your IP:** 
  - Windows: `ipconfig`
  - Mac/Linux: `ifconfig` or `ip addr`

---

## 🔒 **Security Recommendations**

### **After Installation:**
1. **Change Default Passwords**
2. **Update Database Credentials**
3. **Enable HTTPS** (for production)
4. **Regular Backups**
5. **Keep Software Updated**

### **Production Deployment:**
1. Use a dedicated web server
2. Configure SSL certificates
3. Set up regular database backups
4. Monitor server resources
5. Implement access controls

---

## 📞 **Support Information**

### **If You Encounter Issues:**
1. Check the error logs in your web server
2. Verify all requirements are met
3. Ensure database is properly configured
4. Check file permissions

### **Common Error Locations:**
- **Apache:** `/var/log/apache2/error.log`
- **Nginx:** `/var/log/nginx/error.log`
- **PHP:** Check `php.ini` configuration

---

## 🎯 **Quick Start Checklist**

- [ ] Web server installed and running
- [ ] PHP 7.4+ installed
- [ ] MySQL 5.7+ installed and running
- [ ] TaskFlow files copied to web directory
- [ ] Database `taskflow` created
- [ ] `tasks.sql` imported successfully
- [ ] Database credentials configured
- [ ] File permissions set correctly
- [ ] Application accessible via browser
- [ ] Can login with default credentials

---

## 📋 **File Structure**

```
taskflow/
├── Dashboard.php          # Main dashboard
├── Login.php             # Authentication
├── Task.php              # Task management
├── goals.php             # Goal setting
├── analytics.php         # Analytics dashboard
├── collaboration.php     # Team collaboration
├── time-tracking.php     # Time tracking
├── profile.php           # User profiles
├── admin-users.php       # User management
├── admin-system.php      # System administration
├── db_connect.php        # Database connection
├── tasks.sql            # Database schema
├── get_task.php         # API endpoints
├── get_goal.php         # API endpoints
├── README.md            # Documentation
└── INSTALLATION_GUIDE.md # This file
```

---

**🎉 Once installed, your TaskFlow application will be ready to use with all features including role-based access, task management, goal tracking, analytics, and collaboration tools!**
