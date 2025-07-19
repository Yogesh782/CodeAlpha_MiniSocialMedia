<?php
session_start();
include '../includes/db.php';

if (!isset($_POST['query'])) {
    exit;
}

$query = '%' . trim($_POST['query']) . '%';

// Corrected SQL with 'fullname' instead of 'full_name'
$sql = "SELECT id, username, fullname, profile_pic FROM users 
        WHERE username LIKE ? OR fullname LIKE ? LIMIT 10";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL error: " . $conn->error);
}

$stmt->bind_param("ss", $query, $query);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $pic = !empty($row['profile_pic']) ? "../assets/uploads/profiles/" . $row['profile_pic'] : "default.png";
        echo '
        <div class="suggestion-item" onclick="location.href=\'profile.php?id=' . $row['id'] . '\'">
            <img src="' . htmlspecialchars($pic) . '" alt="Profile">
            <div class="user-info">
                <span class="username">' . htmlspecialchars($row['username']) . '</span>
                <span class="fullname">' . htmlspecialchars($row['fullname']) . '</span>
            </div>
        </div>';
    }
} else {
    echo '<div class="suggestion-item">No users found</div>';
}
?>
