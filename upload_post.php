<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

include '../includes/db.php';
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content']);
    $location = trim($_POST['location']);
    $image = '';

    if (!empty($_FILES['image']['name'])) {
        $upload_dir = "../assets/uploads/posts/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $image = basename($_FILES['image']['name']);
        $target_file = $upload_dir . $image;
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
    }

    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image, location, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isss", $user_id, $content, $image, $location);
    $stmt->execute();

    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload Post</title>
  <style>
    body { font-family: Arial; background: #fafafa; padding: 40px; }
    .form-box {
        background: #fff;
        max-width: 600px;
        margin: auto;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    input, textarea {
        width: 100%;
        padding: 10px;
        margin-top: 10px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    button {
        margin-top: 20px;
        padding: 10px 20px;
        background: #3897f0;
        color: white;
        border: none;
        border-radius: 5px;
        font-weight: bold;
    }
  </style>
</head>
<body>

<div class="form-box">
    <h2>Upload New Post</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Caption / Content</label>
        <textarea name="content" rows="4" required></textarea>

        <label>Location</label>
        <input type="text" name="location" placeholder="Enter location (e.g. Delhi, India)">

        <label>Image (optional)</label>
        <input type="file" name="image">

        <button type="submit">Post</button>
    </form>
</div>

</body>
</html>
