<?php
session_start();
include '../includes/db.php';

$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) exit;

// Handle like
if (isset($_POST['like'], $_POST['post_id'])) {
    $post_id = (int)$_POST['post_id'];
    $check = $conn->query("SELECT id FROM likes WHERE user_id = $user_id AND post_id = $post_id");
    if ($check->num_rows > 0) {
        $conn->query("DELETE FROM likes WHERE user_id = $user_id AND post_id = $post_id");
    } else {
        $conn->query("INSERT INTO likes (user_id, post_id) VALUES ($user_id, $post_id)");
    }
    header("Location: dashboard.php");
    exit;
}

// Handle comment
if (isset($_POST['add_comment'], $_POST['post_id'], $_POST['comment'])) {
    $post_id = (int)$_POST['post_id'];
    $comment = $conn->real_escape_string(trim($_POST['comment']));
    if ($comment !== '') {
        $conn->query("INSERT INTO comments (user_id, post_id, comment, created_at) VALUES ($user_id, $post_id, '$comment', NOW())");
    }
    header("Location: dashboard.php");
    exit;
}
?>
