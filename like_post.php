<?php
session_start();
include '../includes/db.php';

$user_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id']);

// Check if already liked
$check = $conn->prepare("SELECT id FROM likes WHERE user_id=? AND post_id=?");
$check->bind_param("ii", $user_id, $post_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    $conn->query("DELETE FROM likes WHERE user_id=$user_id AND post_id=$post_id");
    $liked = false;
} else {
    $conn->query("INSERT INTO likes(user_id, post_id) VALUES($user_id, $post_id)");
    $liked = true;

    // Optional: Add notification logic here
}

$likes = $conn->query("SELECT COUNT(*) as count FROM likes WHERE post_id=$post_id")->fetch_assoc()['count'];

echo json_encode([
    'liked' => $liked,
    'like_count' => $likes
]);
