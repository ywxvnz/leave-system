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
// Database Connection
$conn = new mysqli("localhost", "root", "", "leavemanagementsystem");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch leave requests
$sql = "SELECT name, leave_request_id, id, leave_type_id, start_date, end_date, status FROM leave_requests";
$result = $conn->query($sql);

$leaveData = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $leaveData[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Leave Calendar </title>
    <link rel="stylesheet" href="leavecalendar.css">
    <link rel="stylesheet" href="hr_dashboard.css">
    <script src="emp_calendar.js" defer></script>
</head>
<body>
    <?php include 'sidebar.html'; ?>

        <div class="container">
            <div class="calendar">
                <div class="calendar-header">
                    <span class="month-picker" id="month-picker"> May </span>
                    <div class="year-picker" id="year-picker">
                        <span class="year-change" id="pre-year">
                            <pre><</pre>
                        </span>
                        <span id="year"> 2025 </span>
                        <span class="year-change" id="next-year">
                            <pre>></pre>
                        </span>
                    </div>
                </div>

                <div class="calendar-body">
                    <div class="calendar-week-days">
                        <div>Sun</div>
                        <div>Mon</div>
                        <div>Tue</div>
                        <div>Wed</div>
                        <div>Thu</div>
                        <div>Fri</div>
                        <div>Sat</div>
                    </div>
                    <div class="calendar-days">
                    </div>
                </div>
                <div class="calendar-footer">
                </div>
                <div class="date-time-formate">
                    <div class="day-text-formate"> TODAY </div>
                    <div class="date-time-value">
                        <div class="time-formate"> 02:51:20</div>
                        <div class="date-formate"> 27 - July - 2025 </div>
                    </div>
                    <div class="selected-date-display"></div>
                </div>
                <div class="month-list"></div>
            </div>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const leaveRequests = <?php echo json_encode($leaveData); ?>;

                console.log(leaveRequests); // Debugging: Check data in the console

                leaveRequests.forEach(request => {
                    highlightLeaveDays(request.start_date, request.end_date, request.status);
                });

                function highlightLeaveDays(startDate, endDate, status) {
                    const start = new Date(startDate);
                    const end = new Date(endDate);
                    const days = document.querySelectorAll('.calendar-days div');

                    days.forEach(day => {
                        const dayDate = new Date(day.getAttribute('data-date')); 
                        if (dayDate >= start && dayDate <= end) {
                            day.style.backgroundColor = status === 'Approved' ? '#4CAF50' : '#FFA500';
                            day.title = `Leave: ${status}`;
                        }
                    });
                }
            });
        </script>
</body>
</html>