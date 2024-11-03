<?php
session_start(); // Start the session

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../Login/login.php"); // Redirect to login if not an admin
    exit();
}
include '../db.php';


// Fetch grade levels for the dropdown
$gradeLevelsQuery = "SELECT id, grade_level FROM grade_levels";
$gradeResult = $conn->query($gradeLevelsQuery);

// Fetch sections for the dropdown
$sectionsQuery = "SELECT id, section FROM sections";
$sectionsResult = $conn->query($sectionsQuery);

// Fetch search parameters from POST request
$name = isset($_POST['name']) ? $_POST['name'] : '';
$grade_level_id = isset($_POST['grade_level']) ? $_POST['grade_level'] : '';
$section_id = isset($_POST['section']) ? $_POST['section'] : '';

// Initialize student details variables at the beginning
$lrn = '';
$rfid_uid = '';
$student_name = '';
$address = '';
$age = '';
$sex = '';
$image = '';
$grade_level_name = '';
$section_name = '';

// Flag to check if a student is found
$studentFound = false;

// Prepare the query based on search input
$sql = "SELECT students.lrn, students.rfid_uid, students.address, students.age, students.sex, students.name, students.image, 
        grade_levels.grade_level, sections.section 
        FROM students
        LEFT JOIN grade_levels ON students.grade_level_id = grade_levels.id
        LEFT JOIN sections ON students.section_id = sections.id
        WHERE 1=1";

// Append conditions based on inputs
$params = [];
$types = '';

if (!empty($name)) {
    $sql .= " AND students.name LIKE ?";
    $name = "%$name%";
    $params[] = $name;
    $types .= 's';
}

if (!empty($grade_level_id)) {
    $sql .= " AND students.grade_level_id = ?";
    $params[] = $grade_level_id;
    $types .= 'i';
}

if (!empty($section_id)) {
    $sql .= " AND students.section_id = ?";
    $params[] = $section_id;
    $types .= 'i';
}

// Only proceed if there are any search parameters
if (!empty($name) || !empty($grade_level_id) || !empty($section_id)) {
    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch data if any student is found
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lrn = $row['lrn'];
        $rfid_uid = $row['rfid_uid'];
        $student_name = $row['name'];
        $address = $row['address'];
        $age = $row['age'];
        $sex = $row['sex'];
        $image = $row['image'];
        $grade_level_name = $row['grade_level'];
        $section_name = $row['section'];
        $studentFound = true; // Student found
    }

    $stmt->close();
} 

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <!-- Bootstrap CSS and other styles -->

     <!-- Bootstrap CSS -->
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Styling for content */
        .main-content {
            display: flex;
            flex-wrap: wrap;
            height: calc(100vh - 86px);
            overflow-y: auto;
        }

        /* Profile and details boxes */
        .white-box-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .first-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .white-box-small {
            width: 25%;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .white-box-medium {
            flex: 1;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            overflow-y: visible;
        }

        /* Profile Card Styling */
        .profile-card {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .profile-card img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #007bff;
        }

        .profile-card h3 {
            margin-top: 10px;
            font-size: 16px;
            font-weight: bold;
        }
        .profile-card h6 {
        text-align: left; 
        width: 100%; /* Ensures the h6 takes the full width */
        margin-left: 15px; /* Optional: Removes extra margin */

        font-size: 16px;
        color: #333;
        font-weight: bold;
    }
        .profile-card-two-value {
            display: table;
            width: 95%;
            border-collapse: collapse;
            font-size: 13px;
            font-weight: bold;
            margin: 15px;
        }

        .profile-card-two-value div {
            display: table-row;
        }

        .profile-card-two-value .label,
        .profile-card-two-value .value {
            display: table-cell;
            padding: 10px 15px;
            border: 1px solid #ddd;
        }

        .profile-card-two-value .label {
            width: 25%;
            text-align: left;
        }

        .profile-card-two-value .value {
            text-align: left;
        }

        /* Filter bar styling */
        .filter-bar {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            padding: 30px;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .filter-bar input,
        .filter-bar select {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            min-width: 150px;
        }

        .filter-bar button {
            background-color: blue;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
        }


        hr.fixed-hr {
    width: 240px; /* Set a fixed width for the hr tag */
      border: none;
      border-top: 2px solid black; /* Adjust thickness and color */
      margin: 10px auto; /* Center the hr */
      display: block; /* Ensure it is block-level */
      
  }



          /* Large box spans full width */
   /* Second Row: Fourth White Box */
.white-box-large {
    width: 100%;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #fff;
    margin-top: 20px;
    display: flex;
    
}


  /* Second Row: Fourth White Box */
  .profile-card-three-value {
    display: table; /* Change to table layout */
    width: 97%; /* Make it take full width */
    border-collapse: collapse; /* Ensure borders are clean and collapse nicely */
    font-size: 13px;
    color: #333;
    font-weight: bold;
    margin: 15px;
    
    
}

.profile-card-three-value div {
    display: table-row; /* Each label-value pair should be a row */
}

.profile-card-three-value .label,
.profile-card-three-value .value {
    display: table-cell; /* Align them as cells in a table */
    padding: 10px 15px; /* Add padding for spacing */
    border: 1px solid #ddd; /* Add borders like the first image */
}

.profile-card-three-value .label {
    width: 25%; /* Adjust label width */
    text-align: left;
    padding-right: 10px; /* Space between the label and value */
}

.profile-card-three-value .value {
    text-align: right;
}



  #year {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 5px;
    font-size: 14px;
    width: 150px; /* Control the dropdown width */
    margin-bottom: 10px;
    
   
}
    </style>

</head>
<body>
    <!-- Navbar and other HTML elements -->

     <!-- Navbar -->
     <?php include 'navbar.php'; ?>

<!-- Main Content -->
<div class="main-content" id="main-content">
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Student Profile</h1>
        </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="POST" action="Student_Profile.php">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" placeholder="Enter name" required>

            <label for="grade-level">Grade Level:</label>
            <select id="grade-level" name="grade_level" required>
                <option value="">Select Grade</option>
                <?php
                if ($gradeResult->num_rows > 0) {
                    while ($row = $gradeResult->fetch_assoc()) {
                        echo '<option value="'.$row['id'].'">'.$row['grade_level'].'</option>';
                    }
                }
                ?>
            </select>

            <label for="section">Section:</label>
            <select id="section" name="section" required>
                <option value="">Select Section</option>
                <?php
                if ($sectionsResult->num_rows > 0) {
                    while ($row = $sectionsResult->fetch_assoc()) {
                        echo '<option value="'.$row['id'].'">'.$row['section'].'</option>';
                    }
                }
                ?>
            </select>

            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <!-- White Box (Parent Container) -->
    <div class="white-box-container">
        <?php if ($studentFound): ?>
            <div class="first-row">
                <!-- Small White Box (Profile Card) -->
                <div class="white-box-small">
                    <div class="profile-card">
                        <!-- Ensure the correct image path -->
                        <?php if (!empty($image) && file_exists('../' . $image)): ?>
                            <!-- Display the image stored in the database -->
                            <img src="<?php echo '../' . htmlspecialchars($image); ?>" alt="Profile Picture">
                        <?php else: ?>
                            <!-- Fallback to a default image if the profile picture doesn't exist -->
                            <img src="../uploads/default-avatar.png" alt="Profile Picture">
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($student_name); ?></h3>
                        <hr class="fixed-hr">
                        <h6>Student Id: <?php echo htmlspecialchars($lrn); ?></h6>
                        <h6>Section: <?php echo htmlspecialchars($section_id); ?></h6>
                        <h6>Grade level: <?php echo htmlspecialchars($grade_level_id); ?></h6>
                    </div>
                </div>
                <!-- Additional content -->

                <!-- Medium White Box (Second Box) -->
                <div class="white-box-medium">
                        <div class="profile-card-two-value">
                            <h6 style="font-size: 16px; font-weight: bold; color: #333; margin-bottom: 10px;">
                                <i class="fas fa-info-circle"></i> GENERAL INFO
                            </h6>
                            <div>
                                <div class="label">LRN:</div>
                                <div class="value"><?php echo htmlspecialchars($lrn); ?></div>
                            </div>
                            <div>
                                <div class="label">RFID TAG:</div>
                                <div class="value"><?php echo htmlspecialchars($rfid_uid); ?></div>
                            </div>


                    <div>
                    <div class="label">GRADE LEVEL:</div>
                    <div class="value"><?php echo htmlspecialchars($grade_level_id); ?></div>
                    </div>

                    <div>
                    <div class="label">SECTION:</div>
                    <div class="value"><?php echo htmlspecialchars($section_id); ?></div>
                    </div>


                            <div>
                                <div class="label">ADDRESS:</div>
                                <div class="value"><?php echo htmlspecialchars($address); ?></div>
                            </div>
                            <div>
                                <div class="label">AGE:</div>
                                <div class="value"><?php echo htmlspecialchars($age); ?></div>
                            </div>
                            <div>
                                <div class="label">SEX:</div>
                                <div class="value"><?php echo htmlspecialchars($sex); ?></div>
                            </div>

                            <div>
                    <div class="label">GUARDIAN#:</div>
                    <div class="value">09012345678</div>
                    </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Second Row: Fourth White Box -->
    <div class="second-row">
        <!-- Fourth White Box (New Box) -->
        <div class="white-box-large">
        <div class="profile-card-three-value">  
        <h6 style="font-size: 16px; font-weight: bold; color: red; margin-bottom: 20px;">
       <i class="fas fa-times-circle" style="font-size: 17px; color: red;"></i> ABSENT(S)
       </h6>
      <!-- Dropdown for selecting the year aligned to the right -->
      <select id="year" name="year" class="form-select" style="width: 150px; font-size: 14px;display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">

                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                    <option value="2022">2022</option>
                    <option value="2021">2021</option>
                    <!-- Add more years as needed -->
                </select>
       <div>
                <div class="label">JANUARY:</div>
                    <div class="value">0</div>
                </div>
            <div>
                    <div class="label">FEBRUARY:</div>
                    <div class="value">0</div>
            </div>
            <div>
                    <div class="label">MARCH:</div>
                    <div class="value">0</div>
            </div>
            <div>
                    <div class="label">APRIL:</div>
                    <div class="value">4</div>
            </div>
            <div>
                    <div class="label">MAY:</div>
                    <div class="value">1</div>
            </div>
            <div>
                    <div class="label">JUNE:</div>
                    <div class="value">1</div>
            </div>
            <div>
                    <div class="label">JULY:</div>
                    <div class="value">0</div>
            </div>
            <div>
                    <div class="label">AUGUST:</div>
                    <div class="value">8</div>
            </div>
            <div>
                    <div class="label">SEPTEMBER:</div>
                    <div class="value">0</div>
            </div>
            <div>
                    <div class="label">OCTOBER:</div>
                    <div class="value">0</div>
            </div>
            <div>
                    <div class="label">NOVEMBER:</div>
                    <div class="value">0</div>
            </div>
            <div>
                    <div class="label">DECEMBER:</div>
                    <div class="value">0</div>
            </div>
        </div>
    </div>
    </div>
</div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger" role="alert">
                No students found.
            </div>
        <?php endif; ?>
    </div>

    <!-- Include jQuery and Bootstrap JS -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>   // addedddd 
$(document).ready(function() {
    // Listen for changes in the name input field
    $('#name').on('input', function() {
        let studentName = $(this).val().trim();

        // If the name field is not empty, send an AJAX request
        if (studentName !== '') {
            $.ajax({
                url: 'fetch_student_info.php', // Replace with the correct PHP file path
                method: 'POST',
                data: {
                    name: studentName
                },
                success: function(response) {
                    // Assuming response is in JSON format: { "grade_level_id": "1", "section_id": "2" }
                    let studentInfo = JSON.parse(response);

                    // Set the grade level dropdown
                    $('#grade-level').val(studentInfo.grade_level_id).trigger('change');

                    // Set the section dropdown
                    $('#section').val(studentInfo.section_id).trigger('change');
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching student info:', error);
                }
            });
        }
    });
});

</script>

</body>
</html>