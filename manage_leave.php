<?php
include 'db_connection.php';
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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'Please log in to continue.';
    header("Location: login.php");
    exit();
}

// Handle approve or reject requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['leave_request_id'])) {
    $leave_request_id = $_POST['leave_request_id'];
    $status = '';

    if (isset($_POST['approve_leave'])) {
        $status = 'Approved'; // Set status to approved
    } elseif (isset($_POST['reject_leave'])) {
        $status = 'Rejected'; // Set status to rejected
    }

    // Update the status in the database
    $sql = "UPDATE leave_requests SET status = '$status' WHERE leave_request_id = '$leave_request_id'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_message'] = 'Leave request has been successfully processed!';
        header("Location: manage_leave.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management</title>
    <link rel="stylesheet" href="hr_dashboard.css">
    <link rel="stylesheet" href="sidebar.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
    .modal {
        display: none; 
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(255, 253, 245, 0.9); 
    }
    .modal-content {
        background-color: #103713; 
        margin: 10% auto;
        padding: 20px;
        border-radius: 10px;
        width: 60%;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        color: white;
    }
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
    .approve-btn {
        background-color: #628B35;
        color: white;
        padding: 10px 20px;
        border: none;
        cursor: pointer;
        font-size: 16px;
        border-radius: 5px;
        margin-top: 10px;
    }

    .approve-btn:hover {
        background-color: #628B35;
    }
    .reject-btn {
        background-color: #D32F2F; 
        color: white; 
        padding: 10px 20px;
        border: none;
        cursor: pointer;
        font-size: 16px;
        border-radius: 5px;
        margin-top: 10px;
        margin-left: 10px;
    }

    .reject-btn:hover {
        background-color: #B71C1C;
    }

    .view-details {
        padding: 15px 30px;
        background-color: #628b35; 
        color: white; 
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        height: 50px;
    }

    .view-details:hover {
        background-color: #103713;
    }

    .view-details:focus {
        outline: none;
    }

    .success-prompt {
        display: none;
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #4CAF50;
        color: white;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        z-index: 1000;
    }
    </style>
</head>
<body>

<?php include 'hr_sidebar.html'; ?>

<div id="main-content">
    <h1>Leave Management</h1>
    <p class="prompt">Manage leave requests and view details of each submission below:</p>

    <hr class="divider">

    <div class="table-container">
        <table class="leave-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Name</th>
                    <th>Leave Type</th>
                    <th>Leave Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT lr.*, lt.leave_type_name, lt.priority_level, u.username
                FROM leave_requests lr
                JOIN leave_type lt ON lr.leave_type_id = lt.leave_type_id
                JOIN users u ON lr.id = u.id
                ORDER BY 
                    CASE 
                        WHEN lr.status = 'Pending' THEN 1
                        WHEN lr.status = 'Approved' THEN 2
                        WHEN lr.status = 'Rejected' THEN 3
                    END,  
                    lr.start_date ASC,  
                    lt.priority_level ASC,  
                    lr.created_at ASC";
        
                $result = $conn->query($sql);

                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['status'] . "</td>";
                        echo "<td>" . $row['username'] . "</td>";
                        echo "<td>" . $row['leave_type_name'] . "</td>";
                        echo "<td>" . $row['start_date'] . " to " . $row['end_date'] . "</td>";
                        echo "<td><button class='view-details' data-id='" . $row['leave_request_id'] . "'>View Details</button></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "Error: " . $conn->error;
                }
                ?>          
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for viewing leave details -->
<div id="leaveModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2 id="leaveName">Leave Details</h2>
        <p><strong>Leave Type:</strong> <span id="leaveType"></span></p>
        <p><strong>Status:</strong> <span id="status"></span></p>
        <p><strong>Leave Dates:</strong> <span id="leaveDates"></span></p>
        <p><strong>Priority Level:</strong> <span id="priorityLevel"></span></p>
        <p><strong>Reason:</strong> <span id="reason"></span></p>

        <form method="POST" action="manage_leave.php">
            <input type="hidden" name="leave_request_id" id="leaveRequestId">
            <button type="submit" class="approve-btn" name="approve_leave">Approve Request</button>
            <button type="submit" class="reject-btn" name="reject_leave">Reject Request</button>
        </form>
    </div>
</div>

<!-- Success Prompt -->
<div id="successPrompt" class="success-prompt">
    <span>Leave request has been successfully processed!</span>
</div>

<script>
    $(document).ready(function(){
        // When the "View Details" button is clicked
        $(".view-details").click(function(){
            var leave_request_id = $(this).data('id');

            // AJAX request to fetch leave details
            $.ajax({
                url: 'view_details.php',
                type: 'GET',
                data: { leave_request_id: leave_request_id },
                success: function(response) {
                    var data = JSON.parse(response);
                    $('#leaveName').text('Leave Details for: ' + data.name);
                    $('#leaveType').text(data.leave_type_name);
                    $('#status').text(data.status);
                    $('#leaveDates').text(data.start_date + ' to ' + data.end_date);
                    $('#priorityLevel').text(data.priority_level);
                    $('#reason').text(data.reason);
                    $('#leaveRequestId').val(data.leave_request_id);

                    // Display the modal
                    $('#leaveModal').show();
                }
            });
        });

        // When the user clicks on <span> (x), close the modal
        $('#closeModal').click(function(){
            $('#leaveModal').hide();
        });

        // When the user clicks anywhere outside of the modal, close it
        $(window).click(function(event) {
            if (event.target == document.getElementById('leaveModal')) {
                $('#leaveModal').hide();
            }
        });
    });
</script>

<?php if (isset($_SESSION['success_message'])): ?>
    <script>
        $(document).ready(function() {
            $('#successPrompt').fadeIn().delay(3000).fadeOut();
        });
        document.addEventListener("DOMContentLoaded", function() {
            const sidebar = document.getElementById('sidebar');
            const menuIcon = document.getElementById('menu-icon');
            const mainContent = document.querySelector('.main-content');

            menuIcon.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('collapsed');
            });
        });
    </script>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

</body>
</html>