<?php
// Start session
session_start();

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

$user_id = $_SESSION['user_id'];

// Fetch user's full name from the database
$user_full_name = "";
$sql = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $user_full_name = $row['username'];
}
$stmt->close();

// Fetch leave types from the database
$leave_types = [];
$sql = "SELECT * FROM leave_type";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $leave_types[] = $row;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $leave_type = intval($_POST['leave-type']);
    $start_date = $_POST['start-date'];
    $end_date = $_POST['end-date'];
    $reason = htmlspecialchars(trim($_POST['reason']));

    // Step 1: Find overlapping leave requests with the same leave type and check if status is "pending"
    $sql = "SELECT * FROM leave_requests 
            WHERE id = ? 
            AND leave_type_id = ? 
            AND status = 'Pending' 
            AND (
                (start_date BETWEEN ? AND ?) OR
                (end_date BETWEEN ? AND ?) OR
                (start_date <= ? AND end_date >= ?)
            )";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissssss", $user_id, $leave_type, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Step 2: Merge overlapping leave requests
        $existing_requests = [];
        while ($row = $result->fetch_assoc()) {
            $existing_requests[] = $row;
        }

        // Determine the new merged date range
        $min_start_date = $start_date;
        $max_end_date = $end_date;
        $updated_reason = $reason;

        foreach ($existing_requests as $request) {
            if ($request['start_date'] < $min_start_date) {
                $min_start_date = $request['start_date'];
            }
            if ($request['end_date'] > $max_end_date) {
                $max_end_date = $request['end_date'];
            }
            $updated_reason .= " / " . $request['reason'];
        }

        // Calculate the new duration
        $leave_duration = (strtotime($max_end_date) - strtotime($min_start_date)) / (60 * 60 * 24) + 1;

        // Update the earliest leave request with the new merged data
        $existing_request_id = $existing_requests[0]['leave_request_id'];
        $update_sql = "UPDATE leave_requests SET start_date = ?, end_date = ?, leave_duration = ?, reason = ? WHERE leave_request_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssisi", $min_start_date, $max_end_date, $leave_duration, $updated_reason, $existing_request_id);
        $update_stmt->execute();

        // Delete other merged leave requests (except the one we updated)
        $merged_ids = array_column($existing_requests, 'leave_request_id');
        array_shift($merged_ids); // Remove the first one since we updated it

        if (!empty($merged_ids)) {
            $placeholders = implode(',', array_fill(0, count($merged_ids), '?'));
            $delete_sql = "DELETE FROM leave_requests WHERE leave_request_id IN ($placeholders)";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param(str_repeat('i', count($merged_ids)), ...$merged_ids);
            $delete_stmt->execute();
        }

        echo json_encode(["status" => "success", "message" => "Leave request merged successfully."]);
    } else {
        // No overlapping dates - Insert as a new leave request
        $leave_duration = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;

        $insert_sql = "INSERT INTO leave_requests (name, id, leave_type_id, start_date, end_date, leave_duration, reason) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("siissis", $user_full_name, $user_id, $leave_type, $start_date, $end_date, $leave_duration, $reason);

        if ($insert_stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Leave request submitted successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error: Unable to submit leave request. Please try again."]);
        }
        $insert_stmt->close();
    }

    $stmt->close();
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Leave</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="sidebar.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <style>
        /* General Styles */
        body {
            font-family: 'Poppins', sans-serif;
            color: #222;
            background-color: #fffdf5;
            margin: 0;
            padding: 0;
            display: flex;
            overflow-x: hidden;
        }

        * {
            box-sizing: border-box;
        }

        #main-content {
            margin-left: 250px;
            padding: 30px;
            width: calc(100% - 250px);
            background-color: #e2dbd0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .form-container {
            background-color: #ffffff;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.12);
            width: 100%;
            max-width: 480px;
            transition: all 0.3s ease-in-out;
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #0d4b18;
            font-size: 24px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #222;
            font-size: 15px;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #bbb;
            border-radius: 6px;
            color: #222;
            background-color: #fff;
        }

        input:focus, select:focus, textarea:focus {
            border-color: #4a772d;
            outline: none;
            box-shadow: 0 0 5px rgba(74, 119, 45, 0.3);
        }

        .btn-submit {
            background-color: #4a772d;
            color: #fff;
            padding: 14px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease-in-out;
        }

        .btn-submit:hover {
            background-color: #103713;
        }

        /* Small Green Modal Styles */
        #message-modal {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #4a772d;
            color: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            font-size: 14px;
        }

        #message-modal.show {
            display: block;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

    <div id="main-content">
        <div class="form-container">
            <h2>Leave Request</h2>

            <!-- Leave form -->
            <form id="file-leave-form" method="POST" action="file_leave.php">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" value="<?= htmlspecialchars($user_full_name); ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="leave-type">Leave Type</label>
                    <select id="leave-type" name="leave-type" required>
                        <option value="" disabled selected>Select leave type</option>
                        <?php
                            foreach ($leave_types as $leave) {
                                echo "<option value='" . $leave['leave_type_id'] . "'>" . $leave['leave_type_name'] . "</option>";
                            }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="start-date">Start Date</label>
                    <input type="date" id="start-date" name="start-date" required>
                </div>

                <div class="form-group">
                    <label for="end-date">End Date</label>
                    <input type="date" id="end-date" name="end-date" required>
                </div>

                <div class="form-group">
                    <label for="reason">Reason</label>
                    <textarea id="reason" name="reason" rows="4" placeholder="Enter the reason for leave" required></textarea>
                </div>

                <button type="submit" class="btn-submit">Submit Leave Request</button>
            </form>
        </div>
    </div>

    <!-- Small Green Modal for Messages -->
    <div id="message-modal"></div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const leaveTypeInput = document.getElementById("leave-type");
            const startDateInput = document.getElementById("start-date");
            const endDateInput = document.getElementById("end-date");

            function getFormattedDate(date) {
                return date.getFullYear() + "-" +
                       String(date.getMonth() + 1).padStart(2, '0') + "-" +
                       String(date.getDate()).padStart(2, '0');
            }

            function updateDateRestrictions() {
                const selectedLeaveID = leaveTypeInput.value; // Get selected leave ID
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                let tomorrow = new Date(today);
                tomorrow.setDate(today.getDate() + 1);

                if (selectedLeaveID === "1" || selectedLeaveID === "2") {
                    startDateInput.removeAttribute("min"); // Allow past dates for Sick & Emergency Leave
                } else {
                    startDateInput.setAttribute("min", getFormattedDate(tomorrow)); // Restrict past dates for other leaves
                }

                if (startDateInput.value) {
                    const startDate = new Date(startDateInput.value);
                    let minEndDate = new Date(startDate);
                    minEndDate.setDate(startDate.getDate() + 1);
                    endDateInput.setAttribute("min", getFormattedDate(minEndDate));
                }
            }

            function validateEndDate() {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);

                if (endDate < startDate) {
                    alert("End date cannot be before the start date.");
                    endDateInput.value = ""; 
                }
            }

            leaveTypeInput.addEventListener("change", updateDateRestrictions);
            startDateInput.addEventListener("change", updateDateRestrictions);
            endDateInput.addEventListener("change", validateEndDate);

            updateDateRestrictions(); 

            // Handle form submission via AJAX
            const form = document.getElementById("file-leave-form");
            form.addEventListener("submit", function (e) {
                e.preventDefault(); // Prevent the default form submission

                const formData = new FormData(form);

                fetch("file_leave.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    showModal(data.message); // Show message in modal
                    if (data.status === "success") {
                        form.reset(); // Reset the form
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    showModal("An error occurred. Please try again.");
                });
            });

            // Modal functionality
            const modal = document.getElementById("message-modal");

            function showModal(message) {
                modal.textContent = message; // Set the message
                modal.classList.add("show"); // Show the modal

                // Hide the modal after 3 seconds
                setTimeout(() => {
                    modal.classList.remove("show");
                }, 3000);
            }
        });
    </script>
</body>
</html>