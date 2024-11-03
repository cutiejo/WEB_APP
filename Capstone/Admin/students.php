<?php
include '../db.php';
session_start();

// Redirect non-admin users to the login page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}

// Fetch all student data along with their grade and section assignments
$students = $conn->query("
    SELECT s.*, 
    IFNULL(g.grade_level, 'No Grade Assigned') AS grade_level, 
    IFNULL(sec.section, 'No Section Assigned') AS section,
    CASE 
        WHEN s.status = 0 THEN 'Pending' 
        WHEN s.status = 1 THEN 'Approved' 
        WHEN s.status = 2 THEN 'Rejected' 
        ELSE 'Unknown' 
    END AS status
    FROM students s
    LEFT JOIN grade_levels g ON s.grade_level_id = g.id
    LEFT JOIN sections sec ON s.section_id = sec.id
    WHERE s.is_archived = 0
");

// Fetch grade levels and sections for dropdowns
$grade_levels = $conn->query("SELECT * FROM grade_levels");
$sections = $conn->query("SELECT * FROM sections");

// Handle form submission for updating student's grade and section
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['grade_level_id'], $_POST['section_id'], $_POST['lrn'])) {
        $grade_level_id = intval($_POST['grade_level_id']);
        $section_id = intval($_POST['section_id']);
        $lrn = mysqli_real_escape_string($conn, $_POST['lrn']);
        
        // Update the student's grade level and section
        $sql = "UPDATE students SET grade_level_id = ?, section_id = ? WHERE lrn = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $grade_level_id, $section_id, $lrn);

        if ($stmt->execute()) {
            // Trigger notification for updates (email/SMS can be integrated here)
            echo "Student's grade level and section updated successfully.";
        } else {
            echo "Error updating record: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Required fields are missing.";
    }
}

//bulk approval//
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['lrn_list'])) {
    $action = $_POST['action'];
    $lrn_list = $_POST['lrn_list'];
    $status = ($action === 'approve') ? 1 : 2;

    // Prepare statement to update multiple students' status
    $lrn_placeholders = implode(',', array_fill(0, count($lrn_list), '?'));
    $sql = "UPDATE students SET status = ? WHERE lrn IN ($lrn_placeholders)";
    $stmt = $conn->prepare($sql);

    // Bind the status and each LRN
    $params = array_merge([$status], $lrn_list);
    $stmt->bind_param(str_repeat('i', count($params)), ...$params);

    if ($stmt->execute()) {
        $message = ($action === 'approve') ? "Students approved successfully." : "Students rejected successfully.";
        echo json_encode(['status' => 'success', 'message' => $message]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating students.']);
    }

    $stmt->close();
    exit();
}



?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">
    
    <style>
        /* Main Content Layout */
        .main-content {
            display: flex;
        }

        /* Sidebar */
        .sidebarw {
            width: 60px;
            background-color: white;
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-right: 1px solid #ddd;
            border-radius: .8rem;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
        }

        .grade-list {
            padding: 0;
            list-style: none;
            width: 100%;
            margin: 0;
            margin-right: .5rem;
        }

        .grade-list .grade-item {
            text-align: center;
            margin: 10px 0;
            margin-left: 13px;
            background-color: #f8f9fa;
            border-radius: 40%;
            color: #218838;
            cursor: pointer;
            font-weight: bold;
            width: 40px;
            height: 40px;
            line-height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .grade-list .grade-item.active,
        .grade-list .grade-item:hover {
            background-color: #1cc88a;
            color: #fff;
        }

        /* Student Table Container */
        .student-table-container {
            flex-grow: 1;
            padding: 15px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-left: 20px;
            margin-top: 40px;
        }

        /* DataTable Buttons */
        .btn-group {
            margin-left: 10px;
        }

        .btn-group .btn {
            margin-right: 5px;
        }

        .add-student-btn {
            float: right;
        }
        .edit-student-btn {
            float: right;
        }
        
        .bs-btn-primary{
            width: 50px;
            margin-left: 20px;
            margin-top: 40px;
        }
        /* Bulk action section styling */
        .bulk-action-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .bulk-action-container select {
            width: 200px;
        }

        .bulk-action-container button {
            margin-top: 0; /* Align the button with the select box */
        }


    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="main-content" id="main-content">
    <!-- Grade Level Sidebar -->
    <div class="sidebarw" id="sidebar">
        <ul class="grade-list">
            <li class="grade-item active" data-grade="all">All</li>
            <?php for ($i = 1; $i <= 10; $i++): ?>
                <li class="grade-item" data-grade="<?php echo $i; ?>">G<?php echo $i; ?></li>
            <?php endfor; ?>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="student-table-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 mb-0 text-gray-800">Student Management</h1>
            <button class="btn btn-primary add-student-btn" data-bs-toggle="modal" data-bs-target="#addStudentModal"><i class="fas fa-plus"></i> Add Student</button>
        </div>

        <!-- Bulk Actions Dropdown -->
        <div class="bulk-action-container">
            <select id="bulkActionSelect" class="form-select" aria-label="Bulk actions">
                <option selected>Select Status</option>
                <option value="approve">Approve</option>
                <option value="reject">Reject</option>
                <option value="archive">Archive</option>
            </select>
            <button id="applyBulkAction" class="btn btn-primary">Apply</button>
        </div>




        <table class="table table-bordered table-striped" id="studentTable">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all"></th>
                   
                    <th>LRN</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>RFID Tag</th>
                    <th>Grade Level</th>
                    <th>Section</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $students->fetch_assoc()): ?>
                <tr>
                    <td><input type="checkbox" class="select-checkbox" value="<?php echo $row['lrn']; ?>"></td>
                   
                    <td><?php echo htmlspecialchars($row['lrn']); ?></td>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['rfid_uid']); ?></td>
                    <td><?php echo htmlspecialchars($row['grade_level']); ?></td>
                    <td><?php echo htmlspecialchars($row['section']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
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

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStudentModalLabel">Add Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addStudentForm" enctype="multipart/form-data">
                    <!-- Profile Image Upload -->
                    <div class="text-center mb-3">
                        <img id="addImagePreview" src="assets/avatar.png" alt="Student Image" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                        <input type="file" id="addImageUpload" name="image" accept="image/*" class="form-control mt-2" onchange="previewAddImage(event)">
                        <button type="button" class="btn btn-danger mt-2" id="removeAddImage">Remove Image</button>
                    </div>
                    
                    <!-- Student Details -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <!-- Password and RFID Tag -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <span class="input-group-text" id="togglePasswordIcon" style="cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>

                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="rfid_uid" class="form-label">RFID Tag</label>
                            <input type="text" class="form-control" id="rfid_uid" name="rfid_uid" required>
                        </div>
                    </div>

                    
                    <!-- Grade Level, Section, and Optional Phone -->
                    <div class="row">
                    <div class="col-md-6 mb-3">
                            <label for="grade_level" class="form-label">Grade Level</label>
                            <select class="form-control" id="grade_level" name="grade_level_id" required>
                                <option value="">Select a Grade Level</option>
                                <?php while($gl = $grade_levels->fetch_assoc()): ?>
                                    <option value="<?php echo $gl['id']; ?>"><?php echo $gl['grade_level']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
           
       
                        <div class="col-md-6 mb-3">
                            <label for="section" class="form-label">Section</label>
                            <select class="form-control" id="section" name="section_id" required>
                                <option value="">Select Section</option>
                                <?php while($sec = $sections->fetch_assoc()): ?>
                                    <option value="<?php echo $sec['id']; ?>"><?php echo $sec['section']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    
                    <button type="submit" class="btn btn-primary">Add Student</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Bulk Action Feedback Modal -->
<div class="modal fade" id="bulkActionFeedbackModal" tabindex="-1" aria-labelledby="bulkActionFeedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkActionFeedbackModalLabel">Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="bulkActionFeedbackMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>





<!-- Include Modals -->
<?php include 'student_management_modals.php'; ?>

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
        var table = $('#studentTable').DataTable({
            paging: true,
            ordering: true,
            info: true
        });


        // Grade Level Sidebar Filter
        $('.grade-list .grade-item').on('click', function() {
            $('.grade-list .grade-item').removeClass('active');
            $(this).addClass('active');

            const selectedGrade = $(this).data('grade'); // Get the grade level

            // Show all rows if 'All' is selected, otherwise filter by grade level
            if (selectedGrade === 'all') {
                table.columns(5).search('').draw(); // Adjust column index to Grade Level column
            } else {
                table.columns(5).search(`^${selectedGrade}$`, true, false).draw(); // Exact match filtering
            }
        });



        // Move buttons next to the show entries dropdown
        $('.dataTables_length').after(
            '<div class="btn-group">' +
            
                '<button class="btn btn-outline-primary btn-sm edit-student"><i class="fas fa-edit"></i> Edit</button>' +
                '<button class="btn btn-outline-warning btn-sm archive-student"><i class="fas fa-archive"></i> Archive</button>' +
                '<button class="btn btn-outline-danger btn-sm delete-student"><i class="fas fa-trash-alt"></i> Delete</button>' +
                '<button class="btn btn-outline-secondary btn-sm view-student"><i class="fas fa-eye"></i> View</button>' +
            '</div>'
        );
        

        
        // Select/Deselect all checkboxes
        $('#select-all').on('click', function() {
            var rows = table.rows({ 'search': 'applied' }).nodes();
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
        });

        $('#studentTable tbody').on('change', 'input[type="checkbox"]', function() {
            if (!this.checked) {
                var el = $('#select-all').get(0);
                if (el && el.checked && ('indeterminate' in el)) {
                    el.indeterminate = true;
                }
            }
        });

      
        // Function to calculate age based on birth date
        function calculateAge(birthDate) {
            const today = new Date();
            const birth = new Date(birthDate);
            let age = today.getFullYear() - birth.getFullYear();
            const monthDiff = today.getMonth() - birth.getMonth();

            // Adjust age if the birthday hasn't occurred yet this year
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
                age--;
            }

            return age;
        }

        // Event listener for birth date input change in Add Student Modal
        $('#birth_date').on('change', function() {
            const birthDate = $(this).val();
            const age = calculateAge(birthDate);
            $('#addAge').val(age); // Update the age input field in Add Student Modal
        });

        // Event listener for birth date input change in Edit Student Modal
        $('#editStudentModal').on('change', '#birth_date', function() {
            const birthDate = $(this).val();
            const age = calculateAge(birthDate);
            $('#editAge').val(age); // Update the age input field in Edit Student Modal
        });



        // Handle the "View" button click event
        $('.btn-outline-secondary.view-student').on('click', function () {
        let selectedRows = $('#studentTable tbody input.select-checkbox:checked').closest('tr');
        if (selectedRows.length === 1) {
            let studentId = selectedRows.find('td:eq(1)').text(); // Assuming LRN is in the second column

            // Redirect to view_students.php with LRN in the URL
            window.location.href = 'view_students.php?lrn=' + studentId;
        } else if (selectedRows.length > 1) {
                $('#feedbackMessage').text('Please select only one student to view.');
                $('#feedbackModal').modal('show');
            } else {
                $('#feedbackMessage').text('Please select a student to view.');
                $('#feedbackModal').modal('show');
            }
        });


    


    
        // Handle form submission for adding a student
        // Handle form submission for adding a student
        $('#addStudentForm').on('submit', function(event) {
            event.preventDefault();
            
            var formData = new FormData(this);
            
            $.ajax({
                url: 'add_student.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    var res = JSON.parse(response);
                    $('#feedbackMessage').text(res.message);
                    $('#feedbackModal').modal('show');
                    
                    if (res.status === 'success') {
                        $('#addStudentModal').modal('hide');
                        setTimeout(function() {
                            location.reload(); // Reload the table if the student is added successfully
                        }, 1000);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error adding student:', error);
                    $('#feedbackMessage').text('Error adding student.');
                    $('#feedbackModal').modal('show');
                }
            });
        });


        // Toggle password visibility in Add Student Modal
        $('#togglePasswordIcon').on('click', function() {
            const passwordField = $('#password');
            const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
            passwordField.attr('type', type);
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        });


    


        // Handle the "Edit" button click event
// Handle the "Edit" button click event for direct redirection to the edit page
$('.btn-outline-primary.edit-student').on('click', function () {
    let selectedRows = $('#studentTable tbody input.select-checkbox:checked').closest('tr');
    if (selectedRows.length === 1) {
        let studentLrn = selectedRows.find('td:eq(1)').text(); // Assuming LRN is in the second column

        // Redirect to the edit page with LRN in the URL
        window.location.href = 'edit_students_profile.php?lrn=' + studentLrn;
    } else if (selectedRows.length > 1) {
        $('#feedbackMessage').text('Please select only one student to edit.');
        $('#feedbackModal').modal('show');
    } else {
        $('#feedbackMessage').text('Please select a student to edit.');
        $('#feedbackModal').modal('show');
    }
});

// Handle Bulk Action Apply Button Click
$('#applyBulkAction').on('click', function() {
            const selectedAction = $('#bulkActionSelect').val();
            const selectedStudents = $('#studentTable tbody input.select-checkbox:checked').map(function() {
                return $(this).val(); // Assuming LRN is stored in the checkbox value
            }).get();

            if (selectedStudents.length === 0) {
                $('#bulkActionFeedbackMessage').text('Please select at least one student.');
                $('#bulkActionFeedbackModal').modal('show');
                return;
            }

            if (!selectedAction) {
                $('#bulkActionFeedbackMessage').text('Please select a bulk action.');
                $('#bulkActionFeedbackModal').modal('show');
                return;
            }

            $.ajax({
                url: 'process_bulk_action.php',
                type: 'POST',
                data: { action: selectedAction, lrn_list: selectedStudents },
                success: function(response) {
                    const res = JSON.parse(response);
                    $('#bulkActionFeedbackMessage').text(res.message);
                    $('#bulkActionFeedbackModal').modal('show');
                    if (res.status === 'success') {
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Bulk action error:', error);
                    $('#bulkActionFeedbackMessage').text('Error applying bulk action.');
                    $('#bulkActionFeedbackModal').modal('show');
                }
            });
        });




    

                    // Archive Button Click//
                    $('.btn-outline-warning').on('click', function () {
                    let selectedRows = $('#studentTable tbody input.select-checkbox:checked').closest('tr');
                    if (selectedRows.length > 0) {
                        let studentIds = selectedRows.map(function () {
                            return $(this).find('td:eq(2)').text(); // Get LRN or student ID
                        }).get();

                        $.ajax({
                            url: 'archive_student.php',
                            type: 'POST',
                            data: { ids: studentIds },
                            success: function(response) {
                                let res = JSON.parse(response);
                                $('#feedbackMessage').text(res.message);
                                $('#feedbackModal').modal('show');
                                if (res.status === 'success') {
                                    setTimeout(function() {
                                        location.reload();  // Reload the page to reflect changes
                                    }, 1000);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error archiving student:', error);
                            }
                        });
                    } else {
                        $('#feedbackMessage').text('Please select at least one student to archive.');
                        $('#feedbackModal').modal('show');
                    }
                });


                // Delete Button Click
                $('.btn-outline-danger').on('click', function () {
                let selectedRows = $('#studentTable tbody input.select-checkbox:checked').closest('tr');
                if (selectedRows.length > 0) {
                    let studentIds = selectedRows.map(function () {
                        return $(this).find('td:eq(2)').text(); // Get LRN or student ID
                    }).get();

                    $.ajax({
                        url: 'delete_student.php',
                        type: 'POST',
                        data: { ids: studentIds },
                        success: function(response) {
                            let res = JSON.parse(response);
                            $('#feedbackMessage').text(res.message);
                            $('#feedbackModal').modal('show');
                            if (res.status === 'success') {
                                selectedRows.closest('tr').remove(); // Remove rows from table
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error deleting student:', error);
                        }
                    });
                } else {
                    $('#feedbackMessage').text('Please select at least one student to delete.');
                    $('#feedbackModal').modal('show');
                }
            });

            // Archive Student Form Submission
            $('#archiveStudentForm').on('submit', function(event) {
                event.preventDefault();

                let selectedRows = $('#studentTable tbody input.select-checkbox:checked').closest('tr');
                let studentIds = selectedRows.map(function () {
                    return $(this).children('td:first').text();
                }).get();

                $.ajax({
                    url: 'archive_student.php',
                    type: 'POST',
                    data: { ids: studentIds },
                    success: function(response) {
                        let res = JSON.parse(response);
                        $('#feedbackMessage').text(res.message);
                        $('#feedbackModal').modal('show');
                        if (res.status === 'success') {
                            setTimeout(function() {
                                location.reload();  // This reloads the entire page
                            }, 1000);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error archiving student:', error);
                    }
                });
            });

            // Delete Student Form Submission
            $('#deleteStudentForm').on('submit', function(event) {
                event.preventDefault();

                let selectedRows = $('#studentTable tbody input.select-checkbox:checked').closest('tr');
                let studentIds = selectedRows.map(function () {
                    return $(this).children('td:first').text(); // Make sure this returns the LRN or the primary key for deletion
                }).get();

                $.ajax({
                    url: 'delete_student.php',
                    type: 'POST',
                    data: { ids: studentIds },
                    success: function(response) {
                        let res = JSON.parse(response);
                        $('#feedbackMessage').text(res.message);
                        $('#feedbackModal').modal('show');
                        if (res.status === 'success') {
                            selectedRows.closest('tr').remove();  // This line will remove the rows from the table dynamically
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error deleting student:', error);
                    }
                });
            });
        });
    
        // Add Student: Preview Image
        function previewAddImage(event) {
            const addImagePreview = document.getElementById('addImagePreview');
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    addImagePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                addImagePreview.src = 'assets/avatar.png'; // Reset to default
            }
        }
        // Remove image in Add Modal
        document.getElementById('removeAddImage').addEventListener('click', function () {
            document.getElementById('addImageUpload').value = ''; // Clear file input
            document.getElementById('addImagePreview').src = 'assets/avatar.png'; // Reset to default image
        });
        // Image Preview and Reset functions for add stud modal//
        function previewImage(event) {
            var image = document.getElementById('imagePreview');
            image.src = URL.createObjectURL(event.target.files[0]);
        }

        document.getElementById('removeImage').addEventListener('click', function () {
            var image = document.getElementById('imagePreview');
            var fileInput = document.getElementById('imageUpload');
            
            image.src = 'assets/avatar.png'; // Reset to default image
            fileInput.value = ''; // Clear the file input
        });

        
        

        

        









        
</script>
</body>
</html>
