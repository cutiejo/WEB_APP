<!-- navbar.php -->
<?php
include '../db.php';
include 'navbar_fetch_user.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<!-- Header -->
<div class="header">
    <div class="header-left">
        <img src="../assets/imgs/logo.png" alt="SvM Logo" class="logo" data-bs-toggle="tooltip" data-bs-placement="bottom" title="SvM Logo">
        <div class="menu-icon" id="menuToggle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Toggle Menu">
            <i class="fas fa-bars"></i>
        </div>
    </div>
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-toggle="tooltip" data-bs-placement="bottom" title="View Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="badge bg-danger">3+</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                    <li class="dropdown-header">Notifications</li>
                    <li><a class="dropdown-item" href="notifications.php">Sample text</a></li>
                    <li><a class="dropdown-item" href="notifications.php">Sample text has been deposited into your account!</a></li>
                    <li><a class="dropdown-item" href="notifications.php">Sample text Unusually high spending detected!</a></li>
                    <li><a class="dropdown-item text-center" href="notifications.php">Show All</a></li>
                </ul>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-toggle="tooltip" data-bs-placement="bottom" title="View Messages">
                    <i class="fas fa-envelope"></i>
                    <span class="badge bg-warning">7</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="messagesDropdown">
                    <li class="dropdown-header">Messages</li>
                    <li><a class="dropdown-item" href="messages.php">
                        <img src="../assets/imgs/user.svg" alt="User" class="icon rounded-circle">
                        <div>
                            <div class="message-text">Hi there! I am wondering if you...</div>
                            <div class="message-time">Emily Fowler · 58m</div>
                        </div>
                    </a></li>
                    <li><a class="dropdown-item" href="messages.php">
                        <img src="../assets/imgs/user.svg" alt="User" class="icon rounded-circle">
                        <div>
                            <div class="message-text">I have the photos that you ordered...</div>
                            <div class="message-time">Jae Chun · 1d</div>
                        </div>
                    </a></li>
                    <li><a class="dropdown-item" href="messages.php">
                        <img src="../assets/imgs/user.svg" alt="User" class="icon rounded-circle">
                        <div>
                            <div class="message-text">Am I a good boy? The reason I as...</div>
                            <div class="message-time">Chicken the Dog · 2w</div>
                        </div>
                    </a></li>
                    <li><a class="dropdown-item text-center" href="messages.php">Read More Messages</a></li>
                </ul>
            </li>
            <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-toggle="tooltip" data-bs-placement="bottom" title="User Menu">
        <span class="username"><?php echo htmlspecialchars($teacher_full_name); ?></span>
        <img class="img-profile rounded-circle" src="<?php echo htmlspecialchars($profile_image); ?>" alt="User Avatar">
    </a>
    <ul class="dropdown-menu dropdown-menu-end user-dropdown" aria-labelledby="userDropdown">
        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
        <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</li>


        </ul>
    </nav>
</div>

<?php include 'sidebar.php'; ?>


    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to logout?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="../Login/logout.php" class="btn btn-primary">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        All Rights Reserved © 2024
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- Custom JS -->
    <script src="script.js"></script>
    <script>
        // Toggle sidebar and adjust logo size
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
            $(dropdown).on('click', function(e) {
                e.preventDefault();
                closeAllDropdowns();
                $(this).parent().toggleClass('show');
                $(this).next('.dropdown-menu').toggleClass('show');
            });
        });

        // Close all dropdowns
        function closeAllDropdowns() {
            dropdowns.forEach(dropdown => {
                $(dropdown).parent().removeClass('show');
                $(dropdown).next('.dropdown-menu').removeClass('show');
            });
        }

        // Close dropdowns if clicked outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest(dropdowns.join(',')).length) {
                closeAllDropdowns();
            }
        });
    </script>
</body>
</html>
