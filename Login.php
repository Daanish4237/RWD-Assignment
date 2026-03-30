<?php
session_start();
include("db_connect.php");

$error_message = "";
$success_message = "";

// Handle login
if ($_POST && isset($_POST['login'])) {
    $username = mysqli_real_escape_string($dbconn, $_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($dbconn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];
            
            // Update last login
            $update_sql = "UPDATE users SET last_login = NOW() WHERE id = " . $user['id'];
            mysqli_query($dbconn, $update_sql);
            
            header("Location: Dashboard.php");
            exit();
        } else {
            $error_message = "Invalid password!";
        }
    } else {
        $error_message = "User not found!";
    }
}

// Handle registration
if ($_POST && isset($_POST['register'])) {
    $username = mysqli_real_escape_string($dbconn, $_POST['reg_username']);
    $email = mysqli_real_escape_string($dbconn, $_POST['reg_email']);
    $password = $_POST['reg_password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    
    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        // Check if username or email already exists
        $check_sql = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
        $check_result = mysqli_query($dbconn, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error_message = "Username or email already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashed_password', '$role')";
            
            if (mysqli_query($dbconn, $insert_sql)) {
                $success_message = "Registration successful! Please login.";
            } else {
                $error_message = "Registration failed: " . mysqli_error($dbconn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TaskFlow - Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', 'Helvetica Neue', Helvetica, Arial, sans-serif;
      background: 
        radial-gradient(circle at 20% 20%, rgba(59, 130, 246, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(30, 64, 175, 0.4) 0%, transparent 50%),
        radial-gradient(circle at 40% 60%, rgba(16, 185, 129, 0.2) 0%, transparent 50%),
        linear-gradient(135deg, #0f172a 0%, #1e293b 25%, #334155 50%, #475569 75%, #64748b 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      position: relative;
      overflow-x: hidden;
      overflow-y: auto;
    }

    /* Premium animated background */
    body::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: 
        radial-gradient(circle at 20% 80%, rgba(59, 130, 246, 0.15) 0%, transparent 60%),
        radial-gradient(circle at 80% 20%, rgba(16, 185, 129, 0.1) 0%, transparent 60%),
        radial-gradient(circle at 40% 40%, rgba(139, 92, 246, 0.08) 0%, transparent 60%);
      animation: float 30s infinite linear;
      z-index: 0;
    }

    body::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: 
        radial-gradient(circle at 30% 20%, rgba(255, 255, 255, 0.03) 0%, transparent 70%),
        radial-gradient(circle at 70% 80%, rgba(255, 255, 255, 0.02) 0%, transparent 70%);
      animation: float 40s infinite linear reverse;
      z-index: 0;
    }

    /* Floating particles */
    body::before {
      background-image: 
        radial-gradient(2px 2px at 20px 30px, rgba(255, 255, 255, 0.3), transparent),
        radial-gradient(2px 2px at 40px 70px, rgba(59, 130, 246, 0.4), transparent),
        radial-gradient(1px 1px at 90px 40px, rgba(16, 185, 129, 0.3), transparent),
        radial-gradient(1px 1px at 130px 80px, rgba(139, 92, 246, 0.2), transparent);
      background-repeat: repeat;
      background-size: 150px 150px;
      animation: sparkle 20s linear infinite;
    }

    @keyframes float {
      0% { transform: translateY(0px) rotate(0deg); }
      100% { transform: translateY(-100px) rotate(360deg); }
    }

    @keyframes sparkle {
      0%, 100% { opacity: 0; }
      50% { opacity: 1; }
    }

    @keyframes fadeInUp {
      0% {
        opacity: 0;
        transform: translateY(30px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes slideInRight {
      0% {
        opacity: 0;
        transform: translateX(30px);
      }
      100% {
        opacity: 1;
        transform: translateX(0);
      }
    }

    @keyframes glow {
      0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.3); }
      50% { box-shadow: 0 0 40px rgba(59, 130, 246, 0.6); }
    }

    @keyframes shimmer {
      0% { transform: translateX(-100%); }
      100% { transform: translateX(100%); }
    }

    @keyframes gradientShift {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }

    .login-container {
      background: 
        linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.9) 100%),
        radial-gradient(circle at 20% 20%, rgba(59, 130, 246, 0.1) 0%, transparent 50%);
      backdrop-filter: blur(40px) saturate(180%);
      padding: 70px 60px;
      border-radius: 32px;
      box-shadow:
        0 40px 80px rgba(0, 0, 0, 0.15),
        0 0 0 1px rgba(255, 255, 255, 0.4),
        inset 0 1px 0 rgba(255, 255, 255, 0.6),
        inset 0 -1px 0 rgba(0, 0, 0, 0.05);
      width: 100%;
      max-width: 1200px;
      display: flex;
      gap: 80px;
      position: relative;
      z-index: 1;
      animation: slideUp 1s cubic-bezier(0.16, 1, 0.3, 1);
      border: 1px solid rgba(255, 255, 255, 0.3);
      overflow: hidden;
    }

    .login-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
      pointer-events: none;
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .left-section {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      }

    .logo { 
      width: 140px;
      height: 140px;
      margin: 0 auto 50px;
      border-radius: 50%;
      background: 
        linear-gradient(135deg, #3b82f6 0%, #1d4ed8 30%, #1e40af 70%, #312e81 100%),
        radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.3) 0%, transparent 50%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 48px;
      font-weight: 800;
      box-shadow: 
        0 25px 50px rgba(59, 130, 246, 0.4),
        0 0 0 6px rgba(255, 255, 255, 0.2),
        inset 0 2px 0 rgba(255, 255, 255, 0.3),
        inset 0 -2px 0 rgba(0, 0, 0, 0.1);
      animation: glow 4s ease-in-out infinite;
      position: relative;
      overflow: hidden;
      transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .logo:hover {
      transform: scale(1.05) rotate(5deg);
      box-shadow: 
        0 30px 60px rgba(59, 130, 246, 0.5),
        0 0 0 8px rgba(255, 255, 255, 0.3),
        inset 0 2px 0 rgba(255, 255, 255, 0.4);
    }

    .logo::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
      animation: shine 3s infinite;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.08); }
    }

    @keyframes shine {
      0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
      100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
    }

    h1 {
      color: #1a202c;
      margin-bottom: 20px;
      font-size: 42px;
      font-weight: 900;
      text-align: center;
      background: 
        linear-gradient(135deg, #3b82f6 0%, #1d4ed8 30%, #1e40af 60%, #312e81 100%),
        linear-gradient(45deg, #10b981 0%, #059669 100%);
      background-size: 200% 200%;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: -1px;
      line-height: 1.1;
      animation: gradientShift 6s ease-in-out infinite;
      text-shadow: 0 4px 8px rgba(59, 130, 246, 0.2);
    }

    .subtitle {
      color: #64748b;
      margin-bottom: 60px;
      font-size: 20px;
      font-weight: 600;
      text-align: center;
      line-height: 1.6;
      letter-spacing: 0.3px;
      opacity: 0.9;
    }

    .form-tabs {
      display: flex;
      margin-bottom: 50px;
      background: 
        linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%),
        radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.05) 0%, transparent 70%);
      border-radius: 20px;
      padding: 8px;
      box-shadow: 
        inset 0 2px 4px rgba(0, 0, 0, 0.06),
        0 4px 12px rgba(0, 0, 0, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.5);
    }

    .tab {
      flex: 1;
      padding: 20px 28px;
      text-align: center;
      background: transparent;
      border: none;
      cursor: pointer;
      border-radius: 16px;
      font-weight: 700;
      font-size: 16px;
      transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
      color: #64748b;
      position: relative;
      overflow: hidden;
      letter-spacing: 0.3px;
    }

    .tab.active {
      background: 
        linear-gradient(135deg, #3b82f6 0%, #1d4ed8 50%, #1e40af 100%),
        radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.2) 0%, transparent 50%);
      color: white;
      box-shadow: 
        0 12px 30px rgba(59, 130, 246, 0.4),
        0 0 0 2px rgba(255, 255, 255, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
      transform: translateY(-3px);
    }

    .tab:hover:not(.active) {
      background: 
        linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(29, 78, 216, 0.05) 100%);
      color: #3b82f6;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }

    .form-group {
      margin-bottom: 40px;
      text-align: left;
      position: relative;
    }

    label {
      display: block;
      margin-bottom: 16px;
      color: #1e293b;
      font-weight: 700;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .input-group {
      position: relative;
    }

    input {
      width: 100%;
      padding: 22px 28px;
      border: 2px solid #e2e8f0;
      border-radius: 20px;
      font-size: 17px;
      font-weight: 600;
      transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
      box-sizing: border-box;
      background: 
        linear-gradient(135deg, #ffffff 0%, #f8fafc 100%),
        radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.02) 0%, transparent 70%);
      color: #1e293b;
      box-shadow: 
        0 4px 12px rgba(0, 0, 0, 0.04),
        inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    input:focus {
      outline: none;
      border-color: #3b82f6;
      background: 
        linear-gradient(135deg, #ffffff 0%, #f8fafc 100%),
        radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.05) 0%, transparent 70%);
      box-shadow: 
        0 0 0 6px rgba(59, 130, 246, 0.1),
        0 12px 30px rgba(59, 130, 246, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
      transform: translateY(-4px);
    }

    input::placeholder {
      color: #94a3b8;
      font-weight: 400;
    }

    .input-icon {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #7f8c8d;
      font-size: 18px;
    }

    .password-toggle {
      cursor: pointer;
      transition: color 0.3s ease;
    }

    .password-toggle:hover {
      color: #3b82f6;
    }

    .btn {
      width: 100%;
      padding: 22px 32px;
      background: 
        linear-gradient(135deg, #3b82f6 0%, #1d4ed8 30%, #1e40af 70%, #312e81 100%),
        radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.2) 0%, transparent 50%);
      color: white;
      border: none;
      border-radius: 20px;
      font-size: 18px;
      font-weight: 800;
      cursor: pointer;
      transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
      position: relative;
      overflow: hidden;
      box-shadow: 
        0 12px 30px rgba(59, 130, 246, 0.4),
        0 0 0 2px rgba(255, 255, 255, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      transition: left 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn:hover::before {
      left: 100%;
    }

    .btn:hover {
      transform: translateY(-6px) scale(1.02);
      box-shadow: 
        0 20px 50px rgba(59, 130, 246, 0.5),
        0 0 0 3px rgba(255, 255, 255, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.4);
    }

    .btn:active {
      transform: translateY(-2px);
    }

    .btn.register {
      background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 50%, #3b82f6 100%);
      box-shadow: 
        0 8px 25px rgba(30, 64, 175, 0.3),
        0 0 0 1px rgba(255, 255, 255, 0.1);
    }

    .btn.register:hover {
      box-shadow: 
        0 16px 40px rgba(30, 64, 175, 0.4),
        0 0 0 1px rgba(255, 255, 255, 0.2);
    }

    .alert {
      padding: 16px 20px;
      border-radius: 12px;
      margin-bottom: 25px;
      font-size: 14px;
      font-weight: 500;
      animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .alert-success {
      background: linear-gradient(135deg, #d4edda, #c3e6cb);
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-danger {
      background: linear-gradient(135deg, #f8d7da, #f5c6cb);
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .right-section {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%);
      padding: 50px 40px;
      border-radius: 20px;
      position: relative;
      box-shadow: 
        inset 0 1px 0 rgba(255, 255, 255, 0.4),
        0 8px 25px rgba(0, 0, 0, 0.08);
      overflow: hidden;
    }

    .right-section::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 100%;
      height: 100%;
      background: radial-gradient(circle, rgba(59, 130, 246, 0.05) 0%, transparent 70%);
      pointer-events: none;
    }

    .welcome { 
      font-weight: 800;
      color: #1e293b;
      text-align: center;
      margin-bottom: 35px;
      font-size: 32px;
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 50%, #1e40af 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: -0.8px;
      text-shadow: 0 2px 4px rgba(59, 130, 246, 0.1);
      animation: fadeInUp 0.8s ease-out;
    }

    .features-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      margin-bottom: 40px;
      animation: fadeInUp 0.8s ease-out 0.2s both;
    }

    .feature-card {
      background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
      padding: 28px 24px;
      border-radius: 18px;
      text-align: center;
      box-shadow: 
        0 12px 30px rgba(0, 0, 0, 0.08),
        0 0 0 1px rgba(255, 255, 255, 0.6),
        inset 0 1px 0 rgba(255, 255, 255, 0.8);
      transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
      border: 1px solid rgba(255, 255, 255, 0.3);
      position: relative;
      overflow: hidden;
    }

    .feature-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.02) 0%, transparent 50%);
      pointer-events: none;
    }

    .feature-card:hover {
      transform: translateY(-10px) scale(1.02);
      box-shadow: 
        0 20px 50px rgba(0, 0, 0, 0.15),
        0 0 0 1px rgba(59, 130, 246, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }

    .feature-icon {
      font-size: 40px;
      margin-bottom: 16px;
      filter: drop-shadow(0 4px 8px rgba(59, 130, 246, 0.2));
      transition: all 0.3s ease;
    }

    .feature-card:hover .feature-icon {
      transform: scale(1.1);
      filter: drop-shadow(0 6px 12px rgba(59, 130, 246, 0.3));
    }

    .feature-title {
      font-weight: 800;
      color: #1e293b;
      margin-bottom: 10px;
      font-size: 15px;
      letter-spacing: 0.3px;
    }

    .feature-desc {
      font-size: 13px;
      color: #64748b;
      font-weight: 600;
      line-height: 1.4;
    }

    .role-selection {
      background: 
        linear-gradient(135deg, #0f172a 0%, #1e293b 25%, #334155 50%, #475569 75%, #64748b 100%),
        radial-gradient(circle at 20% 20%, rgba(59, 130, 246, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(16, 185, 129, 0.2) 0%, transparent 50%);
      padding: 40px 36px;
      border-radius: 28px;
      box-shadow: 
        0 30px 60px rgba(0, 0, 0, 0.4),
        0 0 0 2px rgba(255, 255, 255, 0.1),
        inset 0 2px 0 rgba(255, 255, 255, 0.2),
        inset 0 -2px 0 rgba(0, 0, 0, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.2);
      position: relative;
      overflow: hidden;
      animation: slideInRight 0.8s ease-out 0.4s both;
    }

    .role-selection::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
      pointer-events: none;
    }

    .role-title {
      font-weight: 900;
      color: #ffffff;
      margin-bottom: 32px;
      text-align: center;
      font-size: 22px;
      text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
      letter-spacing: 1px;
      text-transform: uppercase;
      background: linear-gradient(135deg, #ffffff 0%, #e2e8f0 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .role-option {
      padding: 32px 28px;
      border: 2px solid rgba(255, 255, 255, 0.15);
      border-radius: 20px;
      cursor: pointer;
      transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
      text-align: center;
      font-weight: 700;
      margin-bottom: 20px;
      background: 
        linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.03) 100%),
        radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
      backdrop-filter: blur(20px) saturate(180%);
      position: relative;
      overflow: hidden;
      box-shadow: 
        0 8px 25px rgba(0, 0, 0, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.1);
    }

    .role-option:hover {
      border-color: rgba(255, 255, 255, 0.5);
      background: 
        linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%),
        radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.2) 0%, transparent 70%);
      transform: translateY(-6px) scale(1.02);
      box-shadow: 
        0 16px 40px rgba(0, 0, 0, 0.3),
        0 0 0 2px rgba(255, 255, 255, 0.4),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
    }

    .role-option.selected {
      border-color: rgba(255, 255, 255, 0.8);
      background: 
        linear-gradient(135deg, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0.2) 100%),
        radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.3) 0%, transparent 70%);
      color: white;
      transform: translateY(-6px) scale(1.02);
      box-shadow: 
        0 20px 50px rgba(0, 0, 0, 0.4),
        0 0 0 3px rgba(255, 255, 255, 0.5),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
    }

    .role-option::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
      transition: left 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .role-option:hover::before {
      left: 100%;
    }

    .loading {
      display: none;
      width: 24px;
      height: 24px;
      border: 3px solid rgba(255, 255, 255, 0.3);
      border-top: 3px solid #ffffff;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto;
      box-shadow: 0 0 10px rgba(59, 130, 246, 0.3);
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .btn.loading {
      pointer-events: none;
    }

    .btn.loading .btn-text {
      display: none;
    }

    .btn.loading .loading {
      display: block;
    }

    /* Mobile Responsive */
    @media (max-width: 1024px) {
      .login-container {
        max-width: 95%;
        gap: 40px;
        padding: 50px 40px;
      }
      
      .left-section {
        display: none;
      }
    }

    @media (max-width: 768px) {
      body {
        align-items: flex-start;
        padding: 15px;
        min-height: 100vh;
        background-attachment: scroll;
      }

      .login-container {
        flex-direction: column;
        gap: 35px;
        padding: 40px 25px;
        max-width: 100%;
        margin: 15px 0;
        min-height: auto;
        border-radius: 24px;
        box-shadow:
          0 25px 50px rgba(0, 0, 0, 0.2),
          0 0 0 1px rgba(255, 255, 255, 0.3);
      }
      
      .left-section {
        display: none;
      }
      
      .features-grid {
        grid-template-columns: 1fr;
        gap: 20px;
        margin-bottom: 35px;
      }
      
      .feature-card {
        padding: 24px 20px;
        border-radius: 16px;
      }
      
      .feature-icon {
        font-size: 36px;
        margin-bottom: 14px;
      }
      
      .feature-title {
        font-size: 15px;
        margin-bottom: 10px;
      }
      
      .feature-desc {
        font-size: 13px;
        line-height: 1.4;
      }
      
      .right-section {
        padding: 30px 25px;
        border-radius: 20px;
      }
      
      h1 {
        font-size: 32px;
        margin-bottom: 12px;
        line-height: 1.1;
      }
      
      .subtitle {
        font-size: 18px;
        margin-bottom: 40px;
        line-height: 1.5;
      }
      
      .logo {
        width: 100px;
        height: 100px;
        font-size: 36px;
        margin-bottom: 35px;
      }

      .welcome {
        font-size: 24px;
        margin-bottom: 30px;
      }

      .form-group {
        margin-bottom: 28px;
      }
      
      label {
        font-size: 12px;
        margin-bottom: 14px;
      }

      input {
        padding: 18px 22px;
        font-size: 16px;
        border-radius: 16px;
      }

      .btn {
        padding: 18px 24px;
        font-size: 16px;
        border-radius: 16px;
      }

      .form-tabs {
        margin-bottom: 35px;
        padding: 6px;
        border-radius: 16px;
      }

      .tab {
        padding: 16px 20px;
        font-size: 15px;
        border-radius: 12px;
      }
      
      .role-selection {
        padding: 28px 24px;
        border-radius: 20px;
        margin-top: 20px;
      }
      
      .role-title {
        font-size: 20px;
        margin-bottom: 24px;
      }
      
      .role-option {
        padding: 24px 20px;
        margin-bottom: 16px;
        border-radius: 16px;
      }
    }

    /* Small Mobile Devices */
    @media (max-width: 480px) {
      body {
        padding: 10px;
      }

      .login-container {
        padding: 25px 20px;
        margin: 10px 0;
        border-radius: 20px;
        gap: 25px;
    }

    .logo {
        width: 80px;
        height: 80px;
        font-size: 32px;
        margin-bottom: 25px;
      }

      h1 {
        font-size: 28px;
        margin-bottom: 10px;
      }
      
      .subtitle {
        font-size: 16px;
        margin-bottom: 30px;
      }

      .welcome {
        font-size: 22px;
        margin-bottom: 25px;
      }

      .right-section {
        padding: 25px 20px;
      }
      
      .form-group {
        margin-bottom: 24px;
      }
      
      input {
        padding: 16px 20px;
        font-size: 16px;
      }

      .btn {
        padding: 16px 20px;
        font-size: 15px;
      }

      .form-tabs {
        margin-bottom: 25px;
        padding: 4px;
      }

      .tab {
        padding: 14px 16px;
        font-size: 14px;
      }
      
      .role-selection {
        padding: 20px 16px;
        border-radius: 16px;
      }
      
      .role-title {
        font-size: 18px;
        margin-bottom: 20px;
      }
      
      .role-option {
        padding: 20px 16px;
        margin-bottom: 12px;
        border-radius: 14px;
      }
      
      .feature-card {
        padding: 20px 16px;
        border-radius: 14px;
      }
    }

    /* Landscape Mobile */
    @media (max-width: 768px) and (orientation: landscape) {
      body {
      align-items: center;
        padding: 10px;
      }
      
      .login-container {
        max-height: 95vh;
        overflow-y: auto;
        margin: 10px 0;
        padding: 30px 25px;
      }
      
      .right-section {
        padding: 25px 20px;
      }
      
      .features-grid {
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 25px;
      }
    }

    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
      .login-container {
        background: rgba(30, 30, 30, 0.95);
        color: #ffffff;
      }
      
      input {
        background: #2c2c2c;
        border-color: #404040;
        color: #ffffff;
      }
      
      input:focus {
        background: #3c3c3c;
      }
      
      label {
        color: #ffffff;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="left-section">
      <div class="logo">🚀</div>
      <h1>TaskFlow</h1>
      <p class="subtitle">Productivity Enhancement Platform</p>

      <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
      <?php endif; ?>
      
      <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
      <?php endif; ?>

      <div class="form-tabs">
        <button class="tab active" onclick="showLogin()">Login</button>
        <button class="tab" onclick="showRegister()">Register</button>
      </div>

      <!-- Login Form -->
      <form id="loginForm" method="POST" action="">
        <div class="form-group">
          <label for="username">Username</label>
          <div class="input-group">
            <input id="username" name="username" type="text" placeholder="Enter your username" required>
            <span class="input-icon">👤</span>
          </div>
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-group">
            <input id="password" name="password" type="password" placeholder="Enter your password" required>
            <span class="input-icon password-toggle" onclick="togglePassword(event, 'password')">👁️</span>
          </div>
        </div>

        <button type="submit" name="login" class="btn">
          <span class="btn-text">LOGIN</span>
          <div class="loading"></div>
        </button>
      </form>

      <!-- Registration Form -->
      <form id="registerForm" method="POST" action="" style="display: none;">
        <div class="form-group">
          <label for="reg_username">Username</label>
          <div class="input-group">
            <input id="reg_username" name="reg_username" type="text" placeholder="Choose a username" required>
            <span class="input-icon">👤</span>
          </div>
        </div>
        
        <div class="form-group">
          <label for="reg_email">Email</label>
          <div class="input-group">
            <input id="reg_email" name="reg_email" type="email" placeholder="Enter your email" required>
            <span class="input-icon">📧</span>
          </div>
        </div>
        
        <div class="form-group">
          <label for="reg_password">Password</label>
          <div class="input-group">
            <input id="reg_password" name="reg_password" type="password" placeholder="Choose a password" required>
            <span class="input-icon password-toggle" onclick="togglePassword(event, 'reg_password')">👁️</span>
          </div>
        </div>
        
        <div class="form-group">
          <label for="confirm_password">Confirm Password</label>
          <div class="input-group">
            <input id="confirm_password" name="confirm_password" type="password" placeholder="Confirm your password" required>
            <span class="input-icon password-toggle" onclick="togglePassword(event, 'confirm_password')">👁️</span>
      </div>
    } 

        <div class="form-group">
          <label for="role">Role</label>
          <select id="role" name="role" required style="width: 100%; padding: 16px 20px; border: 2px solid #e1e8ed; border-radius: 12px; font-size: 16px; background: #f8f9fa;">
            <option value="User">User</option>
            <option value="Admin">Admin</option>
      </select>
        </div>

        <button type="submit" name="register" class="btn register">
          <span class="btn-text">REGISTER</span>
          <div class="loading"></div>
        </button>
      </form>
    </div>

    <div class="right-section">
      <div class="welcome">Welcome to TaskFlow</div>
      
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">📋</div>
          <div class="feature-title">Task Management</div>
          <div class="feature-desc">Organize and track your tasks</div>
        </div>
        <div class="feature-card">
          <div class="feature-icon">⏱️</div>
          <div class="feature-title">Time Tracking</div>
          <div class="feature-desc">Monitor productivity</div>
        </div>
        <div class="feature-card">
          <div class="feature-icon">🎯</div>
          <div class="feature-title">Goal Setting</div>
          <div class="feature-desc">Set and achieve goals</div>
        </div>
        <div class="feature-card">
          <div class="feature-icon">👥</div>
          <div class="feature-title">Collaboration</div>
          <div class="feature-desc">Work with your team</div>
        </div>
      </div>
      
      <div class="role-selection">
        <div class="role-title">Choose Your Role</div>
        <div class="role-option" onclick="selectRole(event, 'User')">
          👤 Individual User<br>
          <small>Manage your own tasks and goals</small>
        </div>
        <div class="role-option" onclick="selectRole(event, 'Admin')">
          👥 Team Manager<br>
          <small>Manage team tasks and collaboration</small>
        </div>
      </div>
    </div>
  </div>

  <script>
    function showLogin() {
      document.getElementById('loginForm').style.display = 'block';
      document.getElementById('registerForm').style.display = 'none';
      document.querySelectorAll('.tab')[0].classList.add('active');
      document.querySelectorAll('.tab')[1].classList.remove('active');
    }
    
    function showRegister() {
      document.getElementById('loginForm').style.display = 'none';
      document.getElementById('registerForm').style.display = 'block';
      document.querySelectorAll('.tab')[0].classList.remove('active');
      document.querySelectorAll('.tab')[1].classList.add('active');
    }
    
    function selectRole(event, role) {
      document.querySelectorAll('.role-option').forEach(option => {
        option.classList.remove('selected');
      });
      event.target.closest('.role-option').classList.add('selected');
      document.getElementById('role').value = role;
    }

    function togglePassword(event, inputId) {
      const input = document.getElementById(inputId);
      const icon = event.target;
      
      if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = '🙈';
      } else {
        input.type = 'password';
        icon.textContent = '👁️';
      }
    }
    
    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
      const alerts = document.querySelectorAll('.alert');
      alerts.forEach(alert => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
      });
    }, 5000);

    // Add loading state to buttons
    document.querySelectorAll('form').forEach(form => {
      form.addEventListener('submit', function() {
        const button = form.querySelector('.btn');
        button.classList.add('loading');
      });
  });
  </script>
</body>
</html>