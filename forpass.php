<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['submit']) && isset($_POST['email'])) {
        // Forgot Password handling
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        
        // Check if email exists in the database
        $stmt = $conn->prepare("SELECT security_question FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Store the email in session to use for verification
            $_SESSION['reset_email'] = $email;
            $_SESSION['security_question'] = $row['security_question'];
            // Redirect to the same page to show the security question form
            header("Location: forpass.php?form=question");
            exit();
        } else {
            echo "<script>alert('Email not found.'); window.location.href='index.php?form=forpass';</script>";
        }
    }

    // Verify security answer and reset password
    if (isset($_POST['verify_answer'])) {
        $email = $_SESSION['reset_email'];
        $answer = mysqli_real_escape_string($conn, $_POST['security_answer']);
        
        // Check if the answer is correct
        $stmt = $conn->prepare("SELECT security_answer FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Verify if the provided answer matches the hashed one
            if (password_verify($answer, $row['security_answer'])) {
                // Correct answer, proceed to reset password
                $new_password = password_hash("default123", PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
                $stmt->bind_param("ss", $new_password, $email);
                $stmt->execute();
                echo "<script>alert('Password reset successful! Your new password is \"default123\". Please change it after logging in.'); window.location.href='index.php?form=login';</script>";
            } else {
                // Incorrect answer
                echo "<script>alert('Incorrect answer! Try again.'); window.location.href='forpass.php';</script>";
            }
        }                
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Forgot Password</h1>

        <?php if (!isset($_GET['form']) || $_GET['form'] != 'question'): ?>
            <!-- Forgot password form (email submission) -->
            <p>Enter your email address to reset your password.</p>
            <form action="forpass.php" method="post">
                <div class="input-control">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="example@domain.com" required>
                </div>
                <button type="submit" name="submit">Submit</button>
            </form>
        <?php elseif (isset($_GET['form']) && $_GET['form'] == 'question'): ?>
            <!-- Security question form (after email submission) -->
            <h2 style="text-align: center;">Security Question</h2>
            <form action="forpass.php" method="POST">
                <input type="hidden" name="email" value="<?php echo $_SESSION['reset_email']; ?>">
                <p><?php echo $_SESSION['security_question']; ?></p>
                <div class="input-control">
                    <label for="security_answer">Answer</label>
                    <input type="text" id="security_answer" name="security_answer" required placeholder="Enter your answer">
                </div>
                <button type="submit" name="verify_answer">Verify</button>
                <p style="font-size: 12px;">Forgot the answer? Contact your Admin and they will help you change your password.</p>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
