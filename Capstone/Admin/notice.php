<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../db.php';

$response = ["status" => "error", "message" => "Something went wrong."];

try {
    // Handle form submission for creating or editing an announcement
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $postingDate = $_POST['postingDate'] ?? '';
        $category = $_POST['category'] ?? '';
        $status = isset($_POST['status']) ? 1 : 0;

        // Log the received data
        error_log("ID: $id, Title: $title, Content: $content, Posting Date: $postingDate, Category: $category, Status: $status");

        // Handle image upload if provided
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../uploads/";
            $image = basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image;
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $response['message'] = "Error uploading the file.";
                echo json_encode($response);
                exit();
            }
        } else {
            if ($id > 0 && isset($_POST['currentImage'])) {
                $image = $_POST['currentImage'];
            }
        }

        // Prepare the SQL statement for update or insert
        if ($id > 0) {
            if ($image) {
                $stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ?, image = ?, posting_date = ?, status = ?, category = ? WHERE id = ?");
                $stmt->bind_param("ssssisi", $title, $content, $image, $postingDate, $status, $category, $id);
            } else {
                $stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ?, posting_date = ?, status = ?, category = ? WHERE id = ?");
                $stmt->bind_param("sssisi", $title, $content, $postingDate, $status, $category, $id);
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO announcements (title, content, image, posting_date, status, category) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $title, $content, $image, $postingDate, $status, $category);
        }

        // Check if statement preparation failed
        if (!$stmt) {
            $response['message'] = "Statement preparation failed: " . $conn->error;
            error_log("SQL Error: " . $conn->error);
            echo json_encode($response);
            exit();
        }

        // Execute the statement and check for execution errors
        if (!$stmt->execute()) {
            $response['message'] = "Statement execution failed: " . $stmt->error;
            error_log("Execution Error: " . $stmt->error);
            echo json_encode($response);
            exit();
        }

        $response = ["status" => "success", "message" => "Announcement saved successfully!"];
        $stmt->close();
        echo json_encode($response);
        exit();
    }

    // Fetch announcements from the database
    $result = $conn->query("SELECT * FROM announcements ORDER BY posting_date DESC");
    if (!$result) {
        error_log("Fetch Error: " . $conn->error);
    }

} catch (Exception $e) {
    error_log("Unexpected error: " . $e->getMessage());
    $response['message'] = "An unexpected server error occurred.";
    echo json_encode($response);
    exit();
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css">
    <style>
         .content-container {
            padding: 20px;
        }
        .fixed-height {
            height: 580px;
            overflow-y: auto;
        }
        .announcement-border, .posted-announcements-border, .create-announcement-border {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #fff;
        }
        .announcement-card {
            width: 150px;
            margin-right: 10px;
            border-radius: 10px;
            overflow: hidden;
            background-color: #f8f9fc;
            white-space: normal;
            flex-shrink: 0;
            transition: transform 0.3s ease;
        }
        .announcement-card img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-bottom: 1px solid #ddd;
        }
        .announcement-card h6 {
            margin: 10px;
            font-size: 1rem;
        }
        .announcement-card p {
            margin: 0 10px 10px;
            font-size: 0.875rem;
            color: #666;
        }
        .dataTables_wrapper .dataTables_length, 
        .dataTables_wrapper .dataTables_filter {
            padding: 10px 0;
        }
        .table th, .table td {
            vertical-align: middle;
            padding: 2px 4px; /* Reduce padding */
            white-space: nowrap; /* Prevent excessive expansion for smaller text */
            overflow: hidden; /* Hide overflow */
            text-overflow: ellipsis; /* Show ellipsis for overflowed content */
          
            text-align: center; /* Center-align text in table cells */
            vertical-align: middle; /* Center-align vertically for more balanced spacing */
          
        }

        /* Set a fixed width for the Action column */
        .table th:nth-child(6), .table td:nth-child(6) {
        
            text-align: center;
            vertical-align: middle;
        }

        .table th {
            width: auto; /* Allow headers to adjust to content */
            text-align: center; /* Center align headers */
            
        }

        .table td {
        vertical-align: middle;
        }


        .table td.content-cell {
        white-space: normal; /* Allow text wrapping only for content cells */
        word-wrap: break-word;
        max-width: 250px; /* Set max width for content cell to prevent table stretching */
        }

        /* Center-align the content in the Title column */
        .table th:nth-child(1),
        .table td:nth-child(1) {
            text-align: center; /* Center the text horizontally */
            vertical-align: middle; /* Center the text vertically */
        }


        
        .table tbody td {
            padding: 6px 4px;
            vertical-align: middle;
        }
        .table .content-cell {
            white-space: normal; /* Allow text wrapping */
            word-wrap: break-word;
            transition: all 0.3s ease; /* Smooth transition for row height */
        }

        .table tbody tr {
            transition: height 0.3s ease; /* Smooth row height transition */
        }

        .table .content-cell.full-text {
            white-space: normal;
            max-width: none;
        }
        .table .content-cell .see-more {
            display: block;
            color: #007bff;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: underline;
        }
        .table .content-cell .see-more:hover {
            color: #0056b3;
        }
        .table .action-icons {
            table-layout: fixed;
          text-align: center;
          vertical-align: middle;
            gap: 5px;
            height: 60px;
        }
        .table .action-icons a {
            display: inline-block; /* Set display to inline-block to keep each icon centered */
            width: 35px; /* Set width for consistency */
            height: 35px; /* Set height for consistency */
            background-color: #f8f9fa; /* Icon background */
            border-radius: 5px;
            font-size: 1rem;
            line-height: 35px;
        }
        .table .action-icons i {
            cursor: pointer;
            font-size: 1.2rem;
        }
        .action-icons {
        justify-content: center;
        align-items: center;
        gap: 10px; /* Adjust gap between icons */
        }

        .action-icons a {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px; /* Adjust icon size if needed */
        height: 30px;
        background-color: #f8f9fa;
        border-radius: 5px;
        font-size: 1rem;
        }
        .table tbody tr {
            transition: height 0.3s ease;  /* Smooth transition when row expands/collapses */
        }

        .btn-sm {
            font-size: 0.875rem;
            margin-bottom: 5px;
        }
        #createAnnouncementCard {
            display: none;
            height: 650px;
        }
        .dataTables_wrapper {
            width: 100%;
            overflow-x: auto;
        }
        .posted-announcement {
            display: flex;
            overflow-x: auto;
            gap: 15px;
            padding: 10px 0;
            white-space: nowrap;
            scroll-behavior: smooth;
            position: relative;
        }
        .posted-announcement::-webkit-scrollbar {
            display: none;
        }
        .scroll-button {
            background-color: #007bff;
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 24px;
            cursor: pointer;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            padding: 10px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 2;
        }
        .scroll-button:hover {
            background-color: #0056b3;
            transform: translateY(-50%) scale(1.1);
        }
        .scroll-button.left {
            left: 0;
        }
        .scroll-button.right {
            right: 0;
        }
        .scroll-button:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.5);
        }
        .posted-announcements-border {
            position: relative;
            padding: 20px 5;
        }
        .custom-alert {
            font-size: 1.1rem;
            border-radius: 5px;
            margin-top: 10px;
            display: none;
        }
        
        .d-none {
            display: none !important;
        }

        .hidden-element {
    display: none !important;
        }

        /* Ensure the table layout remains fixed */
        #announcementsTable {
            table-layout: fixed;
        }

        .content-cell {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .content-cell.expanded {
            white-space: normal;
        }

        .status-column {
            text-align: center; /* Center-align content if needed */
            table-layout: fixed;
            width: 60px; /* Adjust this as necessary */
        
        }
        .editImage{
            height: 20px;
        }
 
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="main-content" id="main-content">
        <div class="container-fluid content-container">
            <div id="alertPlaceholder" class="alert alert-success custom-alert" role="alert"></div>

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Announcements</h1>
            </div>
            <div class="row">
            <div class="col-md-12" id="announcementsContainer"> <!-- Full width by default -->
                <div class="announcement-border">
                    <div class="d-flex justify-content-between">
                        <h5>Announcements</h5>
                        <button id="createAnnouncementBtn" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i></button>
                    </div>
                    <div class="table-responsive">
                        <table id="announcementsTable" class="table table-striped table-bordered" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                  
                                    <th>Category</th>
                                    <th>Date Posted</th>
                                    <th class="status-column">Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                                        
                                        <td><?php echo htmlspecialchars($row['category']); ?></td> <!-- Display Category -->
                                        <td><?php echo htmlspecialchars($row['posting_date']); ?></td>
                                        <td>
                                            <input type="checkbox" class="status-checkbox" data-id="<?php echo $row['id']; ?>" 
                                                <?php echo isset($row['status']) && $row['status'] == 1 ? 'checked disabled' : ''; ?>>
                                        </td>
                                        <td class="action-icons">
                                          <a href="#" class="text-primary edit-announcement" data-id="<?php echo $row['id']; ?>" data-bs-toggle="modal" data-bs-target="#editAnnouncementModal" title="Edit"><i class="fas fa-edit"></i></a>
                                            <a href="#" class="text-danger delete-announcement" data-id="<?php echo $row['id']; ?>" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="posted-announcements-border mt-3">
                        <h5>Posted Announcements</h5>
                        <button class="scroll-button left" onclick="scrollToLeft()">&#8249;</button>
                        <button class="scroll-button right" onclick="scrollToRight()">&#8250;</button>
                        <div class="scroll-container position-relative">
                            <div class="posted-announcement" id="postedAnnouncements">
                                <?php 
                                $result = $conn->query("SELECT * FROM announcements WHERE status = 1 ORDER BY posting_date DESC");
                                while($row = $result->fetch_assoc()): ?>
                                <div class="announcement-card" data-id="<?php echo $row['id']; ?>">
    <img src="../uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Announcement Image">
    <div>
        <h6><?php echo htmlspecialchars($row['title']); ?></h6>
        <p><?php echo htmlspecialchars($row['posting_date']); ?></p>
    </div>
</div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>


            <div class="col-md-4" id="createAnnouncementCard">
                <div class="create-announcement-border fixed-height">
                    <h5>Create Announcement</h5>
                        <form id="createAnnouncementForm" enctype="multipart/form-data">
                        <form id="createAnnouncementForm" enctype="multipart/form-data">
                            <input type="hidden" id="announcementId" name="id">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="postingDate" class="form-label">Posting Date</label>
                                <input type="date" class="form-control" id="postingDate" name="postingDate" required>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-control" id="category" name="category" required>
                                    <option value="announcement">Announcement</option>
                                    <option value="event">Event</option>
                                    <option value="reminder">Reminder</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="image" class="form-label">Image</label>
                                <input type="file" class="form-control" id="image" name="image">
                            </div>
                            <div class="mb-3">
                                <label for="content" class="form-label">Content</label>
                                <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Post Now</label>
                                <input type="checkbox" id="status" name="status">
                            </div>
                            <button type="submit" class="btn btn-primary">Post</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Announcement Modal -->
    <!-- Edit Announcement Modal -->
<div class="modal fade" id="editAnnouncementModal" tabindex="-1" aria-labelledby="editAnnouncementModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAnnouncementModalLabel">Edit Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editAnnouncementForm" enctype="multipart/form-data">
                    <input type="hidden" id="editId" name="id">
                    <div class="mb-3">
                        <label for="editTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="editTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPostingDate" class="form-label">Posting Date</label>
                        <input type="date" class="form-control" id="editPostingDate" name="postingDate" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCategory" class="form-label">Category</label>
                        <select class="form-control" id="editCategory" name="category" required>
                            <option value="announcement">Announcement</option>
                            <option value="event">Event</option>
                            <option value="reminder">Reminder</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editContent" class="form-label">Content</label>
                        <textarea class="form-control" id="editContent" name="content" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editStatus" class="form-label">Post Now</label>
                        <input type="checkbox" id="editStatus" name="status">
                    </div>
                    <div class="mb-3">
                        <label for="editImage" class="form-label">Image</label>
                        <input type="file" class="form-control" id="editImage" name="image">
                        <input type="hidden" id="currentImage" name="currentImage">
                        <img id="imagePreview" src="" alt="Current Image" style="margin-top: 10px; max-width: 100%; height: 100px;">
                    </div>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </form>
            </div>
        </div>
    </div>
</div>


    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmationModalLabel">Delete Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this announcement?</p>
                    <input type="hidden" id="deleteId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Success Modal -->
<div class="modal fade" id="deleteSuccessModal" tabindex="-1" aria-labelledby="deleteSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSuccessModalLabel">Success</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Announcement deleted successfully!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>


    <!-- Success Confirmation Modal -->
<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Success</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Announcement edited successfully!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="successModalOkButton" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

    

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="script3.js"></script>
    <script>
$(document).ready(function () {
    // Initialize DataTable
    $('#announcementsTable').DataTable();

    // Show/hide create announcement form
    $('#createAnnouncementBtn').click(function () {
        const createAnnouncementCard = $('#createAnnouncementCard');
        const announcementsContainer = $('#announcementsContainer');

        if (createAnnouncementCard.css('display') === 'none') {
            // Show the form and resize the table
            createAnnouncementCard.css('display', 'block').addClass('col-md-4');
            announcementsContainer.removeClass('col-md-12').addClass('col-md-8');
        } else {
            // Hide the form and reset the table to full width
            createAnnouncementCard.css('display', 'none').removeClass('col-md-4');
            announcementsContainer.removeClass('col-md-8').addClass('col-md-12');
        }

        console.log("Create Announcement Card toggled."); // Debugging line
    });

    // Populate edit modal with announcement data
    $(document).on('click', '.edit-announcement', function () {
        const id = $(this).data('id');
        $.ajax({
            url: 'fetch_announcement.php',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function (data) {
                if (data.status === 'error') {
                    showAlert(data.message, 'danger');
                } else {
                    $('#editId').val(data.id);
                    $('#editTitle').val(data.title);
                    $('#editPostingDate').val(data.posting_date);
                    $('#editContent').val(data.content);
                    $('#editCategory').val(data.category);
                    $('#editStatus').prop('checked', data.status == 1);
                    $('#currentImage').val(data.image);
                    $('#imagePreview').attr('src', '../uploads/' + data.image);
                    $('#editAnnouncementModal').modal('show');
                }
            },
            error: function () {
                showAlert('An error occurred. Please try again.', 'danger');
            }
        });
    });



    // Handle form submission for creating an announcement
    $('#createAnnouncementForm').submit(function(event) {
        event.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: 'notice.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                var res = JSON.parse(response);
                if (res.status === 'success') {
                    showAlert('Announcement created successfully!', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);  // wait a bit before reloading
                } else {
                    showAlert('Error: ' + res.message, 'danger');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                showAlert('An error occurred: ' + textStatus, 'danger');
            }
        });
    });


    // Custom alert function
    function showAlert(message, type) {
        const alertPlaceholder = $('#alertPlaceholder');
        alertPlaceholder
            .removeClass('d-none alert-success alert-danger')
            .addClass(`alert-${type}`)
            .text(message)
            .fadeIn();

        setTimeout(() => alertPlaceholder.fadeOut(), 3000);
    }
});

            // Populate edit modal with announcement data
      // Populate edit modal with announcement data
    $(document).on('click', '.edit-announcement', function () {
        const id = $(this).data('id');
        $.ajax({
            url: 'fetch_announcement.php',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function (data) {
                if (data.status === 'error') {
                    showAlert(data.message, 'danger');
                } else {
                    $('#editId').val(data.id);
                    $('#editTitle').val(data.title);
                    $('#editPostingDate').val(data.posting_date);
                    $('#editContent').val(data.content);
                    $('#editCategory').val(data.category);
                    $('#editStatus').prop('checked', data.status == 1);
                    $('#currentImage').val(data.image);
                    $('#imagePreview').attr('src', '../uploads/' + data.image);
                    $('#editAnnouncementModal').modal('show');
                }
            },
            error: function () {
                showAlert('An error occurred. Please try again.', 'danger');
            }
        });
    });


          
 // Handle form submission for editing an announcement
// Handle form submission for editing an announcement
$('#editAnnouncementForm').submit(function (event) {
        event.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: 'notice.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                const res = JSON.parse(response);
                if (res.status === 'success') {
                    const announcementId = $('#editId').val();
                    const title = $('#editTitle').val();
                    const content = $('#editContent').val();
                    const postingDate = $('#editPostingDate').val();
                    const category = $('#editCategory').val();
                    const status = $('#editStatus').is(':checked') ? 1 : 0;

                    const row = $('#announcementsTable').find('tr').filter(function () {
                        return $(this).find('.edit-announcement').data('id') == announcementId;
                    });

                    // Update the fields in the table
                    row.find('td:nth-child(1)').text(title); // Title
                    row.find('td:nth-child(2) .truncated-content').text(content.slice(0, 100) + "..."); // Content
                    row.find('td:nth-child(3)').text(category); // Category
                    row.find('td:nth-child(4)').text(postingDate); // Date Posted
                    row.find('td:nth-child(5) input[type="checkbox"]').prop('checked', status === 1); // Status

                    // Check if a new image was uploaded
                    if ($('#editImage')[0].files.length > 0) {
                        const newImageURL = URL.createObjectURL($('#editImage')[0].files[0]);

                        // Update image in the table row
                        row.find('img').attr('src', newImageURL);

                        // Update the image in the posted announcements carousel
                        const carouselImage = $('#postedAnnouncements').find(`.announcement-card[data-id="${announcementId}"] img`);
                        if (carouselImage.length > 0) {
                            carouselImage.attr('src', newImageURL);
                        }

                        // Update image preview in the modal
                        $('#imagePreview').attr('src', newImageURL);
                    }

                    $('#editAnnouncementModal').modal('hide');
                    $('#successModal').modal('show');
                } else {
                    showAlert('Error: ' + res.message, 'danger');
                }
            },
            error: function () {
                showAlert('An error occurred. Please try again.', 'danger');
            }
        });
   


    // Function to remove lingering backdrop when modals are hidden
    $('#editAnnouncementModal, #successModal').on('hidden.bs.modal', function () {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    });

    function showAlert(message, type) {
        const alertPlaceholder = $('#alertPlaceholder');
        alertPlaceholder
            .removeClass('d-none alert-success alert-danger')
            .addClass(`alert-${type}`)
            .text(message)
            .fadeIn();

        setTimeout(() => alertPlaceholder.fadeOut(), 3000);
    }
});


// Handle delete announcement
$(document).on('click', '.delete-announcement', function() {
    var id = $(this).data('id');
    $('#deleteId').val(id);
});

$('#confirmDelete').click(function() {
    var id = $('#deleteId').val();
    $.ajax({
        url: 'delete_announcement.php',
        type: 'POST',
        data: { id: id },
        success: function(response) {
            var res = JSON.parse(response);
            if (res.status === 'success') {
                // Show the delete success modal instead of an alert
                $('#deleteConfirmationModal').modal('hide'); // Close the confirmation modal
                $('#deleteSuccessModal').modal('show'); // Show success modal

                // Optionally, refresh the table content dynamically instead of reloading
                setTimeout(() => {
                    location.reload(); // Reload page after delay
                }, 1500);
            } else {
                showAlert('Error: ' + res.message, 'danger');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            showAlert('An error occurred: ' + textStatus, 'danger');
        }
    });
});

        // Scroll functions for posted announcements
        function scrollToLeft() {
            const postedAnnouncements = document.getElementById('postedAnnouncements');
            postedAnnouncements.scrollBy({
                left: -150,
                behavior: 'smooth'
            });
        }

        function scrollToRight() {
            const postedAnnouncements = document.getElementById('postedAnnouncements');
            postedAnnouncements.scrollBy({
                left: 150,
                behavior: 'smooth'
            });
        }

        // Custom alert function
        function showAlert(message, type) {
            const alertPlaceholder = document.getElementById('alertPlaceholder');
            alertPlaceholder.classList.remove('d-none', 'alert-success', 'alert-danger');
            alertPlaceholder.classList.add(`alert-${type}`);
            alertPlaceholder.textContent = message;
            alertPlaceholder.style.display = 'block';

            setTimeout(() => {
                alertPlaceholder.style.display = 'none';
                alertPlaceholder.classList.add('d-none');
            }, 3000);
        }


   
    </script>
</body>
</html>
