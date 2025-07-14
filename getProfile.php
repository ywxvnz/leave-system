<?php
if (isset($_GET['employee_id'])) {
    $employee_id = $_GET['employee_id'];

    if (!filter_var($employee_id, FILTER_VALIDATE_INT)) {
        http_response_code(400); 
        echo "Invalid employee ID.";
        exit;
    }

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "leavemanagementsystem";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        http_response_code(500); 
        error_log("Connection failed: " . $conn->connect_error);
        echo "An error occurred. Please try again later.";
        exit;
    }

    $sql = "SELECT employee_id, username, email, department, position FROM employees WHERE employee_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        http_response_code(500); // Internal server error
        echo "An error occurred. Please try again later.";
        exit;
    }

    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();

        if (isset($_GET['update']) && $_GET['update'] == 'true') {
            echo json_encode($employee);  // Send data back as JSON
        } else {
            echo "<h3>Name: " . htmlspecialchars($employee['username']) . "</h3>";
            echo "<p>Email: " . htmlspecialchars($employee['email']) . "</p>";
            echo "<p>Department: " . htmlspecialchars($employee['department']) . "</p>";
            echo "<p>Position: " . htmlspecialchars($employee['position']) . "</p>";
        }
    } else {
        http_response_code(404); 
        echo "Employee not found.";
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(400); 
    echo "No employee ID provided.";
}
?>
