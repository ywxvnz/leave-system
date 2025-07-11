<?php
// Check if the employee_id is set in the URL
if (isset($_GET['employee_id'])) {
    $employee_id = $_GET['employee_id'];

    // Sanitize the input to ensure it is a valid integer
    if (!filter_var($employee_id, FILTER_VALIDATE_INT)) {
        http_response_code(400); // Bad request
        echo "Invalid employee ID.";
        exit;
    }

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "leavemanagementsystem";

    // Establish a connection to the database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check the connection
    if ($conn->connect_error) {
        http_response_code(500); // Internal server error
        // Log the error instead of showing it to the user
        error_log("Connection failed: " . $conn->connect_error);
        echo "An error occurred. Please try again later.";
        exit;
    }

    // Prepare SQL query to fetch employee details
    $sql = "SELECT employee_id, username, email, department, position FROM employees WHERE employee_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        http_response_code(500); // Internal server error
        echo "An error occurred. Please try again later.";
        exit;
    }

    // Bind the employee_id parameter and execute the query
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();

        // Return data as JSON if it's for updating (AJAX request)
        if (isset($_GET['update']) && $_GET['update'] == 'true') {
            echo json_encode($employee);  // Send data back as JSON
        } else {
            // Otherwise, display employee details in HTML
            echo "<h3>Name: " . htmlspecialchars($employee['username']) . "</h3>";
            echo "<p>Email: " . htmlspecialchars($employee['email']) . "</p>";
            echo "<p>Department: " . htmlspecialchars($employee['department']) . "</p>";
            echo "<p>Position: " . htmlspecialchars($employee['position']) . "</p>";
        }
    } else {
        http_response_code(404); // Not found
        echo "Employee not found.";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    http_response_code(400); // Bad request
    echo "No employee ID provided.";
}
?>
