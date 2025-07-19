<?php
session_start();
session_unset();
session_destroy();

// Prevent back navigation after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect to login (index) page
header("Location: ../index.php");
exit;
?>
