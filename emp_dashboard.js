document.getElementById('file-leave-btn').addEventListener('click', function() {
  document.getElementById('dashboard-content').innerHTML = '<p>You can now file a leave request.</p>';
});

document.getElementById('leave-summary-btn').addEventListener('click', function() {
  document.getElementById('dashboard-content').innerHTML = '<p>Here is your leave summary.</p>';
});

document.getElementById('leave-calendar-btn').addEventListener('click', function() {
  document.getElementById('dashboard-content').innerHTML = '<p>View your leave calendar here.</p>';
});

document.getElementById('leave-balance-btn').addEventListener('click', function() {
  document.getElementById('dashboard-content').innerHTML = '<p>Here is your current leave balance.</p>';
});

document.getElementById("file-leave-form").addEventListener("submit", function (event) {
  event.preventDefault(); // Prevent form submission and page reload
  // Add code here to handle the form submission (AJAX or validation)
});
