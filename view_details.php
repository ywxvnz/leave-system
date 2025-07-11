<?php
include 'db_connection.php';

if (isset($_GET['leave_request_id'])) {
    $leave_request_id = $_GET['leave_request_id'];

    // Query to fetch leave request details
    $sql = "SELECT lr.*, lt.leave_type_name, lt.priority_level, u.username
            FROM leave_requests lr
            JOIN leave_type lt ON lr.leave_type_id = lt.leave_type_id
            JOIN users u ON lr.id = u.id
            WHERE lr.leave_request_id = '$leave_request_id'";

    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        // Return the data as a JSON object
        echo json_encode(array(
            'leave_request_id' => $row['leave_request_id'],
            'name' => $row['username'], // Use the username as the employee's name
            'leave_type_name' => $row['leave_type_name'],
            'status' => $row['status'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
            'priority_level' => $row['priority_level'],
            'reason' => $row['reason']
        ));
    } else {
        echo json_encode(array('error' => 'No data found'));
    }
}
?>