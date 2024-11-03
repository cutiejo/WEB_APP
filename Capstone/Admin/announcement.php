<?php
session_start(); // Start the session

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}
include '../db.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcement</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        /* Custom styles */
        .announcement-container {
            display: flex;
            margin-left: -15px;
            justify-content: space-between;
            padding: 20px;
        }

        .announcement-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            width: 50%;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-right: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .announcement-card {
            display: flex;
            align-items: center;
            background-color: #fff;
            border-radius: 10px;
            padding: 15px;
            width: 100%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .announcement-card img {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 15px;
        }

        .announcement-card .details {
            display: flex;
            flex-direction: column;
        }

        .announcement-card h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: bold;
        }

        .announcement-card button {
            background-color: #137e5e;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            margin-left: auto;
        }

        .view-details-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .view-details-header i {
            font-size: 24px;
            color: #137e5e;
        }

        /* Fixed size details container */
        .details-container {
            display: none;
            width: 55%;
            height: 750px;
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            margin-right: auto;
            margin-bottom: 20px;
        }

        .details-container img {
            width: 100%;
            height: 200px;
            border-radius: 10px;
            object-fit: cover;
            cursor: pointer; /* Add pointer cursor to indicate clickability */
        }

        .details-container h3 {
            margin-top: 25px;
            font-size: 1.8rem;
            color: #2c3e50;
        }

        .details-container .date {
            color: #7f8c8d;
            font-size: 1rem;
            margin-top: 15px;
        }

        .details-container p {
            margin-top: 20px;
            font-size: 1.1rem;
            line-height: 1.7;
            word-wrap: break-word;
            font-weight: 400;
            color: #2c3e50;
            text-align: justify;
            padding: 10px;
            background-color: #f8f8f8;
            border-left: 4px solid #137e5e;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <div class="main-content" id="main-content">
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Announcement</h1>
            </div>

            <!-- Announcement Section -->
            <div class="announcement-container">
                <!-- Announcement List -->
                <div class="announcement-list">
                    <h5>Posted Announcements</h5>

                    <?php
                    // Fetching announcements from the database
                    $query = "SELECT * FROM announcements ORDER BY posting_date DESC";
                    $result = $conn->query($query);
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="announcement-card">';
                        echo '<img src="../uploads/' . $row['image'] . '" alt="Announcement Image">';
                        echo '<div class="details">';
                        echo '<h5>' . $row['title'] . '</h5>';
                        echo '<div class="date">Posted on: ' . $row['posting_date'] . '</div>';
                        echo '</div>';
                        echo '<button type="button" onclick="viewDetails(' . $row['id'] . ')">View Details</button>';
                        echo '</div>';
                    }
                    ?>
                </div>

                <!-- Announcement Details Section -->
                <div class="details-container" id="details-container">
                    <div class="view-details-header">
                        <i class="fas fa-info-circle"></i>
                        <h5>View details</h5>
                    </div>
                    <img src="default.jpg" id="detail-image" alt="Announcement Detail Image" onclick="viewImageFullScreen(this.src)">
                    <h3 id="detail-title">Title</h3>
                    <div class="date" id="detail-date">Posted on: Date</div>
                    <p id="detail-content">Content will appear here once you click on an announcement...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function viewDetails(id) {
            $.ajax({
                url: 'get_announcement.php', // Separate PHP file to fetch announcement details
                type: 'GET',
                data: { id: id },
                success: function (response) {
                    const data = JSON.parse(response);
                    $('#detail-image').attr('src', '../uploads/' + data.image);
                    $('#detail-title').text(data.title);
                    $('#detail-date').text('Posted on: ' + data.posting_date);
                    $('#detail-content').text(data.content);

                    // Show the details container when an announcement is clicked
                    $('.details-container').show();
                }
            });
        }

        function viewImageFullScreen(imageSrc) {
            // Open the image in a new window or tab for full-screen view
            window.open(imageSrc, '_blank');
        }
    </script>
</body>

</html>
