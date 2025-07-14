document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.getElementById('sidebar');
    const menuIcon = document.getElementById('menu-icon');

    menuIcon.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
    });
});

document.getElementById("show-logout").addEventListener("click", function(event) {
    event.preventDefault(); 

    console.log("Logout clicked");

    let confirmLogout = confirm("Are you sure you want to log out?");

    if (confirmLogout) {
        console.log("User confirmed logout");

        // Perform an AJAX request to logout.php
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "logout.php", true);  
        xhr.onload = function() {
            if (xhr.status === 200) {
                window.location.href = "login.php";  
            } else {
                alert("Error logging out.");
            }
        };
        xhr.send();
    } else {
        console.log("User canceled logout");
    }
});
