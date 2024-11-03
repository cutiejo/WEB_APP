<?php
include '../db.php';

// Fetch student data for the student management section
$students = $conn->query("SELECT s.*, g.grade_level, sec.section FROM students s 
                          JOIN grade_levels g ON s.grade_level_id = g.id 
                          JOIN sections sec ON s.section_id = sec.id
                          WHERE s.is_archived = 0");




// Fetch grade levels and sections for the dropdowns
$grade_levels = $conn->query("SELECT * FROM grade_levels");
$sections = $conn->query("SELECT * FROM sections");
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
                <button class="btn btn-primary add-student-btn" data-bs-toggle="modal"
                    data-bs-target="#addStudentModal"><i class="fas fa-plus"></i> Add Student</button>
            </div>

            <table class="table table-bordered table-striped" id="studentTable">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>Image</th>
                        <th>LRN</th>
                        <th>Full Name</th>
                        <th>RFID Tag</th>
                        <th>Grade Level</th>
                        <th>Section</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $students->fetch_assoc()): ?>
                        <tr data-grade="<?php echo htmlspecialchars($row['grade_level']); ?>">
                            <td><input type="checkbox" class="select-checkbox"></td>
                            <td>
                                <?php if (!empty($row['image']) && file_exists('../' . $row['image'])): ?>
                                    <img src="<?php echo '../' . htmlspecialchars($row['image']); ?>" alt="Student Image"
                                        style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                <?php else: ?>
                                    <img src="../uploads/default-avatar.png" alt="Student Image"
                                        style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['lrn']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['rfid_tag']); ?></td>
                            <td><?php echo htmlspecialchars($row['grade_level']); ?></td>
                            <td><?php echo htmlspecialchars($row['section']); ?></td>
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
    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStudentModalLabel">Add Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addStudentForm" enctype="multipart/form-data">
                        <div class="text-center mb-3">
                            <img id="editImagePreview" src="assets/avatar.png" alt="Student Image"
                                class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                            <input type="file" id="editImageUpload" name="image" accept="image/*"
                                class="form-control mt-2" onchange="previewEditImage(event)">
                            <button type="button" class="btn btn-danger mt-2" id="removeEditImage">Remove Image</button>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="lrn" class="form-label">LRN</label>
                                <input type="text" class="form-control" id="lrn" name="lrn" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="birth_date" class="form-label">Birth Date</label>
                                <input type="date" class="form-control" id="birth_date" name="birth_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="rfid_tag" class="form-label">RFID Tag</label>
                                <input type="text" class="form-control" id="rfid_tag" name="rfid_tag" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="age" class="form-label">Age</label>
                                <input type="number" class="form-control" id="age" name="age" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sex" class="form-label">Sex</label>
                                <select class="form-control" id="sex" name="sex" required>
                                    <option value="">Select a gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="grade_level" class="form-label">Grade Level</label>
                                <select class="form-control" id="grade_level" name="grade_level_id" required>
                                    <option value="">Select a Grade Level</option>
                                    <?php while ($gl = $grade_levels->fetch_assoc()): ?>
                                        <option value="<?php echo $gl['id']; ?>"><?php echo $gl['grade_level']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="section" class="form-label">Section</label>
                                <select class="form-control" id="section" name="section_id" required>
                                    <option value="">Select Section</option>
                                    <?php while ($sec = $sections->fetch_assoc()): ?>
                                        <option value="<?php echo $sec['id']; ?>"><?php echo $sec['section']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="guardian" class="form-label">Guardian</label>
                                <input type="text" class="form-control" id="guardian" name="guardian" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contact" class="form-label">Contact</label>
                                <input type="text" class="form-control" id="contact" name="contact" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Student</button>
                    </form>
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
    <script src="script3.js"></script>

    <script>
        $(document).ready(function () {
            var table = $('#studentTable').DataTable({
                paging: true,
                ordering: true,
                info: true
            });

            // Handle grade level sidebar selection
            // Handle grade level sidebar selection
            $('.grade-list .grade-item').on('click', function () {
                $('.grade-list .grade-item').removeClass('active');
                $(this).addClass('active');

                var selectedGrade = $(this).data('grade'); // Get the grade level

                // Show all rows if 'All' is selected, otherwise filter by grade level
                if (selectedGrade === 'all') {
                    table.columns(5).search('').draw(); // Show all rows
                } else {
                    table.columns(5).search('^' + selectedGrade + '$', true, false).draw(); // Filter by grade
                }
            });




            // Move buttons next to the show entries dropdown
            $('.dataTables_length').after(
                '<div class="btn-group">' +
                '<button class="btn btn-outline-info btn-sm filter-student"><i class="fas fa-filter"></i> Filter</button>' +
                '<button class="btn btn-outline-primary btn-sm edit-student"><i class="fas fa-edit"></i> Edit</button>' +
                '<button class="btn btn-outline-warning btn-sm archive-student"><i class="fas fa-archive"></i> Archive</button>' +
                '<button class="btn btn-outline-danger btn-sm delete-student"><i class="fas fa-trash-alt"></i> Delete</button>' +
                '<button class="btn btn-outline-secondary btn-sm view-student"><i class="fas fa-eye"></i> View</button>' +
                '</div>'
            );

            // Select/Deselect all checkboxes
            $('#select-all').on('click', function () {
                var rows = table.rows({ 'search': 'applied' }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
            });

            $('#studentTable tbody').on('change', 'input[type="checkbox"]', function () {
                if (!this.checked) {
                    var el = $('#select-all').get(0);
                    if (el && el.checked && ('indeterminate' in el)) {
                        el.indeterminate = true;
                    }
                }
            });

            // Handle row click for selecting a student (only one row can be selected at a time)
            $('#studentTable tbody').on('click', 'input[type="checkbox"]', function () {
                $('input[type="checkbox"]').prop('checked', false);
                $(this).prop('checked', true);

                // Mark the row as selected
                $('#studentTable tbody tr').removeClass('selected');
                $(this).closest('tr').addClass('selected');
            });

            // Handle form submission for adding a student
            $('#addStudentForm').on('submit', function (event) {
                event.preventDefault();

                var formData = new FormData(this);

                $.ajax({
                    url: 'add_student.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        var res = JSON.parse(response);
                        $('#feedbackMessage').text(res.message);
                        $('#feedbackModal').modal('show');
                        if (res.status === 'success') {
                            $('#addStudentModal').modal('hide');
                            setTimeout(function () {
                                location.reload(); // Reload the table if the student is added successfully
                            }, 1000);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error adding student:', error);
                    }
                });
            });


            // Handle the "View" button click event
            $('.btn-outline-secondary.view-student').on('click', function () {
                let selectedRows = $('#studentTable tbody input.select-checkbox:checked').closest('tr');
                if (selectedRows.length === 1) {
                    let studentId = selectedRows.find('td:eq(2)').text(); // Assuming LRN is in the third column

                    // Redirect to view_students.php with LRN in the URL
                    window.location.href = 'view_students.php?lrn=' + studentId;
                } else {
                    $('#feedbackMessage').text('Please select a student to view.');
                    $('#feedbackModal').modal('show');
                }
            });


            // Edit Button Click
            $(document).ready(function () {
                // Handle Edit Button Click
                $('.btn-outline-primary').on('click', function () {
                    let selectedRows = $('#studentTable tbody input.select-checkbox:checked').closest('tr');
                    if (selectedRows.length === 1) {
                        let studentId = selectedRows.find('td:eq(2)').text(); // Assuming the LRN is in the third column

                        $.ajax({
                            url: 'fetch_student.php',
                            type: 'POST',
                            data: { student_id: studentId },
                            dataType: 'json',
                            success: function (data) {
                                if (data) {
                                    $('#editStudentModal #lrn').val(data.lrn);
                                    $('#editStudentModal #name').val(data.name);
                                    $('#editStudentModal #birth_date').val(data.birth_date);
                                    $('#editStudentModal #rfid_tag').val(data.rfid_tag);
                                    $('#editStudentModal #age').val(data.age);
                                    $('#editStudentModal #address').val(data.address);
                                    $('#editStudentModal #sex').val(data.sex);
                                    $('#editStudentModal #grade_level').val(data.grade_level_id);
                                    $('#editStudentModal #section').val(data.section_id);
                                    $('#editStudentModal #guardian').val(data.guardian);
                                    $('#editStudentModal #contact').val(data.contact);

                                    // Check and update image preview
                                    let imagePath = data.image && data.image !== '' ? '../' + data.image : '../uploads/default-avatar.png';
                                    $('#editImagePreview').attr('src', imagePath);

                                    $('#editStudentModal').modal('show');
                                } else {
                                    $('#feedbackMessage').text('Error fetching student data.');
                                    $('#feedbackModal').modal('show');
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error('Error fetching student data:', error);
                            }
                        });
                    } else {
                        $('#feedbackMessage').text('Please select a student to edit.');
                        $('#feedbackModal').modal('show');
                    }
                });
            });


            // Edit Student Form Submission
            $('#editStudentForm').on('submit', function (event) {
                event.preventDefault();

                let formData = new FormData(this);

                $.ajax({
                    url: 'edit_student.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        let res = JSON.parse(response);
                        $('#feedbackMessage').text(res.message);
                        $('#feedbackModal').modal('show');
                        if (res.status === 'success') {
                            $('#editStudentModal').modal('hide');
                            setTimeout(function () {
                                location.reload();  // Reload the table to reflect changes
                            }, 1000);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error editing student:', error);
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
                        success: function (response) {
                            let res = JSON.parse(response);
                            $('#feedbackMessage').text(res.message);
                            $('#feedbackModal').modal('show');
                            if (res.status === 'success') {
                                setTimeout(function () {
                                    location.reload();  // Reload the page to reflect changes
                                }, 1000);
                            }
                        },
                        error: function (xhr, status, error) {
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
                        success: function (response) {
                            let res = JSON.parse(response);
                            $('#feedbackMessage').text(res.message);
                            $('#feedbackModal').modal('show');
                            if (res.status === 'success') {
                                selectedRows.closest('tr').remove(); // Remove rows from table
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('Error deleting student:', error);
                        }
                    });
                } else {
                    $('#feedbackMessage').text('Please select at least one student to delete.');
                    $('#feedbackModal').modal('show');
                }
            });


            // Archive Student Form Submission
            $('#archiveStudentForm').on('submit', function (event) {
                event.preventDefault();

                let selectedRows = $('#studentTable tbody input.select-checkbox:checked').closest('tr');
                let studentIds = selectedRows.map(function () {
                    return $(this).children('td:first').text();
                }).get();

                $.ajax({
                    url: 'archive_student.php',
                    type: 'POST',
                    data: { ids: studentIds },
                    success: function (response) {
                        let res = JSON.parse(response);
                        $('#feedbackMessage').text(res.message);
                        $('#feedbackModal').modal('show');
                        if (res.status === 'success') {
                            setTimeout(function () {
                                location.reload();  // This reloads the entire page
                            }, 1000);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error archiving student:', error);
                    }
                });
            });

            // Delete Student Form Submission
            $('#deleteStudentForm').on('submit', function (event) {
                event.preventDefault();

                let selectedRows = $('#studentTable tbody input.select-checkbox:checked').closest('tr');
                let studentIds = selectedRows.map(function () {
                    return $(this).children('td:first').text(); // Make sure this returns the LRN or the primary key for deletion
                }).get();

                $.ajax({
                    url: 'delete_student.php',
                    type: 'POST',
                    data: { ids: studentIds },
                    success: function (response) {
                        let res = JSON.parse(response);
                        $('#feedbackMessage').text(res.message);
                        $('#feedbackModal').modal('show');
                        if (res.status === 'success') {
                            selectedRows.closest('tr').remove();  // This line will remove the rows from the table dynamically
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error deleting student:', error);
                    }
                });
            });
        });

        // Image Preview and Reset functions for add stud modal//
        function previewImage(event) {
            var image = document.getElementById('imagePreview');
            image.src = URL.createObjectURL(event.target.files[0]);
        }

        function previewEditImage(event) {
            var image = document.getElementById('editImagePreview');
            image.src = URL.createObjectURL(event.target.files[0]);
        }

        document.getElementById('removeImage').addEventListener('click', function () {
            var image = document.getElementById('imagePreview');
            var fileInput = document.getElementById('imageUpload');

            image.src = 'assets/avatar.png'; // Reset to default image
            fileInput.value = ''; // Clear the file input
        });

        document.getElementById('removeEditImage').addEventListener('click', function () {
            var image = document.getElementById('editImagePreview');
            var fileInput = document.getElementById('editImageUpload');

            image.src = 'assets/avatar.png'; // Reset to default image
            fileInput.value = ''; // Clear the file input
        });


    </script>
</body>

</html>