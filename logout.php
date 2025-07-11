<?php
session_start();
session_unset();  // Unset all session variables
session_destroy();  // Destroy the session

// Prevent caching of the page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Return a success response
echo "Logged out successfully";
?>
