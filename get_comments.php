<?php
include '../includes/db.php';
$post_id = intval($_GET['post_id']);
$res = $conn->query("SELECT c.content, c.created_at, u.username FROM comments c JOIN users u ON c.user_id=u.id WHERE c.post_id=$post_id ORDER BY c.created_at DESC");

$output = '';
while($row = $res->fetch_assoc()){
    $output .= "<p><strong>{$row['username']}</strong>: " . htmlspecialchars($row['content']) . "<br><small>" . date('d M Y, h:i A', strtotime($row['created_at'])) . "</small></p>";
}
echo $output ?: "<p>No comments yet.</p>";
