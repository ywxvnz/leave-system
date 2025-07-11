<?php
// Start session
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

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "leavemanagementsystem"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: Unauthorized access. Please log in.");
}

// Get user ID
$user_id = $_SESSION['user_id'];

// Query to get the leave balance from the employees table
$query_leave_balance = "SELECT leaves_allowed FROM employees WHERE employee_id = ?";
$stmt = $conn->prepare($query_leave_balance);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$leaves_allowed = $row['leave_balance'] ?? 30; // Default to 15 if no record found

// Query to get the count of approved leave requests (past and present)
$query_approved = "SELECT COUNT(*) AS leaves_taken 
                   FROM leave_requests 
                   WHERE id = ? 
                   AND status = 'Approved'";

$stmt = $conn->prepare($query_approved);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$leaves_taken = $row['leaves_taken'] ?? 0;

// Query to get the count of pending leave requests
$query_pending = "SELECT COUNT(*) AS pending_requests 
                  FROM leave_requests 
                  WHERE id = ? 
                  AND status = 'Pending'";

$stmt = $conn->prepare($query_pending);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$pending_requests = $row['pending_requests'] ?? 0;

$query_incoming = "SELECT COUNT(*) AS incoming_leave 
                   FROM leave_requests 
                   WHERE id = ? 
                   AND status = 'Approved' 
                   AND start_date > CURDATE()"; 

$stmt = $conn->prepare($query_incoming);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$incoming_leave = $row['incoming_leave'] ?? 0;

// Calculate remaining leaves
$leaves_remaining = $leaves_allowed - $leaves_taken; 

// Ensure remaining leaves cannot be negative
if ($leaves_remaining < 0) {
    $leaves_remaining = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Leave Balance</title>
    <link rel="stylesheet" type="text/css" href="sidebar.css"> <!-- Sidebar CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="balance.css"> <!-- CSS for styling -->
</head>
<body>

<?php include 'sidebar.html'; ?>
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <h1>Employee Leave Balance</h1>
            <table>
                <thead>
                    <tr>
                        <th>Leaves Allowed</th>
                        <th>Leaves Taken</th>
                        <th>Pending Requests</th>
                        <th>Incoming Leave</th>
                        <th>Leaves Remaining</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td id="leaves-allowed"><?php echo $leaves_allowed; ?></td>  <!-- Allowed leaves -->
                        <td id="leaves-taken"><?php echo $leaves_taken; ?></td>     <!-- Approved leaves -->
                        <td id="pending-requests"><?php echo $pending_requests; ?></td> <!-- Pending requests -->
                        <td id="incoming-leave"><?php echo $incoming_leave; ?></td> <!-- Incoming leaves -->
                        <td id="leaves-remaining"><?php echo $leaves_remaining; ?></td>  <!-- Remaining leaves -->
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script src="sidebar.js"></script> <!-- Sidebar JS -->
</body>
</html>
