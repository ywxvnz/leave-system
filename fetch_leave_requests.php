<?php
$servername = "localhost";
$username = "root"; // Change if needed
$password = ""; // Change if needed
$dbname = "leavemanagementsystem";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT name, start_date, end_date FROM leave_requests WHERE status = 'Approved'";
$result = $conn->query($sql);

$leave_dates = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $leave_dates[] = [
            "name" => $row["name"],
            "start_date" => $row["start_date"],
            "end_date" => $row["end_date"]
        ];
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($leave_dates);
?>