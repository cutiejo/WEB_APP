<!-- sidebar.php -->
<div class="sidebar" id="sidebar">
    <nav class="nav flex-column">
        <a class="nav-item<?php echo (basename($_SERVER['PHP_SELF']) == 'Dashboard.php') ? ' active' : ''; ?>" href="Dashboard.php"><i class="fas fa-tachometer-alt"></i><span> Dashboard</span></a>
        <a class="nav-item<?php echo (basename($_SERVER['PHP_SELF']) == 'Attendance.php') ? ' active' : ''; ?>" href="Attendance.php"><i class="fas fa-user-graduate"></i><span> Attendance</span></a>
        <a class="nav-item<?php echo (basename($_SERVER['PHP_SELF']) == 'announcement.php') ? ' active' : ''; ?>" href="announcement.php"><i class="fas fa-bullhorn"></i><span> Announcement</span></a>
        <a class="nav-item<?php echo (basename($_SERVER['PHP_SELF']) == 'RFID_Logs.php') ? ' active' : ''; ?>" href="RFID_Logs.php"><i class="fas fa-chalkboard-teacher"></i><span> RFID Logs</span></a>
        <a class="nav-item<?php echo (basename($_SERVER['PHP_SELF']) == 'Report.php') ? ' active' : ''; ?>" href="Report.php"><i class="fas fa-id-card"></i><span> Report</span></a>
        <a class="nav-item<?php echo (basename($_SERVER['PHP_SELF']) == 'Student_Profile.php') ? ' active' : ''; ?>" href="Student_Profile.php"><i class="fas fa-users"></i><span> Students Profile</span></a>

        <a class="nav-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
    <i class="fas fa-sign-out-alt"></i><span> Logout</span>
</a>

    </nav>
</div>
<!-- Logout Modal -->
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
