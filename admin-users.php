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

// Handle user management operations
if ($_POST) {
    if (isset($_POST['delete_user'])) {
        $delete_user_id = $_POST['delete_user_id'];
        if ($delete_user_id != $user_id) { // Prevent admin from deleting themselves
            $delete_sql = "DELETE FROM users WHERE id = '$delete_user_id'";
            if (mysqli_query($dbconn, $delete_sql)) {
                $success_message = "User deleted successfully!";
            } else {
                $error_message = "Error deleting user: " . mysqli_error($dbconn);
            }
        } else {
            $error_message = "Cannot delete your own account!";
        }
    }
    
    if (isset($_POST['update_user'])) {
        $update_user_id = $_POST['update_user_id'];
        $new_username = mysqli_real_escape_string($dbconn, $_POST['new_username']);
        $new_email = mysqli_real_escape_string($dbconn, $_POST['new_email']);
        $new_role = $_POST['new_role'];
        
        $update_sql = "UPDATE users SET username='$new_username', email='$new_email', role='$new_role' WHERE id='$update_user_id'";
        if (mysqli_query($dbconn, $update_sql)) {
            $success_message = "User updated successfully!";
        } else {
            $error_message = "Error updating user: " . mysqli_error($dbconn);
        }
    }
}

// Get all users
$users_sql = "SELECT * FROM users ORDER BY created_at DESC";
$users_result = mysqli_query($dbconn, $users_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Management - TaskFlow Admin</title>
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

    /* User Management Table */
    .users-table {
      width: 100%;
      border-collapse: collapse;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      border-radius: 16px;
      overflow: hidden;
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .users-table th {
      background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
      color: white;
      padding: 20px;
      text-align: left;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-size: 14px;
    }

    .users-table td {
      padding: 20px;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
      vertical-align: middle;
    }

    .users-table tr:hover {
      background: rgba(220, 38, 38, 0.05);
    }

    .role-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .role-admin {
      background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
    }

    .role-user {
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }

    .btn {
      padding: 8px 16px;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-size: 12px;
    }

    .btn-edit {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }

    .btn-edit:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .btn-delete {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
    }

    .btn-delete:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 2000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(5px);
    }

    .modal-content {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.9) 100%);
      margin: 5% auto;
      padding: 30px;
      border-radius: 20px;
      width: 90%;
      max-width: 500px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #374151;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 12px 16px;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      font-size: 14px;
      transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .alert {
      padding: 15px 20px;
      border-radius: 12px;
      margin-bottom: 20px;
      font-weight: 600;
    }

    .alert-success {
      background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%);
      color: #065f46;
      border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .alert-danger {
      background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.05) 100%);
      color: #991b1b;
      border: 1px solid rgba(239, 68, 68, 0.2);
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

      .users-table {
        font-size: 12px;
      }

      .users-table th,
      .users-table td {
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
      <button style="background: linear-gradient(135deg, rgba(220, 38, 38, 0.3) 0%, rgba(185, 28, 28, 0.2) 100%);">👤 USER MANAGEMENT</button>
      <a href="admin-system.php"><button>⚙️ SYSTEM ADMIN</button></a>
      <a href="profile.php"><button>👤 PROFILE</button></a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main">
    <div class="page-header">
      <h1>👤 User Management</h1>
    </div>

    <?php if (isset($success_message)): ?>
      <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
      <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <table class="users-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Email</th>
          <th>Role</th>
          <th>Created</th>
          <th>Last Login</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
          <tr>
            <td><?php echo $user['id']; ?></td>
            <td><?php echo htmlspecialchars($user['username']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td>
              <span class="role-badge <?php echo $user['role'] === 'Admin' ? 'role-admin' : 'role-user'; ?>">
                <?php echo $user['role']; ?>
              </span>
            </td>
            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
            <td><?php echo $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never'; ?></td>
            <td>
              <button class="btn btn-edit" onclick="editUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo htmlspecialchars($user['email']); ?>', '<?php echo $user['role']; ?>')">Edit</button>
              <?php if ($user['id'] != $user_id): ?>
                <button class="btn btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">Delete</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Edit User Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <h3>Edit User</h3>
      <form method="POST">
        <input type="hidden" name="update_user_id" id="editUserId">
        <div class="form-group">
          <label for="new_username">Username</label>
          <input type="text" id="new_username" name="new_username" required>
        </div>
        <div class="form-group">
          <label for="new_email">Email</label>
          <input type="email" id="new_email" name="new_email" required>
        </div>
        <div class="form-group">
          <label for="new_role">Role</label>
          <select id="new_role" name="new_role" required>
            <option value="User">User</option>
            <option value="Admin">Admin</option>
          </select>
        </div>
        <div style="display: flex; gap: 10px; margin-top: 20px;">
          <button type="submit" class="btn btn-edit">Update User</button>
          <button type="button" class="btn" onclick="closeEditModal()" style="background: #6b7280; color: white;">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete User Modal -->
  <div id="deleteModal" class="modal">
    <div class="modal-content">
      <h3>Delete User</h3>
      <p>Are you sure you want to delete user: <strong id="deleteUserName"></strong>?</p>
      <p style="color: #dc2626; font-weight: 600;">This action cannot be undone!</p>
      <form method="POST" style="display: flex; gap: 10px; margin-top: 20px;">
        <input type="hidden" name="delete_user_id" id="deleteUserId">
        <button type="submit" name="delete_user" class="btn btn-delete">Delete User</button>
        <button type="button" class="btn" onclick="closeDeleteModal()" style="background: #6b7280; color: white;">Cancel</button>
      </form>
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

    function editUser(id, username, email, role) {
      document.getElementById('editUserId').value = id;
      document.getElementById('new_username').value = username;
      document.getElementById('new_email').value = email;
      document.getElementById('new_role').value = role;
      document.getElementById('editModal').style.display = 'block';
    }

    function closeEditModal() {
      document.getElementById('editModal').style.display = 'none';
    }

    function deleteUser(id, username) {
      document.getElementById('deleteUserId').value = id;
      document.getElementById('deleteUserName').textContent = username;
      document.getElementById('deleteModal').style.display = 'block';
    }

    function closeDeleteModal() {
      document.getElementById('deleteModal').style.display = 'none';
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
      const editModal = document.getElementById('editModal');
      const deleteModal = document.getElementById('deleteModal');
      
      if (event.target === editModal) {
        closeEditModal();
      }
      if (event.target === deleteModal) {
        closeDeleteModal();
      }
    }
  </script>
</body>
</html>
