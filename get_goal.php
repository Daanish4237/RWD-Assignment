<?php
session_start();
include("db_connect.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['goal_id'])) {
    $goal_id = mysqli_real_escape_string($dbconn, $_GET['goal_id']);
    
    // Verify the goal belongs to the logged-in user
    $sql = "SELECT * FROM goals WHERE id = '$goal_id' AND user_id = '$user_id'";
    $result = mysqli_query($dbconn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $goal = mysqli_fetch_assoc($result);
        echo json_encode($goal);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Goal not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Goal ID required']);
}
?>
