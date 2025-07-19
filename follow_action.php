<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');

// Check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$current_user_id = $_SESSION['user_id'];

// Validate follow_user_id
if (!isset($_POST['follow_user_id']) || !is_numeric($_POST['follow_user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid user ID']);
    exit;
}

$follow_user_id = intval($_POST['follow_user_id']);

// Prevent user from following themselves
if ($current_user_id === $follow_user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Cannot follow yourself']);
    exit;
}

// Check if already following
$stmt = $conn->prepare("SELECT id FROM follows WHERE follower_id = ? AND following_id = ?");
$stmt->bind_param("ii", $current_user_id, $follow_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Already following — Unfollow
    $del = $conn->prepare("DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
    $del->bind_param("ii", $current_user_id, $follow_user_id);
    $del->execute();

    echo json_encode(['status' => 'unfollowed']);
} else {
    // Not following — Follow
    $ins = $conn->prepare("INSERT INTO follows (follower_id, following_id, created_at) VALUES (?, ?, NOW())");
    $ins->bind_param("ii", $current_user_id, $follow_user_id);
    $ins->execute();

    echo json_encode(['status' => 'followed']);
}
?>
