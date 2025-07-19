<?php
include '../includes/db.php';
session_start();

$post_id = intval($_GET['post_id']);

// Get post
$post = $conn->query("SELECT p.*, u.username, u.profile_pic FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = $post_id")->fetch_assoc();

// Get all comments
$comments = $conn->query("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = $post_id ORDER BY c.created_at ASC");

?>

<div class="modal-post">
  <div class="modal-image">
    <img src="../assets/uploads/posts/<?= htmlspecialchars($post['image']) ?>">
  </div>
  <div class="modal-info">
    <div class="modal-header">
      <img src="../assets/uploads/profiles/<?= $post['profile_pic'] ?: 'default.jpg' ?>">
      <strong>@<?= htmlspecialchars($post['username']) ?></strong>
    </div>

    <div class="modal-description">
      <strong>@<?= htmlspecialchars($post['username']) ?></strong>
      <?= nl2br(htmlspecialchars($post['content'])) ?>
    </div>

    <div class="modal-comments">
      <?php while ($c = $comments->fetch_assoc()): ?>
        <div class="comment">
          <strong>@<?= htmlspecialchars($c['username']) ?></strong>
          <?= htmlspecialchars($c['comment']) ?>
        </div>
      <?php endwhile; ?>
    </div>

    <!-- Comment Box -->
    <form class="comment-form" action="like_comment_handler.php" method="POST">
      <input type="hidden" name="post_id" value="<?= $post_id ?>">
      <input type="text" name="comment" placeholder="Add a comment..." required>
      <button name="add_comment">Post</button>
    </form>
  </div>
</div>
