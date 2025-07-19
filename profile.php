<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$current_user_id = (int)$_SESSION['user_id'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "User ID not provided.";
    exit;
}

$profile_user_id = (int)$_GET['id'];

// ✅ Fetch user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $profile_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "User not found.";
    exit;
}

$user = $result->fetch_assoc();

// ✅ Check if current user follows profile user
$is_following = false;
if ($current_user_id !== $profile_user_id) {
    $check_stmt = $conn->prepare("SELECT id FROM follows WHERE follower_id = ? AND following_id = ?");
    $check_stmt->bind_param("ii", $current_user_id, $profile_user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $is_following = $check_result->num_rows > 0;
    $check_stmt->close();
}

// ✅ Post count
$post_count_stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?");
$post_count_stmt->bind_param("i", $profile_user_id);
$post_count_stmt->execute();
$post_count_stmt->bind_result($post_count);
$post_count_stmt->fetch();
$post_count_stmt->close();

// ✅ Followers count (using follows table)
$followers_stmt = $conn->prepare("SELECT COUNT(*) FROM follows WHERE following_id = ?");
$followers_stmt->bind_param("i", $profile_user_id);
$followers_stmt->execute();
$followers_stmt->bind_result($followers_count);
$followers_stmt->fetch();
$followers_stmt->close();

// ✅ Following count (using follows table)
$following_stmt = $conn->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ?");
$following_stmt->bind_param("i", $profile_user_id);
$following_stmt->execute();
$following_stmt->bind_result($following_count);
$following_stmt->fetch();
$following_stmt->close();

// ✅ Fetch user posts
$posts_stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$posts_stmt->bind_param("i", $profile_user_id);
$posts_stmt->execute();
$posts_query = $posts_stmt->get_result();

// ✅ Time formatting
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = [
        'y' => 'year', 'm' => 'month', 'w' => 'week',
        'd' => 'day', 'h' => 'hour', 'i' => 'minute', 's' => 'second'
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
  <title><?= htmlspecialchars($user['username']) ?> - Profile</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

  .main-content { margin-left: 250px; padding: 30px; }
.profile-section {
  display: flex;
  gap: 20px;
  margin-bottom: 30px;
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.1);
}

.profile-pic {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
}

.profile-info {
  flex: 1;
}

.profile-header h3 {
  display: flex;
  align-items: center;
  gap: 10px;
}

.icon-btn {
  color: #555;
  font-size: 18px;
  margin-left: 10px;
  text-decoration: none;
}

.stats {
  display: flex;
  gap: 20px;
  margin-top: 10px;
  font-weight: 500;
}

.bio p {
  margin: 4px 0;
  font-size: 14px;
}

.logout-btn {
  display: inline-block;
  margin-top: 10px;
  padding: 6px 14px;
  background: #e74c3c;
  color: #fff;
  text-decoration: none;
  border-radius: 6px;
}

.edit-btn {
  background: #3498db;
  margin-left: 10px;
}
.follow-btn {
  background: #007bff;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  margin-left: 10px;
}

.follow-btn:hover {
  background: #0056b3;
}

 .posts {
  padding: 20px;
  background-color: #f9f9f9;
}

.posts h2 {
  margin-bottom: 20px;
  font-size: 24px;
  color: #333;
}

.posts .post {
  display: inline-block;
  width: 25%;
  margin: 1%;
  vertical-align: top;
  background-color: #fff;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease;
}

.posts .post:hover {
  transform: scale(1.02);
}

.posts .post img {
  width: 100%;
  height: 400px;
  object-fit: cover;
  display: block;
}

.posts .no-image {
  width: 100%;
  height: 300px;
  background-color: #ccc;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #666;
  font-size: 18px;
}

.posts .timestamp {
  padding: 10px;
  font-size: 14px;
  color: #777;
  text-align: center;
}

/* Responsive Design */
@media screen and (max-width: 768px) {
  .posts .post {
    width: 48%;
  }
}

@media screen and (max-width: 480px) {
  .posts .post {
    width: 98%;
  }

  .posts .post img,
  .posts .no-image {
    height: 220px;
  }
}

@media (max-width: 600px) {
  .profile-section {
    flex-direction: column;
    align-items: flex-start;
  }

  .profile-pic {
    width: 120px;
    height: 120px;
  }

  .profile-info h3 {
    font-size: 20px;
  }

  .stats {
    flex-direction: column;
    gap: 5px;
  }
}


.timestamp {
  padding: 10px;
  font-size: 13px;
  color: #555;
  background: #fafafa;
  text-align: center;
}

/* Responsive */
@media (max-width: 768px) {
  .posts .post {
    width: calc(50% - 20px);
  }
}

@media (max-width: 480px) {
  .posts .post {
    width: 100%;
    margin: 10px 0;
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
  <div class="profile-section">
    <img src="../assets/uploads/profiles/<?= htmlspecialchars($user['profile_pic'] ?: 'default.jpg') ?>" class="profile-pic" alt="Profile Picture">

    <div class="profile-info">
      <div class="profile-header">
        <h3>
          @<?= htmlspecialchars($user['username']) ?>

          <?php if ($current_user_id !== $profile_user_id): ?>
            <button id="followBtn" class="follow-btn" data-user-id="<?= $profile_user_id ?>">
              <?= $is_following ? 'Unfollow' : 'Follow' ?>
            </button>
          <?php endif; ?>

          <a href="messages.php" class="icon-btn" title="Messages"><i class="fas fa-comment-dots"></i></a>
          <a href="settings.php" class="icon-btn" title="Settings"><i class="fas fa-cog"></i></a>
        </h3>
      </div>

      <div class="stats">
        <div><?= $post_count ?> posts</div>
        <div><?= $followers_count ?> followers</div>
        <div><?= $following_count ?> following</div>
      </div>

      <div class="bio">
        <p><strong><?= htmlspecialchars($user['fullname']) ?></strong></p>
        <p><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
        <?php if (!empty($user['links'])): ?>
          <p><a href="<?= htmlspecialchars($user['links']) ?>" target="_blank">🔗 <?= htmlspecialchars($user['links']) ?></a></p>
        <?php endif; ?>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>DOB:</strong> <?= htmlspecialchars($user['dob']) ?></p>
        <p><strong>Joined:</strong> <?= time_elapsed_string($user['created_at']) ?></p>
      </div>
    </div>
  </div>

  <!-- Posts Section -->
  <div class="posts">
    <h2><?= $current_user_id === $profile_user_id ? 'Your Posts' : 'Posts' ?></h2>
    <?php if ($posts_query->num_rows > 0): ?>
      <?php while ($post = $posts_query->fetch_assoc()): ?>
        <div class="post">
          <a href="view_post.php?id=<?= $post['id'] ?>">
            <?php if (!empty($post['image'])): ?>
              <img src="../assets/uploads/posts/<?= htmlspecialchars($post['image']) ?>" alt="Post Image">
            <?php else: ?>
              <div class="no-image">No Image</div>
            <?php endif; ?>
          </a>
          <div class="timestamp">Posted on <?= date('M d, Y H:i', strtotime($post['created_at'])) ?></div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No posts uploaded yet.</p>
    <?php endif; ?>
  </div>
</div>

<script>
document.getElementById("followBtn")?.addEventListener("click", function () {
  const button = this;
  const followUserId = button.dataset.userId;

  fetch("follow_action.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "follow_user_id=" + followUserId
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === "followed") {
      button.textContent = "Unfollow";
    } else if (data.status === "unfollowed") {
      button.textContent = "Follow";
    }
  });
});
</script>
</body>
</html>
