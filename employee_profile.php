<?php
session_start();
include 'db_connection.php';

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
    header("Location: login.php"); // Redirect if not logged in
    exit();
}

$user_id = $_SESSION['user_id']; // Get user_id from session

// Fetch employee ID using user_id
$employeeQuery = "SELECT * FROM employees WHERE user_id = '$user_id'";
$employeeResult = mysqli_query($conn, $employeeQuery);
$employeeInfo = mysqli_fetch_assoc($employeeResult);

if (!$employeeInfo) {
    echo "No employee profile found.";
    exit();
}

// Store employee data in variables for easy access
$employee_id = $employeeInfo['employee_id']; // Use correct column name
$employee_username = $employeeInfo['username'];
$employee_email = $employeeInfo['email'];
$employee_department = $employeeInfo['department'];
$employee_position = $employeeInfo['position'];
$employee_role = 'employee'; // Since this page is for employees

$updateSuccess = false;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $updated_username = $_POST['username'];
    $updated_email = $_POST['email'];
    $updated_password = $_POST['password'];

    if (!empty($updated_password)) {
        // If password is provided, hash it
        $hashed_password = password_hash($updated_password, PASSWORD_BCRYPT);
        $update_sql = "UPDATE employees SET 
            username='$updated_username',  
            email='$updated_email'
            WHERE user_id='$user_id'";
    } else {
        // Update without password
        $update_sql = "UPDATE employees SET 
            username='$updated_username',  
            email='$updated_email'
            WHERE user_id='$user_id'";
    }

    if (mysqli_query($conn, $update_sql)) {
        // Update session variables
        $_SESSION['employee_username'] = $updated_username;
        $_SESSION['employee_email'] = $updated_email;
        $updateSuccess = true;
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Profile</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">
    <style>
    body {
        background-color: #E2DBD0;
    }
    </style>
    <?php if ($updateSuccess): ?>
    <script>
        if (!sessionStorage.getItem('profileUpdated')) {
            sessionStorage.setItem('profileUpdated', 'true');
            setTimeout(function() {
                location.reload();
            }, 1000);
        } else {
            sessionStorage.removeItem('profileUpdated');
        }
    </script>
    <?php endif; ?>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="container">
    <h1>Employee Profile</h1>

    <form action="" method="POST">
        <div class="input-control">
            <label for="name">Name</label>
            <input type="text" id="username" name="username" value="<?php echo $employee_username; ?>">
        </div>

        <div class="input-control">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo $employee_email; ?>">
        </div>

        <div class="input-control">
            <label for="department">Department</label>
            <input type="text" id="department" name="department" value="<?php echo $employee_department; ?>" disabled>
        </div>

        <div class="input-control">
            <label for="position">Position</label>
            <input type="text" id="position" name="position" value="<?php echo $employee_position; ?>" disabled>
        </div>

        <div class="input-control">
            <label for="role">Role</label>
            <input type="text" id="role" name="role" value="<?php echo $employee_role; ?>" disabled>
        </div>

        <div class="input-control">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Leave blank if you do not want to change your password">
        </div>

        <button type="submit">Update Profile</button>
    </form>

    <?php if ($updateSuccess): ?>
        <p style="color: green;">Profile updated successfully!</p>
    <?php endif; ?>

</div>

</body>
</html>
