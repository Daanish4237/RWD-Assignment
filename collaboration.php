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
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Collaboration</title>
  <style>
    body {
      margin: 0;
      font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', 'Helvetica Neue', Helvetica, Arial, sans-serif;
      display: flex;
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      min-height: 100vh;
      position: relative;
    }

    body::before {
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

    .menu button.active {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.8) 0%, rgba(16, 185, 129, 0.6) 100%);
      border-color: rgba(59, 130, 246, 0.5);
      box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
    }

    /* Enhanced Main Content */
    .main-content {
      flex: 1;
      padding: 30px;
      display: flex;
      flex-direction: column;
      gap: 25px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      min-height: 100vh;
      position: relative;
      margin-left: 280px;
    }

    .main-content::before {
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

    .sidebar.collapsed + .main-content {
      margin-left: 0;
    }

    .section {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
      padding: 25px;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }

    .section::before {
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

    .section:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    .section:hover::before {
      opacity: 1;
    }

    /* Enhanced Headings */
    h2 {
      margin-top: 0;
      margin-bottom: 20px;
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-weight: 800;
      font-size: 24px;
      text-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
      position: relative;
      z-index: 1;
    }

    /* Project Members */
    #membersList {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }
    .member {
      background: #007bff;
      color: white;
      padding: 8px 12px;
      border-radius: 6px;
    }

    /* Chat */
    #chatBox {
      border: 2px solid rgba(59, 130, 246, 0.2);
      border-radius: 16px;
      height: 300px;
      overflow-y: auto;
      padding: 20px;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
      backdrop-filter: blur(10px);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }
    
    #chatBox:hover {
      border-color: rgba(59, 130, 246, 0.4);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }
    
    #chatBox::-webkit-scrollbar {
      width: 8px;
    }
    
    #chatBox::-webkit-scrollbar-track {
      background: rgba(59, 130, 246, 0.1);
      border-radius: 4px;
    }
    
    #chatBox::-webkit-scrollbar-thumb {
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      border-radius: 4px;
    }
    
    #chatBox::-webkit-scrollbar-thumb:hover {
      background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    }
    #chatInput {
      width: 80%;
      padding: 15px 20px;
      border: 2px solid rgba(59, 130, 246, 0.2);
      border-radius: 12px;
      font-size: 16px;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
      backdrop-filter: blur(10px);
      transition: all 0.3s ease;
      outline: none;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    #chatInput:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
      transform: translateY(-2px);
    }
    
    #sendBtn {
      padding: 15px 25px;
      border: none;
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      color: white;
      border-radius: 12px;
      cursor: pointer;
      font-weight: 600;
      font-size: 16px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
      position: relative;
      overflow: hidden;
    }
    
    #sendBtn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }
    
    #sendBtn:hover {
      background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
    }
    
    #sendBtn:hover::before {
      left: 100%;
    }
    
    #sendBtn:active {
      transform: translateY(0) scale(0.98);
    }

    /* Shared Workspace */
    #workspace {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .task-item {
      display: flex;
      justify-content: space-between;
      background: #e9ecef;
      padding: 10px;
      border-radius: 6px;
      align-items: center;
    }

    /* Enhanced Mobile Responsive Styles */
    @media (max-width: 1024px) {
      .sidebar {
        width: 200px;
      }
      
      .main-content {
        padding: 15px;
      }
    }

    @media (max-width: 768px) {
      body {
        flex-direction: column;
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
        background: linear-gradient(180deg, #1a1a2e 0%, #16213e 50%, #0f1419 100%);
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        padding: 20px 0;
      }

      .sidebar.open {
        transform: translateX(0);
      }

      .sidebar.collapsed {
        transform: translateX(-100%);
        width: 280px;
        opacity: 1;
      }

      .sidebar h2 {
        display: block;
        text-align: center;
        color: #3b82f6;
        margin-bottom: 25px;
        font-size: 20px;
        font-weight: 700;
      }

      .menu button {
        display: block;
        color: white;
        padding: 14px 20px;
        border-radius: 10px;
        margin: 8px 20px;
        font-size: 15px;
        font-weight: 600;
        transition: all 0.3s ease;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
        border: 1px solid rgba(255, 255, 255, 0.1);
      }

      .menu button:hover,
      .menu button.active {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.8) 0%, rgba(16, 185, 129, 0.6) 100%);
        transform: translateX(8px);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
      }

      .main-content {
        width: 100%;
        padding: 20px 15px;
        margin-left: 0;
        padding-top: 60px;
      }

      .section {
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
      }

      .section h2 {
        font-size: 20px;
        margin-bottom: 15px;
      }

      #membersList {
        flex-direction: column;
        gap: 12px;
      }

      .member {
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 14px;
      }

      #chatBox {
        height: 250px;
        padding: 15px;
        border-radius: 8px;
      }

      #chatInput {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        font-size: 16px;
        margin-bottom: 10px;
      }

      #sendBtn {
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 14px;
        width: 100%;
      }

      .chat-controls {
        display: flex;
        flex-direction: column;
        gap: 10px;
      }

      .task-item {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 12px;
      }

      .task-item h4 {
        font-size: 16px;
        margin-bottom: 8px;
      }

      .task-item p {
        font-size: 14px;
        margin-bottom: 10px;
      }

      .task-actions {
        flex-direction: column;
        gap: 8px;
      }

      .btn {
        padding: 10px 16px;
        font-size: 13px;
        border-radius: 6px;
      }
    }

    /* Enhanced Mobile Responsive Styles */
    @media (max-width: 1024px) {
      .sidebar {
        width: 200px;
      }
      
      .main-content {
        padding: 15px;
      }
    }

    @media (max-width: 768px) {
      body {
        flex-direction: column;
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
        background: linear-gradient(180deg, #1a1a2e 0%, #16213e 50%, #0f1419 100%);
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        padding: 20px 0;
      }

      .sidebar.open {
        transform: translateX(0);
      }

      .sidebar.collapsed {
        transform: translateX(-100%);
        width: 280px;
        opacity: 1;
      }

      .sidebar h2 {
        display: block;
        text-align: center;
        color: #3b82f6;
        margin-bottom: 25px;
        font-size: 20px;
        font-weight: 700;
      }

      .menu button {
        display: block;
        color: white;
        padding: 14px 20px;
        border-radius: 10px;
        margin: 8px 20px;
        font-size: 15px;
        font-weight: 600;
        transition: all 0.3s ease;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
        border: 1px solid rgba(255, 255, 255, 0.1);
      }

      .menu button:hover,
      .menu button.active {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.8) 0%, rgba(16, 185, 129, 0.6) 100%);
        transform: translateX(8px);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
      }

      .main-content {
        width: 100%;
        padding: 20px 15px;
        margin-left: 0;
        padding-top: 60px;
      }

      .section {
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
      }

      .section h2 {
        font-size: 20px;
        margin-bottom: 15px;
      }

      #membersList {
        flex-direction: column;
        gap: 12px;
      }

      .member {
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 14px;
      }

      #chatBox {
        height: 250px;
        padding: 15px;
        border-radius: 8px;
      }

      #chatInput {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        font-size: 16px;
        margin-bottom: 10px;
      }

      #sendBtn {
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 14px;
        width: 100%;
      }

      .chat-controls {
        display: flex;
        flex-direction: column;
        gap: 10px;
      }

      .task-item {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 12px;
      }

      .task-item h4 {
        font-size: 16px;
        margin-bottom: 8px;
      }

      .task-item p {
        font-size: 14px;
        margin-bottom: 10px;
      }

      .task-actions {
        flex-direction: column;
        gap: 8px;
      }

      .btn {
        padding: 10px 16px;
        font-size: 13px;
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

      .sidebar h2 {
        font-size: 18px;
        margin-bottom: 20px;
      }

      .sidebar a {
        padding: 12px 15px;
        margin: 6px 15px;
        font-size: 14px;
      }

      .main-content {
        padding: 15px 10px;
        padding-top: 50px;
      }

      .section {
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 15px;
      }

      .section h2 {
        font-size: 18px;
        margin-bottom: 12px;
      }

      #membersList {
        gap: 10px;
      }

      .member {
        padding: 10px 14px;
        border-radius: 6px;
        font-size: 13px;
      }

      #chatBox {
        height: 200px;
        padding: 12px;
        border-radius: 6px;
      }

      #chatInput {
        padding: 10px;
        font-size: 14px;
        margin-bottom: 8px;
      }

      #sendBtn {
        padding: 10px 16px;
        font-size: 13px;
      }

      .task-item {
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 10px;
      }

      .task-item h4 {
        font-size: 15px;
        margin-bottom: 6px;
      }

      .task-item p {
        font-size: 13px;
        margin-bottom: 8px;
      }

      .task-actions {
        gap: 6px;
      }

      .btn {
        padding: 8px 12px;
        font-size: 12px;
        border-radius: 4px;
      }
    }

    /* Landscape Mobile */
    @media (max-width: 768px) and (orientation: landscape) {
      .main-content {
        padding-top: 60px;
      }

      #chatBox {
        height: 180px;
      }

      .section {
        padding: 15px;
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
      backdrop-filter: blur(5px);
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
  
  <!-- Toggle Button -->
  <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
  
  <div class="sidebar">
    <img src="Logo RWD.jpeg" alt="TaskFlow Logo">
    <div class="menu">
      <button onclick="window.location.href='Dashboard.php'">🏠 Dashboard</button>
      <button onclick="window.location.href='Task.php'">📋 Tasks</button>
      <button onclick="window.location.href='time-tracking.php'">⏱️ Time Tracking</button>
      <button onclick="window.location.href='goals.php'">🎯 Goals</button>
      <button onclick="window.location.href='collaboration.php'" class="active">👥 Collaboration</button>
      <button onclick="window.location.href='analytics.php'">📊 Analytics</button>
      <?php if ($role === 'Admin'): ?>
        <button onclick="window.location.href='admin-users.php'">👤 User Management</button>
        <button onclick="window.location.href='admin-system.php'">⚙️ System Admin</button>
      <?php endif; ?>
      <button onclick="window.location.href='profile.php'">👤 Profile</button>
    </div>
  </div>

  <div class="main-content">
    <!-- Project Members -->
    <div class="section">
      <h2>Project Members</h2>
      <div id="membersList"></div>
    </div>

    <!-- Chat Box -->
    <div class="section">
      <h2>Team Chat</h2>
      <div id="chatBox"></div>
      <div style="margin-top:10px;">
        <input id="chatInput" type="text" placeholder="Type your message...">
        <button id="sendBtn">Send</button>
      </div>
    </div>

    <!-- Shared Workspace -->
    <div class="section">
      <h2>Shared Workspace</h2>
      <ul id="sharedWorkspaceList"></ul>
      <div id="workspace"></div>
    </div>
  </div>

  <script>
   // Load tasks from localStorage (existing workspace list)
  const sharedWorkspaceList = document.getElementById('sharedWorkspaceList');
  let tasks = JSON.parse(localStorage.getItem('tasks')) || [];

  function renderTasks() {
    sharedWorkspaceList.innerHTML = '';
    tasks.forEach((task, index) => {
      const li = document.createElement('li');
      li.innerHTML = `
        <span>${task.title || 'Untitled Task'}</span>
        <button onclick="markCompleted(${index})" title="Mark as completed ✅">
          ✅
        </button>
      `;
      sharedWorkspaceList.appendChild(li);
    });
  }

  // Mark task as completed
  function markCompleted(index) {
    const completedTask = tasks[index];
    alert(`Task "${completedTask.title}" marked as completed ✅`);
    tasks.splice(index, 1);
    localStorage.setItem('tasks', JSON.stringify(tasks));
    renderTasks();
  }

  // Initialize tasks on load
  renderTasks();

 
  // Enhanced Chat functionality
  
  const chatBox = document.getElementById('chatBox');
  const chatInput = document.getElementById('chatInput');
  const sendBtn = document.getElementById('sendBtn');
  const CHAT_KEY = 'taskflow_chat_messages';

  // Initialize chat on page load
  document.addEventListener('DOMContentLoaded', function() {
    console.log('Chat initialized');
    renderMessages();
    
    // Add focus to input for better UX
    chatInput.focus();
  });

  function loadMessages() {
    try { 
      const messages = JSON.parse(localStorage.getItem(CHAT_KEY) || '[]');
      console.log('Loaded messages:', messages.length);
      return messages;
    }
    catch (e) { 
      console.error('chat parse error', e); 
      return []; 
    }
  }
  
  function saveMessages(msgs) {
    try { 
      localStorage.setItem(CHAT_KEY, JSON.stringify(msgs));
      console.log('Saved messages:', msgs.length);
    }
    catch (e) { 
      console.error('chat save error', e); 
    }
  }
  function renderMessages() {
    const msgs = loadMessages();
    chatBox.innerHTML = '';
    msgs.forEach(m => {
      const messageDiv = document.createElement('div');
      messageDiv.style.marginBottom = '12px';
      messageDiv.style.padding = '10px 15px';
      messageDiv.style.borderRadius = '12px';
      messageDiv.style.background = 'linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%)';
      messageDiv.style.border = '1px solid rgba(59, 130, 246, 0.2)';
      messageDiv.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.1)';
      
      const time = new Date(m.time).toLocaleTimeString();
      messageDiv.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
          <strong style="color: #3b82f6; font-size: 14px;">${escapeHtml(m.user || 'Me')}</strong>
          <span style="color: #64748b; font-size: 12px;">${time}</span>
        </div>
        <div style="color: #374151; line-height: 1.4;">${escapeHtml(m.text)}</div>
      `;
      chatBox.appendChild(messageDiv);
    });
    // scroll to bottom
    chatBox.scrollTop = chatBox.scrollHeight;
  }
  function escapeHtml(s){ return (s||'').toString().replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

  function sendMessage() {
    const text = (chatInput.value || '').trim();
    if (!text) {
      console.log('Empty message, not sending');
      return;
    }
    
    const user = '<?php echo htmlspecialchars($username); ?>' || 'Anonymous';
    console.log('Sending message from:', user, 'Text:', text);
    
    const msgs = loadMessages();
    const newMessage = { 
      user, 
      text, 
      time: Date.now(),
      id: Date.now() + Math.random() // Unique ID for each message
    };
    
    msgs.push(newMessage);
    saveMessages(msgs);
    renderMessages();
    
    // Clear input and refocus
    chatInput.value = '';
    chatInput.focus();
    
    // Add visual feedback
    sendBtn.style.background = '#10b981';
    sendBtn.textContent = '✓ Sent';
    setTimeout(() => {
      sendBtn.style.background = '';
      sendBtn.textContent = 'Send';
    }, 1000);
    
    // notify other tabs/pages
    window.dispatchEvent(new Event('chatUpdated'));
    console.log('Message sent successfully');
  }

  // wire button + Enter key
  sendBtn.addEventListener('click', sendMessage);
  chatInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') { e.preventDefault(); sendMessage(); }
  });

  // react to storage events (other tabs)
  window.addEventListener('storage', (e) => {
    if (e.key === CHAT_KEY) renderMessages();
  });
  // custom event for same-tab notifications
  window.addEventListener('chatUpdated', renderMessages);

  // render on load
  document.addEventListener('DOMContentLoaded', () => {
    renderMessages();
  });

  // Mobile functionality
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
