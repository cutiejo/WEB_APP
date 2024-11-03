// script.js

document.getElementById("menuToggle").addEventListener("click", function () {
    var sidebar = document.getElementById("sidebar");
    var mainContent = document.getElementById("main-content");
    var logo = document.querySelector(".logo");
    var menuIcon = document.querySelector(".menu-icon");

    sidebar.classList.toggle("collapsed");
    mainContent.classList.toggle("collapsed");
    
    if (sidebar.classList.contains("collapsed")) {
        logo.style.height = "30px";
        logo.style.marginLeft = "0";
        menuIcon.style.marginLeft = "20px";
    } else {
        logo.style.height = "40px";
        logo.style.marginLeft = "5px";
        menuIcon.style.marginLeft = "140px";
    }
});z

// Initialize Bootstrap dropdowns and modals
$(document).ready(function () {
    


    // Toggle main content visibility when the menubar is clicked
    $('#menubar').on('click', function() {
        $('.main-content').toggle();
    });


    // Initialize Bootstrap tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Remove manual jQuery dropdown handling
    var notificationsDropdown = new bootstrap.Dropdown(document.getElementById('notificationsDropdown'));
    var messagesDropdown = new bootstrap.Dropdown(document.getElementById('messagesDropdown'));
    var userDropdown = new bootstrap.Dropdown(document.getElementById('userDropdown'));
    var logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));

    // Close the dropdowns if clicked outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#userDropdown, #notificationsDropdown, #messagesDropdown').length) {
            $('.dropdown-menu').removeClass('show');
        }
    });
});
