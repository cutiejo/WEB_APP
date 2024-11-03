<?php
include '../db.php';

// Fetch all grade levels
$grade_levels = $conn->query("SELECT * FROM grade_levels");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Grade Levels</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Manage Grade Levels</h2>
        <a href="add_grade_level.php" class="btn btn-primary mb-3">Add Grade Level</a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Grade Level</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $grade_levels->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['grade_level']; ?></td>
                        <td>
                            <a href="edit_grade_level.php?id=<?php echo $row['id']; ?>" class="btn btn-warning">Edit</a>
                            <a href="delete_grade_level.php?id=<?php echo $row['id']; ?>" class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to delete this grade level?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>