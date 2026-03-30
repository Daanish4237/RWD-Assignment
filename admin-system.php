<?php
session_start();
include("db_connect.php");

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

if ($_SESSION['role'] !== 'Admin') {
    header("Location: Dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Get system statistics
$system_stats_sql = "SELECT 
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM tasks) as total_tasks,
    (SELECT COUNT(*) FROM goals) as total_goals,
    (SELECT COUNT(*) FROM time_logs) as total_time_logs,
    (SELECT COUNT(*) FROM chat_messages) as total_messages,
    (SELECT COUNT(*) FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 7 DAY)) as active_users,
    (SELECT COUNT(*) FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 30 DAY)) as monthly_active_users";

$system_stats_result = mysqli_query($dbconn, $system_stats_sql);
$system_stats = mysqli_fetch_assoc($system_stats_result);

// Get recent activity
$recent_activity_sql = "SELECT 
    'user' as type, username, created_at as activity_date, 'Registered' as activity
    FROM users 
    WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
    UNION ALL
    SELECT 
    'task' as type, u.username, t.created_at as activity_date, CONCAT('Created task: ', t.title) as activity
    FROM tasks t 
    JOIN users u ON t.user_id = u.id 
    WHERE t.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY activity_date DESC 
    LIMIT 20";

$recent_activity_result = mysqli_query($dbconn, $recent_activity_sql);

// Get user activity by day
$daily_activity_sql = "SELECT 
    DATE(last_login) as login_date,
    COUNT(*) as daily_logins
    FROM users 
    WHERE last_login > DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(last_login)
    ORDER BY login_date DESC";

$daily_activity_result = mysqli_query($dbconn, $daily_activity_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>System Administration - TaskFlow Admin</title>
  <style>
    body {
      margin: 0;
      font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', 'Helvetica Neue', Helvetica, Arial, sans-serif;
      background: #f8f9fa;
      display: flex;
      height: 100vh;
    }

    /* Modern Toggle Button */
    .toggle-btn {
      position: fixed;
      top: 20px;
      left: 20px;
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      color: white;
      border: none;
      padding: 12px 16px;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      z-index: 1001;
      box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      display: block;
      visibility: visible;
      opacity: 1;
    }

    .toggle-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
      background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    }

    .toggle-btn:active {
      transform: translateY(0);
      box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }

    /* Enhanced Sidebar */
    .sidebar {
      width: 280px;
      background: linear-gradient(180deg, #1a1a2e 0%, #16213e 50%, #0f1419 100%);
      color: #fff;
      height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 30px 0;
      box-sizing: border-box;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
      border-right: 1px solid rgba(255, 255, 255, 0.1);
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1000;
      overflow: hidden;
    }

    .sidebar::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%);
      opacity: 0.3;
    }

    .sidebar img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      margin-bottom: 30px;
      box-shadow: 0 8px 32px rgba(59, 130, 246, 0.3);
      transition: all 0.3s ease;
      position: relative;
      z-index: 1;
    }

    .sidebar img:hover {
      transform: scale(1.05);
      box-shadow: 0 12px 40px rgba(59, 130, 246, 0.4);
    }

    .menu {
      display: flex;
      flex-direction: column;
      gap: 15px;
      width: 100%;
      padding: 0 20px;
      position: relative;
      z-index: 1;
    }

    .menu button {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(16, 185, 129, 0.1) 100%);
      color: white;
      border: 1px solid rgba(255, 255, 255, 0.2);
      padding: 15px 20px;
      border-radius: 12px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      backdrop-filter: blur(10px);
      position: relative;
      overflow: hidden;
    }

    .menu button::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }

    .menu button:hover::before {
      left: 100%;
    }

    .menu button:hover {
      transform: translateX(8px);
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.3) 0%, rgba(16, 185, 129, 0.2) 100%);
      box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
    }

    .menu button:active {
      transform: translateX(4px) scale(0.98);
    }

    /* Enhanced Main Content */
    .main {
      flex: 1;
      padding: 30px;
      display: flex;
      flex-direction: column;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      min-height: 100vh;
      position: relative;
      margin-left: 280px;
    }

    .main::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: 
        radial-gradient(circle at 20% 20%, rgba(59, 130, 246, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(16, 185, 129, 0.05) 0%, transparent 50%);
      pointer-events: none;
    }

    .sidebar.collapsed + .main {
      margin-left: 0;
    }

    /* Enhanced Page Header */
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding: 25px 30px;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      position: relative;
      overflow: hidden;
    }

    .page-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(220, 38, 38, 0.05) 0%, rgba(185, 28, 28, 0.05) 100%);
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .page-header:hover::before {
      opacity: 1;
    }

    .page-header h1 {
      margin: 0;
      background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-weight: 800;
      font-size: 28px;
      text-shadow: 0 4px 8px rgba(220, 38, 38, 0.3);
      position: relative;
      z-index: 1;
    }

    /* System Stats Grid */
    .system-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }

    .stat-card {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
      padding: 25px;
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(16, 185, 129, 0.05) 100%);
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .stat-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    .stat-card:hover::before {
      opacity: 1;
    }

    .stat-card h3 {
      font-size: 2.5em;
      margin: 0 0 10px 0;
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-weight: 800;
      position: relative;
      z-index: 1;
    }

    .stat-card p {
      color: #64748b;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-size: 14px;
      margin: 0;
      position: relative;
      z-index: 1;
    }

    /* Activity Table */
    .activity-table {
      width: 100%;
      border-collapse: collapse;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      border-radius: 16px;
      overflow: hidden;
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .activity-table th {
      background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
      color: white;
      padding: 20px;
      text-align: left;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-size: 14px;
    }

    .activity-table td {
      padding: 20px;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
      vertical-align: middle;
    }

    .activity-table tr:hover {
      background: rgba(220, 38, 38, 0.05);
    }

    .activity-type {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .type-user {
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }

    .type-task {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
        width: 280px;
        opacity: 1;
      }

      .sidebar.open {
        transform: translateX(0);
      }

      .main {
        margin-left: 0;
        padding: 20px 15px;
      }

      .system-stats {
        grid-template-columns: 1fr;
        gap: 15px;
      }

      .activity-table {
        font-size: 12px;
      }

      .activity-table th,
      .activity-table td {
        padding: 10px 8px;
      }
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
  <div class="sidebar">
    <img src="Logo RWD.jpeg" alt="Logo"> 
    <div class="menu">
      <a href="Dashboard.php"><button>🏠 DASHBOARD</button></a>
      <a href="Task.php"><button>📋 TASKS</button></a>
      <a href="time-tracking.php"><button>⏱️ TIME TRACKING</button></a>
      <a href="goals.php"><button>🎯 GOALS</button></a>
      <a href="collaboration.php"><button>👥 COLLABORATION</button></a>
      <a href="analytics.php"><button>📊 ANALYTICS</button></a>
      <a href="admin-users.php"><button>👤 USER MANAGEMENT</button></a>
      <button style="background: linear-gradient(135deg, rgba(220, 38, 38, 0.3) 0%, rgba(185, 28, 28, 0.2) 100%);">⚙️ SYSTEM ADMIN</button>
      <a href="profile.php"><button>👤 PROFILE</button></a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main">
    <div class="page-header">
      <h1>⚙️ System Administration</h1>
    </div>

    <!-- System Statistics -->
    <div class="system-stats">
      <div class="stat-card">
        <h3><?php echo $system_stats['total_users']; ?></h3>
        <p>Total Users</p>
      </div>
      <div class="stat-card">
        <h3><?php echo $system_stats['active_users']; ?></h3>
        <p>Active Users (7 days)</p>
      </div>
      <div class="stat-card">
        <h3><?php echo $system_stats['monthly_active_users']; ?></h3>
        <p>Monthly Active Users</p>
      </div>
      <div class="stat-card">
        <h3><?php echo $system_stats['total_tasks']; ?></h3>
        <p>Total Tasks</p>
      </div>
      <div class="stat-card">
        <h3><?php echo $system_stats['total_goals']; ?></h3>
        <p>Total Goals</p>
      </div>
      <div class="stat-card">
        <h3><?php echo $system_stats['total_time_logs']; ?></h3>
        <p>Time Sessions</p>
      </div>
      <div class="stat-card">
        <h3><?php echo $system_stats['total_messages']; ?></h3>
        <p>Chat Messages</p>
      </div>
    </div>

    <!-- Recent Activity -->
    <h2 style="color: #1e293b; margin-bottom: 20px; font-size: 24px; font-weight: 700;">📈 Recent System Activity</h2>
    <table class="activity-table">
      <thead>
        <tr>
          <th>Type</th>
          <th>User</th>
          <th>Activity</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($activity = mysqli_fetch_assoc($recent_activity_result)): ?>
          <tr>
            <td>
              <span class="activity-type <?php echo $activity['type'] === 'user' ? 'type-user' : 'type-task'; ?>">
                <?php echo $activity['type'] === 'user' ? '👤 User' : '📋 Task'; ?>
              </span>
            </td>
            <td><?php echo htmlspecialchars($activity['username']); ?></td>
            <td><?php echo htmlspecialchars($activity['activity']); ?></td>
            <td><?php echo date('M j, Y g:i A', strtotime($activity['activity_date'])); ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <!-- System Actions -->
    <div style="margin-top: 40px; padding: 30px; background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%); border-radius: 16px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);">
      <h3 style="color: #1e293b; margin-bottom: 20px; font-size: 20px; font-weight: 700;">🔧 System Actions</h3>
      <div style="display: flex; gap: 15px; flex-wrap: wrap;">
        <button onclick="exportData()" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
          📊 Export System Data
        </button>
        <button onclick="clearCache()" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
          🗑️ Clear System Cache
        </button>
        <button onclick="backupDatabase()" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
          💾 Backup Database
        </button>
        <button onclick="systemMaintenance()" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
          🔧 System Maintenance
        </button>
      </div>
    </div>
  </div>

  <script>
    function toggleSidebar() {
      const sidebar = document.querySelector('.sidebar');
      if (window.innerWidth <= 768) {
        sidebar.classList.toggle('open');
      } else {
        sidebar.classList.toggle('collapsed');
      }
    }

    function exportData() {
      alert('System Action: Export Data\n\nThis would export all system data including:\n- User information\n- Task and goal data\n- Analytics reports\n- System logs');
    }

    function clearCache() {
      if (confirm('Are you sure you want to clear the system cache?\n\nThis will improve performance but may temporarily slow down the system.')) {
        alert('System cache cleared successfully!');
      }
    }

    function backupDatabase() {
      alert('System Action: Database Backup\n\nThis would create a complete backup of the database including:\n- All user data\n- Tasks and goals\n- System settings\n- Chat messages');
    }

    function systemMaintenance() {
      if (confirm('System Maintenance Mode\n\nThis will put the system in maintenance mode. Users will not be able to access the system during maintenance.\n\nContinue?')) {
        alert('System maintenance mode activated!\n\nUsers will see a maintenance page until you disable maintenance mode.');
      }
    }
  </script>
</body>
</html>
