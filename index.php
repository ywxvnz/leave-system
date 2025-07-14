<?php
session_start();

$defaultForm = 'signup'; 
if (isset($_GET['form'])) {
    $defaultForm = $_GET['form']; // 'signup', 'login', or 'forpass'
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
                        <!-- <option value="hr">HR</option> -->
                        <option value="employee">Employee</option>
                    </select>
                </div>
                <button type="submit" name="submit">Sign Up</button>
            </form>
        </div>

        <!-- Login Form -->
        <div class="form-container" id="login-container" <?php if ($defaultForm !== 'login') echo 'style="display: none;"'; ?>>
            <h1>Log In</h1>
            <p>Sign in to continue.</p>
            <form action="login.php" method="post">
                <div class="input-control">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="example@domain.com" required>
                </div>
                <div class="input-control">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="********" required>
                </div>
                <button type="submit" name="submit">Log In</button>
                <a href="forpass.php">Forgot Password</a>
                <a href="#" id="show-signup">Sign up</a>
            </form>
        </div>

        <!-- Forgot Password Form 
        <div class="form-container" id="forpass-container" <?php if ($defaultForm !== 'forpass') echo 'style="display: none;"'; ?>>
            <h1>Forgot Password</h1>
            <p>Generate a new password.</p>
            <form action="forpass.php" method="post">
                <div class="input-control">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="example@domain.com" required>
                </div>
                <button type="submit" name="submit">Send</button>
                <a href="?form=login" id="show-login">Log in</a>
                <a href="?form=signup" id="show-signup">Sign up</a>
            </form>
        </div>-->
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('show-signup')?.addEventListener('click', function (event) {
            event.preventDefault();
            window.location.href = "?form=signup";
        });

        document.getElementById('show-login')?.addEventListener('click', function (event) {
            event.preventDefault();
            window.location.href = "?form=login";
        });

        document.getElementById('show-forpass')?.addEventListener('click', function (event) {
            event.preventDefault();
            window.location.href = "?form=forpass";
        });
    });
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function (event) {
                let passwordField = form.querySelector('#password');
                if (passwordField) {
                    let password = passwordField.value;
                    if (password.length < 8 || password.length > 20) {
                        alert("Password must be between 8 and 20 characters.");
                        event.preventDefault();
                    }
                }
            });
        });
    });

    </script>
</body>
</html>
