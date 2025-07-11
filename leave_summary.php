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

$servername = "localhost";
$username = "root";  // Replace with your database username
$password = "";      // Replace with your database password
$dbname = "leavemanagementsystem";  // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in. Please log in first.");
}

// Fetch leave requests for the logged-in user
$users_id = $_SESSION['user_id'];

// Ensure column name matches database schema
$query = "
    SELECT 
        lr.start_date, 
        lr.end_date, 
        lt.leave_type_name, 
        lr.status, 
        lr.reason 
    FROM leave_requests lr
    JOIN leave_type lt ON lr.leave_type_id = lt.leave_type_id
    WHERE lr.id = ?
    ORDER BY 
        -- Prioritize Approved (1), then Pending (2), then Rejected (3)
        CASE 
            WHEN lr.status = 'Approved' THEN 1
            WHEN lr.status = 'Pending' THEN 2
            WHEN lr.status = 'Rejected' THEN 3
        END ASC,  
        -- Sort by start date (earliest leave first)
        lr.start_date ASC,  
        -- If start date is the same, sort by end date (earliest end date first)
        lr.end_date ASC,  
        -- If start date and end date are the same, sort by priority (lowest number = highest priority)
        lt.priority_level ASC,
        -- If priority level is the same, sort by request date (earliest request first)
        lr.created_at ASC";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param('i', $users_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Summary</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fffdf5;
            display: flex;
        }

        * {
            box-sizing: border-box;
        }
        /* Main content */
        #main-content {
            margin-left: 250px;
            padding: 20px;
            background-color: #e2dbd0;
            width: calc(100% - 250px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        h1 {
            text-align: center;
            color: #103713;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #628b35;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
    <div id="main-content">
        <h1>Leave Summary</h1>
        <table>
            <thead>
                <tr>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Leave Type</th>
                    <th>Status</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['start_date']) ?></td>
                            <td><?= htmlspecialchars($row['end_date']) ?></td>
                            <td><?= htmlspecialchars($row['leave_type_name']) ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                            <td><?= htmlspecialchars($row['reason']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">No leave requests found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
