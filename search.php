<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search Users</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f0f2f5;
      display: flex;
      min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
      width: 220px;
      background-color: #fff;
      border-right: 1px solid #ddd;
      padding: 20px;
      position: fixed;
      height: 100vh;
    }

    .sidebar h2 {
      font-size: 22px;
      margin-bottom: 20px;
      color: #333;
    }

    .sidebar ul {
      list-style: none;
    }

    .sidebar ul li {
      margin: 15px 0;
    }

    .sidebar ul li a {
      color: #333;
      text-decoration: none;
      font-size: 16px;
      display: flex;
      align-items: center;
      transition: 0.2s ease-in-out;
    }

    .sidebar ul li a i {
      margin-right: 10px;
    }

    .sidebar ul li a:hover {
      color: #007bff;
    }

    /* Main content */
    .main-content {
      margin-left: 220px;
      padding: 40px;
      flex: 1;
    }

    .search-box {
      max-width: 600px;
      margin: auto;
      position: relative;
    }

    input[type="text"] {
      width: 100%;
      padding: 14px 18px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 16px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .suggestions {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: #fff;
      border: 1px solid #ccc;
      border-top: none;
      z-index: 1000;
      max-height: 300px;
      overflow-y: auto;
      border-radius: 0 0 6px 6px;
    }

    .suggestion-item {
      padding: 12px;
      cursor: pointer;
      display: flex;
      align-items: center;
      border-bottom: 1px solid #f0f0f0;
      transition: 0.2s;
    }

    .suggestion-item img {
      height: 45px;
      width: 45px;
      border-radius: 50%;
      margin-right: 12px;
      object-fit: cover;
    }

    .suggestion-item:hover {
      background-color: #f5f5f5;
    }

    .user-info span {
      display: block;
    }

    .user-info .username {
      font-weight: bold;
      color: #333;
    }

    .user-info .fullname {
      color: #666;
      font-size: 14px;
    }

    @media (max-width: 768px) {
      .sidebar {
        display: none;
      }

      .main-content {
        margin-left: 0;
        padding: 20px;
      }

      .search-box {
        width: 100%;
      }
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>Dashboard</h2>
  <ul>
    <li><a href="home.php"><i class="fas fa-home"></i> Home</a></li>
    <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
    <li><a href="search.php"><i class="fas fa-search"></i> Search</a></li>
    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="search-box">
    <input type="text" id="search" placeholder="Search by username or full name...">
    <div class="suggestions" id="suggestions"></div>
  </div>
</div>

<!-- JavaScript -->
<script>
document.getElementById("search").addEventListener("keyup", function () {
  const query = this.value.trim();

  if (query.length < 2) {
    document.getElementById("suggestions").innerHTML = "";
    return;
  }

  const xhr = new XMLHttpRequest();
  xhr.open("POST", "search_user.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

  xhr.onload = function () {
    document.getElementById("suggestions").innerHTML = this.responseText;
  };

  xhr.send("query=" + encodeURIComponent(query));
});
</script>

</body>
</html>
