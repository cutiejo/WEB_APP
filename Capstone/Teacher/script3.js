document.addEventListener("DOMContentLoaded", function() {
    // Toggle sidebar and adjust content width
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
    });

    // Toggle dropdowns
    const dropdowns = ['#userDropdown', '#notificationsDropdown', '#messagesDropdown'];

    dropdowns.forEach(dropdown => {
        document.querySelector(dropdown).addEventListener('click', function(e) {
            e.preventDefault();
            closeAllDropdowns();
            this.parentElement.classList.toggle('show');
            this.nextElementSibling.classList.toggle('show');
        });
    });

    // Close all dropdowns
    function closeAllDropdowns() {
        dropdowns.forEach(dropdown => {
            document.querySelector(dropdown).parentElement.classList.remove('show');
            document.querySelector(dropdown).nextElementSibling.classList.remove('show');
        });
    }

    // Close dropdowns if clicked outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest(dropdowns.join(','))) {
            closeAllDropdowns();
        }
    });
});
