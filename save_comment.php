<?php
session_start();
if (!isset($_SESSION['user_id']) || empty($_POST['comment']) || empty($_POST['post_id'])) {
    header("Location: dashboard.php");
    exit;
}

include '../includes/db.php';

$post_id = intval($_POST['post_id']);
$user_id = $_SESSION['user_id'];
$comment = trim($_POST['comment']);

$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $post_id, $user_id, $comment);
$stmt->execute();

header("Location: view_post.php?id=" . $post_id);
exit;
