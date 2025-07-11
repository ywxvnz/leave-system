document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.getElementById('sidebar');
    const menuIcon = document.getElementById('menu-icon');

    // Toggle sidebar collapse
    menuIcon.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
    });
});

document.getElementById("show-logout").addEventListener("click", function(event) {
    event.preventDefault(); // Prevents the default link action

    console.log("Logout clicked");

    // Ask for confirmation
    let confirmLogout = confirm("Are you sure you want to log out?");
    
    // If user confirms, use AJAX to log out
    if (confirmLogout) {
        console.log("User confirmed logout");

        // Perform an AJAX request to logout.php
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "logout.php", true);  // Make sure to create this file for session destruction
        xhr.onload = function() {
            if (xhr.status === 200) {
                window.location.href = "login.php";  // Redirect to login page after session destruction
            } else {
                alert("Error logging out.");
            }
        };
        xhr.send();
    } else {
        console.log("User canceled logout");
    }
});
