<?php
session_start();
include 'includes/db.php';

$success = '';
$error = '';
$suggestions = [];

function generateUsernameSuggestions($username, $conn) {
    $suggestions = [];
    for ($i = 1; $i <= 5; $i++) {
        $new_username = $username . rand(10, 999);
        $check = $conn->query("SELECT id FROM users WHERE username = '$new_username'");
        if ($check->num_rows == 0) {
            $suggestions[] = $new_username;
        }
    }
    return $suggestions;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_or_phone = trim($_POST['email_or_phone']);
    $fullname = trim($_POST['fullname']);
    $dob = $_POST['dob'];
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($email_or_phone) || empty($fullname) || empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Please fill all required fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $check_username = $conn->query("SELECT * FROM users WHERE username = '$username'");
        if ($check_username->num_rows > 0) {
            $error = "Username already taken.";
            $suggestions = generateUsernameSuggestions($username, $conn);
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $sql = "INSERT INTO users (fullname, email, phone, dob, username, password, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);

            $email = filter_var($email_or_phone, FILTER_VALIDATE_EMAIL) ? $email_or_phone : null;
            $phone = $email ? null : $email_or_phone;

            $stmt->bind_param("ssssss", $fullname, $email, $phone, $dob, $username, $hashed_password);
            if ($stmt->execute()) {
                $success = "Account created successfully. <a href='index.php'>Log in now</a>.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register | Jhalak</title>
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
      padding: 40px 15px;
    }

    .register-box {
      background-color: #ffffff;
      padding: 40px 35px;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 460px;
    }

    .register-box h2 {
      text-align: center;
      font-size: 28px;
      color: #1e2a38;
      margin-bottom: 15px;
    }

    .register-box p {
      text-align: center;
      color: #777;
      font-size: 14px;
      margin-bottom: 30px;
    }

    .input-group {
      position: relative;
      margin-bottom: 18px;
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

    .message {
      text-align: center;
      margin-bottom: 15px;
      font-size: 14px;
      color: #d32f2f;
    }

    .message.success {
      color: green;
    }

    .suggestions {
      background: #f1f1f1;
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 15px;
    }

    .suggestions span {
      display: inline-block;
      margin: 5px 5px 0 0;
      background: #e0e0e0;
      padding: 6px 10px;
      border-radius: 3px;
      cursor: pointer;
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
      .register-box {
        padding: 30px 25px;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <div class="register-box">
    <h2>Create Account</h2>
    <p>Join SocialMedia– your digital world starts here.</p>

    <?php if ($error): ?>
      <div class="message"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="message success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="input-group">
        <i class="fas fa-envelope"></i>
        <input type="text" name="email_or_phone" placeholder="Email or Phone" required>
      </div>
      <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" name="fullname" placeholder="Full Name" required>
      </div>
      <div class="input-group">
        <i class="fas fa-calendar"></i>
        <input type="date" name="dob" required>
      </div>
      <div class="input-group">
        <i class="fas fa-user-tag"></i>
        <input type="text" name="username" placeholder="Username" required>
      </div>

      <?php if (!empty($suggestions)): ?>
        <div class="suggestions">
          Try:
          <?php foreach ($suggestions as $name): ?>
            <span onclick="document.querySelector('[name=username]').value='<?= $name ?>'"><?= $name ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" name="password" placeholder="Password" required>
      </div>
      <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
      </div>

      <button type="submit" name="register" class="btn">Register</button>
    </form>

    <div class="bottom-text">
      Already have an account? <a href="index.php">Log In</a>
    </div>
  </div>
</div>

<div class="footer">
  Made with ❤️ by <span>Yogesh Kumar</span>
</div>

</body>
</html>
