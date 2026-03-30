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

// Handle goal operations
if ($_POST) {
    if (isset($_POST['add_goal'])) {
        $title = mysqli_real_escape_string($dbconn, $_POST['title']);
        $description = mysqli_real_escape_string($dbconn, $_POST['description']);
        $target_date = $_POST['target_date'];
        
        $sql = "INSERT INTO goals (user_id, title, description, target_date) VALUES ('$user_id', '$title', '$description', '$target_date')";
        if (mysqli_query($dbconn, $sql)) {
            $success_message = "Goal added successfully!";
        } else {
            $error_message = "Error adding goal: " . mysqli_error($dbconn);
        }
    }
    
    if (isset($_POST['update_goal'])) {
        $goal_id = $_POST['goal_id'];
        $title = mysqli_real_escape_string($dbconn, $_POST['title']);
        $description = mysqli_real_escape_string($dbconn, $_POST['description']);
        $target_date = $_POST['target_date'];
        $progress = $_POST['progress'];
        $status = $_POST['status'];
        
        $sql = "UPDATE goals SET title='$title', description='$description', target_date='$target_date', progress='$progress', status='$status' WHERE id='$goal_id' AND user_id='$user_id'";
        if (mysqli_query($dbconn, $sql)) {
            $success_message = "Goal updated successfully!";
        } else {
            $error_message = "Error updating goal: " . mysqli_error($dbconn);
        }
    }
    
    if (isset($_POST['delete_goal'])) {
        $goal_id = $_POST['goal_id'];
        $sql = "DELETE FROM goals WHERE id='$goal_id' AND user_id='$user_id'";
        if (mysqli_query($dbconn, $sql)) {
            $success_message = "Goal deleted successfully!";
        } else {
            $error_message = "Error deleting goal: " . mysqli_error($dbconn);
        }
    }
}

// Get user's goals
$sql = "SELECT * FROM goals WHERE user_id = $user_id ORDER BY target_date ASC";
$result = mysqli_query($dbconn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Goals - TaskFlow</title>
  <style>
    body {
      margin: 0;
      font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', 'Helvetica Neue', Helvetica, Arial, sans-serif;
      background: #f8f9fa;
      display: flex;
      height: 100vh;
      overflow-x: hidden;
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
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 30px 0;
      box-sizing: border-box;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
      border-right: 1px solid rgba(255, 255, 255, 0.1);
      height: 100vh;
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
      margin-left: 280px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      min-height: 100vh;
      position: relative;
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

    /* Goals Page Styling */
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

    /* Goals Grid */
    .goals-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
      gap: 20px;
    }

    .goal-card {
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .goal-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }

    .goal-title {
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 10px;
      color: #333;
    }

    .goal-description {
      color: #666;
      margin-bottom: 15px;
      line-height: 1.4;
    }

    .goal-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .goal-date {
      font-size: 12px;
      color: #666;
    }

    .status-badge {
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: bold;
    }
    .status-Active { background: #cce5ff; color: #004085; }
    .status-Completed { background: #d4edda; color: #155724; }
    .status-Paused { background: #fff3cd; color: #856404; }
    .status-Cancelled { background: #f8d7da; color: #721c24; }

    .progress-section {
      margin-bottom: 15px;
    }

    .progress-bar {
      width: 100%;
      height: 8px;
      background: #e9ecef;
      border-radius: 4px;
      overflow: hidden;
      margin-bottom: 5px;
    }

    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, #007bff, #0056b3);
      transition: width 0.3s;
    }

    .progress-text {
      font-size: 12px;
      color: #666;
      text-align: center;
    }

    .goal-actions {
      display: flex;
      gap: 10px;
      justify-content: flex-end;
    }

    .btn-sm {
      padding: 8px 14px;
      font-size: 12px;
      font-weight: 600;
      border-radius: 6px;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .btn-sm:hover {
      transform: translateY(-1px);
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
    }

    .btn-sm::before {
      content: '';
      width: 14px;
      height: 14px;
      background-size: contain;
      background-repeat: no-repeat;
      background-position: center;
    }

    .btn-sm:first-child::before {
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'/%3E%3C/svg%3E");
    }

    .btn-danger::before {
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'/%3E%3C/svg%3E");
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
        body {
            flex-direction: column;
        }

        .sidebar {
            transform: translateX(-100%);
            opacity: 0;
            position: fixed;
            width: 200px;
        }

        .sidebar.active {
        transform: translateX(0);
        opacity: 1;
        }

        .main {
            margin-left: 0;
            padding: 20px;
        }

      .goals-grid {
        grid-template-columns: 1fr;
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

      .goals-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 25px;
        padding-top: 60px;
      }

      .goals-header h1 {
        font-size: 24px;
        margin: 0;
      }

      .btn {
        padding: 12px 20px;
        font-size: 14px;
        width: 100%;
        max-width: 200px;
      }

      .goals-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .goal-card {
        padding: 24px;
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.9) 100%);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
      }

      .goal-card::before {
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

      .goal-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
      }

      .goal-card:hover::before {
        opacity: 1;
      }

      .goal-title {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 12px;
        color: #1e293b;
        position: relative;
        z-index: 1;
      }

      .goal-description {
        font-size: 15px;
        margin-bottom: 18px;
        line-height: 1.5;
        color: #64748b;
        position: relative;
        z-index: 1;
      }

      .goal-meta {
        flex-direction: column;
        gap: 10px;
        margin-bottom: 18px;
        position: relative;
        z-index: 1;
      }

      .status-badge {
        font-size: 12px;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }

      .progress-bar {
        height: 8px;
        margin-bottom: 8px;
      }

      .goal-actions {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
      }

      .btn-sm {
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 6px;
        flex: 1;
        justify-content: center;
      }

      .btn-sm:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      }

      .btn-sm::before {
        content: '';
        width: 16px;
        height: 16px;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
      }

      .btn-sm:first-child::before {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'/%3E%3C/svg%3E");
      }

      .btn-danger::before {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'/%3E%3C/svg%3E");
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

      .goals-header {
        padding-top: 50px;
        margin-bottom: 20px;
      }

      .goals-header h1 {
        font-size: 20px;
      }

      .btn {
        padding: 10px 16px;
        font-size: 13px;
      }

      .goals-grid {
        gap: 20px;
        padding: 0 10px;
      }

      /* Enhanced mobile card interactions */
      .goal-card:active {
        transform: scale(0.98);
        transition: transform 0.1s ease;
      }

      /* Mobile-specific goal card enhancements */
      .goal-card {
        margin-bottom: 5px;
        padding: 20px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.9) 100%);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
      }

      .goal-card .goal-actions {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
      }

      .goal-card::before {
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

      .goal-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.12);
      }

      .goal-card:hover::before {
        opacity: 1;
      }

      .goal-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 10px;
        color: #1e293b;
        position: relative;
        z-index: 1;
      }

      .goal-description {
        font-size: 14px;
        margin-bottom: 15px;
        line-height: 1.4;
        color: #64748b;
        position: relative;
        z-index: 1;
      }

      .goal-meta {
        gap: 8px;
        margin-bottom: 15px;
        position: relative;
        z-index: 1;
      }

      .status-badge {
        font-size: 11px;
        padding: 5px 10px;
        border-radius: 6px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }

      .progress-bar {
        height: 6px;
        margin-bottom: 6px;
      }

      .goal-actions {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
      }

      .btn-sm {
            padding: 8px 12px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 6px;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 4px;
        flex: 1;
        justify-content: center;
      }

      .btn-sm:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
      }

      .btn-sm::before {
        content: '';
        width: 14px;
        height: 14px;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
      }

      .btn-sm:first-child::before {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'/%3E%3C/svg%3E");
      }

      .btn-danger::before {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'/%3E%3C/svg%3E");
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
      .goals-header {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        padding-top: 60px;
      }

      .goals-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
      }

      .goal-card {
        padding: 15px;
      }

      .goal-title {
        font-size: 16px;
      }

      .goal-description {
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
      <button>🎯 GOALS</button>
      <a href="collaboration.php"><button>👥 COLLABORATION</button></a>
      <a href="analytics.php"><button>📊 ANALYTICS</button></a>
      <?php if ($role === 'Admin'): ?>
        <a href="admin-users.php"><button>👤 USER MANAGEMENT</button></a>
        <a href="admin-system.php"><button>⚙️ SYSTEM ADMIN</button></a>
      <?php endif; ?>
      <a href="profile.php"><button>👤 PROFILE</button></a>
    </div>
  </div>

  <!-- Main Goals Section -->
  <div class="main">
    <div class="page-header">
      <h1>🎯 Goal Setting</h1>
      <button class="btn btn-success" onclick="openModal('add')">+ Add New Goal</button>
    </div>

    <?php if (isset($success_message)): ?>
      <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
      <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Goals Grid -->
    <div class="goals-grid">
      <?php
      if (mysqli_num_rows($result) > 0) {
        while ($goal = mysqli_fetch_assoc($result)) {
          $progress = $goal['progress'];
          $status = $goal['status'];
          $target_date = date('M d, Y', strtotime($goal['target_date']));
          $days_left = ceil((strtotime($goal['target_date']) - time()) / (60 * 60 * 24));
          ?>
          <div class="goal-card">
            <div class="goal-title"><?php echo htmlspecialchars($goal['title']); ?></div>
            <div class="goal-description"><?php echo htmlspecialchars($goal['description']); ?></div>
            
            <div class="goal-meta">
              <span class="goal-date">Target: <?php echo $target_date; ?></span>
              <span class="status-badge status-<?php echo $status; ?>"><?php echo $status; ?></span>
            </div>
            
            <div class="progress-section">
              <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
              </div>
              <div class="progress-text"><?php echo $progress; ?>% Complete</div>
            </div>
            
            <div style="font-size: 12px; color: #666; margin-bottom: 15px;">
              <?php if ($days_left > 0): ?>
                <?php echo $days_left; ?> days remaining
              <?php elseif ($days_left == 0): ?>
                Due today!
              <?php else: ?>
                <?php echo abs($days_left); ?> days overdue
              <?php endif; ?>
            </div>
            
            <div class="goal-actions">
              <button class="btn btn-sm" onclick="editGoal(<?php echo $goal['id']; ?>)">Edit</button>
              <button class="btn btn-danger btn-sm" onclick="deleteGoal(<?php echo $goal['id']; ?>)">Delete</button>
            </div>
          </div>
          <?php
        }
      } else {
        echo '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
                <h3>No goals yet!</h3>
                <p>Set your first goal to start tracking your progress.</p>
              </div>';
      }
      ?>
    </div>
  </div>

  <!-- Add/Edit Goal Modal -->
  <div id="goalModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 id="modalTitle">Add New Goal</h3>
        <span class="close" onclick="closeModal()">&times;</span>
      </div>
      
      <form id="goalForm" method="POST" action="">
        <input type="hidden" id="goalId" name="goal_id">
        <input type="hidden" id="formAction" name="add_goal">
        
        <div class="form-group">
          <label for="title">Goal Title</label>
          <input type="text" id="title" name="title" required>
        </div>
        
        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" name="description"></textarea>
        </div>
        
        <div class="form-group">
          <label for="target_date">Target Date</label>
          <input type="date" id="target_date" name="target_date" required>
        </div>
        
        <div class="form-group" id="progressGroup" style="display: none;">
          <label for="progress">Progress (%)</label>
          <input type="number" id="progress" name="progress" min="0" max="100" value="0">
        </div>
        
        <div class="form-group" id="statusGroup" style="display: none;">
          <label for="status">Status</label>
          <select id="status" name="status">
            <option value="Active">Active</option>
            <option value="Completed">Completed</option>
            <option value="Paused">Paused</option>
            <option value="Cancelled">Cancelled</option>
          </select>
        </div>
        
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
          <button type="button" class="btn" onclick="closeModal()">Cancel</button>
          <button type="submit" class="btn btn-success">Save Goal</button>
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
      <p>Are you sure you want to delete this goal? This action cannot be undone.</p>
      <form id="deleteForm" method="POST" action="">
        <input type="hidden" id="deleteGoalId" name="goal_id">
        <input type="hidden" name="delete_goal" value="1">
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
        sidebar.classList.toggle('active');
    }
    }

    function closeSidebar() {
      const sidebar = document.querySelector('.sidebar');
      const overlay = document.querySelector('.sidebar-overlay');
      
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
    }

    function openModal(action) {
      const modal = document.getElementById('goalModal');
      const form = document.getElementById('goalForm');
      const title = document.getElementById('modalTitle');
      const actionInput = document.getElementById('formAction');
      const progressGroup = document.getElementById('progressGroup');
      const statusGroup = document.getElementById('statusGroup');
      
      if (action === 'add') {
        title.textContent = 'Add New Goal';
        actionInput.name = 'add_goal';
        progressGroup.style.display = 'none';
        statusGroup.style.display = 'none';
        form.reset();
        document.getElementById('goalId').value = '';
      } else {
        title.textContent = 'Edit Goal';
        actionInput.name = 'update_goal';
        progressGroup.style.display = 'block';
        statusGroup.style.display = 'block';
      }
      
      modal.style.display = 'block';
    }

    function closeModal() {
      document.getElementById('goalModal').style.display = 'none';
    }

    function editGoal(goalId) {
      // Fetch goal data via AJAX
      fetch(`get_goal.php?goal_id=${goalId}`)
        .then(response => response.json())
        .then(data => {
          if (data.error) {
            alert('Error: ' + data.error);
        return;
      }
          
          // Reset and populate the form with existing goal data
          document.getElementById('goalForm').reset();
          document.getElementById('goalId').value = data.id;
          document.getElementById('title').value = data.title;
          document.getElementById('description').value = data.description;
          document.getElementById('target_date').value = data.target_date;
          document.getElementById('progress').value = data.progress || 0;
          document.getElementById('status').value = data.status || 'Active';
          
          // Show the modal with edit mode
          openModal('edit');
        })
        .catch(error => {
          console.error('Error fetching goal:', error);
          alert('Error fetching goal data. Please try again.');
        });
    }

    function deleteGoal(goalId) {
      document.getElementById('deleteGoalId').value = goalId;
      document.getElementById('deleteModal').style.display = 'block';
    }

    function closeDeleteModal() {
      document.getElementById('deleteModal').style.display = 'none';
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
      const goalModal = document.getElementById('goalModal');
      const deleteModal = document.getElementById('deleteModal');
      
      if (event.target === goalModal) {
        closeModal();
      }
      if (event.target === deleteModal) {
        closeDeleteModal();
      }
    }

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
