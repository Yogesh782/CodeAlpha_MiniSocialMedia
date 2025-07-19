<?php
include '../includes/db.php';
$post_id = (int)$_GET['post_id'];

$comments = $conn->query("SELECT comments.comment, users.username 
                          FROM comments 
                          JOIN users ON comments.user_id = users.id 
                          WHERE post_id = $post_id ORDER BY created_at DESC");

while ($c = $comments->fetch_assoc()) {
    echo "<div style='margin-bottom:10px;'><strong>@{$c['username']}</strong> " . htmlspecialchars($c['comment']) . "</div>";
}
?>
