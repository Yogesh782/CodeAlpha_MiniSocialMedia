<?php
session_start();
include 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: user/dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $res = $conn->query("SELECT * FROM users WHERE username = '$username' OR email = '$username'");
    if ($res->num_rows == 1) {
        $row = $res->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['fullname'];
            $_SESSION['welcome'] = true;
        } else {
            $login_error = "Invalid password.";
        }
    } else {
        $login_error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | Jhalak</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', sans-serif;
      background: radial-gradient(circle at top left, #ffffff, #e0f7fa);
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .container {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-box {
      background-color: #ffffff;
      padding: 40px 35px;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 420px;
    }

    .login-box h2 {
      text-align: center;
      font-size: 28px;
      color: #1e2a38;
      margin-bottom: 15px;
    }

    .login-box p {
      text-align: center;
      color: #777;
      font-size: 14px;
      margin-bottom: 30px;
    }

    .input-group {
      position: relative;
      margin-bottom: 20px;
    }

    .input-group input {
      width: 100%;
      padding: 14px 16px 14px 44px;
      border: 1px solid #ccc;
      border-radius: 8px;
      background: #f5f9fc;
      font-size: 14px;
    }

    .input-group i {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: #888;
    }

    .btn {
      width: 100%;
      background-color: #00796b;
      color: white;
      border: none;
      padding: 12px;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.3s ease;
      font-size: 16px;
    }

    .btn:hover {
      background-color: #005f56;
    }

    .bottom-text {
      text-align: center;
      margin-top: 20px;
      font-size: 14px;
    }

    .bottom-text a {
      color: #00796b;
      text-decoration: none;
      font-weight: 600;
    }

    .error-msg {
      color: #d32f2f;
      font-size: 14px;
      text-align: center;
      margin-bottom: 15px;
    }

    .footer {
      text-align: center;
      padding: 15px 0;
      background-color: #f2f2f2;
      font-size: 13px;
      color: #555;
    }

    .footer span {
      color: #00796b;
      font-weight: bold;
    }

    @media (max-width: 480px) {
      .login-box {
        padding: 30px 25px;
      }
    }

    .modal {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background-color: rgba(0,0,0,0.6);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    .modal-content {
      background-color: #fff;
      padding: 40px;
      border-radius: 12px;
      text-align: center;
      animation: pop 0.5s ease-in-out;
      font-size: 22px;
      font-weight: bold;
      color: #333;
      box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }

    @keyframes pop {
      from { transform: scale(0.7); opacity: 0; }
      to   { transform: scale(1); opacity: 1; }
    }
  </style>
</head>
<body>

<?php if (isset($_SESSION['welcome']) && $_SESSION['welcome']): ?>
  <div id="welcomeModal" class="modal">
    <div class="modal-content">
      <h2>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>! 🎉</h2>
    </div>
  </div>
  <script>
    setTimeout(() => {
      window.location.href = "user/dashboard.php";
    }, 3000);
  </script>
  <?php unset($_SESSION['welcome']); ?>
<?php endif; ?>

<div class="container">
  <div class="login-box">
    <h2>Welcome to SocialMedia</h2>
    <p>Your digital world in one glance.</p>

    <?php if (isset($login_error)): ?>
      <div class="error-msg"><?= $login_error ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="input-group">
        <i class="fas fa-envelope"></i>
        <input type="text" name="username" placeholder="Username or Email" required>
      </div>
      <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" name="password" placeholder="Password" required>
      </div>
      <button type="submit" class="btn">Log In</button>
    </form>

    <div class="bottom-text">
      Don't have an account? <a href="register.php">Register Here</a>
    </div>
  </div>
</div>

<div class="footer">
  Made with ❤️ by <span>Yogesh Kumar</span>
</div>

</body>
</html>
