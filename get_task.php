<?php
session_start();
include("db_connect.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$task_id = $_GET['id'] ?? null;

if (!$task_id) {
    echo json_encode(['success' => false, 'message' => 'Task ID required']);
    exit();
}

// Get task data
$sql = "SELECT * FROM tasks WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($dbconn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $task_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    $task = mysqli_fetch_assoc($result);
    echo json_encode([
        'success' => true,
        'task' => [
            'id' => $task['id'],
            'title' => $task['title'],
            'description' => $task['description'],
            'priority' => $task['priority'],
            'status' => $task['status'],
            'progress' => $task['progress'],
            'due_date' => $task['due_date']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Task not found']);
}

mysqli_stmt_close($stmt);
mysqli_close($dbconn);
?>
