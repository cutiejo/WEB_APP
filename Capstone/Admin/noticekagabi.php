<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../db.php';

$response = ["status" => "error", "message" => "Something went wrong."];

// Handle form submission for creating or editing an announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $title = $_POST['title'];
    $content = $_POST['content'];
    $postingDate = $_POST['postingDate'];
    $category = $_POST['category'];  // Get the category from the form
    $status = isset($_POST['status']) ? 1 : 0;

    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/";
        $image = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Image uploaded successfully
        } else {
            $response = ["status" => "error", "message" => "Error uploading the file."];
            echo json_encode($response);
            exit();
        }
    } else {
        if ($id > 0 && isset($_POST['currentImage'])) {
            $image = $_POST['currentImage']; // Preserve current image during edit
        }
    }

    if ($id > 0) {
        // Update existing announcement
        if ($image) {
            $stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ?, image = ?, posting_date = ?, status = ?, category = ? WHERE id = ?");
            $stmt->bind_param("ssssisi", $title, $content, $image, $postingDate, $status, $category, $id);
        } else {
            $stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ?, posting_date = ?, status = ?, category = ? WHERE id = ?");
            $stmt->bind_param("sssisi", $title, $content, $postingDate, $status, $category, $id);
        }
    } else {
        // Create new announcement
        $stmt = $conn->prepare("INSERT INTO announcements (title, content, image, posting_date, status, category) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $title, $content, $image, $postingDate, $status, $category);
    }

    if ($stmt->execute()) {
        $response = ["status" => "success", "message" => "Announcement saved successfully!"];
    }
    $stmt->close();

    echo json_encode($response);
    exit();
}

// Fetch announcements from the database
$result = $conn->query("SELECT * FROM announcements ORDER BY posting_date DESC");
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
        .table-container {
            width: 100%;
            padding: 15px;
        }

        .full-width-table {
            width: 100%;
        }

        .content-container {
            padding: 20px;
        }

        .fixed-height {
            height: 300px;
            overflow-y: auto;
        }

        .announcement-border,
        .posted-announcements-border,
        .create-announcement-border {
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

        .table th,
        .table td {
            vertical-align: middle;
        }

        .table tbody td {
            padding: 6px 4px;
            vertical-align: middle;
        }

        .table .content-cell {
            white-space: normal;
            /* Allow text wrapping */
            word-wrap: break-word;
            transition: all 0.3s ease;
            /* Smooth transition for row height */
        }

        .table tbody tr {
            transition: height 0.3s ease;
            /* Smooth row height transition */
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
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            height: 50px;
        }

        .table .action-icons a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .table .action-icons i {
            cursor: pointer;
            font-size: 1.2rem;
        }

        .table tbody tr {
            transition: height 0.3s ease;
            /* Smooth transition when row expands/collapses */
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
            <!-- Main Row -->
            <div class="row">
                <!-- Table Section -->
                <div class="col-md-12" id="tableSection">
                    <div class="announcement-border">
                        <div class="d-flex justify-content-between">
                            <h5>Announcements</h5>
                            <button id="createAnnouncementBtn" class="btn btn-primary btn-sm"><i
                                    class="fas fa-plus"></i></button>
                        </div>
                        <div class="table-responsive">
                            <table id="announcementsTable" class="table table-striped table-bordered full-width-table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Content</th>
                                        <th>Category</th>
                                        <th>Date Posted</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                                            <td class="content-cell">
                                                <div class="truncated-content">
                                                    <?php echo substr(htmlspecialchars($row['content']), 0, 100); ?>...
                                                </div>
                                                <span class="see-more"
                                                    onclick="toggleContent(<?php echo $row['id']; ?>)">See More</span>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                                            <td><?php echo htmlspecialchars($row['posting_date']); ?></td>
                                            <td>
                                                <input type="checkbox" class="status-checkbox" <?php echo $row['status'] == 1 ? 'checked disabled' : ''; ?>>
                                            </td>
                                            <td>
                                                <a href="#" class="text-primary edit-announcement"
                                                    data-id="<?php echo $row['id']; ?>" data-bs-toggle="modal"
                                                    data-bs-target="#editAnnouncementModal" title="Edit"><i
                                                        class="fas fa-edit"></i></a>
                                                <a href="#" class="text-danger delete-announcement"
                                                    data-id="<?php echo $row['id']; ?>" title="Delete"
                                                    data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal"><i
                                                        class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
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
                            while ($row = $result->fetch_assoc()): ?>
                                <div class="announcement-card">
                                    <img src="../uploads/<?php echo htmlspecialchars($row['image']); ?>"
                                        alt="Announcement Image">
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
            <!-- Create Announcement Section -->
            <div class="col-md-12" id="createAnnouncementCard">
                <div class="create-announcement-border">
                    <h5>Create Announcement</h5>
                    <form id="createAnnouncementForm">
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
    <div class="modal fade" id="editAnnouncementModal" tabindex="-1" aria-labelledby="editAnnouncementModalLabel"
        aria-hidden="true">
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
                            <img id="imagePreview" src="" alt="Current Image"
                                style="margin-top: 10px; max-width: 100%; height: auto;">
                        </div>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel"
        aria-hidden="true">
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="script3.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize DataTable
            $('#announcementsTable').DataTable();

            $('#createAnnouncementBtn').click(function () {
                $('#tableSection').toggle(); // Toggle table visibility
                $('#createAnnouncementCard').toggle(); // Toggle form visibility
            });
        });

        // Handle form submission for creating an announcement
        $('#createAnnouncementForm').submit(function (event) {
            event.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: 'notice.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
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
                error: function (jqXHR, textStatus, errorThrown) {
                    showAlert('An error occurred: ' + textStatus, 'danger');
                }
            });
        });

        // Populate edit modal with announcement data
        $(document).on('click', '.edit-announcement', function () {
            var id = $(this).data('id');
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
                        $('#editStatus').prop('checked', data.status == 1);
                        $('#currentImage').val(data.image);
                        $('#imagePreview').attr('src', '../uploads/' + data.image);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    showAlert('An error occurred: ' + textStatus, 'danger');
                }
            });
        });

        // Handle form submission for editing an announcement
        $('#editAnnouncementForm').submit(function (event) {
            event.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: 'notice.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    var res = JSON.parse(response);
                    if (res.status === 'success') {
                        showAlert('Announcement edited successfully!', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);  // wait a bit before reloading
                    } else {
                        showAlert('Error: ' + res.message, 'danger');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    showAlert('An error occurred: ' + textStatus, 'danger');
                }
            });
        });

        // Handle delete announcement
        $(document).on('click', '.delete-announcement', function () {
            var id = $(this).data('id');
            $('#deleteId').val(id);
        });

        $('#confirmDelete').click(function () {
            var id = $('#deleteId').val();
            $.ajax({
                url: 'delete_announcement.php',
                type: 'POST',
                data: { id: id },
                success: function (response) {
                    var res = JSON.parse(response);
                    if (res.status === 'success') {
                        showAlert('Announcement deleted successfully!', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert('Error: ' + res.message, 'danger');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
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


        //see more//
        function toggleContent(id) {
            var truncated = document.getElementById('truncated_' + id);
            var full = document.getElementById('full_' + id);
            var seeMore = document.querySelector('span.see-more[data-id="' + id + '"]');

            if (full.classList.contains('d-none')) {
                // Show the full content
                truncated.classList.add('d-none');
                full.classList.remove('d-none');
                seeMore.textContent = "See Less";
            } else {
                // Hide the full content
                full.classList.add('d-none');
                truncated.classList.remove('d-none');
                seeMore.textContent = "See More";
            }
        }


        // Toggle content function
        function toggleContent(id) {
            // Your logic for toggling full/partial content
        }


    </script>
</body>

</html>