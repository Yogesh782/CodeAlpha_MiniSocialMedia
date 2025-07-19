<?php
session_start();
include '../includes/db.php';

$user_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id']);
$content = trim($_POST['content']);

if (!empty($content)) {
    $stmt = $conn->prepare("INSERT INTO comments(user_id, post_id, content, created_at) VALUES(?, ?, ?, NOW())");
    $stmt->bind_param("iis", $user_id, $post_id, $content);
    $stmt->execute();

    // Optional: Add notification for post owner
}
