<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

include '../includes/db.php';

$user_id = (int)$_SESSION['user_id'];

// Fetch user data
$res = $conn->query("SELECT * FROM users WHERE id = $user_id");
if (!$res) {
    die("User query failed: " . $conn->error);
}
$user = $res->fetch_assoc();

// Count posts
$post_result = $conn->query("SELECT COUNT(*) AS total_posts FROM posts WHERE user_id = $user_id");
if (!$post_result) {
    die("Post count query failed: " . $conn->error);
}
$post_count = $post_result->fetch_assoc()['total_posts'] ?? 0;


// Count followers
$followers_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS total_followers FROM follows WHERE following_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($followers_count);
$stmt->fetch();
$stmt->close();

// Count following
$following_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS total_following FROM follows WHERE follower_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($following_count);
$stmt->fetch();
$stmt->close();

// Fetch posts
$posts_query = $conn->query("SELECT * FROM posts WHERE user_id = $user_id ORDER BY created_at DESC");
if (!$posts_query) {
    die("Posts fetch query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Jhalak</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <style>
    body { margin: 0; font-family: 'Segoe UI', sans-serif; background-color: #fafafa; }
    .sidebar { width: 250px; background: #fff; position: fixed; top: 0; left: 0; height: 100%; padding-top: 30px; border-right: 1px solid #dbdbdb; }
    .sidebar h2 { font-family: 'Billabong', cursive; font-size: 36px; text-align: center; margin-bottom: 30px; color: #3897f0; }
    .sidebar a { display: block; padding: 15px 30px; color: #333; text-decoration: none; }
    .sidebar a:hover { background-color: #efefef; }
    .main-content { margin-left: 250px; padding: 30px; }
    /* Profile Section Container */
    .profile-header {
  display: flex;
  align-items: center;
  gap: 10px;
}

.profile-header h3 {
  font-size: 24px;
  display: flex;
  align-items: center;
  gap: 15px;
}

/* Icon Button Styling */
.icon-btn {
  color: #333;
  font-size: 20px;
  text-decoration: none;
  transition: color 0.2s;
}

.icon-btn:hover {
  color: #0095f6;
}

.profile-section {
  display: flex;
  align-items: center;
  gap: 30px;
  padding: 30px;
  border-bottom: 1px solid #ddd;
  background: #fff;
  flex-wrap: wrap;
}

/* Profile Image */
.profile-pic {
  width: 150px;
  height: 150px;
  object-fit: cover;
  border-radius: 50%;
  border: 2px solid #ddd;
}

/* Info Section */
.profile-info {
  flex: 1;
  min-width: 250px;
}

.profile-info h3 {
  font-size: 24px;
  margin-bottom: 10px;
}

/* Stats Section */
.stats {
  display: flex;
  gap: 20px;
  font-size: 16px;
  font-weight: 500;
  margin: 10px 0;
}

/* Bio Section */
.bio p {
  margin: 5px 0;
  font-size: 15px;
  line-height: 1.4;
}

.bio a {
  color: #0095f6;
  text-decoration: none;
  font-weight: 500;
}

/* Buttons */
.logout-btn {
  display: inline-block;
  padding: 8px 16px;
  background-color: #efefef;
  color: #333;
  text-decoration: none;
  font-weight: bold;
  border-radius: 4px;
  margin-right: 10px;
  margin-top: 10px;
  transition: background 0.3s;
}

.logout-btn:hover {
  background-color: #ddd;
}

.edit-btn {
  background-color: #0095f6;
  color: white;
}

.edit-btn:hover {
  background-color: #0077cc;
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

  </style>
</head>
<body>

<div class="sidebar">
  <h2>SocialMedia</h2>
  <a href="dashboard.php">Home</a>
  <a href="search.php">Search</a>
  <a href="#">Explore</a>
  <a href="#">Reels</a>
  <a href="#">Messages</a>
  <a href="#">Notifications</a>
  <a href="upload_post.php">Create Post</a>
  <a href="user_profile.php">My Profile</a>
  <a href="followers.php">Followers</a>
  <a href="logout.php" class="logout-btn">Logout</a>
</div>

<div class="main-content">
  <!-- Profile Info -->
  <div class="profile-section">
    <img src="../assets/uploads/profiles/<?= htmlspecialchars($user['profile_pic'] ?: 'default.jpg') ?>" class="profile-pic" alt="Profile Picture">
    <div class="profile-info">
        <div class="profile-header">
  <h3>
    <?= htmlspecialchars($user['username']) ?>
    <a href="messages.php" class="icon-btn" title="Messages">
      <i class="fas fa-comment-dots"></i>
    </a>
    <a href="settings.php" class="icon-btn" title="Settings">
      <i class="fas fa-cog"></i>
    </a>
  </h3>
</div>

      <h3><?= htmlspecialchars($user['username']) ?></h3>
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
      </div>
      <a href="logout.php" class="logout-btn">Logout</a>
      <a href="edit_profile.php" class="logout-btn edit-btn">Edit Profile</a>
    </div>
  </div>

<!-- Posts -->
<div class="posts">
  <h2>Your Posts</h2>
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

</body>
</html>
