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
$email = $_SESSION['email'];

// Handle profile updates
if ($_POST && isset($_POST['update_profile'])) {
    $new_username = mysqli_real_escape_string($dbconn, $_POST['username']);
    $new_email = mysqli_real_escape_string($dbconn, $_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get current user data
    $user_sql = "SELECT * FROM users WHERE id = $user_id";
    $user_result = mysqli_query($dbconn, $user_sql);
    $user_data = mysqli_fetch_assoc($user_result);
    
    $success_message = "";
    $error_message = "";
    
    // Verify current password if changing password
    if (!empty($new_password)) {
        if (!password_verify($current_password, $user_data['password'])) {
            $error_message = "Current password is incorrect!";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "New passwords do not match!";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET username='$new_username', email='$new_email', password='$hashed_password' WHERE id=$user_id";
            if (mysqli_query($dbconn, $update_sql)) {
                $_SESSION['username'] = $new_username;
                $_SESSION['email'] = $new_email;
                $success_message = "Profile updated successfully!";
            } else {
                $error_message = "Error updating profile: " . mysqli_error($dbconn);
            }
        }
    } else {
        // Update without password change
        $update_sql = "UPDATE users SET username='$new_username', email='$new_email' WHERE id=$user_id";
        if (mysqli_query($dbconn, $update_sql)) {
            $_SESSION['username'] = $new_username;
            $_SESSION['email'] = $new_email;
            $success_message = "Profile updated successfully!";
        } else {
            $error_message = "Error updating profile: " . mysqli_error($dbconn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - TaskFlow</title>
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
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(16, 185, 129, 0.05) 100%);
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .page-header:hover::before {
      opacity: 1;
    }

    .page-header h1 {
      margin: 0;
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-weight: 800;
      font-size: 28px;
      text-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
      position: relative;
      z-index: 1;
    }

    /* Modern Profile Container */
    .profile-container {
      display: grid;
      grid-template-columns: 1fr 2fr;
      gap: 30px;
      margin-bottom: 30px;
    }

    /* Profile Info Card */
    .profile-info-card {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      position: relative;
      overflow: hidden;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .profile-info-card::before {
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

    .profile-info-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    .profile-info-card:hover::before {
      opacity: 1;
    }

    /* Profile Avatar */
    .profile-avatar {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 48px;
      font-weight: 700;
      color: white;
      margin: 0 auto 20px;
      box-shadow: 0 8px 32px rgba(59, 130, 246, 0.3);
      transition: all 0.3s ease;
      position: relative;
      z-index: 1;
    }

    .profile-avatar:hover {
      transform: scale(1.05);
      box-shadow: 0 12px 40px rgba(59, 130, 246, 0.4);
    }

    /* Profile Stats */
    .profile-stats {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 15px;
      margin-top: 20px;
    }

    .stat-card {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
      padding: 20px;
      border-radius: 16px;
      text-align: center;
      border: 1px solid rgba(59, 130, 246, 0.2);
      transition: all 0.3s ease;
      position: relative;
      z-index: 1;
    }

    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(59, 130, 246, 0.2);
    }

    .stat-number {
      font-size: 24px;
      font-weight: 700;
      color: #3b82f6;
      margin-bottom: 5px;
    }

    .stat-label {
      font-size: 14px;
      color: #64748b;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    /* Modern Form Card */
    .profile-form-card {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      position: relative;
      overflow: hidden;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .profile-form-card::before {
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

    .profile-form-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 15px 50px rgba(0, 0, 0, 0.12);
    }

    .profile-form-card:hover::before {
      opacity: 1;
    }

    /* Form Sections */
    .form-section {
      margin-bottom: 30px;
      position: relative;
      z-index: 1;
    }

    .form-section h3 {
      color: #1e293b;
      font-size: 18px;
      font-weight: 700;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid #e2e8f0;
      position: relative;
    }

    .form-section h3::after {
      content: '';
      position: absolute;
      bottom: -2px;
      left: 0;
      width: 50px;
      height: 2px;
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      border-radius: 1px;
    }

    /* Enhanced Form Groups */
    .form-group {
      margin-bottom: 20px;
      position: relative;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #374151;
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 12px 16px;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      font-size: 14px;
      transition: all 0.3s ease;
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(10px);
    }

    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
      transform: translateY(-1px);
    }

    .form-group input:hover,
    .form-group select:hover {
      border-color: #94a3b8;
    }

    /* Enhanced Buttons */
    .btn {
      padding: 12px 24px;
      border: none;
      border-radius: 12px;
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      position: relative;
      overflow: hidden;
    }

    .btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }

    .btn:hover::before {
      left: 100%;
    }

    .btn-primary {
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      color: white;
      box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
    }

    .btn-danger {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      color: white;
      box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }

    .btn-danger:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
    }

    /* Form Actions */
    .form-actions {
      display: flex;
      gap: 15px;
      margin-top: 30px;
      flex-wrap: wrap;
    }

    .profile-info {
      display: flex;
      align-items: center;
      margin-bottom: 30px;
      padding: 20px;
      background: #f8f9fa;
      border-radius: 10px;
    }

    .avatar {
      width: 80px;
      height: 80px;
      background: #007bff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 32px;
      color: white;
      margin-right: 20px;
    }

    .user-info h2 {
      margin: 0 0 5px 0;
      color: #333;
    }

    .user-info p {
      margin: 0;
      color: #666;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: 600;
      color: #333;
    }

    .form-group input {
      width: 100%;
      padding: 12px;
      border: 2px solid #ddd;
      border-radius: 6px;
      font-size: 14px;
    }

    .form-group input:focus {
      outline: none;
      border-color: #007bff;
    }

    .btn {
      padding: 12px 24px;
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

    .btn-danger {
      background: #dc3545;
    }
    .btn-danger:hover {
      background: #c82333;
    }

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

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 20px;
      margin: 20px 0;
    }

    .stat-card {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 8px;
      text-align: center;
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

    /* Mobile Responsive */
    @media (max-width: 768px) {
      .profile-container {
        margin: 0 10px;
        padding: 20px;
      }
      
      .profile-info {
        flex-direction: column;
        text-align: center;
      }
      
      .avatar {
        margin-right: 0;
        margin-bottom: 15px;
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

      .profile-container {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .profile-stats {
        grid-template-columns: repeat(2, 1fr);
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

      .profile-container {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .profile-stats {
        grid-template-columns: 1fr;
        gap: 10px;
      }

      .profile-avatar {
        width: 100px;
        height: 100px;
        font-size: 40px;
      }

      .form-actions {
        flex-direction: column;
        gap: 10px;
      }

      .btn {
        width: 100%;
        padding: 14px 20px;
        font-size: 16px;
      }

      .profile-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
        margin-bottom: 30px;
        padding-top: 60px;
      }

      .profile-header h1 {
        font-size: 24px;
        margin-bottom: 15px;
      }

      .profile-info {
        flex-direction: column;
        align-items: center;
        gap: 15px;
      }

      .avatar {
        margin-right: 0;
        margin-bottom: 15px;
      }

      .user-details h2 {
        font-size: 20px;
        margin-bottom: 8px;
      }

      .user-details p {
        font-size: 14px;
        margin-bottom: 5px;
      }

      .profile-stats {
        grid-template-columns: 1fr;
        gap: 15px;
        margin: 25px 0;
      }

      .stat-card {
        padding: 20px 15px;
        border-radius: 12px;
        text-align: center;
      }

      .stat-card h3 {
        font-size: 1.8em;
        margin-bottom: 8px;
      }

      .stat-card p {
        font-size: 14px;
      }

      .profile-actions {
        flex-direction: column;
        gap: 15px;
        margin-top: 25px;
      }

      .btn {
        padding: 12px 20px;
        font-size: 14px;
        width: 100%;
        max-width: 300px;
      }

      .form-section {
        margin-top: 30px;
      }

      .form-section h3 {
        font-size: 18px;
        margin-bottom: 20px;
      }

      .form-group {
        margin-bottom: 20px;
      }

      .form-group label {
        font-size: 14px;
        margin-bottom: 8px;
      }

      .form-group input {
        padding: 12px;
        font-size: 16px;
        border-radius: 8px;
      }

      .form-actions {
        flex-direction: column;
        gap: 10px;
        margin-top: 25px;
      }

      .form-actions .btn {
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

      .profile-header {
        padding-top: 50px;
        margin-bottom: 25px;
      }

      .profile-header h1 {
        font-size: 20px;
        margin-bottom: 12px;
      }

      .profile-info {
        gap: 12px;
      }

      .avatar {
        width: 80px;
        height: 80px;
        font-size: 32px;
        margin-bottom: 12px;
      }

      .user-details h2 {
        font-size: 18px;
        margin-bottom: 6px;
      }

      .user-details p {
        font-size: 13px;
        margin-bottom: 4px;
      }

      .profile-stats {
        gap: 12px;
        margin: 20px 0;
      }

      .stat-card {
        padding: 15px 12px;
        border-radius: 10px;
      }

      .stat-card h3 {
        font-size: 1.6em;
        margin-bottom: 6px;
      }

      .stat-card p {
        font-size: 13px;
      }

      .profile-actions {
        gap: 12px;
        margin-top: 20px;
      }

      .btn {
        padding: 10px 16px;
        font-size: 13px;
        max-width: 100%;
      }

      .form-section {
        margin-top: 25px;
      }

      .form-section h3 {
        font-size: 16px;
        margin-bottom: 15px;
      }

      .form-group {
        margin-bottom: 15px;
      }

      .form-group label {
        font-size: 13px;
        margin-bottom: 6px;
      }

      .form-group input {
        padding: 10px;
        font-size: 14px;
        border-radius: 6px;
      }

      .form-actions {
        gap: 8px;
        margin-top: 20px;
      }

      .form-actions .btn {
        padding: 10px;
        font-size: 13px;
      }
    }

    /* Landscape Mobile */
    @media (max-width: 768px) and (orientation: landscape) {
      .profile-header {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        text-align: left;
        padding-top: 60px;
      }

      .profile-info {
        flex-direction: row;
        align-items: center;
        gap: 20px;
      }

      .avatar {
        margin-right: 15px;
        margin-bottom: 0;
      }

      .profile-stats {
        grid-template-columns: repeat(3, 1fr);
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
      <a href="analytics.php"><button>📊 ANALYTICS</button></a>
      <?php if ($role === 'Admin'): ?>
        <a href="admin-users.php"><button>👤 USER MANAGEMENT</button></a>
        <a href="admin-system.php"><button>⚙️ SYSTEM ADMIN</button></a>
      <?php endif; ?>
      <button>👤 PROFILE</button>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main">
    <div class="page-header">
      <h1>👤 User Profile</h1>
    </div>

    <?php if (isset($success_message)): ?>
      <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
      <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="profile-container">
      <!-- Profile Info Card -->
      <div class="profile-info-card">
        <div class="profile-avatar">
          <?php echo strtoupper(substr($username, 0, 1)); ?>
        </div>
        <div class="user-info">
          <h2 style="text-align: center; margin-bottom: 10px; color: #1e293b; font-size: 24px; font-weight: 700;">
            <?php echo htmlspecialchars($username); ?>
          </h2>
          <p style="text-align: center; color: #64748b; margin-bottom: 5px; font-size: 16px;">
            <?php echo htmlspecialchars($email); ?>
          </p>
          <p style="text-align: center; color: #3b82f6; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
            <?php echo htmlspecialchars($role); ?>
          </p>
        </div>

        <!-- Profile Stats -->
        <?php
        // Get user statistics
        $stats_sql = "SELECT 
          (SELECT COUNT(*) FROM tasks WHERE user_id = $user_id) as total_tasks,
          (SELECT COUNT(*) FROM tasks WHERE user_id = $user_id AND status = 'Completed') as completed_tasks,
          (SELECT COUNT(*) FROM goals WHERE user_id = $user_id) as total_goals,
          (SELECT COUNT(*) FROM time_logs WHERE user_id = $user_id AND end_time IS NOT NULL) as time_sessions";
        $stats_result = mysqli_query($dbconn, $stats_sql);
        $stats = mysqli_fetch_assoc($stats_result);
        ?>
        
        <div class="profile-stats">
          <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_tasks'] ?? 0; ?></div>
            <div class="stat-label">Total Tasks</div>
          </div>
          <div class="stat-card">
            <div class="stat-number"><?php echo $stats['completed_tasks'] ?? 0; ?></div>
            <div class="stat-label">Completed</div>
          </div>
          <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_goals'] ?? 0; ?></div>
            <div class="stat-label">Goals</div>
          </div>
          <div class="stat-card">
            <div class="stat-number"><?php echo $stats['time_sessions'] ?? 0; ?></div>
            <div class="stat-label">Time Sessions</div>
          </div>
        </div>
      </div>

      <!-- Profile Form Card -->
      <div class="profile-form-card">
        <form method="POST" action="">
          <div class="form-section">
            <h3>Personal Information</h3>
            
            <div class="form-group">
              <label for="username">Username</label>
              <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            
            <div class="form-group">
              <label for="email">Email Address</label>
              <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
          </div>

          <div class="form-section">
            <h3>Change Password</h3>
            <p style="color: #64748b; font-size: 14px; margin-bottom: 20px;">Leave password fields empty if you don't want to change your password.</p>
            
            <div class="form-group">
              <label for="current_password">Current Password</label>
              <input type="password" id="current_password" name="current_password" placeholder="Enter your current password">
            </div>
            
            <div class="form-group">
              <label for="new_password">New Password</label>
              <input type="password" id="new_password" name="new_password" placeholder="Enter your new password">
            </div>
            
            <div class="form-group">
              <label for="confirm_password">Confirm New Password</label>
              <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your new password">
            </div>
          </div>
          
          <div class="form-actions">
            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
            <a href="Login.php" class="btn btn-danger" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
          </div>
        </form>
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
