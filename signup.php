<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connection.php'; // Ensure db_connection.php exists in the same directory or provide the correct path

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($conn)) {
        // Get form data and escape special characters for security
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $role = mysqli_real_escape_string($conn, $_POST['role']); // 'employee' or 'hr'
        $security_question = mysqli_real_escape_string($conn, $_POST['security_question']);
        $security_answer = password_hash($_POST['security_answer'], PASSWORD_DEFAULT); // Hash security answer
        
        // Check if email already exists
        $checkEmailSql = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($checkEmailSql);

        if ($result->num_rows > 0) {
            echo '<script>
                        alert("This email has already been used. Please use a different email.");
                        window.location.href = "index.php"; 
                    </script>';
        } else {
            // Insert new user into users table
            $sql = "INSERT INTO users (username, email, password_hash, role, security_question, security_answer) VALUES ('$name', '$email', '$password', '$role', '$security_question', '$security_answer')";

            if ($conn->query($sql) === TRUE) {
                // Get the last inserted user ID
                $user_id = mysqli_insert_id($conn);

                // Insert user details into employees table only if the role is 'employee'
                if ($role === 'employee') {
                    $employeeSql = "INSERT INTO employees (user_id, username, email) VALUES ('$user_id', '$name', '$email')";

                    if (!$conn->query($employeeSql)) {
                        echo "Error inserting into employees table: " . $conn->error;
                    }
                }

                // Redirect to login page after successful registration
                header("Location: login.php");
                exit();
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    } else {
        echo "Database connection failed";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Sign-Up Form -->
        <div class="form-container" id="signup-container" <?php if ($defaultForm !== 'signup') echo 'style="display: none;"'; ?>>
            <h1>Create New Account</h1>
            <p>Already Registered? <a href="#" id="show-login">Log in here.</a></p>
            <form action="signup.php" method="post">
                <div class="input-control">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Last Name, First Name, MI" required>
                </div>
                <div class="input-control">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="example@domain.com" required>
                </div>
                <div class="input-control">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="********" required minlength="8" maxlength="20">
                </div>
                <div class="input-control">
                    <label for="security_question">Security Question</label>
                    <select id="security_question" name="security_question" required>
                        <option value="" disabled selected>Select a Security Question</option>
                        <option value="What is your pet’s name?">What is your pet’s name?</option>
                        <option value="What is your mother’s maiden name?">What is your mother’s maiden name?</option>
                        <option value="What was the name of your first school?">What was the name of your first school?</option>
                    </select>
                </div>
                <div class="input-control">
                    <label for="security_answer">Answer</label>
                    <input type="text" id="security_answer" name="security_answer" placeholder="Your answer" required>
                </div>
                <div class="input-control">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="" disabled selected>Select Role</option>
                        <!--<option value="hr">HR</option>-->
                        <option value="employee">Employee</option>
                    </select>
                </div>
                <button type="submit" name="submit">Sign Up</button>
            </form>
        </div>