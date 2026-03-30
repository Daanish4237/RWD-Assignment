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

// Handle task operations
if ($_POST) {
    if (isset($_POST['add_task'])) {
        $title = mysqli_real_escape_string($dbconn, $_POST['title']);
        $description = mysqli_real_escape_string($dbconn, $_POST['description']);
        $priority = $_POST['priority'];
        $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
        
        $sql = "INSERT INTO tasks (user_id, title, description, priority, due_date) VALUES ('$user_id', '$title', '$description', '$priority', " . ($due_date ? "'$due_date'" : "NULL") . ")";
        if (mysqli_query($dbconn, $sql)) {
            $success_message = "Task added successfully!";
        } else {
            $error_message = "Error adding task: " . mysqli_error($dbconn);
        }
    }
    
    if (isset($_POST['update_task'])) {
        $task_id = $_POST['task_id'];
        $title = mysqli_real_escape_string($dbconn, $_POST['title']);
        $description = mysqli_real_escape_string($dbconn, $_POST['description']);
        $priority = $_POST['priority'];
        $status = $_POST['status'];
        $progress = $_POST['progress'];
        $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
        
        $sql = "UPDATE tasks SET title='$title', description='$description', priority='$priority', status='$status', progress='$progress', due_date=" . ($due_date ? "'$due_date'" : "NULL") . " WHERE id='$task_id' AND user_id='$user_id'";
        if (mysqli_query($dbconn, $sql)) {
            $success_message = "Task updated successfully!";
        } else {
            $error_message = "Error updating task: " . mysqli_error($dbconn);
        }
    }
    
    if (isset($_POST['delete_task'])) {
        $task_id = $_POST['task_id'];
        $sql = "DELETE FROM tasks WHERE id='$task_id' AND user_id='$user_id'";
        if (mysqli_query($dbconn, $sql)) {
            $success_message = "Task deleted successfully!";
        } else {
            $error_message = "Error deleting task: " . mysqli_error($dbconn);
        }
    }
}

// Get user's tasks
$sql = "SELECT * FROM tasks WHERE user_id = $user_id ORDER BY created_at DESC";
$result = mysqli_query($dbconn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tasks - TaskFlow</title>
  <style>
    body {
      margin: 0;
      font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', 'Helvetica Neue', Helvetica, Arial, sans-serif;
      background: #f8f9fa;
      display: flex;
      height: 100vh;
    }

    /* Sidebar Toggle Button */
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

    /* Enhanced Task Header */
    .task-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 40px;
      padding: 25px 30px;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      position: relative;
      z-index: 1;
    }

    .task-header h1 {
      font-size: 2.2em;
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-weight: 800;
      margin: 0;
      text-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
    }

    .btn {
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      background: #007bff;
      color: #fff;
      text-decoration: none;
      display: inline-block;
    }
    .btn:hover {
      background: #0056b3;
    }
    .btn-success {
      background: #28a745;
    }
    .btn-success:hover {
      background: #1e7e34;
    }
    .btn-danger {
      background: #dc3545;
    }
    .btn-danger:hover {
      background: #c82333;
    }

    /* Enhanced Task Cards */
    .tasks-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
      gap: 25px;
      position: relative;
      z-index: 1;
    }

    .task-card {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.85) 100%);
      padding: 0;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(0, 0, 0, 0.06);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
      cursor: pointer;
    }

    .task-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.03) 0%, rgba(16, 185, 129, 0.03) 100%);
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .task-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12), 0 4px 12px rgba(0, 0, 0, 0.08);
      border-color: rgba(59, 130, 246, 0.2);
    }

    .task-card:hover::before {
      opacity: 1;
    }

    /* Task Card Header */
    .task-card-header {
      padding: 20px 20px 16px 20px;
      border-bottom: 1px solid rgba(0, 0, 0, 0.06);
      position: relative;
    }

    .task-card-title {
      font-size: 16px;
      font-weight: 600;
      color: #1e293b;
      margin: 0 0 8px 0;
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .task-card-description {
      font-size: 14px;
      color: #64748b;
      line-height: 1.5;
      margin: 0;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    /* Task Card Body */
    .task-card-body {
      padding: 16px 20px;
    }

    /* Task Meta with Icons */
    .task-meta {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 16px;
      flex-wrap: wrap;
    }

    .meta-item {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      font-weight: 500;
    }

    .meta-icon {
      width: 16px;
      height: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 4px;
    }

    /* Priority Badge with Icon */
    .priority-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 10px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .priority-Low {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: white;
    }

    .priority-Medium {
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      color: white;
    }

    .priority-High {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      color: white;
    }

    .priority-Critical {
      background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
      color: white;
    }

    /* Status Badge with Icon */
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 10px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .status-Pending {
      background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
      color: white;
    }

    .status-In-Progress {
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      color: white;
    }

    .status-Completed {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: white;
    }

    .status-Cancelled {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      color: white;
    }

    /* Progress Section */
    .progress-section {
      margin-bottom: 16px;
    }

    .progress-label {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 8px;
      font-size: 12px;
      font-weight: 500;
      color: #64748b;
    }

    .progress-bar {
      height: 6px;
      background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
      border-radius: 3px;
      overflow: hidden;
      box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .progress-fill {
      height: 100%;
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      border-radius: 3px;
      transition: width 0.3s ease;
      position: relative;
    }

    .progress-fill::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
      animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
      0% { transform: translateX(-100%); }
      100% { transform: translateX(100%); }
    }

    /* Due Date */
    .due-date {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      color: #64748b;
      margin-bottom: 16px;
    }

    /* Task Card Footer */
    .task-card-footer {
      padding: 16px 20px;
      border-top: 1px solid rgba(0, 0, 0, 0.06);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    /* Action Buttons with Icons */
    .task-actions {
      display: flex;
      gap: 8px;
    }

    .btn-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.2s ease;
      font-size: 14px;
    }

    .btn-edit {
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      color: white;
      box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
    }

    .btn-edit:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
    }

    .btn-delete {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      color: white;
      box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
    }

    .btn-delete:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(239, 68, 68, 0.4);
    }

    .task-title {
      font-size: 20px;
      font-weight: 700;
      margin-bottom: 12px;
      color: #1e293b;
      position: relative;
      z-index: 1;
    }

    .task-description {
      color: #64748b;
      margin-bottom: 20px;
      line-height: 1.6;
      position: relative;
      z-index: 1;
    }

    .task-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      position: relative;
      z-index: 1;
    }

    /* Enhanced Priority Badges */
    .priority-badge {
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 700;
      color: white;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      transition: all 0.3s ease;
    }

    .priority-Low { 
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .priority-Medium { 
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      color: white;
      box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }

    .priority-High { 
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .priority-Critical { 
      background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
      box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
    }

    /* Enhanced Status Badges */
    .status-badge {
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 700;
      color: white;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      transition: all 0.3s ease;
    }

    .status-Pending { 
      background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
      box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
    }

    .status-In\ Progress { 
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .status-Completed { 
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .status-Cancelled { 
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    /* Enhanced Progress Bar */
    .progress-bar {
      width: 100%;
      height: 12px;
      background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
      border-radius: 20px;
      overflow: hidden;
      margin: 15px 0;
      box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
      position: relative;
    }

    .progress-fill {
      height: 100%;
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
      border-radius: 20px;
      position: relative;
      overflow: hidden;
    }

    .progress-fill::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
      animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
      0% { left: -100%; }
      100% { left: 100%; }
    }

    /* Enhanced Task Actions */
    .task-actions {
      display: flex;
      gap: 12px;
      position: relative;
      z-index: 1;
    }

    .btn-sm {
      padding: 10px 16px;
      font-size: 13px;
      font-weight: 700;
      border-radius: 10px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      position: relative;
      overflow: hidden;
    }

    .btn-sm::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }

    .btn-sm:hover::before {
      left: 100%;
    }

    .btn-sm:hover {
      transform: translateY(-2px);
    }

    /* Modal */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 1000;
    }

    .modal-content {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      width: 90%;
      max-width: 500px;
      max-height: 80vh;
      overflow-y: auto;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .close {
      font-size: 24px;
      cursor: pointer;
      color: #999;
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

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 10px;
      border: 2px solid #ddd;
      border-radius: 6px;
      font-size: 14px;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #007bff;
    }

    .form-group textarea {
      height: 80px;
      resize: vertical;
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

    /* Mobile Responsive */
    @media (max-width: 768px) {
      .tasks-grid {
        grid-template-columns: 1fr;
      }
      
      .task-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
      }
      
      .modal-content {
        width: 95%;
        padding: 20px;
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

      .task-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 25px;
        padding-top: 60px;
      }

      .task-header h1 {
        font-size: 24px;
        margin: 0;
      }

      .btn {
        padding: 12px 20px;
        font-size: 14px;
        width: 100%;
        max-width: 200px;
      }

      .tasks-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .task-card {
        padding: 20px;
        border-radius: 12px;
      }

      .task-title {
        font-size: 18px;
        margin-bottom: 10px;
      }

      .task-description {
        font-size: 14px;
        margin-bottom: 15px;
        line-height: 1.4;
      }

      .task-meta {
        flex-direction: column;
        gap: 8px;
        margin-bottom: 15px;
      }

      .priority-badge, .status-badge {
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 6px;
      }

      .progress-bar {
        height: 8px;
        margin-bottom: 8px;
      }

      .task-actions {
        flex-direction: column;
        gap: 10px;
      }

      .btn-sm {
        padding: 10px 16px;
        font-size: 13px;
        width: 100%;
      }

      /* Modal adjustments for mobile */
      .modal-content {
        width: 95%;
        max-width: 500px;
        margin: 5% auto;
        max-height: 90vh;
        overflow-y: auto;
      }

      .modal-header {
        padding: 20px;
      }

      .modal-header h3 {
        font-size: 20px;
      }

      .form-group {
        margin-bottom: 20px;
      }

      .form-group label {
        font-size: 14px;
        margin-bottom: 8px;
      }

      .form-group input,
      .form-group select,
      .form-group textarea {
        padding: 12px;
        font-size: 16px;
        border-radius: 8px;
      }

      .form-group textarea {
        min-height: 80px;
      }

      .modal-footer {
        padding: 20px;
        flex-direction: column;
        gap: 10px;
      }

      .modal-footer .btn {
        width: 100%;
        padding: 12px;
        font-size: 14px;
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

      .task-header {
        padding-top: 50px;
        margin-bottom: 20px;
      }

      .task-header h1 {
        font-size: 20px;
      }

      .btn {
        padding: 10px 16px;
        font-size: 13px;
      }

      .tasks-grid {
        gap: 15px;
      }

      .task-card {
        padding: 15px;
        border-radius: 10px;
      }

      .task-title {
        font-size: 16px;
        margin-bottom: 8px;
      }

      .task-description {
        font-size: 13px;
        margin-bottom: 12px;
      }

      .task-meta {
        gap: 6px;
        margin-bottom: 12px;
      }

      .priority-badge, .status-badge {
        font-size: 10px;
        padding: 3px 6px;
        border-radius: 4px;
      }

      .progress-bar {
        height: 6px;
        margin-bottom: 6px;
      }

      .task-actions {
        gap: 8px;
      }

      .btn-sm {
        padding: 8px 12px;
        font-size: 12px;
      }

      /* Modal adjustments for small mobile */
      .modal-content {
        width: 98%;
        margin: 2% auto;
        max-height: 95vh;
      }

      .modal-header {
        padding: 15px;
      }

      .modal-header h3 {
        font-size: 18px;
      }

      .form-group {
        margin-bottom: 15px;
      }

      .form-group label {
        font-size: 13px;
        margin-bottom: 6px;
      }

      .form-group input,
      .form-group select,
      .form-group textarea {
        padding: 10px;
        font-size: 14px;
        border-radius: 6px;
      }

      .form-group textarea {
        min-height: 60px;
      }

      .modal-footer {
        padding: 15px;
        gap: 8px;
      }

      .modal-footer .btn {
        padding: 10px;
        font-size: 13px;
      }
    }

    /* Landscape Mobile */
    @media (max-width: 768px) and (orientation: landscape) {
      .task-header {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        padding-top: 60px;
      }

      .tasks-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
      }

      .task-card {
        padding: 15px;
      }

      .task-title {
        font-size: 16px;
      }

      .task-description {
        font-size: 13px;
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

      /* Mobile Task Card Enhancements */
      .task-card {
        margin-bottom: 16px;
        border-radius: 10px;
      }

      .task-card-header {
        padding: 16px 16px 12px 16px;
      }

      .task-card-title {
        font-size: 15px;
        line-height: 1.3;
      }

      .task-card-description {
        font-size: 13px;
        line-height: 1.4;
      }

      .task-card-body {
        padding: 12px 16px;
      }

      .task-meta {
        gap: 8px;
        margin-bottom: 12px;
      }

      .priority-badge,
      .status-badge {
        padding: 4px 8px;
        font-size: 10px;
        border-radius: 4px;
      }

      .progress-section {
        margin-bottom: 12px;
      }

      .progress-label {
        font-size: 11px;
      }

      .progress-bar {
        height: 4px;
      }

      .due-date {
        font-size: 11px;
        margin-bottom: 12px;
      }

      .task-card-footer {
        padding: 12px 16px;
        flex-direction: column;
        gap: 8px;
        align-items: flex-start;
      }

      .task-actions {
        align-self: flex-end;
        gap: 6px;
      }

      .btn-icon {
        width: 28px;
        height: 28px;
        font-size: 12px;
        border-radius: 4px;
      }
    }

    @media (max-width: 480px) {
      .task-card {
        border-radius: 8px;
      }

      .task-card-header {
        padding: 14px 14px 10px 14px;
      }

      .task-card-title {
        font-size: 14px;
      }

      .task-card-description {
        font-size: 12px;
      }

      .task-card-body {
        padding: 10px 14px;
      }

      .task-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
        margin-bottom: 10px;
      }

      .priority-badge,
      .status-badge {
        padding: 3px 6px;
        font-size: 9px;
      }

      .progress-section {
        margin-bottom: 10px;
      }

      .progress-label {
        font-size: 10px;
      }

      .due-date {
        font-size: 10px;
        margin-bottom: 10px;
      }

      .task-card-footer {
        padding: 10px 14px;
        gap: 6px;
      }

      .btn-icon {
        width: 24px;
        height: 24px;
        font-size: 10px;
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
      <button>📋 TASKS</button>
      <a href="time-tracking.php"><button>⏱️ TIME TRACKING</button></a>
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
    <div class="task-header">
      <h1>📋 My Tasks</h1>
      <button class="btn btn-success" onclick="openModal('add')">+ Add New Task</button>
    </div>

    <?php if (isset($success_message)): ?>
      <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
      <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Tasks Grid -->
    <div class="tasks-grid">
      <?php
      if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
          $progress = $row['progress'];
          $priority = $row['priority'];
          $status = $row['status'];
          $due_date = $row['due_date'] ? date('M d, Y', strtotime($row['due_date'])) : 'No due date';
          ?>
          <div class="task-card">
            <!-- Task Card Header -->
            <div class="task-card-header">
              <div class="task-card-title"><?php echo htmlspecialchars($row['title']); ?></div>
              <div class="task-card-description"><?php echo htmlspecialchars($row['description']); ?></div>
            </div>
            
            <!-- Task Card Body -->
            <div class="task-card-body">
              <!-- Task Meta -->
              <div class="task-meta">
                <span class="priority-badge priority-<?php echo $priority; ?>">
                  <span class="meta-icon"><?php echo $priority === 'Low' ? '🟢' : ($priority === 'Medium' ? '🟡' : ($priority === 'High' ? '🟠' : '🔴')); ?></span>
                  <?php echo $priority; ?>
                </span>
                <span class="status-badge status-<?php echo $status; ?>">
                  <span class="meta-icon"><?php echo $status === 'Pending' ? '⏳' : ($status === 'In Progress' ? '🔄' : ($status === 'Completed' ? '✅' : '❌')); ?></span>
                  <?php echo $status; ?>
                </span>
              </div>
              
              <!-- Progress Section -->
              <div class="progress-section">
                <div class="progress-label">
                  <span>Progress</span>
                  <span><?php echo $progress; ?>%</span>
                </div>
                <div class="progress-bar">
                  <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                </div>
              </div>
              
              <!-- Due Date -->
              <div class="due-date">
                <span class="meta-icon">📅</span>
                <span>Due: <?php echo $due_date; ?></span>
              </div>
            </div>
            
            <!-- Task Card Footer -->
            <div class="task-card-footer">
              <div style="font-size: 11px; color: #94a3b8; font-weight: 500;">
                Created <?php echo date('M j', strtotime($row['created_at'])); ?>
              </div>
              <div class="task-actions">
                <button class="btn-icon btn-edit" onclick="editTask(<?php echo $row['id']; ?>)" title="Edit Task">
                  ✏️
                </button>
                <button class="btn-icon btn-delete" onclick="deleteTask(<?php echo $row['id']; ?>)" title="Delete Task">
                  🗑️
                </button>
              </div>
            </div>
          </div>
          <?php
        }
      } else {
        echo '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
                <h3>No tasks yet!</h3>
                <p>Create your first task to get started.</p>
              </div>';
      }
      ?>
    </div>
  </div>

  <!-- Add/Edit Task Modal -->
  <div id="taskModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 id="modalTitle">Add New Task</h3>
        <span class="close" onclick="closeModal()">&times;</span>
      </div>
      
      <form id="taskForm" method="POST" action="">
        <input type="hidden" id="taskId" name="task_id">
        <input type="hidden" id="formAction" name="add_task">
        
        <div class="form-group">
          <label for="title">Task Title</label>
          <input type="text" id="title" name="title" required>
        </div>
        
        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" name="description"></textarea>
        </div>
        
        <div class="form-group">
          <label for="priority">Priority</label>
          <select id="priority" name="priority" required>
            <option value="Low">Low</option>
            <option value="Medium" selected>Medium</option>
            <option value="High">High</option>
            <option value="Critical">Critical</option>
          </select>
        </div>
        
        <div class="form-group" id="statusGroup" style="display: none;">
          <label for="status">Status</label>
          <select id="status" name="status">
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Completed">Completed</option>
            <option value="Cancelled">Cancelled</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="due_date">Due Date</label>
          <input type="date" id="due_date" name="due_date">
        </div>
        
        <div class="form-group" id="progressGroup" style="display: none;">
          <label for="progress">Progress (%)</label>
          <input type="number" id="progress" name="progress" min="0" max="100" value="0">
        </div>
        

        
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
          <button type="button" class="btn" onclick="closeModal()">Cancel</button>
          <button type="submit" class="btn btn-success">Save Task</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
      <div class="modal-header">
        <h3>Confirm Delete</h3>
        <span class="close" onclick="closeDeleteModal()">&times;</span>
      </div>
      <p>Are you sure you want to delete this task? This action cannot be undone.</p>
      <form id="deleteForm" method="POST" action="">
        <input type="hidden" id="deleteTaskId" name="task_id">
        <input type="hidden" name="delete_task" value="1">
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
          <button type="button" class="btn" onclick="closeDeleteModal()">Cancel</button>
          <button type="submit" class="btn btn-danger">Delete</button>
        </div>
      </form>
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

    function openModal(action) {
      const modal = document.getElementById('taskModal');
      const form = document.getElementById('taskForm');
      const title = document.getElementById('modalTitle');
      const actionInput = document.getElementById('formAction');
      const progressGroup = document.getElementById('progressGroup');
      const statusGroup = document.getElementById('statusGroup');
      
      if (action === 'add') {
        title.textContent = 'Add New Task';
        actionInput.name = 'add_task';
        progressGroup.style.display = 'none';
        statusGroup.style.display = 'none';
        form.reset();
        document.getElementById('taskId').value = '';
      }
      
      modal.style.display = 'block';
    }

    function closeModal() {
      document.getElementById('taskModal').style.display = 'none';
    }

    function editTask(taskId) {
      // Fetch task data via AJAX
      fetch('get_task.php?id=' + taskId)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Populate form with task data
            document.getElementById('taskId').value = data.task.id;
            document.getElementById('title').value = data.task.title;
            document.getElementById('description').value = data.task.description;
            document.getElementById('priority').value = data.task.priority;
            document.getElementById('status').value = data.task.status;
            document.getElementById('progress').value = data.task.progress;
            document.getElementById('due_date').value = data.task.due_date;
            
            // Update form action
            document.getElementById('formAction').name = 'update_task';
            document.getElementById('modalTitle').textContent = 'Edit Task';
            
            // Show progress and status fields
            document.getElementById('progressGroup').style.display = 'block';
            document.getElementById('statusGroup').style.display = 'block';
            
            // Open modal
            document.getElementById('taskModal').style.display = 'block';
          } else {
            alert('Error loading task: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error loading task data');
        });
    }

    function deleteTask(taskId) {
      document.getElementById('deleteTaskId').value = taskId;
      document.getElementById('deleteModal').style.display = 'block';
    }

    function closeDeleteModal() {
      document.getElementById('deleteModal').style.display = 'none';
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
      const taskModal = document.getElementById('taskModal');
      const deleteModal = document.getElementById('deleteModal');
      
      if (event.target === taskModal) {
        closeModal();
      }
      if (event.target === deleteModal) {
        closeDeleteModal();
      }
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

    // Auto-hide alerts
    setTimeout(() => {
      const alerts = document.querySelectorAll('.alert');
      alerts.forEach(alert => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
      });
    }, 5000);
  </script>
</body>
</html>