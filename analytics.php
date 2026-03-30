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

// Get analytics data based on user role
if ($role === 'Admin') {
    // Admin sees system-wide analytics
    $tasks_sql = "SELECT 
        COUNT(*) as total_tasks,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_tasks,
        SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_tasks,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_tasks,
        AVG(progress) as avg_progress
        FROM tasks";

    $goals_sql = "SELECT 
        COUNT(*) as total_goals,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_goals,
        SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_goals,
        AVG(progress) as avg_goal_progress
        FROM goals";

    $users_sql = "SELECT 
        COUNT(*) as total_users,
        COUNT(CASE WHEN last_login > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as active_users
        FROM users";
} else {
    // Regular users see only their own data
    $tasks_sql = "SELECT 
        COUNT(*) as total_tasks,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_tasks,
        SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_tasks,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_tasks,
        AVG(progress) as avg_progress
        FROM tasks WHERE user_id = $user_id";

    $goals_sql = "SELECT 
        COUNT(*) as total_goals,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_goals,
        SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_goals,
        AVG(progress) as avg_goal_progress
        FROM goals WHERE user_id = $user_id";
}

$time_sql = "SELECT 
    COUNT(*) as total_sessions,
    SUM(duration) as total_time,
    AVG(duration) as avg_session_time
    FROM time_logs WHERE user_id = $user_id AND end_time IS NOT NULL";

$tasks_result = mysqli_query($dbconn, $tasks_sql);
$goals_result = mysqli_query($dbconn, $goals_sql);
$time_result = mysqli_query($dbconn, $time_sql);

$tasks_data = mysqli_fetch_assoc($tasks_result);
$goals_data = mysqli_fetch_assoc($goals_result);
$time_data = mysqli_fetch_assoc($time_result);

// Get recent activity
$recent_tasks_sql = "SELECT title, status, progress, created_at FROM tasks WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
$recent_goals_sql = "SELECT title, progress, status, target_date FROM goals WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
$recent_time_sql = "SELECT tl.duration, t.title as task_title, tl.start_time FROM time_logs tl 
                   JOIN tasks t ON tl.task_id = t.id 
                   WHERE tl.user_id = $user_id AND tl.end_time IS NOT NULL 
                   ORDER BY tl.start_time DESC LIMIT 5";

$recent_tasks_result = mysqli_query($dbconn, $recent_tasks_sql);
$recent_goals_result = mysqli_query($dbconn, $recent_goals_sql);
$recent_time_result = mysqli_query($dbconn, $recent_time_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Analytics - TaskFlow</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      margin: 0;
      font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', 'Helvetica Neue', Helvetica, Arial, sans-serif;
      background: #f8f9fa;
      display: flex;
      height: 100vh;
    }

    /* Enhanced Sticky Sidebar Toggle Button */
    .toggle-btn {
      position: fixed;
      top: 20px;
      left: 20px;
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      color: #fff;
      border: none;
      padding: 12px 16px;
      border-radius: 12px;
      font-size: 18px;
      cursor: pointer;
      z-index: 1001;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
      display: block;
      visibility: visible;
      opacity: 1;
    }

    .toggle-btn:hover {
      background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
      transform: scale(1.05);
      box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
    }

    /* Enhanced Sticky Sidebar */
    .sidebar {
      width: 280px;
      background: linear-gradient(180deg, #1a1a2e 0%, #16213e 50%, #0f1419 100%);
      color: #fff;
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
      height: 100vh;
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
      transform: translateX(-100%);
      opacity: 0;
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

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding: 20px;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .page-header h1 {
      margin: 0;
      color: #333;
    }

    /* Analytics Grid */
    .analytics-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .analytics-card {
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .card-title {
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 15px;
      color: #333;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 15px;
    }

    .stat-item {
      text-align: center;
      padding: 15px;
      background: #f8f9fa;
      border-radius: 8px;
    }

    .stat-value {
      font-size: 24px;
      font-weight: bold;
      color: #007bff;
      margin-bottom: 5px;
    }

    .stat-label {
      font-size: 12px;
      color: #666;
      text-transform: uppercase;
    }

    /* Charts */
    .chart-container {
      position: relative;
      height: 300px;
      margin: 20px 0;
    }

    /* Recent Activity */
    .activity-section {
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }

    .activity-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px solid #eee;
    }

    .activity-item:last-child {
      border-bottom: none;
    }

    .activity-info {
      flex: 1;
    }

    .activity-title {
      font-weight: bold;
      color: #333;
      margin-bottom: 5px;
    }

    .activity-meta {
      font-size: 12px;
      color: #666;
    }

    .activity-status {
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: bold;
    }

    .status-completed { background: #d4edda; color: #155724; }
    .status-in-progress { background: #cce5ff; color: #004085; }
    .status-pending { background: #e2e3e5; color: #383d41; }

    /* Mobile Responsive */
    @media (max-width: 768px) {
      .analytics-grid {
        grid-template-columns: 1fr;
      }
      
      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
      }
      
      .chart-container {
        height: 250px;
      }
    }

    /* Enhanced Mobile Responsive Styles */
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
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        border: none;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 20px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        transition: all 0.3s ease;
      }

      .toggle-btn:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
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

      .analytics-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 25px;
        padding-top: 60px;
      }

      .analytics-header h1 {
        font-size: 24px;
        margin: 0;
      }

      .stats-grid {
        grid-template-columns: 1fr;
        gap: 20px;
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

      .charts-grid {
        grid-template-columns: 1fr;
        gap: 25px;
        margin: 25px 0;
      }

      .chart-container {
        height: 300px;
        padding: 20px;
        border-radius: 12px;
      }

      .chart-container canvas {
        max-height: 250px;
      }

      .activity-section {
        margin-top: 30px;
      }

      .activity-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .activity-card {
        padding: 20px;
        border-radius: 12px;
      }

      .activity-card h3 {
        font-size: 18px;
        margin-bottom: 15px;
      }

      .activity-item {
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
      }

      .activity-item:last-child {
        border-bottom: none;
      }

      .activity-item .title {
        font-size: 14px;
        margin-bottom: 5px;
      }

      .activity-item .meta {
        font-size: 12px;
        color: #6c757d;
      }

      .progress-bar {
        height: 8px;
        margin: 8px 0;
      }

      .status-badge {
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 6px;
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

      .analytics-header {
        padding-top: 50px;
        margin-bottom: 20px;
      }

      .analytics-header h1 {
        font-size: 20px;
      }

      .stats-grid {
        gap: 15px;
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

      .charts-grid {
        gap: 20px;
        margin: 20px 0;
      }

      .chart-container {
        height: 250px;
        padding: 15px;
        border-radius: 10px;
      }

      .chart-container canvas {
        max-height: 200px;
      }

      .activity-section {
        margin-top: 25px;
      }

      .activity-grid {
        gap: 15px;
      }

      .activity-card {
        padding: 15px;
        border-radius: 10px;
      }

      .activity-card h3 {
        font-size: 16px;
        margin-bottom: 12px;
      }

      .activity-item {
        padding: 10px 0;
      }

      .activity-item .title {
        font-size: 13px;
        margin-bottom: 4px;
      }

      .activity-item .meta {
        font-size: 11px;
      }

      .progress-bar {
        height: 6px;
        margin: 6px 0;
      }

      .status-badge {
        font-size: 10px;
        padding: 3px 6px;
        border-radius: 4px;
      }
    }

    /* Landscape Mobile */
    @media (max-width: 768px) and (orientation: landscape) {
      .analytics-header {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        padding-top: 60px;
      }

      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
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

      .charts-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
      }

      .chart-container {
        height: 200px;
        padding: 10px;
      }

      .chart-container canvas {
        max-height: 150px;
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
      <a href="Dashboard.php"><button>🏠 DASHBOARD</button></a>
      <a href="Task.php"><button>📋 TASKS</button></a>
      <a href="time-tracking.php"><button>⏱️ TIME TRACKING</button></a>
      <a href="goals.php"><button>🎯 GOALS</button></a>
      <a href="collaboration.php"><button>👥 COLLABORATION</button></a>
      <button class="active">📊 ANALYTICS</button>
      <?php if ($role === 'Admin'): ?>
        <a href="admin-users.php"><button>👤 USER MANAGEMENT</button></a>
        <a href="admin-system.php"><button>⚙️ SYSTEM ADMIN</button></a>
      <?php endif; ?>
      <a href="profile.php"><button>👤 PROFILE</button></a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main">
    <div class="page-header">
      <?php if ($role === 'Admin'): ?>
        <h1>📊 System Analytics Dashboard</h1>
        <p style="color: #dc2626; font-weight: 600; margin-top: 5px;">🔧 Admin View - System-wide Analytics</p>
      <?php else: ?>
        <h1>📊 My Analytics Dashboard</h1>
        <p style="color: #3b82f6; font-weight: 600; margin-top: 5px;">👤 Personal Analytics</p>
      <?php endif; ?>
    </div>

    <!-- Overview Stats -->
    <div class="analytics-grid">
      <div class="analytics-card">
        <div class="card-title">📋 Task Overview</div>
        <div class="stats-grid">
          <div class="stat-item">
            <div class="stat-value"><?php echo $tasks_data['total_tasks'] ?? 0; ?></div>
            <div class="stat-label">Total Tasks</div>
          </div>
          <div class="stat-item">
            <div class="stat-value"><?php echo $tasks_data['completed_tasks'] ?? 0; ?></div>
            <div class="stat-label">Completed</div>
          </div>
          <div class="stat-item">
            <div class="stat-value"><?php echo $tasks_data['in_progress_tasks'] ?? 0; ?></div>
            <div class="stat-label">In Progress</div>
          </div>
          <div class="stat-item">
            <div class="stat-value"><?php echo round($tasks_data['avg_progress'] ?? 0, 1); ?>%</div>
            <div class="stat-label">Avg Progress</div>
          </div>
        </div>
      </div>

      <div class="analytics-card">
        <div class="card-title">🎯 Goal Overview</div>
        <div class="stats-grid">
          <div class="stat-item">
            <div class="stat-value"><?php echo $goals_data['total_goals'] ?? 0; ?></div>
            <div class="stat-label">Total Goals</div>
          </div>
          <div class="stat-item">
            <div class="stat-value"><?php echo $goals_data['completed_goals'] ?? 0; ?></div>
            <div class="stat-label">Completed</div>
          </div>
          <div class="stat-item">
            <div class="stat-value"><?php echo $goals_data['active_goals'] ?? 0; ?></div>
            <div class="stat-label">Active</div>
          </div>
          <div class="stat-item">
            <div class="stat-value"><?php echo round($goals_data['avg_goal_progress'] ?? 0, 1); ?>%</div>
            <div class="stat-label">Avg Progress</div>
          </div>
        </div>
      </div>

      <div class="analytics-card">
        <div class="card-title">⏱️ Time Tracking</div>
        <div class="stats-grid">
          <div class="stat-item">
            <div class="stat-value"><?php echo $time_data['total_sessions'] ?? 0; ?></div>
            <div class="stat-label">Sessions</div>
          </div>
          <div class="stat-item">
            <div class="stat-value"><?php echo round(($time_data['total_time'] ?? 0) / 60, 1); ?></div>
            <div class="stat-label">Hours</div>
          </div>
          <div class="stat-item">
            <div class="stat-value"><?php echo round(($time_data['avg_session_time'] ?? 0), 1); ?></div>
            <div class="stat-label">Avg Session (min)</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="analytics-grid">
      <div class="analytics-card">
        <div class="card-title">📊 Task Status Distribution</div>
        <div class="chart-container">
          <canvas id="taskStatusChart"></canvas>
        </div>
      </div>

      <div class="analytics-card">
        <div class="card-title">🎯 Goal Progress</div>
        <div class="chart-container">
          <canvas id="goalProgressChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="activity-section">
      <div class="card-title">📈 Recent Activity</div>
      
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <div>
          <h4>Recent Tasks</h4>
          <?php
          if (mysqli_num_rows($recent_tasks_result) > 0) {
            while ($task = mysqli_fetch_assoc($recent_tasks_result)) {
              $status_class = strtolower(str_replace(' ', '-', $task['status']));
              ?>
              <div class="activity-item">
                <div class="activity-info">
                  <div class="activity-title"><?php echo htmlspecialchars($task['title']); ?></div>
                  <div class="activity-meta">
                    Created: <?php echo date('M d, Y', strtotime($task['created_at'])); ?> | 
                    Progress: <?php echo $task['progress']; ?>%
                  </div>
                </div>
                <span class="activity-status status-<?php echo $status_class; ?>"><?php echo $task['status']; ?></span>
              </div>
              <?php
            }
          } else {
            echo '<p style="color: #666; text-align: center; padding: 20px;">No recent tasks</p>';
          }
          ?>
        </div>

        <div>
          <h4>Recent Goals</h4>
          <?php
          if (mysqli_num_rows($recent_goals_result) > 0) {
            while ($goal = mysqli_fetch_assoc($recent_goals_result)) {
              $status_class = strtolower($goal['status']);
              ?>
              <div class="activity-item">
                <div class="activity-info">
                  <div class="activity-title"><?php echo htmlspecialchars($goal['title']); ?></div>
                  <div class="activity-meta">
                    Target: <?php echo date('M d, Y', strtotime($goal['target_date'])); ?> | 
                    Progress: <?php echo $goal['progress']; ?>%
                  </div>
                </div>
                <span class="activity-status status-<?php echo $status_class; ?>"><?php echo $goal['status']; ?></span>
              </div>
              <?php
            }
          } else {
            echo '<p style="color: #666; text-align: center; padding: 20px;">No recent goals</p>';
          }
          ?>
        </div>

        <div>
          <h4>Recent Time Sessions</h4>
          <?php
          if (mysqli_num_rows($recent_time_result) > 0) {
            while ($time = mysqli_fetch_assoc($recent_time_result)) {
              ?>
              <div class="activity-item">
                <div class="activity-info">
                  <div class="activity-title"><?php echo htmlspecialchars($time['task_title']); ?></div>
                  <div class="activity-meta">
                    <?php echo date('M d, H:i', strtotime($time['start_time'])); ?> | 
                    Duration: <?php echo round($time['duration'], 1); ?> min
                  </div>
                </div>
              </div>
              <?php
            }
          } else {
            echo '<p style="color: #666; text-align: center; padding: 20px;">No recent time sessions</p>';
          }
          ?>
        </div>
      </div>
    </div>
  </div>

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

    // Task Status Chart
    const taskStatusCtx = document.getElementById('taskStatusChart').getContext('2d');
    new Chart(taskStatusCtx, {
      type: 'doughnut',
      data: {
        labels: ['Completed', 'In Progress', 'Pending'],
        datasets: [{
          data: [
            <?php echo $tasks_data['completed_tasks'] ?? 0; ?>,
            <?php echo $tasks_data['in_progress_tasks'] ?? 0; ?>,
            <?php echo $tasks_data['pending_tasks'] ?? 0; ?>
          ],
          backgroundColor: ['#28a745', '#007bff', '#6c757d'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });

    // Goal Progress Chart
    const goalProgressCtx = document.getElementById('goalProgressChart').getContext('2d');
    new Chart(goalProgressCtx, {
      type: 'bar',
      data: {
        labels: ['Completed', 'Active', 'Paused', 'Cancelled'],
        datasets: [{
          label: 'Goals',
          data: [
            <?php echo $goals_data['completed_goals'] ?? 0; ?>,
            <?php echo $goals_data['active_goals'] ?? 0; ?>,
            0, // Paused goals (would need to be calculated)
            0  // Cancelled goals (would need to be calculated)
          ],
          backgroundColor: ['#28a745', '#007bff', '#ffc107', '#dc3545']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    // Mobile functionality
    document.addEventListener('DOMContentLoaded', function () {
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
  </script>
</body>
</html>
