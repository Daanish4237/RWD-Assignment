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

// Handle time tracking operations
if ($_POST) {
    if (isset($_POST['start_timer'])) {
        $task_id = $_POST['task_id'];
        $description = mysqli_real_escape_string($dbconn, $_POST['description']);
        
        // Stop any existing timer for this user
        $stop_sql = "UPDATE time_logs SET end_time = NOW() WHERE user_id = $user_id AND end_time IS NULL";
        mysqli_query($dbconn, $stop_sql);
        
        // Start new timer
        $start_sql = "INSERT INTO time_logs (task_id, user_id, start_time, description) VALUES ('$task_id', '$user_id', NOW(), '$description')";
        if (mysqli_query($dbconn, $start_sql)) {
            $success_message = "Timer started successfully!";
        } else {
            $error_message = "Error starting timer: " . mysqli_error($dbconn);
        }
    }
    
    if (isset($_POST['stop_timer'])) {
        $log_id = $_POST['log_id'];
        
        // Get start time and calculate duration
        $get_sql = "SELECT start_time FROM time_logs WHERE id = '$log_id' AND user_id = '$user_id'";
        $result = mysqli_query($dbconn, $get_sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $start_time = strtotime($row['start_time']);
            $end_time = time();
            $duration = ($end_time - $start_time) / 60; // Duration in minutes
            
            $update_sql = "UPDATE time_logs SET end_time = NOW(), duration = '$duration' WHERE id = '$log_id'";
            if (mysqli_query($dbconn, $update_sql)) {
                $success_message = "Timer stopped! Duration: " . round($duration, 2) . " minutes";
            } else {
                $error_message = "Error stopping timer: " . mysqli_error($dbconn);
            }
        }
    }
}

// Get user's tasks for timer
$tasks_sql = "SELECT * FROM tasks WHERE user_id = $user_id AND status != 'Completed' ORDER BY title";
$tasks_result = mysqli_query($dbconn, $tasks_sql);

// Get current active timer
$active_timer_sql = "SELECT tl.*, t.title as task_title FROM time_logs tl 
                    JOIN tasks t ON tl.task_id = t.id 
                    WHERE tl.user_id = $user_id AND tl.end_time IS NULL 
                    ORDER BY tl.start_time DESC LIMIT 1";
$active_timer_result = mysqli_query($dbconn, $active_timer_sql);
$active_timer = mysqli_fetch_assoc($active_timer_result);

// Get recent time logs
$logs_sql = "SELECT tl.*, t.title as task_title FROM time_logs tl 
             JOIN tasks t ON tl.task_id = t.id 
             WHERE tl.user_id = $user_id 
             ORDER BY tl.start_time DESC LIMIT 10";
$logs_result = mysqli_query($dbconn, $logs_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Time Tracking - TaskFlow</title>
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

    /* Timer Section */
    .timer-section {
      background: #fff;
      border-radius: 10px;
      padding: 30px;
      margin-bottom: 30px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      text-align: center;
    }

    .timer-display {
      font-size: 4em;
      font-weight: bold;
      color: #007bff;
      margin: 20px 0;
      font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
    }

    .timer-controls {
      display: flex;
      gap: 15px;
      justify-content: center;
      margin-top: 20px;
    }

    .btn {
      padding: 12px 24px;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      font-size: 16px;
      transition: all 0.3s;
    }

    .btn-start {
      background: #28a745;
      color: white;
    }
    .btn-start:hover {
      background: #1e7e34;
    }

    .btn-stop {
      background: #dc3545;
      color: white;
    }
    .btn-stop:hover {
      background: #c82333;
    }

    .btn-pause {
      background: #ffc107;
      color: black;
    }
    .btn-pause:hover {
      background: #e0a800;
    }

    /* Task Selection */
    .task-selection {
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 30px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: 600;
      color: #333;
    }

    .form-group select,
    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 10px;
      border: 2px solid #ddd;
      border-radius: 6px;
      font-size: 14px;
    }

    .form-group select:focus,
    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #007bff;
    }

    /* Time Logs */
    .time-logs {
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .log-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px;
      border-bottom: 1px solid #eee;
    }

    .log-item:last-child {
      border-bottom: none;
    }

    .log-info {
      flex: 1;
    }

    .log-task {
      font-weight: bold;
      color: #333;
      margin-bottom: 5px;
    }

    .log-time {
      font-size: 12px;
      color: #666;
    }

    .log-duration {
      font-weight: bold;
      color: #007bff;
    }

    /* Active Timer Indicator */
    .active-timer {
      background: #e8f5e8;
      border: 2px solid #28a745;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
    }

    .active-timer h3 {
      color: #28a745;
      margin: 0 0 10px 0;
    }

    /* Alerts */
    .alert {
      padding: 12px 16px;
      border-radius: 6px;
      margin-bottom: 20px;
      font-weight: 500;
    }

    .alert-success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-danger {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
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

      .timer-section {
        padding-top: 60px;
        margin-bottom: 30px;
      }

      .timer-display {
        font-size: 2.5em;
        margin-bottom: 20px;
      }
      
      .timer-controls {
        flex-direction: column;
        align-items: center;
        gap: 15px;
      }
      
      .btn {
        width: 100%;
        max-width: 300px;
        padding: 15px 20px;
        font-size: 16px;
      }

      .timer-info {
        flex-direction: column;
        gap: 15px;
        margin-bottom: 25px;
      }

      .timer-info div {
        text-align: center;
        padding: 15px;
        border-radius: 10px;
      }

      .form-group {
        margin-bottom: 20px;
      }

      .form-group label {
        font-size: 14px;
        margin-bottom: 8px;
      }

      .form-group select,
      .form-group input,
      .form-group textarea {
        padding: 12px;
        font-size: 16px;
        border-radius: 8px;
      }

      .logs-section {
        margin-top: 30px;
      }

      .logs-table {
        font-size: 14px;
        overflow-x: auto;
        display: block;
        white-space: nowrap;
      }

      .logs-table th,
      .logs-table td {
        padding: 12px 8px;
        min-width: 100px;
      }

      .logs-table th:first-child,
      .logs-table td:first-child {
        min-width: 150px;
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

      .timer-section {
        padding-top: 50px;
        margin-bottom: 25px;
      }

      .timer-display {
        font-size: 2em;
        margin-bottom: 15px;
      }

      .timer-controls {
        gap: 12px;
      }

      .btn {
        padding: 12px 16px;
        font-size: 14px;
        max-width: 100%;
      }

      .timer-info {
        gap: 12px;
        margin-bottom: 20px;
      }

      .timer-info div {
        padding: 12px;
        border-radius: 8px;
      }

      .form-group {
        margin-bottom: 15px;
      }

      .form-group label {
        font-size: 13px;
        margin-bottom: 6px;
      }

      .form-group select,
      .form-group input,
      .form-group textarea {
        padding: 10px;
        font-size: 14px;
        border-radius: 6px;
      }

      .logs-section {
        margin-top: 25px;
      }

      .logs-table {
        font-size: 12px;
      }

      .logs-table th,
      .logs-table td {
        padding: 10px 6px;
        min-width: 80px;
      }

      .logs-table th:first-child,
      .logs-table td:first-child {
        min-width: 120px;
      }
    }

    /* Landscape Mobile */
    @media (max-width: 768px) and (orientation: landscape) {
      .timer-section {
        padding-top: 60px;
      }

      .timer-controls {
        flex-direction: row;
        justify-content: center;
        gap: 20px;
      }

      .btn {
        width: auto;
        min-width: 150px;
        max-width: 200px;
      }

      .timer-info {
        flex-direction: row;
        gap: 20px;
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
      <button>⏱️ TIME TRACKING</button>
      <a href="goals.php"><button>🎯 GOALS</button></a>
      <a href="collaboration.php"><button>👥 COLLABORATION</button></a>
      <a href="analytics.php"><button>📊 ANALYTICS</button></a>
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
      <h1>⏱️ Time Tracking</h1>
    </div>

    <?php if (isset($success_message)): ?>
      <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
      <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Active Timer -->
    <?php if ($active_timer): ?>
      <div class="active-timer">
        <h3>🟢 Timer Running</h3>
        <p><strong>Task:</strong> <?php echo htmlspecialchars($active_timer['task_title']); ?></p>
        <p><strong>Started:</strong> <?php echo date('M d, Y H:i:s', strtotime($active_timer['start_time'])); ?></p>
        <div class="timer-display" id="activeTimer">00:00:00</div>
        <form method="POST" style="display: inline;">
          <input type="hidden" name="log_id" value="<?php echo $active_timer['id']; ?>">
          <button type="submit" name="stop_timer" class="btn btn-stop">Stop Timer</button>
        </form>
      </div>
    <?php endif; ?>

    <!-- Timer Section -->
    <div class="timer-section">
      <h2>⏱️ Time Tracker</h2>
      <div class="timer-display" id="timerDisplay">00:00:00</div>
      
      <?php if (!$active_timer): ?>
        <form method="POST" class="task-selection">
          <div class="form-group">
            <label for="task_id">Select Task</label>
            <select id="task_id" name="task_id" required>
              <option value="">Choose a task...</option>
              <?php
              if (mysqli_num_rows($tasks_result) > 0) {
                while ($task = mysqli_fetch_assoc($tasks_result)) {
                  echo "<option value='{$task['id']}'>{$task['title']}</option>";
                }
              }
              ?>
            </select>
          </div>
          
          <div class="form-group">
            <label for="description">Description (optional)</label>
            <textarea id="description" name="description" placeholder="What are you working on?"></textarea>
          </div>
          
          <button type="submit" name="start_timer" class="btn btn-start">Start Timer</button>
        </form>
      <?php endif; ?>
    </div>

    <!-- Recent Time Logs -->
    <div class="time-logs">
      <h3>📊 Recent Time Logs</h3>
      <?php
      if (mysqli_num_rows($logs_result) > 0) {
        while ($log = mysqli_fetch_assoc($logs_result)) {
          $start_time = date('M d, H:i', strtotime($log['start_time']));
          $end_time = $log['end_time'] ? date('M d, H:i', strtotime($log['end_time'])) : 'Running...';
          $duration = $log['duration'] ? round($log['duration'], 2) . ' min' : 'Running...';
          ?>
          <div class="log-item">
            <div class="log-info">
              <div class="log-task"><?php echo htmlspecialchars($log['task_title']); ?></div>
              <div class="log-time">
                <?php echo $start_time; ?> - <?php echo $end_time; ?>
                <?php if ($log['description']): ?>
                  <br><em><?php echo htmlspecialchars($log['description']); ?></em>
                <?php endif; ?>
              </div>
            </div>
            <div class="log-duration"><?php echo $duration; ?></div>
          </div>
          <?php
        }
      } else {
        echo '<p style="text-align: center; color: #666; padding: 20px;">No time logs yet. Start tracking your time!</p>';
      }
      ?>
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

    // Timer functionality
    let timerInterval;
    let startTime;

    function updateTimer() {
      if (startTime) {
        const now = new Date().getTime();
        const elapsed = now - startTime;
        
        const hours = Math.floor(elapsed / (1000 * 60 * 60));
        const minutes = Math.floor((elapsed % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((elapsed % (1000 * 60)) / 1000);
        
        const timeString = 
          String(hours).padStart(2, '0') + ':' +
          String(minutes).padStart(2, '0') + ':' +
          String(seconds).padStart(2, '0');
        
        document.getElementById('timerDisplay').textContent = timeString;
        if (document.getElementById('activeTimer')) {
          document.getElementById('activeTimer').textContent = timeString;
        }
      }
    }

    // Start timer if there's an active timer
    <?php if ($active_timer): ?>
      startTime = new Date('<?php echo $active_timer['start_time']; ?>').getTime();
      timerInterval = setInterval(updateTimer, 1000);
      updateTimer();
    <?php endif; ?>

    // Auto-hide alerts
    setTimeout(() => {
      const alerts = document.querySelectorAll('.alert');
      alerts.forEach(alert => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
      });
    }, 5000);

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
