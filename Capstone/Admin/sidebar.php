<!-- sidebar.php -->
<div class="sidebar" id="sidebar">
    <nav class="nav flex-column">
        <a class="nav-item<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? ' active' : ''; ?>" href="index.php"><i class="fas fa-tachometer-alt"></i><span> Dashboard</span></a>
        <a class="nav-item<?php echo (basename($_SERVER['PHP_SELF']) == 'students.php') ? ' active' : ''; ?>" href="students.php"><i class="fas fa-user-graduate"></i><span> Student Management</span></a>
        <a class="nav-item<?php echo (basename($_SERVER['PHP_SELF']) == 'teacher_management.php') ? ' active' : ''; ?>" href="teacher_management.php"><i class="fas fa-chalkboard-teacher"></i><span> Teacher Management</span></a>
        <a class="nav-item<?php echo (basename($_SERVER['PHP_SELF']) == 'rfid_log.php') ? ' active' : ''; ?>" href="rfid_log.php"><i class="fas fa-id-card"></i><span> RFID Logs</span></a>
        <a class="nav-item<?php echo (basename($_SERVER['PHP_SELF']) == 'Attendance.php') ? ' active' : ''; ?>" href="Attendance.php"><i class="fas fa-user-graduate"></i><span> Attendance</span></a>
        <a class="nav-item<?php echo (basename($_SERVER['PHP_SELF']) == 'users.php') ? ' active' : ''; ?>" href="users.php"><i class="fas fa-users"></i><span> Users</span></a>
        <a class="nav-item<?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php') ? ' active' : ''; ?>" href="settings.php"><i class="fas fa-cogs"></i><span> Settings</span></a>
        <a class="nav-item<?php echo (basename($_SERVER['PHP_SELF']) == 'report.php') ? ' active' : ''; ?>" href="report.php"><i class="fas fa-chart-line"></i><span> Report</span></a>
        <a class="nav-item<?php echo (basename($_SERVER['PHP_SELF']) == 'notice.php') ? ' active' : ''; ?>" href="notice.php"><i class="fas fa-bullhorn"></i><span> Notice</span></a>
      
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
