<?php
include '../db.php';

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}

// Fetch all teacher data along with their grade and section assignments
$teachers = $conn->query("
    SELECT t.*, 
           IFNULL(GROUP_CONCAT(DISTINCT g.grade_level, ' - ', s.section SEPARATOR ', '), 'No assignments') AS grade_section
    FROM teachers t
    LEFT JOIN teacher_assignments ta ON t.employee_id = ta.employee_id
    LEFT JOIN grade_levels g ON ta.grade_level_id = g.id
    LEFT JOIN sections s ON ta.section_id = s.id
    WHERE t.is_archived = 0
    GROUP BY t.employee_id
");

// Fetch grade levels and sections for dropdowns
$grade_levels = $conn->query("SELECT * FROM grade_levels");
$sections = $conn->query("SELECT * FROM sections");

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">

    <style>
        .teacher-table-container {
            padding: 15px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
        }

        .btn-group {
            margin-left: 10px;
        }

        .btn-group .btn {
            margin-right: 5px;
        }

        .add-teacher-btn {
            float: right;
        }
        .modal {
        z-index: 1050; /* Ensures the modal stays on top */
        }

        .modal-backdrop {
            z-index: 1040; /* Backdrop should be behind the modal but above other content */
        }
        
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="main-content" id="main-content">
    <div class="teacher-table-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 mb-0 text-gray-800">Teacher Management</h1>
            <button class="btn btn-primary add-teacher-btn" data-bs-toggle="modal" data-bs-target="#addTeacherModal"><i class="fas fa-plus"></i> Add Teacher</button>
        
        </div>

        <!-- Teacher Table -->
        <table class="table table-bordered table-striped" id="teacherTable">
        <thead>
            <tr>
                <th><input type="checkbox" id="select-all"></th>
                <th>Image</th>
                <th>Employee ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>RFID Tag</th>
                <th>Grade Level & Section</th>
            </tr>
        </thead>
        <tbody>
    <?php while($row = $teachers->fetch_assoc()): ?>
    <tr>
        <td><input type="checkbox" class="select-checkbox"></td>
        <td>
            <?php if (!empty($row['image']) && file_exists('../' . $row['image'])): ?>
                <img src="<?php echo '../' . htmlspecialchars($row['image']); ?>" alt="Teacher Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
            <?php else: ?>
                <img src="../uploads/default-avatar.png" alt="Default Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
            <?php endif; ?>
        </td>
        <td><?php echo htmlspecialchars($row['employee_id']); ?></td>
        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
        <td><?php echo htmlspecialchars($row['email']); ?></td>
        <td><?php echo htmlspecialchars($row['phone']); ?></td>
        <td><?php echo htmlspecialchars($row['rfid_uid']); ?></td>
        <td><?php echo htmlspecialchars($row['grade_section']); ?></td>
    </tr>
    <?php endwhile; ?>
</tbody>

        </table>
    </div>
</div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="feedbackModalLabel">Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="feedbackMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Archive Confirmation Modal -->
<div class="modal fade" id="archiveConfirmationModal" tabindex="-1" aria-labelledby="archiveConfirmationLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="archiveConfirmationLabel">Confirm Archive</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to archive the selected teacher(s)?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmArchive">Archive</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the selected teacher(s)? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>


<!-- Add Teacher Modal -->
<div class="modal fade" id="addTeacherModal" tabindex="-1" aria-labelledby="addTeacherModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTeacherModalLabel">Add Teacher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addTeacherForm" enctype="multipart/form-data">
                    <!-- Teacher Image Upload -->
                    <div class="text-center mb-3">
                        <img id="teacherImagePreview" src="assets/avatar.png" alt="Teacher Image" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                        <input type="file" id="teacherImageUpload" name="image" accept="image/*" class="form-control mt-2" onchange="previewTeacherImage(event)">
                        <button type="button" class="btn btn-danger mt-2" id="removeTeacherImage">Remove Image</button>
                    </div>

                    <!-- Full Name, Email, and Password Fields -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <!-- Password field with eye toggle -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <span class="input-group-text">
                                    <i class="fas fa-eye" id="togglePassword" style="cursor: pointer;"></i>
                                </span>
                            </div>
                        </div>

                    <!-- Phone and RFID Tag Fields -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="rfid_uid" class="form-label">RFID Tag</label>
                            <input type="text" class="form-control" id="rfid_uid" name="rfid_uid" required>
                        </div>
                    </div>

                    <!-- Grade Level and Section Fields -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="grade_level" class="form-label">Grade Level</label>
                            <select class="form-control" id="grade_level" name="grade_level_id" required>
                                <option value="">Select a Grade Level</option>
                                <?php while ($grade = $grade_levels->fetch_assoc()): ?>
                                    <option value="<?php echo $grade['id']; ?>"><?php echo htmlspecialchars($grade['grade_level']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="section" class="form-label">Section</label>
                            <select class="form-control" id="section" name="section_id" required>
                                <option value="">Select Section</option>
                                <?php while ($section = $sections->fetch_assoc()): ?>
                                    <option value="<?php echo $section['id']; ?>"><?php echo htmlspecialchars($section['section']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">Add Teacher</button>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Assign Grade and Section Modal -->
<div class="modal fade" id="assignGradeSectionModal" tabindex="-1" aria-labelledby="assignGradeSectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignGradeSectionModalLabel">Assign Grade Level & Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assignGradeSectionForm">
                    <input type="hidden" id="assign_teacher_id" name="teacher_id">

                    <!-- Display currently assigned grade and section -->
                    <div class="mb-3">
                        <label class="form-label">Currently Assigned:</label>
                        <div id="current_assignments" class="alert alert-info">
                            <!-- Current assignments will be displayed here -->
                        </div>
                    </div>

                    <!-- New grade level and section assignment -->
                    <div class="mb-3">
                        <label for="assign_grade_level" class="form-label">Assign New Grade Level</label>
                        <select class="form-control" id="assign_grade_level" name="grade_level_ids[]" multiple>
                            <!-- Options will be populated via AJAX -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="assign_section" class="form-label">Assign New Section</label>
                        <select class="form-control" id="assign_section" name="section_ids[]" multiple>
                            
                            <!-- Options will be populated via AJAX -->
                             
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Assignment</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="script3.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#teacherTable').DataTable({
            paging: true,
            ordering: true,
            info: true
        });

        // Move buttons next to the show entries dropdown
        $('.dataTables_length').after(
            '<div class="btn-group">' +
                '<button class="btn btn-outline-primary btn-sm edit-teacher"><i class="fas fa-edit"></i> Edit</button>' +
                '<button class="btn btn-outline-warning btn-sm archive-teacher"><i class="fas fa-archive"></i> Archive</button>' +
                '<button class="btn btn-outline-danger btn-sm delete-teacher"><i class="fas fa-trash-alt"></i> Delete</button>' +
                '<button class="btn btn-outline-secondary btn-sm view-teacher"><i class="fas fa-eye"></i> View</button>' +
                '<button class="btn btn-outline-success btn-sm assign-grade-section"><i class="fas fa-chalkboard-teacher"></i> Assign Grade/Section</button>'+

                '</div>'
        );
        // Select/Deselect all checkboxes
        $('#select-all').on('click', function() {
            var rows = table.rows({ 'search': 'applied' }).nodes();
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
        });

        $('#teacherTable tbody').on('change', 'input[type="checkbox"]', function() {
            if (!this.checked) {
                var el = $('#select-all').get(0);
                if (el && el.checked && ('indeterminate' in el)) {
                    el.indeterminate = true;
                }
            }
        });

        //MODAL//
        // Show modals with proper stacking and z-index
        $('.modal').on('show.bs.modal', function () {
            var zIndex = 1050 + (10 * $('.modal:visible').length);
            $(this).css('z-index', zIndex);
            setTimeout(function () {
                $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
            }, 0);
        });
        
        // Ensure focus is moved back to the modal
        $('.modal').on('hidden.bs.modal', function () {
            if ($('.modal:visible').length) {
                $('body').addClass('modal-open');
            }
        });
        

        

        // Edit Button Click Event
        // Edit Button Click Event
        $(document).on('click', '.edit-teacher', function() {
            let selectedRows = $('#teacherTable tbody input[type="checkbox"]:checked').closest('tr');
            
            if (selectedRows.length === 1) {
                let teacherId = selectedRows.find('td:eq(2)').text().trim();
                window.location.href = 'edit_teacher.php?id=' + teacherId;
            } else {
                $('#feedbackMessage').text('Please select one teacher to edit.');
                $('#feedbackModal').modal('show');
            }
        });




//edit modl submission//
$('#editTeacherForm').on('submit', function(e) {
    e.preventDefault(); // Prevent the default form submission

    var formData = new FormData(this); // Capture the form data

    $.ajax({
        url: 'update_teacher.php', // Your backend script to handle updates
        type: 'POST',
        data: formData,
        processData: false, // Don't process the data into query string format
        contentType: false, // Tell jQuery not to set the content type header
        success: function(response) {
            var res = JSON.parse(response);
            if (res.status === 'success') {
                $('#feedbackMessage').text(res.message);
                $('#feedbackModal').modal('show');

                setTimeout(function() {
                    location.reload(); // Reload the page to reflect the updates
                }, 1000);
            } else {
                $('#feedbackMessage').text(res.message);
                $('#feedbackModal').modal('show');
            }
        },
        error: function(xhr, status, error) {
            $('#feedbackMessage').text('Error: ' + xhr.responseText);
            $('#feedbackModal').modal('show');
        }
    });
});


let selectedTeacherIds = [];

// Archive Button Click Event
$(document).on('click', '.archive-teacher', function() {
    selectedTeacherIds = $('#teacherTable tbody input[type="checkbox"]:checked').closest('tr').map(function () {
        return $(this).find('td:eq(2)').text(); // Assuming the Employee ID is in the 3rd column
    }).get();

    if (selectedTeacherIds.length > 0) {
        $('#archiveConfirmationModal').modal('show'); // Show archive confirmation modal
    } else {
        $('#feedbackMessage').text('Please select at least one teacher to archive.');
        $('#feedbackModal').modal('show');
    }
});

// Confirm Archive Action
$('#confirmArchive').on('click', function() {
    $('#archiveConfirmationModal').modal('hide');
    $.ajax({
        url: 'archive_teacher.php',
        method: 'POST',
        data: { ids: selectedTeacherIds },
        success: function(response) {
            let res = JSON.parse(response);
            $('#feedbackMessage').text(res.message);
            $('#feedbackModal').modal('show');
            if (res.status === 'success') {
                setTimeout(function() {
                    location.reload();
                }, 1000);
            }
        },
        error: function(xhr, status, error) {
            $('#feedbackMessage').text('Error archiving teacher.');
            $('#feedbackModal').modal('show');
        }
    });
});

// Delete Button Click Event
$(document).on('click', '.delete-teacher', function() {
    selectedTeacherIds = $('#teacherTable tbody input[type="checkbox"]:checked').closest('tr').map(function () {
        return $(this).find('td:eq(2)').text(); // Assuming the Employee ID is in the 3rd column
    }).get();

    if (selectedTeacherIds.length > 0) {
        $('#deleteConfirmationModal').modal('show'); // Show delete confirmation modal
    } else {
        $('#feedbackMessage').text('Please select at least one teacher to delete.');
        $('#feedbackModal').modal('show');
    }
});

// Confirm Delete Action
$('#confirmDelete').on('click', function() {
    $('#deleteConfirmationModal').modal('hide');
    $.ajax({
        url: 'delete_teacher.php',
        method: 'POST',
        data: { ids: selectedTeacherIds },
        success: function(response) {
            let res = JSON.parse(response);
            $('#feedbackMessage').text(res.message);
            $('#feedbackModal').modal('show');
            if (res.status === 'success') {
                setTimeout(function() {
                    location.reload();
                }, 1000);
            }
        },
        error: function(xhr, status, error) {
            $('#feedbackMessage').text('Error deleting teacher.');
            $('#feedbackModal').modal('show');
        }
    });
});

        
            // Assign Grade & Section Click Event
            $('.assign-grade-section').on('click', function() {
            let selectedRows = $('#teacherTable tbody input[type="checkbox"]:checked').closest('tr');
            if (selectedRows.length === 1) {
                let teacherId = selectedRows.find('td:eq(2)').text(); // Assuming the ID is in the 3rd column
                window.location.href = 'assigning_grade_section.php?teacher_id=' + teacherId; // Redirect to the new page with teacher_id
            } else {
                $('#feedbackMessage').text('Please select one teacher to assign grade levels and sections.');
                $('#feedbackModal').modal('show');
            }
        });



        


        // View Button Click Event
        $('.view-teacher').on('click', function () {
            let selectedRows = $('#teacherTable tbody input[type="checkbox"]:checked').closest('tr');
            if (selectedRows.length === 1) {
                let teacherId = selectedRows.find('td:eq(2)').text(); // Assuming the ID is in the 3rd column
                window.location.href = 'view_teacher.php?id=' + teacherId;
                
            } else if (selectedRows.length === 0) {
                $('#feedbackMessage').text('Please select a teacher to view.');
                $('#feedbackModal').modal('show');
            } else {
                $('#feedbackMessage').text('Please select only one teacher to view.');
                $('#feedbackModal').modal('show');
            }
        });

        // Preview image for Add Teacher
        window.previewTeacherImage = function(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('teacherImagePreview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        // Preview image for Edit Teacher
        window.previewEditTeacherImage = function(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('editTeacherImagePreview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        // Remove image functionality
        document.getElementById('removeTeacherImage').addEventListener('click', function() {
            document.getElementById('teacherImagePreview').src = 'assets/avatar.png';
            document.getElementById('teacherImageUpload').value = '';
        });

        document.getElementById('removeEditTeacherImage').addEventListener('click', function() {
            document.getElementById('editTeacherImagePreview').src = 'assets/avatar.png';
            document.getElementById('editTeacherImageUpload').value = '';
        });

       




        //ADD TEACHER//

        $(document).ready(function() {
    $('#addTeacherForm').on('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission
        var formData = new FormData(this);

        $.ajax({
            url: 'add_teacher.php', // Backend script for processing
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                var res = JSON.parse(response);
                if (res.status === 'success') {
                    $('#feedbackMessage').text(res.message);
                    $('#feedbackModal').modal('show');
                    setTimeout(function() {
                        location.reload(); // Reload to reflect new teacher
                    }, 1000);
                } else {
                    $('#feedbackMessage').text(res.message);
                    $('#feedbackModal').modal('show');
                }
            },
            error: function(xhr, status, error) {
                $('#feedbackMessage').text('Error: ' + xhr.responseText);
                $('#feedbackModal').modal('show');
            }
        });
    });
});

//PASSWORD EYE//
// Toggle password visibility
const togglePassword = document.querySelector('#togglePassword');
const password = document.querySelector('#password');

togglePassword.addEventListener('click', function (e) {
    // Toggle the type attribute
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);

    // Toggle the eye icon
    this.classList.toggle('fa-eye-slash');
});








    });
</script>
</body>
</html>
