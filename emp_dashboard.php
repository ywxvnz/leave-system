<?php
// Start the session
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

// Redirect to login.php if the user is not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Dashboard</title>
  <link rel="stylesheet" href="emp_dashboard.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'sidebar.php'; ?>

  <!-- Main Dashboard Content -->
  <div id="main-content">
    <h1>Hi, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
    
    <!-- Action Buttons -->
    <div class="action-buttons">
      <button class="btn" onclick="location.href='file_leave.php'">File Leave</button>
      <button class="btn" onclick="location.href='leave_summary.php'">Leave Summary</button>
      <button class="btn" onclick="location.href='emp_calendar.php'">Leave Calendar</button>
      <button class="btn" onclick="location.href='leave_balance.php'">Leave Balance</button>
    </div>
    
    <!-- Placeholder for Dashboard Content -->
    <div id="dashboard-content">
      <p>Select an option to manage your leave details.</p>
    </div>
  </div>

</body>
</html>

