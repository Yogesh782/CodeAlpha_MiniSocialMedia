<?php
session_start();
include '../includes/db.php';

$me = $_SESSION['user_id'];
$them = $_POST['user_to_follow'];

if (isset($_POST['follow'])) {
    $conn->query("INSERT IGNORE INTO followers (follower_id, following_id) VALUES ($me, $them)");
}
if (isset($_POST['unfollow'])) {
    $conn->query("DELETE FROM followers WHERE follower_id=$me AND following_id=$them");
}
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>
