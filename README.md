# 🚀 TaskFlow - Productivity Enhancement Web Application

A comprehensive productivity management web application built with PHP, MySQL, and modern web technologies.

## ✨ Features

- 📋 **Task Management** - Create, organize, and track tasks with priority levels
- ⏱️ **Time Tracking** - Start/stop timers and track time spent on tasks
- 🎯 **Goal Setting** - Set and monitor progress towards personal and professional goals
- 👥 **Team Collaboration** - Share tasks and communicate with team members
- 📊 **Analytics Dashboard** - Visualize productivity data with interactive charts
- 📱 **Responsive Design** - Works seamlessly on desktop, tablet, and mobile devices

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP 8.2+
- **Database**: MySQL/MariaDB
- **Charts**: Chart.js
- **Styling**: Custom CSS with Flexbox/Grid

## 📋 Prerequisites

- Web server (Apache/Nginx)
- PHP 8.2 or higher
- MySQL 5.7 or higher
- Modern web browser

## 🚀 Installation

### **For Clients - Easy Setup (Recommended)**

#### **Option 1: XAMPP (Windows/Mac)**
1. **Download XAMPP:** https://www.apachefriends.org/download.html
2. **Install and Start:** Start Apache and MySQL from XAMPP Control Panel
3. **Download TaskFlow:** Download ZIP from GitHub or clone repository
4. **Copy Files:** Place files in `C:\xampp\htdocs\taskflow\` (Windows) or `/Applications/XAMPP/htdocs/taskflow/` (Mac)
5. **Setup Database:** 
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create database named `taskflow`
   - Import `tasks.sql` file
6. **Access:** Go to `http://localhost/taskflow/`

#### **Option 2: WAMP (Windows Only)**
1. **Download WAMP:** https://www.wampserver.com/en/
2. **Follow same steps as XAMPP** but use `C:\wamp64\www\taskflow\`

#### **Option 3: MAMP (Mac Only)**
1. **Download MAMP:** https://www.mamp.info/en/downloads/
2. **Follow same steps as XAMPP** but use `/Applications/MAMP/htdocs/taskflow/`

### **For Developers - Manual Setup**

#### 1. Clone the Repository
```bash
git clone https://github.com/51xengineer/Rwd.git
cd Rwd
```

#### 2. Database Setup
1. Create a new MySQL database named `taskflow`
2. Import the database schema:
```bash
mysql -u your_username -p taskflow < tasks.sql
```

#### 3. Configuration
Update the database credentials in `db_connect.php`:
```php
$db_servername = "localhost";
$db_username = "your_username";
$db_password = "your_password";
$db_name = "taskflow";
```

### **📋 Installation Verification**
Run the setup script to verify your installation:
- Access: `http://localhost/taskflow/setup.php`
- This will check all requirements and guide you through any issues

### **📖 Detailed Installation Guide**
For comprehensive installation instructions, see [INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md)

### **🔧 Troubleshooting**
If you encounter issues, check [TROUBLESHOOTING.md](TROUBLESHOOTING.md) for solutions

### 4. Web Server Setup
- Place the project files in your web server's document root
- Ensure PHP and MySQL are running
- Set appropriate file permissions

### 5. Access the Application
Open your web browser and navigate to:
```
http://localhost/taskflow/Login.php
```

## 👤 Default Users

The application comes with pre-configured test users:

| Username | Password | Role |
|----------|----------|------|
| admin | password | Admin |
| user1 | password | User |

**Note**: Change these default passwords in production!

## 📱 Usage

### Getting Started
1. **Login** - Use the default credentials or register a new account
2. **Dashboard** - View your productivity overview and quick stats
3. **Tasks** - Create and manage your tasks with priorities and due dates
4. **Time Tracking** - Start timers to track time spent on tasks
5. **Goals** - Set and track progress towards your objectives
6. **Analytics** - View detailed productivity reports and insights

### Key Features

#### Task Management
- Create tasks with titles, descriptions, and priorities
- Set due dates and track progress
- Organize tasks by status (Pending, In Progress, Completed)
- Visual progress indicators

#### Time Tracking
- Start/stop timers for active tasks
- Automatic time calculation
- Session history and reports
- Real-time timer display

#### Goal Setting
- Set personal and professional goals
- Track progress with visual indicators
- Set target dates and monitor deadlines
- Goal status management

#### Analytics
- Task completion statistics
- Time tracking reports
- Goal progress visualization
- Productivity trends and insights

## 🗂️ File Structure

```
taskflow/
├── Login.php                 # User authentication
├── Dashboard.php             # Main dashboard
├── Task.php                  # Task management
├── time-tracking.php         # Time tracking functionality
├── goals.php                 # Goal setting and tracking
├── collaboration.php         # Team collaboration features
├── analytics.php             # Analytics dashboard
├── db_connect.php            # Database connection
├── tasks.sql                # Database schema
├── Logo RWD.jpeg            # Application logo
├── PROJECT_DOCUMENTATION.md # Comprehensive documentation
├── PROJECT_PROPOSAL.md      # Project proposal
└── README.md                # This file
```

## 🔧 Configuration

### Database Configuration
Edit `db_connect.php` to match your database settings:
```php
$db_servername = "localhost";
$db_username = "your_username";
$db_password = "your_password";
$db_name = "taskflow";
```

### Security Settings
- Change default passwords
- Enable HTTPS in production
- Configure proper file permissions
- Set up database user with limited privileges

## 📊 Database Schema

The application uses the following main tables:

- **users** - User accounts and authentication
- **tasks** - Task management and tracking
- **goals** - Goal setting and progress
- **time_logs** - Time tracking sessions
- **collaborations** - Team collaboration data
- **chat_messages** - Real-time communication

## 🎨 Customization

### Styling
- Modify CSS in the `<style>` sections of each PHP file
- Update color scheme by changing CSS variables
- Customize responsive breakpoints for different devices

### Features
- Add new task priorities in the database
- Extend user roles and permissions
- Integrate additional chart types
- Add new analytics metrics

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in `db_connect.php`
   - Ensure MySQL service is running
   - Check database name and user permissions

2. **Page Not Loading**
   - Verify web server is running
   - Check file permissions
   - Ensure PHP is properly configured

3. **Charts Not Displaying**
   - Check internet connection for Chart.js CDN
   - Verify JavaScript is enabled
   - Check browser console for errors

### Debug Mode
Enable debug mode by uncommenting the alert in `db_connect.php`:
```php
echo "<script>alert('Successfully connected to TaskFlow database!');</script>";
```

## 🔒 Security Considerations

- **Password Security**: Uses PHP's `password_hash()` for secure password storage
- **SQL Injection Prevention**: Prepared statements and input validation
- **XSS Protection**: Input sanitization and output escaping
- **Session Management**: Secure session handling

## 📈 Performance Optimization

- **Database Indexing**: Proper indexes on frequently queried columns
- **Query Optimization**: Efficient SQL queries with proper joins
- **Caching**: Consider implementing caching for analytics data
- **CDN**: Use CDN for Chart.js and other external resources

## 🚀 Deployment

### Production Deployment
1. **Web Server**: Configure Apache/Nginx with PHP support
2. **Database**: Set up MySQL with proper user permissions
3. **SSL**: Enable HTTPS for secure data transmission
4. **Backup**: Implement regular database backups
5. **Monitoring**: Set up application monitoring and logging

### Environment Variables
Consider using environment variables for sensitive configuration:
```php
$db_servername = $_ENV['DB_HOST'] ?? 'localhost';
$db_username = $_ENV['DB_USER'] ?? 'root';
$db_password = $_ENV['DB_PASS'] ?? '';
$db_name = $_ENV['DB_NAME'] ?? 'taskflow';
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 📞 Support

For support and questions:
- Create an issue in the repository
- Check the documentation files
- Review the troubleshooting section

## 🎯 Roadmap

### Upcoming Features
- [ ] Real-time collaboration with WebSockets
- [ ] Mobile app development
- [ ] Advanced reporting and analytics
- [ ] Third-party integrations
- [ ] API development
- [ ] Advanced automation features

### Version History
- **v1.0.0** - Initial release with core features
- **v1.1.0** - Enhanced collaboration features (planned)
- **v1.2.0** - Mobile app release (planned)

---

**TaskFlow** - Making productivity simple and effective! 🚀

**Last Updated**: December 2024  
**Version**: 1.0.0
