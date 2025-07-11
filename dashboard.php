<?php
session_start();

// If the session is not set, redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Prevent caching of the page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Handle the logout logic
if (isset($_POST['logout'])) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy(); // Destroy the session
    }
    header("Location: index.php?form=login"); // Redirect with form=login parameter
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <style>
        body {
            font-family: "Poppins", sans-serif;
            font-weight: 300;
            background-color: #f8f8f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .logout-container {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            font-family: "DM Sans", sans-serif;
            font-weight: 700;
        }

        button {
            padding: 10px 20px;
            margin: 10px;
            border: none;
            border-radius: 5px;
            background-color: #000;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #333;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <h1>Do you want to log out?</h1>
        <form method="post">
            <button type="submit" name="logout" value="yes">Yes</button>
            <button type="button" id="no-logout">No</button>
        </form>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const noLogoutButton = document.getElementById('no-logout');

            noLogoutButton.addEventListener('click', function() {
                // Redirect to the dashboard or home page
                window.location.href = 'employee_dashboard.php';
            });
        });
    </script>
</body>
</html>
