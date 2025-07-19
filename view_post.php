<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Post ID.");
}

include '../includes/db.php';

$post_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Get Post
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Post not found.");
}
$post = $result->fetch_assoc();

// Get Likes Count
$likes_result = $conn->query("SELECT COUNT(*) as total FROM likes WHERE post_id = $post_id");
$likes_data = $likes_result->fetch_assoc();
$total_likes = $likes_data['total'];

// Check if user liked
$liked_by_user = false;
$check = $conn->query("SELECT 1 FROM likes WHERE post_id = $post_id AND user_id = $user_id");
if ($check && $check->num_rows > 0) {
    $liked_by_user = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Post</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: #fafafa;
      margin: 0;
      padding: 40px;
    }
    .container {
      display: flex;
      max-width: 900px;
      margin: auto;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
      overflow: hidden;
    }
    .post-left {
      flex: 1;
      max-width: 60%;
      background: #000;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .post-left img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      max-height: 600px;
    }
    .post-right {
      flex: 1;
      padding: 20px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .details p {
      margin: 10px 0;
      font-size: 15px;
      color: #333;
    }
    .details .label {
      font-weight: 600;
      color: #555;
    }
    #likeBtn {
      background: none;
      border: none;
      font-size: 18px;
      cursor: pointer;
    }
    #like-count {
      font-size: 14px;
      color: #666;
    }
    #comments {
      max-height: 200px;
      overflow-y: auto;
      margin-bottom: 10px;
    }
    #comments .comment {
      font-size: 14px;
      padding: 4px 0;
      border-bottom: 1px solid #eee;
    }
    #submitComment {
      background: #3897f0;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 5px;
      margin-top: 8px;
      cursor: pointer;
      font-weight: bold;
    }
    textarea {
      width: 100%;
      resize: none;
      padding: 8px;
      border-radius: 4px;
      border: 1px solid #ccc;
      margin-top: 5px;
    }
    .back {
      display: block;
      text-align: center;
      margin: 30px auto 0;
      background: #ddd;
      color: #333;
      text-decoration: none;
      padding: 10px 20px;
      width: fit-content;
      border-radius: 8px;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="post-left">
    <?php if (!empty($post['image'])): ?>
      <img src="../assets/uploads/posts/<?= htmlspecialchars($post['image']) ?>" alt="Post Image">
    <?php endif; ?>
  </div>
  <div class="post-right">
    <div class="details">
      <?php if (!empty($post['content'])): ?>
        <p><span class="label">Caption:</span> <?= nl2br(htmlspecialchars($post['content'])) ?></p>
      <?php endif; ?>
      <?php if (!empty($post['song'])): ?>
        <p><span class="label">Song:</span> 🎵 <?= htmlspecialchars($post['song']) ?></p>
      <?php endif; ?>
      <?php if (!empty($post['location'])): ?>
        <p><span class="label">Location:</span> 📍 <?= htmlspecialchars($post['location']) ?></p>
      <?php endif; ?>
      <p><span class="label">Posted on:</span> <?= date('M d, Y H:i', strtotime($post['created_at'])) ?></p>

      <!-- Like Section -->
      <div id="like-section">
        <button id="likeBtn"><?= $liked_by_user ? '❤️ Liked' : '🤍 Like' ?></button>
        <span id="like-count"><?= $total_likes ?> Likes</span>
      </div>

      <!-- Comment Section -->
      <div id="comment-section">
        <div id="comments">Loading comments...</div>
        <textarea id="commentText" rows="2" placeholder="Write a comment..."></textarea>
        <button id="submitComment">Post</button>
      </div>
    </div>
  </div>
</div>

<a href="dashboard.php" class="back">← Back to Dashboard</a>

<script>
$(document).ready(function(){
  let postId = <?= $post_id ?>;

  // Like toggle
  $('#likeBtn').click(function(){
    $.post('like_post.php', { post_id: postId }, function(data){
      if (data.status === 'liked') {
        $('#likeBtn').text('❤️ Liked');
      } else {
        $('#likeBtn').text('🤍 Like');
      }
      $('#like-count').text(data.like_count + ' Likes');
    }, 'json');
  });

  // Load comments
  function loadComments(){
    $.get('get_comments.php', { post_id: postId }, function(comments){
      let html = '';
      if (comments.length > 0) {
        comments.forEach(function(c){
          html += `<div class="comment"><strong>${c.username}</strong>: ${c.comment} <div><small>${new Date(c.created_at).toLocaleString()}</small></div></div>`;
        });
      } else {
        html = '<p>No comments yet.</p>';
      }
      $('#comments').html(html);
    }, 'json');
  }
  loadComments();

  // Submit comment
  $('#submitComment').click(function(){
    let content = $('#commentText').val().trim();
    if (content !== '') {
      $.post('add_comment.php', {
        post_id: postId,
        comment: content
      }, function(){
        $('#commentText').val('');
        loadComments();
      });
    }
  });
});
</script>

</body>
</html>
