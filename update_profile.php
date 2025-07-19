<?php
session_start();

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $bio = mysqli_real_escape_string($conn, trim($_POST['bio']));
    $links = mysqli_real_escape_string($conn, trim($_POST['links']));
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Upload profile picture if a new one is uploaded
    if (!empty($_FILES['profile_pic']['name'])) {
        $upload_dir = "../assets/uploads/profiles/"; // Corrected path
        $profile_pic_name = time() . "_" . basename($_FILES["profile_pic"]["name"]);
        $target_file = $upload_dir . $profile_pic_name;

        // Ensure directory exists
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
            $update_pic = ", profile_pic = '$profile_pic_name'";
        } else {
            $update_pic = "";
        }
    } else {
        $update_pic = "";
    }

    $update_password = $password ? ", password = '$password'" : "";

    $sql = "UPDATE users SET 
            fullname = '$fullname',
            email = '$email',
            phone = '$phone',
            dob = '$dob',
            username = '$username',
            bio = '$bio',
            links = '$links'
            $update_password
            $update_pic
            WHERE id = $user_id";

    if ($conn->query($sql)) {
        header("Location: profile.php?success=1");
        exit;
    } else {
        echo "Error updating profile: " . $conn->error;
    }
}
?>
