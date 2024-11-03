<?php
include '../db.php';


// Fetch user data for the user management section, excluding archived users
$users = $conn->query("SELECT * FROM users WHERE archived = 0");


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
            width: responsive;
        }
        #userTable {
    width: 100% !important; /* Ensure it takes up the full width */
}

        /* Success/Error Message Styling */
        .alert {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            display: inline-block;
            margin-bottom: 0;
            padding: 8px 12px;
        }

        /* Styling for DataTables elements */
        .dataTables_wrapper .dataTables_length,
       /* .dataTables_wrapper .dataTables_filter,*/
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
            margin-top: -0px;
        }

        .select-checkbox {
            margin-right: 10px;
        }

        #duplicateModal {
    z-index: 1060; /* Set a higher z-index if needed */
}
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="main-content" id="main-content">
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Manage User</h1>

                <!-- Group the buttons for Add User, Edit, Archive, and Delete -->
                <div class="btn-group">
                    <button class="btn btn-primary add-user-btn" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-plus"></i> Add User
                    </button>
                    <button class="btn btn-outline-primary btn-sm" id="editUserButton">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-outline-warning btn-sm" id="archiveUserButton">
                        <i class="fas fa-archive"></i> Archive
                    </button>
                    <button class="btn btn-outline-danger btn-sm" id="deleteUserButton">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                    <button class="btn btn-secondary" id="archiveListButton" data-bs-toggle="tooltip" data-bs-placement="top" title="View Archived Users">
                        <i class="fas fa-box"></i>
                    </button>
                </div>
            </div>

            <!-- Success/Error Message -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php echo $_GET['success']; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_GET['error']; ?>
                </div>
            <?php endif; ?>

            <!-- User Management Table -->
            <div class="user-table-container">
                <h7>User List</h7>
                <div class="table-responsive">
                <table class="table table-bordered table-striped" id="userTable">
    <thead>
        <tr>
            <th>Full Name</th>
            <th>Email</th>
            <th>Acc Type</th>
            <th>Date Created</th>
        </tr>
    </thead>
                        <tbody>
                            <?php while($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="select-checkbox" 
                                               data-id="<?php echo $user['id']; ?>" 
                                               data-name="<?php echo $user['full_name']; ?>" 
                                               data-email="<?php echo $user['email']; ?>" 
                                               data-role="<?php echo $user['role']; ?>">
                                        <?php echo $user['full_name']; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Duplicate Entry Modal -->
            <div class="modal fade" id="duplicateModal" tabindex="-1" aria-labelledby="duplicateModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="duplicateModalLabel">Duplicate Entry</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="duplicateModalBody">
                            <!-- The error message will be dynamically inserted here -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Modal -->
            <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="successModalLabel">User Added</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            The user has been added successfully!
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Modal for User Update -->
            <div class="modal fade" id="userUpdateSuccessModal" tabindex="-1" aria-labelledby="userUpdateSuccessModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="userUpdateSuccessModalLabel">User Updated</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            User updated successfully!
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Unarchive Success Modal -->
            <div class="modal fade" id="unarchiveSuccessModal" tabindex="-1" aria-labelledby="unarchiveSuccessModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="unarchiveSuccessModalLabel">Success</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            User unarchived successfully!
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Selection Modal -->
            <div class="modal fade" id="noSelectionModal" tabindex="-1" aria-labelledby="noSelectionModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="noSelectionModalLabel">No User Selected</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Please select one user to edit.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <?php include 'add_user_modal.php'; ?>
    <!-- Edit User Modal -->
    <?php include 'edit_user_modal.php'; ?>
    <!-- Archive List Modal -->
    <?php include 'archive_list_modal.php'; ?>
    <!-- No Selection Modal -->
    <?php include 'no_selection_modal.php'; ?>
    <!-- Delete Confirmation Modal -->
    <?php include 'delete_confirmation_modal.php'; ?>
    <?php include 'delete_alert_modal.php'; ?>
    <!-- Archive Confirmation Modal -->
    <?php include 'archive_alert_modal.php'; ?>
    <?php include 'archive_confirmation_modal.php'; ?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom JS -->
    <script src="script3.js"></script>

    <script>
      $('#userTable').DataTable({
        "ajax": "fetch_users.php",
        "columns": [
            {
                "data": "full_name",
                "render": function(data, type, row) {
                    return '<input type="checkbox" class="select-checkbox" ' +
                           'data-id="' + row.id + '" ' +
                           'data-name="' + row.full_name + '" ' +
                           'data-email="' + row.email + '" ' +
                           'data-role="' + row.role + '">' + ' ' + data;
                }
            },
            { "data": "email" },
            { "data": "role" },
            { "data": "created_at" }
        ]
    });


            // Automatically hide the alert after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Initialize all tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Handle Archive List button click to show archived users
            $('#archiveListButton').click(function () {
                $.ajax({
                    url: 'fetch_archived_users.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        var tbody = $('#archiveTable tbody');
                        tbody.empty(); // Clear the table first

                        if (data.length > 0) {
                            data.forEach(function (user) {
                                var row = `
                                    <tr>
                                        <td>${user.full_name}</td>
                                        <td>${user.email}</td>
                                        <td>${user.role}</td>
                                        <td>${user.archived_at}</td>
                                        <td>
                                            <button class="btn btn-sm btn-success unarchiveUserButton" data-id="${user.id}">
                                                <i class="fas fa-undo"></i> Unarchive
                                            </button>
                                        </td>
                                    </tr>`;
                                tbody.append(row);
                            });
                        } else {
                            tbody.append('<tr><td colspan="5">No archived users found.</td></tr>');
                        }

                        $('#archiveListModal').modal('show');
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error("Error fetching archived users:", textStatus, errorThrown);
                        alert("Error fetching archived users. Please try again.");
                    }
                });
            });

            // Handle Unarchive button click inside the archive list
            $(document).on('click', '.unarchiveUserButton', function () {
                var userId = $(this).data('id');
                var button = $(this);

                $.post('unarchive_user.php', { id: userId }, function (response) {
                    if (response.success) {
                        // Show the unarchive success modal instead of the alert
                        $('#unarchiveSuccessModal').modal('show');

                        // Optionally remove the row from the table
                        button.closest('tr').remove();
                        $('#userTable').DataTable().ajax.reload(); // Refresh DataTables if applicable
                    } else {
                        // Show the error modal if there's an issue unarchiving
                        $('#errorModal').modal('show');
                        $('#errorModalBody').text("Error unarchiving user: " + response.error);
                    }
                }, 'json').fail(function (xhr, status, error) {
                    // Show the error modal in case of a server error or network failure
                    $('#errorModal').modal('show');
                    $('#errorModalBody').text("An error occurred: " + error);
                    console.error(xhr.responseText);
                });
            });

            // Archive users when the Archive button is clicked
            $('#archiveUserButton').click(function () {
                var selectedCheckboxes = $('.select-checkbox:checked');
                var userIds = [];

                selectedCheckboxes.each(function() {
                    userIds.push($(this).data('id'));
                });

                if (userIds.length > 0) {
                    // Store selected user IDs in the confirm archive button for future use
                    $('#confirmArchiveButton').data('user-ids', userIds);
                    // Show confirmation modal
                    $('#archiveConfirmationModal').modal('show');
                } else {
                    // Show alert modal if no users are selected
                    $('#archiveAlertModal').modal('show');
                }
            });

            // Handle Archive Confirmation Button click
            $('#confirmArchiveButton').click(function () {
                var userIds = $(this).data('user-ids'); // Retrieve stored user IDs

                // Send the selected user IDs to archive in a batch
                $.post('archive_user_admin.php', { ids: userIds }, function (response) {
                    if (response.success) {
                        // Immediately remove archived users from the DataTable
                        $('.select-checkbox:checked').each(function () {
                            var row = $(this).closest('tr'); // Get the row of the checkbox
                            row.fadeOut(300, function() { // Fade out the row
                                table.row(row).remove().draw(false); // Remove from DataTable without redrawing
                            });
                        });

                        // Show the success modal
                        $('#archiveSuccessModal').modal('show');

                    } else {
                        alert("Error archiving users: " + response.error);
                    }
                }, 'json').fail(function (xhr, status, error) {
                    alert("An error occurred: " + error); // Improved error handling
                    console.error(xhr.responseText);
                });
            });

            // Automatically hide the success alert if it's used elsewhere (non-modal)
            setTimeout(function() {
                $('#archiveSuccessAlert').fadeOut('slow');
            }, 5000); // 5000 milliseconds = 5 seconds

            // Open the edit modal when Edit button is clicked
            $('#editUserButton').click(function() {
    var selectedCheckbox = $('.select-checkbox:checked');

    if (selectedCheckbox.length === 1) {
        var id = selectedCheckbox.data('id');
        var name = selectedCheckbox.data('name');
        var email = selectedCheckbox.data('email');
        var role = selectedCheckbox.data('role');

        $('#userId').val(id);
        $('#userName').val(name);
        $('#userEmail').val(email);
        $('#userRole').val(role);

        $('#editUserModal').modal('show');
    } else {
        $('#noSelectionModal').modal('show');
    }
});



            // Delete users when the Delete button is clicked
            $('#deleteUserButton').click(function () {
                var selectedCheckboxes = $('.select-checkbox:checked');
                var userIds = [];

                selectedCheckboxes.each(function() {
                    userIds.push($(this).data('id'));
                });

                if (userIds.length > 0) {
                    $('#deleteConfirmationModal').modal('show');
                    $('#confirmDeleteButton').data('user-ids', userIds);
                } else {
                    $('#deleteAlertModal').modal('show');
                }
            });

            // Handle Delete Confirmation Button click
            $('#confirmDeleteButton').click(function () {
                var userIds = $(this).data('user-ids');

                $.post('delete_user_admin.php', { ids: userIds }, function (response) {
                    if (response.success) {
                        $('.select-checkbox:checked').each(function () {
                            var row = $(this).closest('tr'); // Get the row of the checkbox
                            row.fadeOut(300, function() { // Fade out the row
                                table.row(row).remove().draw(false); // Remove from DataTable without redrawing
                            });
                        });
                        $('#deleteConfirmationModal').modal('hide'); // Hide confirmation modal
                        $('#deleteSuccessModal').modal('show'); // Show success modal
                    } else {
                        alert("Error deleting users: " + response.error);
                    }
                }, 'json').fail(function (xhr, status, error) {
                    alert("An error occurred: " + error); // Improved error handling
                    console.error(xhr.responseText);
                });
            });

            // Add user form submission
            $('#addUserForm').submit(function(e) {
                e.preventDefault(); // Prevent the default form submission

                // Capture form data
                var formData = $(this).serialize();

                // Send the AJAX request to add the user
                $.post('add_user.php', formData, function(response) {
                    console.log(response); // Log the entire response to debug

                    if (response.success) {
                        // Hide the "Add User" modal before showing the success modal
                        $('#addUserModal').modal('hide');

                        // Show success modal
                        $('#successModal').modal('show');

                        // Optionally, reset the form fields if you want to clear them
                        $('#addUserForm')[0].reset();
                    } else {
                        // Check if the duplicate response is for email or full name
                        if (response.duplicate === 'email') {
                            $('#duplicateModalBody').text('The email you entered already exists. Please use a different one.');
                            $('#duplicateModal').modal('show');
                        } else if (response.duplicate === 'fullname') {
                            $('#duplicateModalBody').text('The full name you entered already exists. Please use a different one.');
                            $('#duplicateModal').modal('show');
                        } else {
                            // Handle any other errors
                            alert("An error occurred: " + response.error);
                        }
                    }
                }, 'json').fail(function(xhr, status, error) {
                    alert("An error occurred: " + error);
                    console.error(xhr.responseText);
                });
            });

            // Remove modal backdrop when the success modal is closed
            $('#successModal').on('hidden.bs.modal', function () {
                // Remove any leftover modal backdrop
                $('.modal-backdrop').remove();

                // Refresh the page to reflect the added user
                location.reload(); // Full page reload to ensure data is refreshed
            });

            // Reset form fields when the modal is closed
            $('#addUserModal').on('hidden.bs.modal', function () {
                // Clear all the input fields
                $('#addUserForm')[0].reset();
            });

            // Edit user form submission
            $('#editUserForm').submit(function(e) {
    e.preventDefault(); // Prevent the default form submission

    // Capture form data
    var formData = $(this).serialize();

    // Send the AJAX request to update the user
    $.post('update_user_admin.php', formData, function(response) {
        if (response.success) {
            // Find the DataTable instance
            var table = $('#userTable').DataTable();

            // Get the user ID from the form
            var userId = $('#userId').val();

            // Find the row in the DataTable for the updated user
            var row = table.rows().nodes().to$().filter(function() {
                return $(this).find('.select-checkbox').data('id') == userId;
            });

            // Update the row data with the new form values
            table.row(row).data({
                "full_name": $('#userName').val(),
                "email": $('#userEmail').val(),
                "role": $('#userRole').val(),
                "created_at": table.row(row).data().created_at // Assuming the creation date is not changing
            }).draw(false); // Redraw the table without resetting pagination

            // Hide the Edit User modal
            $('#editUserModal').modal('hide');

            // Show the success modal
            $('#userUpdateSuccessModal').modal('show');
        } else {
            // Handle error messages (optional)
            alert(response.message);
        }
    }, 'json').fail(function(xhr, status, error) {
        alert("An error occurred: " + error);
        console.error(xhr.responseText);
    });
});


            // Handle Unarchive button click inside the archive list
            $(document).on('click', '.unarchiveUserButton', function () {
                var userId = $(this).data('id');
                var button = $(this);

                $.post('unarchive_user.php', { id: userId }, function (response) {
                    if (response.success) {
                        // Close the archive list modal if it's open
                        $('#archiveListModal').modal('hide');

                        // Show the unarchive success modal instead of alert
                        $('#unarchiveSuccessModal').modal('show');

                        // Optionally, remove the row from the table
                        button.closest('tr').remove();
                        $('#userTable').DataTable().ajax.reload(); // Reload the DataTable if applicable
                    } else {
                        alert("Error unarchiving user: " + response.error);
                    }
                }, 'json').fail(function (xhr, status, error) {
                    alert("An error occurred: " + error);
                    console.error(xhr.responseText);
                });
            });

            // When the success modal is closed, optionally reload the page or refresh the user table
            $('#unarchiveSuccessModal').on('hidden.bs.modal', function () {
                $('#userTable').DataTable().ajax.reload(); // Refresh the user table if using DataTables
            });
     

        <!-- JavaScript to handle the modal population and dynamic UI update -->

        document.addEventListener('DOMContentLoaded', () => {
    const editUserForm = document.getElementById('editUserForm');

    // Event listener to open the modal and populate with user data
    document.querySelectorAll('.editUserBtn').forEach(button => {
        button.addEventListener('click', function () {
            const userId = this.getAttribute('data-id');
            console.log(`Fetching user data for ID: ${userId}`); // Log the user ID for debugging

            // Fetch user data using AJAX
            fetch(`fetch_users.php?id=${userId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error fetching user data: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Fetched user data:', data); // Log fetched data

                    // Check if the user data is available
                    if (data && data.id) {
                        // Populate modal with user data
                        document.getElementById('userId').value = data.id;
                        document.getElementById('userName').value = data.full_name || ''; 
                        document.getElementById('userEmail').value = data.email || ''; 
                        document.getElementById('userRole').value = data.role || ''; 

                        // Show the modal
                        $('#editUserModal').modal('show');
                    } else {
                        console.error('User data is incomplete or missing:', data);
                    }
                })
                .catch(error => {
                    console.error('Error occurred while fetching user data:', error);
                });
        });
    });
});

    // Handle form submission to update user data
    editUserForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(editUserForm);

        // AJAX request to update the user
        fetch('update_user_admin.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error updating user: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update the UI without refreshing the page
                const userRow = document.querySelector(`tr[data-id="${formData.get('id')}"]`);
                if (userRow) {
                    userRow.querySelector('.userName').textContent = formData.get('full_name');
                    userRow.querySelector('.userEmail').textContent = formData.get('email');
                    userRow.querySelector('.userRole').textContent = formData.get('role');
                }

                // Close the modal
                $('#editUserModal').modal('hide');
            } else {
                console.error('Failed to update user:', data.message);
            }
        })
        .catch(error => {
            console.error('Error occurred while updating user data:', error);
        });
    });



    </script>
</body>
</html>