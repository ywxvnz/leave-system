<?php
include 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'HR') {
  header("Location: index.php");  
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Leave Filing System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <link rel="stylesheet" href="sidebar.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="hr_dashboard.css">
</head>
<body>

  <!-- Include Sidebar -->
  <?php include 'hr_sidebar.html'; ?>

  <!-- Main Dashboard Content -->
  <div id="main-content">
    <h1>Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>

    <!-- Divider Line -->
    <hr class="divider">

    <!-- Action Buttons -->
    <div class="action-buttons">
      <a href="manage_leave.php" class="btn">Leave Management</a>
      <a href="leave_calendar.php" class="btn">Leave Calendar</a>
      <a href="manage_emp.php" class="btn">Employee Management</a>
    </div>
    
    <!-- Placeholder for Dashboard Content -->
    <div id="dashboard-content">
      <p>Check for any pending leave requests or approvals.</p>
    </div>
  </div>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.getElementById('sidebar');
    const menuIcon = document.getElementById('menu-icon');
    const mainContent = document.querySelector('.main-content');

    menuIcon.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    });
  });
</script>
</body>
</html>
