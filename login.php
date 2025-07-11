<?php
session_start();
include 'db_connection.php'; // Ensure this points to the correct database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Prepare SQL statement to check the user's email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Verify the password
        if (password_verify($password, $row['password_hash'])) {
            // Set session variables for the user
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['username'];
            $_SESSION['user_role'] = $row['role'];

            // Redirect user based on their role
            if ($row['role'] == 'HR') {
                header('Location: hr_dashboard.php');
            } else if ($row['role'] == 'employee') {
                header('Location: emp_dashboard.php');
            }
            exit();
        } else {
            echo '<script>
                alert("Invalid password.");
                </script>';
        }
    } else {
        echo '<script>
            alert("No user found with this email.");
            </script>';
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management System - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: "Poppins", sans-serif;
            font-weight: 300;
            background:#103713;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            margin: 20px;
            text-align: center;
        }
        .login-container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #ccc;
        }
        h1 {
            margin-bottom: 7px;
            font-family: "DM Sans", sans-serif;
            font-weight: 700;
        }
        p {
            font-family: "Poppins", sans-serif;
            font-weight: 300;
            margin-top: 0px;
            font-size: 0.85em;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        label {
            align-self: flex-start;
            margin: 5px 0 2px;
            font-size: 0.75em;
            letter-spacing: 1px;
        }
        input, select, button {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
            width: 100%;
            font-size: 0.85em;
            box-sizing: border-box;
            outline: none;
        }
        button {
            background-color:#628B35;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #8fc852;
        }
        a {
            color: #000;
            text-decoration: none;
            font-family: "Poppins", sans-serif;
            font-weight: 300;
            font-size: 0.95em;
            margin-top: 10px;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h1>Log In</h1>
            <p>Sign in to continue.</p>
            <form action="login.php" method="post">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="example@company.com" required>
                
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="*********" required>
                
                <button type="submit">Log In</button>
                <a href="forpass.php">Forgot Password</a>
                <a href="#" id="show-signup">Sign Up</a>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('show-signup').addEventListener('click', function(event) {
            event.preventDefault();
            window.location.href = 'index.php'; // Redirect to sign up page
        });
    </script>
</body>
</html>