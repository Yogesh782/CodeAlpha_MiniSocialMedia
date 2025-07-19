<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $dob = $_POST['dob'];
    $username = trim($_POST['username']);
    $bio = trim($_POST['bio']);
    $links = trim($_POST['links']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Handle file upload (correct folder path)
    if (!empty($_FILES['profile_pic']['name'])) {
        $target_dir = "../assets/uploads/profiles/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true); // create folder if not exists
        }
        $file_name = time() . '_' . basename($_FILES['profile_pic']['name']);
        $target_file = $target_dir . $file_name;
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file);
        $profile_pic = $file_name;
        $update_pic = ", profile_pic='$profile_pic'";
    } else {
        $update_pic = "";
    }

    $update_password = $password ? ", password='$password'" : "";

    $sql = "UPDATE users 
            SET fullname='$fullname', email='$email', phone='$phone', dob='$dob', 
                username='$username', bio='$bio', links='$links' 
                $update_password 
                $update_pic 
            WHERE id=$user_id";
    $conn->query($sql);
    header("Location: profile.php");
    exit;
}

$res = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile - Jhalak</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #fafafa;
    }
    .main {
      margin: 40px auto;
      max-width: 800px;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    h2 {
      text-align: center;
      margin-bottom: 30px;
    }
    form label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }
    form input, form textarea {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    .btn {
      margin-top: 20px;
      padding: 10px 20px;
      background: #3897f0;
      color: #fff;
      border: none;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
    }
    .btn:hover {
      background: #287dc0;
    }
  </style>
</head>
<body>

<div class="main">
  <h2>Edit Your Profile</h2>
  <form method="POST" enctype="multipart/form-data">
    <label>Full Name</label>
    <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">

    <label>Phone</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">

    <label>Date of Birth</label>
    <input type="date" name="dob" value="<?= htmlspecialchars($user['dob']) ?>">

    <label>Username</label>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

    <label>New Password (leave blank to keep existing)</label>
    <input type="password" name="password">

    <label>Bio</label>
    <textarea name="bio" rows="4"><?= htmlspecialchars($user['bio']) ?></textarea>

    <label>Links</label>
    <input type="text" name="links" value="<?= htmlspecialchars($user['links']) ?>">

    <label>Profile Picture</label>
    <input type="file" name="profile_pic">

    <button type="submit" class="btn">Update Profile</button>
  </form>
</div>

</body>
</html>
