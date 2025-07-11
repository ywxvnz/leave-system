<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['employee_id']) || empty($_POST['employee_id'])) {
        echo "Error: Employee ID missing";
        exit;
    }

    $employee_id = intval($_POST['employee_id']);

    // Database connection
    $conn = new mysqli("localhost", "root", "", "leavemanagementsystem");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Start transaction
    $conn->autocommit(false); // Disable autocommit

    try {
        // Get user_id associated with the employee
        $checkStmt = $conn->prepare("SELECT user_id FROM employees WHERE employee_id = ? FOR UPDATE");
        $checkStmt->bind_param("i", $employee_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows === 0) {
            echo "Error: Employee not found";
            $conn->rollback(); // Rollback transaction
            exit;
        }

        $row = $result->fetch_assoc();
        $user_id = $row['user_id']; // Store the user_id

        $checkStmt->close();

        // Delete employee
        $deleteEmployeeStmt = $conn->prepare("DELETE FROM employees WHERE employee_id = ?");
        $deleteEmployeeStmt->bind_param("i", $employee_id);

        if ($deleteEmployeeStmt->execute()) {
            // Now delete the user from the users table
            $deleteUserStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $deleteUserStmt->bind_param("i", $user_id);

            if ($deleteUserStmt->execute()) {
                $conn->commit(); // Commit transaction
                echo "Success";
            } else {
                $conn->rollback(); // Rollback if deleting user fails
                echo "Error: Unable to delete user";
            }

            $deleteUserStmt->close();
        } else {
            $conn->rollback(); // Rollback if deleting employee fails
            echo "Error: Unable to delete employee";
        }

        $deleteEmployeeStmt->close();
    } catch (Exception $e) {
        $conn->rollback(); // Rollback in case of error
        echo "Error: " . $e->getMessage();
    }

    $conn->close();
}
?>
