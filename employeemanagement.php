<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "leavemanagementsystem";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'update') {
        $employeeId = $_POST['employee_id'];
        $username = $_POST['username'];
        $department = $_POST['department'];
        $position = $_POST['position'];
        $email = $_POST['email'];
        $role = $_POST['role'];

        $sql = "SELECT user_id FROM employees WHERE employee_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $employeeId);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_assoc();
        $stmt->close();

        if ($employee) {
            $user_id = $employee['user_id'];

            // Update the users table
            $updateUsersSql = "UPDATE users SET username=?, email=?, role=? WHERE id=?";
            $stmtUsers = $conn->prepare($updateUsersSql);
            $stmtUsers->bind_param("sssi", $username, $email, $role, $user_id);
            $successUsers = $stmtUsers->execute();
            $stmtUsers->close();

            // Update password if provided
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
                $updatePasswordSql = "UPDATE users SET password_hash=? WHERE id=?";
                $stmtPassword = $conn->prepare($updatePasswordSql);
                $stmtPassword->bind_param("si", $password, $user_id);
                $stmtPassword->execute();
                $stmtPassword->close();
            }

            // Update the employees table
            $updateEmployeeSql = "UPDATE employees SET username=?, department=?, position=? WHERE employee_id=?";
            $stmtEmployee = $conn->prepare($updateEmployeeSql);
            $stmtEmployee->bind_param("ssss", $username, $department, $position, $employeeId);
            $successEmployee = $stmtEmployee->execute();
            $stmtEmployee->close();

            if ($successUsers && $successEmployee) {
                $_SESSION['alert_message'] = 'Employee updated successfully.';
            } else {
                $_SESSION['alert_message'] = 'Error updating employee.';
            }
        } else {
            $_SESSION['alert_message'] = 'User not found.';
        }

    } elseif ($action == 'add') {
        $username = $_POST['username'];
        $department = $_POST['department'];
        $position = $_POST['position'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $role = $_POST['role'];

        if (empty($role)) {
            $_SESSION['alert_message'] = 'Role is required.';
            exit;
        }

        // Insert into users table
        $sql = "INSERT INTO users (username, password_hash, email, role, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $password, $email, $role);

        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;

            // Insert into employees table
            $sql2 = "INSERT INTO employees (user_id, username, email, department, position) VALUES (?, ?, ?, ?, ?)";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("issss", $user_id, $username, $email, $department, $position);

            if ($stmt2->execute()) {
                $_SESSION['alert_message'] = 'New employee added successfully.';
            } else {
                $_SESSION['alert_message'] = 'Error adding employee to employees table.';
            }
            $stmt2->close();
        } else {
            $_SESSION['alert_message'] = 'Error adding user to users table.';
        }
        $stmt->close();
    }
}

// Fetch employee data for listing
$sql = "SELECT employee_id, username FROM employees";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modify Employees</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="sidebar.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="hr_dashboard.css">
    <style>
        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            transition: margin-left 0.3s, width 0.3s;
        }
        .sidebar.collapsed ~ .main-content {
            margin-left: 60px;
            width: calc(100% - 60px);
        }

        .employee-list ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .employee-item {
            background-color:rgb(255, 255, 255);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }

        .employee-item:hover {
            background-color: #e0e0e0;
        }

        .employee-item span {
            font-weight: 500;
        }

        .update-container, .employee-profile {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .employee-profile h2, .update-container h2 {
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        .employee-update {
            margin-bottom: 15px;
        }

        .employee-update label {
            font-weight: 600;
            display: block;
        }

        .employee-update input, .employee-update select {
            width: 98%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 1em;
        }

        .employee-update button {
            padding: 12px 20px;
            background-color: #628B35;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .employee-update button:hover {
            background-color: #8fc852;
        }

        .update-button, .delete-button {
            margin-top: 10px;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .update-button:hover {
            background-color: #45a049;
            z-index: 9999; 
        }

        .delete-button {
            background-color: #f44336;
        }

        .delete-button:hover {
            background-color: #da190b;
        }

    </style>
</head>
<body>
    <?php include 'hr_sidebar.html'; ?>
    <div class="main-content" id="main-content">
        <h1>Employees</h1>
        <hr class="title-line">
        <div class="container">
            <div class="employee-list">
                <ul>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <li>
                            <div class="employee-item">
                                <span onclick="showProfile(<?php echo $row['employee_id']; ?>)">
                                    <?php echo $row['username']; ?>
                                </span>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <div class="employee-profile" id="employee-profile" style="display: none;">
                <div id="profile-content">
                </div>
            </div>
        </div>

        <!-- Add Employee -->
        <div class="update-container">
            <h2>Add Employee</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="employee-update">
                    <label for="name">Name:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="employee-update">
                   <label for="department">Department:</label>
                    <input type="text" id="department" name="department" required>
                </div>

                <div class="employee-update">
                    <label for="position">Position:</label>
                    <input type="text" id="position" name="position" required>
                </div>

                <div class="employee-update">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="employee-update">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="employee-update">
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="employee">Employee</option>
                        <option value="manager">Manager</option>
                    </select>
                </div>

                <div class="employee-update">
                    <button type="submit">Add Employee</button>
                </div>
            </form>
        </div>

    </div>

    <script>
        let currentEmployeeId = null;

        function showProfile(employeeId) {
            console.log("Clicked on employee:", employeeId); 
            currentEmployeeId = employeeId;
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'getProfile.php?employee_id=' + employeeId, true);
            xhr.onload = function () {
                if (this.status === 200) {
                    document.getElementById('profile-content').innerHTML = this.responseText;
                    document.getElementById('employee-profile').style.display = 'flex';

                    const updateButton = document.createElement('button');
                    updateButton.textContent = 'Update';
                    updateButton.className = 'update-button';
                    updateButton.onclick = function() { showUpdateForm(employeeId); };
                    document.getElementById('profile-content').appendChild(updateButton);

                    console.log("Adding update button");  
                    document.getElementById('profile-content').appendChild(updateButton);
                    
                    const deleteButton = document.createElement('button');
                    deleteButton.textContent = 'Delete';
                    deleteButton.className = 'delete-button';
                    deleteButton.onclick = function() { deleteEmployee(employeeId); };
                    document.getElementById('profile-content').appendChild(deleteButton);
                } else {
                    console.error("Failed to load profile:", this.status, this.statusText);
                }
            };
            xhr.send();
        }

        function showUpdateForm(employeeId) {
            console.log("Update button clicked for employee id:", employeeId);
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'getProfile.php?employee_id=' + employeeId + '&update=true', true);
            xhr.onload = function () {
                if (this.status === 200) {
                    const profile = JSON.parse(this.responseText);
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '';
                    form.innerHTML = `
                        <form method="POST" action="" value="update">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="employee_id" value="${employeeId}">
                            <div class="employee-update">
                                <label for="name">Name:</label>
                                <input type="text" id="username" name="username" value="${profile.username}" required>
                            </div>
                            <div class="employee-update">
                                <label for="department">Department:</label>
                                <input type="text" id="department" name="department" value="${profile.department}" required>
                            </div>
                            <div class="employee-update">
                                <label for="position">Position:</label>
                                <input type="text" id="position" name="position" value="${profile.position}" required>
                            </div>
                            <div class="employee-update">
                                <label for="email">Email:</label>
                                <input type="email" id="email" name="email" value="${profile.email}" required>
                            </div>
                            <div class="employee-update">
                                <label for="password">Password:</label>
                                <input type="password" id="password" name="password">
                            </div>
                            <div class="employee-update">
                                <label for="role">Role:</label>
                                <select id="role" name="role" required>
                                    <option value="employee" ${profile.role === 'employee' ? 'selected' : ''}>Employee</option>
                                    <option value="manager" ${profile.role === 'manager' ? 'selected' : ''}>Manager</option>
                                </select>
                            </div>
                            <div class="employee-update">
                                <button type="submit">Save</button>
                                <button type="button" onclick="cancelUpdate()">Cancel</button>
                            </div>
                        </form>
                    `;
                    document.getElementById('profile-content').innerHTML = '';
                    document.getElementById('profile-content').appendChild(form);
                }
            };
            xhr.send();
        }

        function deleteEmployee(employeeId) {
            if (confirm('Are you sure you want to delete this employee?')) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'deleteEmployee.php', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.onload = function () {
                    if (this.status === 200) {
                        alert('Employee deleted successfully.');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        alert('Failed to delete employee.');
                    }
                };
                xhr.send('employee_id=' + employeeId);
            }
        }

        function cancelUpdate() {
            showProfile(currentEmployeeId);
        }

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
</body>
</html>
