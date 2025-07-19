<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

include '../includes/db.php';
$user_id = (int)$_SESSION['user_id'];

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = [
        'y' => 'year', 'm' => 'month', 'w' => 'week',
        'd' => 'day', 'h' => 'hour', 'i' => 'minute', 's' => 'second',
    ];
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Jhalak - Feed</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

 <style>
  body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background-color: #fafafa;
    transition: background-color 0.3s ease;
  }

  /* Sidebar */
  .sidebar {
    width: 250px;
    background: #fff;
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    padding-top: 30px;
    border-right: 1px solid #dbdbdb;
    box-shadow: 2px 0 10px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
  }

  .sidebar h2 {
    font-family: 'Billabong', cursive;
    font-size: 36px;
    text-align: center;
    margin-bottom: 30px;
    color: #3897f0;
    animation: fadeInDown 1s ease;
  }

  .sidebar a {
    display: block;
    padding: 15px 30px;
    color: #333;
    text-decoration: none;
    transition: background-color 0.2s ease, transform 0.2s ease;
  }

  .sidebar a:hover {
    background-color: #f5f5f5;
    transform: translateX(5px);
  }

  /* Main Content */
  .main-content {
    width: 100%;
    max-width: 600px;
    margin: auto;
    padding: 20px;
    animation: fadeInUp 1s ease;
  }

  /* Post Card */
  .post-card {
    border: 1px solid #dbdbdb;
    border-radius: 12px;
    margin-bottom: 20px;
    background-color: #fff;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .post-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
  }

  /* Post Header */
  .post-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 15px;
  }

  .post-header img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
    object-fit: cover;
    transition: transform 0.3s ease;
  }

  .post-header img:hover {
    transform: scale(1.05);
  }

  .post-img {
  width: 100%;
  height: 500px;
  object-fit: cover;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
}

.post-img:hover {
  transform: scale(1.03);
}


  /* Post Actions */
.post-actions {
  display: flex;
  align-items: center;
  margin-left:10px;
  gap: 15px;
  margin-top: 10px;
  font-size: 20px;
}

.icon {
  font-size: 24px;
  cursor: pointer;
  transition: transform 0.3s ease, color 0.3s ease;
}

.icon:hover {
  transform: scale(1.2);
  color: #ff4d4d;
}

.icon-btn {
  background: none;
  border: none;
  padding: 0;
  margin: 0;
}

.liked {
  color: red !important;
}

.like-count {
  margin-left: auto;
  font-size: 15px;
  color: #444;
}


.post-actions form {
  display: inline;
}

/* Icons styling */
.post-actions i {
  cursor: pointer;
  transition: transform 0.3s ease, color 0.3s ease;
}

/* Like button bounce animation */
.post-actions button[name="like"]:active i {
  animation: heart-bounce 0.4s ease;
}

@keyframes heart-bounce {
  0%   { transform: scale(1); }
  25%  { transform: scale(1.3); }
  50%  { transform: scale(0.9); }
  75%  { transform: scale(1.1); }
  100% { transform: scale(1); }
}

/* Hover Effects */
.post-actions i:hover {
  transform: scale(1.2);
  color: #555;
}

/* Like count style */
.post-actions span {
  margin-left: auto;
  font-size: 16px;
  font-weight: 500;
  color: #555;
}


  /* Post Content, Comments, Form */
  .post-content,
  .comments,
  .comment-form {
    padding: 0 15px;
  }

  .comment-form {
    display: flex;
    border-top: 1px solid #dbdbdb;
  }

  .comment-form input {
    flex: 1;
    border: none;
    padding: 10px;
    font-size: 14px;
    outline: none;
    background: transparent;
  }

  .comment-form button {
    border: none;
    background: none;
    color: #3897f0;
    font-weight: bold;
    cursor: pointer;
    padding: 10px;
    transition: color 0.2s ease;
  }

  .comment-form button:hover {
    color: #266db2;
  }

  .timestamp {
    color: gray;
    font-size: 12px;
    margin-top: 4px;
  }

  /* Responsive */
  @media screen and (max-width: 768px) {
    .sidebar {
      display: none;
    }

    .main-content {
      margin-left: 0;
      padding: 10px;
    }
  }

  /* Animations */
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  @keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-20px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .modal-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.modal-content {
  background: white;
  width: 90%;
  max-width: 900px;
  max-height: 90vh;
  overflow-y: auto;
  display: flex;
  flex-direction: row;
  border-radius: 10px;
  padding: 10px;
}

.modal-image {
  flex: 1;
  padding-right: 10px;
}

.modal-image img {
  max-width: 100%;
  border-radius: 10px;
}

.modal-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.close-btn {
  position: absolute;
  top: 10px;
  right: 15px;
  font-size: 24px;
  cursor: pointer;
  color: white;
}

@media (max-width: 768px) {
  .modal-content {
    flex-direction: column;
    padding: 0;
  }
  .modal-image {
    padding: 0;
  }
}

</style>

</head>
<body>

<div class="sidebar">
  <h2>SocialMedia</h2>
  <a href="dashboard.php"><i class="fa fa-home"></i> Home</a>
  <a href="search.php"><i class="fa fa-search"></i> Search</a>
  <a href="#"><i class="fa fa-compass"></i> Explore</a>
  <a href="#"><i class="fa fa-clapperboard"></i> Reels</a>
  <a href="#"><i class="fa fa-envelope"></i> Messages</a>
  <a href="#"><i class="fa fa-bell"></i> Notifications</a>
  <a href="upload_post.php"><i class="fa fa-plus"></i> Create Post</a>
  <a href="user_profile.php"><i class="fa fa-user"></i> My Profile</a>
  <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
<?php
$q = $conn->query("SELECT posts.*, users.username, users.profile_pic, users.id AS post_user_id 
                   FROM posts 
                   JOIN users ON posts.user_id = users.id 
                   ORDER BY posts.created_at DESC");

while ($post = $q->fetch_assoc()):
  $post_id = $post['id'];
  $like_count = $conn->query("SELECT COUNT(*) AS total FROM likes WHERE post_id = $post_id")->fetch_assoc()['total'];
  $liked = $conn->query("SELECT 1 FROM likes WHERE user_id = $user_id AND post_id = $post_id")->num_rows > 0;
  $comment_res = $conn->query("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = $post_id ORDER BY created_at DESC");
  $postUserId = $post['post_user_id'];
?>

<div class="post-card">

  <!-- Header -->
  <div class="post-header">
    <div style="display:flex; align-items:center;">
      <img src="../assets/uploads/profiles/<?= $post['profile_pic'] ?: 'default.jpg' ?>">
      <div>
        <strong>@<?= htmlspecialchars($post['username']) ?></strong><br>
        <small style="color:gray;"><?= $post['location'] ?> • <?= time_elapsed_string($post['created_at']) ?></small>
      </div>
    </div>

    <!-- Follow Button -->
   <!-- Follow/Unfollow Button -->
<?php if ($user_id != $postUserId): ?>

  <?php
    // Secure follow status check using prepared statement
    $stmt = $conn->prepare("SELECT 1 FROM follows WHERE follower_id = ? AND following_id = ?");
    $stmt->bind_param("ii", $user_id, $postUserId);
    $stmt->execute();
    $stmt->store_result();
    $isFollowing = $stmt->num_rows > 0;
    $stmt->close();
  ?>

  <form method="post" action="follow_action.php">
    <input type="hidden" name="follower_id" value="<?= htmlspecialchars($user_id) ?>">
    <input type="hidden" name="following_id" value="<?= htmlspecialchars($postUserId) ?>">
    <button type="submit" name="<?= $isFollowing ? 'unfollow' : 'follow' ?>"
      style="padding:5px 12px; border:none; border-radius:20px; background:<?= $isFollowing ? '#e0e0e0' : '#3897f0' ?>; color:<?= $isFollowing ? '#000' : '#fff' ?>;">
      <?= $isFollowing ? '✔ Following' : '➕ Follow' ?>
    </button>
  </form>

<?php endif; ?>

  </div>

  <!-- Image -->
  <?php if (!empty($post['image'])): ?>
  <img src="../assets/uploads/posts/<?= htmlspecialchars($post['image']) ?>" class="post-img">
  <?php endif; ?>

  <!-- Actions -->
 <div class="post-actions">
  <form action="like_comment_handler.php" method="POST" style="display:inline;">
    <input type="hidden" name="post_id" value="<?= $post_id ?>">
    <button name="like" class="icon-btn">
      <i class="<?= $liked ? 'fas' : 'far' ?> fa-heart icon <?= $liked ? 'liked' : '' ?>"></i>
    </button>
  </form>

  <i class="far fa-comment icon"></i>
  <i class="far fa-paper-plane icon"></i>

  <span class="like-count"><?= $like_count ?> likes</span>
</div>


  <!-- Content -->
  <div class="post-content">
    <strong>@<?= htmlspecialchars($post['username']) ?></strong>
    <?= nl2br(htmlspecialchars($post['content'])) ?>
    <div class="timestamp"><?= time_elapsed_string($post['created_at']) ?></div>
  </div>

  <!-- Comments -->
  <!-- Single Latest Comment -->
  <div class="comments">
    <?php if ($latest_comment = $comment_res->fetch_assoc()): ?>
      <div class="comment">
        <strong>@<?= htmlspecialchars($latest_comment['username']) ?></strong>
        <?= htmlspecialchars($latest_comment['comment']) ?>
      </div>
    <?php endif; ?>

    <!-- View All Comments Trigger -->
    <a href="#" class="view-all-comments" onclick="openCommentsModal(<?= $post_id ?>)">View all <?= $total_comments ?> comments</a>
  </div>

  <!-- Comment Box (main page) -->
  <form class="comment-form" action="like_comment_handler.php" method="POST">
    <input type="hidden" name="post_id" value="<?= $post_id ?>">
    <input type="text" name="comment" placeholder="Add a comment..." required>
    <button name="add_comment">Post</button>
  </form>

</div>

<?php endwhile; ?>
</div>

<!-- Modal Container -->
<div id="comments-modal" class="modal-overlay" style="display:none;">
  <div class="modal-content">
    <span class="close-btn" onclick="closeCommentsModal()">&times;</span>
    <div id="modal-data">Loading...</div>
  </div>
</div>


<script>
function openCommentsModal(postId) {
  document.getElementById("comments-modal").style.display = "flex";

  // Load content via AJAX
  const xhr = new XMLHttpRequest();
  xhr.open("GET", "load_post_modal.php?post_id=" + postId, true);
  xhr.onload = function() {
    if (xhr.status === 200) {
      document.getElementById("modal-data").innerHTML = xhr.responseText;
    } else {
      document.getElementById("modal-data").innerHTML = "Error loading data";
    }
  };
  xhr.send();
}

function closeCommentsModal() {
  document.getElementById("comments-modal").style.display = "none";
}
</script>


</body>
</html>
