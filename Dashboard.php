<?php
session_start();
include("db_connect.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - TaskFlow</title>
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
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
      pointer-events: none;
    }

    .sidebar.collapsed {
      width: 0;
      opacity: 0;
      overflow: hidden;
      padding: 0;
      transform: translateX(-100%);
    }

    .sidebar img {
      width: 140px;
      height: 140px;
      margin-bottom: 40px;
      border-radius: 20px;
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
      gap: 8px;
      width: 100%;
      padding: 0 20px;
      position: relative;
      z-index: 1;
    }

    .menu button {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: #fff;
      padding: 16px 20px;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      text-align: left;
      width: 100%;
      box-sizing: border-box;
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

    .menu button:hover {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.8) 0%, rgba(16, 185, 129, 0.6) 100%);
      border-color: rgba(59, 130, 246, 0.5);
      transform: translateX(8px);
      box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
    }

    .menu button:hover::before {
      left: 100%;
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

    /* Enhanced Header */
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 20px;
      margin-bottom: 30px;
      padding: 20px 30px;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      position: relative;
      z-index: 1;
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .user-name {
      font-weight: 700;
      font-size: 20px;
      color: #1e293b;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .avatar {
      width: 50px;
      height: 50px;
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 700;
      font-size: 18px;
      box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
      transition: all 0.3s ease;
    }

    .avatar:hover {
      transform: scale(1.1);
      box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
    }
    /* Dashboard sections */
    .dashboard-section {
      margin-bottom: 20px;
    }
    /* Enhanced Task Table */
    .task-table {
      width: 100%;
      border-collapse: collapse;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      border-radius: 16px;
      overflow: hidden;
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .task-table th, .task-table td {
      padding: 20px;
      text-align: left;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .task-table th {
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      color: #fff;
      font-weight: 700;
      font-size: 16px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .task-table td {
      font-weight: 500;
      color: #374151;
    }

    .task-table tr:hover {
      background: rgba(59, 130, 246, 0.05);
    }
    .progress-circle {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: conic-gradient(#007bff calc(var(--progress) * 1%), #e6e6e6 0%);
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
      margin: 6px auto;
      box-shadow: 0 1px 3px rgba(0,0,0,0.8);
    }

    .progress-text {
      position: absolute;
      font-weight: 700;
      font-size: 14px;
      color: #111;
    }

    /* Enhanced Stats Grid */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
      margin: 30px 0;
      position: relative;
      z-index: 1;
    }

    .stat-card {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
      padding: 30px 25px;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      text-align: center;
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

    /* Admin-specific styles */
    .admin-stat {
      background: linear-gradient(135deg, rgba(220, 38, 38, 0.1) 0%, rgba(185, 28, 28, 0.05) 100%);
      border: 1px solid rgba(220, 38, 38, 0.2);
    }

    .admin-stat h3 {
      color: #dc2626;
    }

    .admin-btn {
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .admin-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .stat-card h3 {
      font-size: 2.5em;
      margin: 0 0 10px 0;
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-weight: 800;
      text-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
    }

    .stat-card p {
      margin: 0;
      color: #64748b;
      font-weight: 600;
      font-size: 16px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    /* Priority and Status Badges */
    .priority-Low { background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    .priority-Medium { background: #ffc107; color: black; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    .priority-High { background: #fd7e14; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    .priority-Critical { background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }

    .status-Pending { background: #6c757d; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    .status-In\ Progress { background: #007bff; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    .status-Completed { background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    .status-Cancelled { background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }

    /* Mobile Responsive Styles */
    @media (max-width: 1024px) {
      .sidebar {
        width: 200px;
      }
      
      .sidebar img {
        width: 100px;
      }
      
      .main {
        padding: 15px;
      }
    }

    @media (max-width: 768px) {
      body {
        flex-direction: column;
        height: auto;
        min-height: 100vh;
      }

      .toggle-btn {
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1001;
        background: #007bff;
        color: white;
        border: none;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 20px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      }

      .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 280px;
        height: 100vh;
        z-index: 1000;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        background: #0b111a;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
      }

      .sidebar.open {
        transform: translateX(0);
      }

      .sidebar.collapsed {
        transform: translateX(-100%);
        width: 280px;
        opacity: 1;
      }

      .sidebar img {
        width: 100px;
        margin-bottom: 25px;
      }

      .menu {
        padding: 0 20px;
        gap: 12px;
      }

      .menu button {
        padding: 14px 18px;
        font-size: 15px;
        border-radius: 10px;
      }

      .main {
        width: 100%;
        padding: 20px 15px;
        margin-left: 0;
      }

      .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 25px;
        padding-top: 60px;
      }

      .user-name {
        font-size: 20px;
      }

      .avatar {
        width: 45px;
        height: 45px;
      }

      .content h2 {
        font-size: 24px;
        margin-bottom: 15px;
      }

      .content p {
        font-size: 16px;
        margin-bottom: 20px;
      }

      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin: 20px 0;
      }

      .stat-card {
        padding: 20px 15px;
        border-radius: 12px;
      }

      .stat-card h3 {
        font-size: 1.8em;
      }

      .stat-card p {
        font-size: 14px;
      }

      .task-table {
        font-size: 14px;
        overflow-x: auto;
        display: block;
        white-space: nowrap;
      }

      .task-table th,
      .task-table td {
        padding: 12px 8px;
        min-width: 100px;
      }

      .task-table th:first-child,
      .task-table td:first-child {
        min-width: 150px;
      }

      .progress-circle {
        width: 50px;
        height: 50px;
        font-size: 12px;
      }

      .priority-Low, .priority-Medium, .priority-High, .priority-Critical,
      .status-Pending, .status-In\ Progress, .status-Completed, .status-Cancelled {
        font-size: 11px;
        padding: 3px 6px;
      }
    }

    @media (max-width: 480px) {
      .toggle-btn {
        top: 10px;
        left: 10px;
        padding: 10px 12px;
        font-size: 18px;
      }

      .sidebar {
        width: 100%;
        padding: 20px 0;
      }

      .sidebar img {
        width: 80px;
        margin-bottom: 20px;
      }

      .menu {
        padding: 0 15px;
        gap: 10px;
      }

      .menu button {
        padding: 12px 15px;
        font-size: 14px;
      }

      .main {
        padding: 15px 10px;
      }

      .header {
        padding-top: 50px;
        margin-bottom: 20px;
      }

      .user-name {
        font-size: 18px;
      }

      .avatar {
        width: 40px;
        height: 40px;
      }

      .content h2 {
        font-size: 20px;
        margin-bottom: 12px;
      }

      .content h3 {
        font-size: 18px;
        margin-bottom: 15px;
      }

      .stats-grid {
        grid-template-columns: 1fr;
        gap: 12px;
        margin: 15px 0;
      }

      .stat-card {
        padding: 15px 12px;
        border-radius: 10px;
      }

      .stat-card h3 {
        font-size: 1.6em;
      }

      .stat-card p {
        font-size: 13px;
      }

      .task-table {
        font-size: 12px;
      }

      .task-table th,
      .task-table td {
        padding: 10px 6px;
        min-width: 80px;
      }

      .task-table th:first-child,
      .task-table td:first-child {
        min-width: 120px;
      }

      .progress-circle {
        width: 40px;
        height: 40px;
        font-size: 10px;
      }

      .priority-Low, .priority-Medium, .priority-High, .priority-Critical,
      .status-Pending, .status-In\ Progress, .status-Completed, .status-Cancelled {
        font-size: 10px;
        padding: 2px 4px;
      }
    }

    /* Landscape Mobile */
    @media (max-width: 768px) and (orientation: landscape) {
      .header {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        padding-top: 60px;
      }

      .stats-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
      }

      .stat-card {
        padding: 12px 8px;
      }

      .stat-card h3 {
        font-size: 1.4em;
      }

      .stat-card p {
        font-size: 12px;
      }
    }

    /* Overlay for mobile sidebar */
    .sidebar-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 999;
    }

    @media (max-width: 768px) {
      .sidebar-overlay.show {
        display: block;
      }
    }
  </style>
</head>
<body>
  <!-- Mobile Overlay -->
  <div class="sidebar-overlay" onclick="closeSidebar()"></div>
  
  <!-- Sidebar -->
  <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
  <div class="sidebar">
    <img src="Logo RWD.jpeg" alt="Logo"> 
    <div class="menu">
      <?php if ($role === 'Admin'): ?>
        <!-- Admin Menu -->
        <a href="Task.php"><button>📋 TASKS</button></a>
        <a href="time-tracking.php"><button>⏱️ TIME TRACKING</button></a>
        <a href="goals.php"><button>🎯 GOALS</button></a>
        <a href="collaboration.php"><button>👥 COLLABORATION</button></a>
        <a href="analytics.php"><button>📊 ANALYTICS</button></a>
        <a href="admin-users.php"><button>👤 USER MANAGEMENT</button></a>
        <a href="admin-system.php"><button>⚙️ SYSTEM ADMIN</button></a>
        <a href="profile.php"><button>👤 PROFILE</button></a>
      <?php else: ?>
        <!-- User Menu -->
        <a href="Task.php"><button>📋 TASKS</button></a>
        <a href="time-tracking.php"><button>⏱️ TIME TRACKING</button></a>
        <a href="goals.php"><button>🎯 GOALS</button></a>
        <a href="collaboration.php"><button>👥 COLLABORATION</button></a>
        <a href="analytics.php"><button>📊 ANALYTICS</button></a>
        <a href="profile.php"><button>👤 PROFILE</button></a>
      <?php endif; ?>
    </div>
  </div>
  <!-- Main -->
  <div class="main">
    <!-- Enhanced Header -->
    <div class="header">
      <div class="user-info">
        <div class="avatar" id="userAvatar"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
        <div class="user-name" id="userNameDisplay">Welcome back, <?php echo htmlspecialchars($username); ?>!</div>
      </div>
    </div>

    <!-- Dashboard Content -->
    <div class="content">
      <h2>Welcome back, <?php echo htmlspecialchars($username); ?>!</h2>
      <p>Role: <strong><?php echo htmlspecialchars($role); ?></strong></p>

      <?php if ($role === 'Admin'): ?>
        <!-- Admin Dashboard -->
        <div class="admin-section">
          <h3 style="color: #dc2626; margin-bottom: 20px; font-size: 24px; font-weight: 700;">
            🔧 Admin Dashboard
          </h3>
          
          <!-- Admin Stats -->
          <div class="stats-grid">
            <?php
            // Get system-wide statistics for admin
            $admin_stats_sql = "SELECT 
              (SELECT COUNT(*) FROM users) as total_users,
              (SELECT COUNT(*) FROM tasks) as total_tasks,
              (SELECT COUNT(*) FROM goals) as total_goals,
              (SELECT COUNT(*) FROM time_logs) as total_time_logs";
            $admin_stats_result = mysqli_query($dbconn, $admin_stats_sql);
            $admin_stats = mysqli_fetch_assoc($admin_stats_result);
            ?>
            
            <div class="stat-card admin-stat">
              <h3><?php echo $admin_stats['total_users'] ?? 0; ?></h3>
              <p>Total Users</p>
            </div>
            <div class="stat-card admin-stat">
              <h3><?php echo $admin_stats['total_tasks'] ?? 0; ?></h3>
              <p>System Tasks</p>
            </div>
            <div class="stat-card admin-stat">
              <h3><?php echo $admin_stats['total_goals'] ?? 0; ?></h3>
              <p>System Goals</p>
            </div>
            <div class="stat-card admin-stat">
              <h3><?php echo $admin_stats['total_time_logs'] ?? 0; ?></h3>
              <p>Time Sessions</p>
            </div>
          </div>

          <!-- Admin Actions -->
          <div class="admin-actions" style="margin-top: 30px;">
            <h4 style="color: #1e293b; margin-bottom: 15px;">Admin Actions</h4>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
              <button onclick="viewAllUsers()" class="admin-btn" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                👥 View All Users
              </button>
              <button onclick="viewSystemAnalytics()" class="admin-btn" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                📊 System Analytics
              </button>
              <button onclick="manageUsers()" class="admin-btn" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                ⚙️ Manage Users
              </button>
            </div>
          </div>
        </div>
      <?php else: ?>
        <!-- User Dashboard -->
        <div class="user-section">
          <!-- Quick Stats -->
          <div class="stats-grid">
            <?php
            // Get user's task statistics
            $task_stats_sql = "SELECT 
              COUNT(*) as total_tasks,
              SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_tasks,
              SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_tasks,
              AVG(progress) as avg_progress
              FROM tasks WHERE user_id = $user_id";
            $task_stats_result = mysqli_query($dbconn, $task_stats_sql);
            $stats = mysqli_fetch_assoc($task_stats_result);
            ?>
            
            <div class="stat-card">
              <h3><?php echo $stats['total_tasks'] ?? 0; ?></h3>
              <p>My Tasks</p>
            </div>
            <div class="stat-card">
              <h3><?php echo $stats['completed_tasks'] ?? 0; ?></h3>
              <p>Completed</p>
            </div>
            <div class="stat-card">
              <h3><?php echo $stats['in_progress_tasks'] ?? 0; ?></h3>
              <p>In Progress</p>
            </div>
            <div class="stat-card">
              <h3><?php echo round($stats['avg_progress'] ?? 0, 1); ?>%</h3>
              <p>Avg Progress</p>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Recent Tasks -->
      <h3>Recent Tasks</h3>
      <?php
      // Get user's recent tasks
      $sql = "SELECT t.*, u.username as owner_name 
              FROM tasks t 
              LEFT JOIN users u ON t.user_id = u.id 
              WHERE t.user_id = $user_id 
              ORDER BY t.created_at DESC 
              LIMIT 5";
      $result = mysqli_query($dbconn, $sql);

      if (!$result) {
        echo "<p>Error retrieving data: " . mysqli_error($dbconn) . "</p>";
      } else {
        if (mysqli_num_rows($result) > 0) {
          echo "<table class='task-table'>
                  <tr>
                    <th>Task</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Progress</th>
                  </tr>";
          while ($row = mysqli_fetch_assoc($result)) {
            $progress = isset($row['progress']) ? (int)$row['progress'] : 0;
            if ($progress < 0) $progress = 0;
            if ($progress > 100) $progress = 100;
           
            $title = htmlspecialchars($row['title'] ?? '');
            $priority = htmlspecialchars($row['priority'] ?? '');
            $status = htmlspecialchars($row['status'] ?? '');
            $due_date = $row['due_date'] ? date('M d, Y', strtotime($row['due_date'])) : 'No due date';
            
            echo "<tr>
                    <td>{$title}</td>
                    <td><span class='priority-{$priority}'>{$priority}</span></td>
                    <td><span class='status-{$status}'>{$status}</span></td>
                    <td>{$due_date}</td>
                    <td>
                      <div class='progress-circle' style='
                        width: 60px;
                        height: 60px;
                        border-radius: 50%;
                        background: conic-gradient(#007bff {$progress}%, #ddd {$progress}%);
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        font-size: 14px;
                        font-weight: bold;
                        color: #333;
                      '>
                        {$progress}%
                      </div>
                    </td>
                  </tr>";
          }
          echo "</table>";
        } else {
          echo "<p>No tasks found. <a href='Task.php'>Create your first task!</a></p>";
        }
      }
      ?>
    </div>

    <script src="js/script.js"></script>
  </div>
</body>
<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (window.innerWidth <= 768) {
            // Mobile behavior
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        } else {
            // Desktop behavior
            sidebar.classList.toggle('collapsed');
        }
    }

    function closeSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
    }

    // Close sidebar when clicking on menu items (mobile)
    document.addEventListener('DOMContentLoaded', function () {
        // Update username display from session
        var el = document.getElementById('userNameDisplay');
        if (el) el.textContent = '<?php echo htmlspecialchars($username); ?>';
        
        // Add click listeners to menu items for mobile
        const menuButtons = document.querySelectorAll('.menu button');
        menuButtons.forEach(button => {
            button.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    closeSidebar();
                }
            });
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                const sidebar = document.querySelector('.sidebar');
                const overlay = document.querySelector('.sidebar-overlay');
                sidebar.classList.remove('open');
                overlay.classList.remove('show');
            }
        });
    });

    // Admin functions
    function viewAllUsers() {
        alert('Admin Feature: View All Users\n\nThis would show a list of all registered users with their activity and statistics.');
        // In a real implementation, this would redirect to an admin users page
    }

    function viewSystemAnalytics() {
        alert('Admin Feature: System Analytics\n\nThis would show comprehensive analytics across all users including:\n- User activity patterns\n- System performance metrics\n- Usage statistics');
        // In a real implementation, this would redirect to an admin analytics page
    }

    function manageUsers() {
        alert('Admin Feature: Manage Users\n\nThis would allow you to:\n- Edit user profiles\n- Suspend/activate accounts\n- Reset passwords\n- View user activity logs');
        // In a real implementation, this would redirect to a user management page
    }
</script>
</html>

