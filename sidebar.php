<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave System Sidebar</title>
    <link rel="stylesheet" type="text/css" href="sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="menu-icon" id="menu-icon">
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
        <div class="sidebar-content">
            <ul>
                <li><a href="emp_dashboard.php" id="show-dashboard"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
<li><a href="employee_profile.php" id="show-profile"><i class="fas fa-user"></i> <span>Profile</span></a></li>
<li><a href="file_leave.php" id="show-file-leave"><i class="fas fa-file-alt"></i> <span>File Leave</span></a></li>
<li><a href="leave_summary.php" id="show-leave-summary"><i class="fas fa-chart-line"></i> <span>Leave Summary</span></a></li>
<li><a href="emp_calendar.php" id="show-leave-calendar"><i class="fas fa-calendar"></i> <span>Leave Calendar</span></a></li>
<li><a href="leave_balance.php" id="show-leave-balance"><i class="fas fa-balance-scale"></i> <span>Leave Balance</span></a></li>
<li><a href="login.php" id="show-logout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>

            </ul>
        </div>
    </div>
    <script src="sidebar.js"></script>
</body>
</html>
