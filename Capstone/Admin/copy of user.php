<?php
include '../db.php';


// Fetch user data for the user management section
$users = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">

    <style>
        /* User-table Container */
        .user-table-container {
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Styling for DataTables elements */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_paginate,
        .dataTables_wrapper .dataTables_info {
            margin-top: 20px;
        }

        .btn-group {
            margin-left: 20px;
            display: inline-block;
        }

        .dataTables_length,
        .dataTables_length select {
            display: inline-block;
            vertical-align: middle;
        }

        .dataTables_wrapper .dataTables_length select {
            width: auto;
        }

        .btn-group .btn {
            margin-right: 5px;
        }

        .btn-group .btn i {
            margin-right: 5px;
        }

        .add-user-btn {
            display: inline-block;
            margin-top: -5px;
            float: right;
        }

        .select-checkbox {
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Manage User</h1>
                <button class="btn btn-primary add-user-btn"><i class="fas fa-plus"></i> Add User</button>
            </div>

            <!-- User Management Table -->
            <div class="user-table-container">
                <h7>User List</h7>
                <div class="table-responsive">
                    <div class="d-flex justify-content-between align-items-center mb-3">

                    </div>
                    <table class="table table-bordered table-striped" id="userTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Email</th>
                                <th>Mobile Number</th>
                                <th>Acc Type</th>
                                <th>Date Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><input type="checkbox" class="select-checkbox"> <?php echo $user['name']; ?></td>
                                    <td><?php echo $user['address']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><?php echo $user['mobile']; ?></td>
                                    <td><?php echo $user['acc_type']; ?></td>
                                    <td><?php echo $user['date_created']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>




    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="script.js"></script>
    <script>
        $(document).ready(function () {
            var table = $('#userTable').DataTable({
                "paging": true,
                "ordering": true,
                "info": true
            });

            // Move buttons next to the show entries dropdown
            $('.dataTables_length').after(
                '<div class="btn-group">' +
                '<button class="btn btn-outline-info btn-sm"><i class="fas fa-filter"></i> Filter</button>' +
                '<button class="btn btn-outline-primary btn-sm"><i class="fas fa-edit"></i> Edit</button>' +
                '<button class="btn btn-outline-warning btn-sm"><i class="fas fa-archive"></i> Archive</button>' +
                '<button class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</button>' +
                '</div>'
            );

            // Select/Deselect all checkboxes
            $('#select-all').on('click', function () {
                var rows = table.rows({ 'search': 'applied' }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
            });

            $('#userTable tbody').on('change', 'input[type="checkbox"]', function () {
                if (!this.checked) {
                    var el = $('#select-all').get(0);
                    if (el && el.checked && ('indeterminate' in el)) {
                        el.indeterminate = true;
                    }
                }
            });
        });

    </script>
</body>

</html>