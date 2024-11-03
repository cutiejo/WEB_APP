// Edit Button Click
$('.btn-outline-primary').on('click', function () {
    let selectedRow = $('#studentTable tbody input.select-checkbox:checked').closest('tr');
    if (selectedRow.length > 0) {
        let studentData = selectedRow.children('td').map(function () {
            return $(this).text();
        }).get();

        $('#editStudentModal #lrn').val(studentData[1]);
        $('#editStudentModal #name').val(studentData[2]);
        $('#editStudentModal #birth_date').val(studentData[3]);
        $('#editStudentModal #rfid_tag').val(studentData[4]);
        $('#editStudentModal #age').val(studentData[5]);
        $('#editStudentModal #address').val(studentData[6]);

        // Set sex with a placeholder if not set
        $('#editStudentModal #sex').val(studentData[7] || "");

        $('#editStudentModal #grade_level').val(studentData[8]);
        $('#editStudentModal #section').val(studentData[9]);
        $('#editStudentModal #guardian').val(studentData[10]);
        $('#editStudentModal #contact').val(studentData[11]);

        $('#editStudentModal').modal('show');
    } else {
        alert('Please select a student to edit.');
    }
});



// Archive Confirmation
$('#confirmArchive').on('click', function() {
    $('#archiveStudentForm').submit();
});

// Delete Confirmation
$('#confirmDelete').on('click', function() {
    $('#deleteStudentForm').submit();
});

$(document).ready(function() {
    // Archive Button Click
    $('.btn-outline-warning').on('click', function () {
        let selectedRow = $('#studentTable tbody input.select-checkbox:checked').closest('tr');
        if (selectedRow.length > 0) {
            $('#archiveStudentModal').modal('show');
        } else {
            alert('Please select a student to archive.');
        }
    });

    // Delete Button Click
    $('.btn-outline-danger').on('click', function () {
        let selectedRow = $('#studentTable tbody input.select-checkbox:checked').closest('tr');
        if (selectedRow.length > 0) {
            $('#deleteStudentModal').modal('show');
        } else {
            alert('Please select a student to delete.');
        }
    });

    // Archive Student Form Submission
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
            if (res.status === 'success') {
                alert(res.message);
                location.reload();  // This reloads the entire page

                // OR dynamically remove the row without reloading the page
                // selectedRows.closest('tr').remove();  // Uncomment this line to remove rows dynamically
            } else {
                alert(res.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error archiving student:', error);
        }
    });
});


    // Delete Student Form Submission
    // Delete Student Form Submission
$('#deleteStudentForm').on('submit', function(event) {
    event.preventDefault();

    let selectedRows = $('#studentTable tbody input.select-checkbox:checked').closest('tr');
    let studentIds = selectedRows.map(function () {
        return $(this).children('td:first').text();
    }).get();

    console.log(studentIds); // Log the IDs to ensure correct data is being sent

    $.ajax({
        url: 'delete_student.php',
        type: 'POST',
        data: { ids: studentIds },
        success: function(response) {
            let res = JSON.parse(response);
            if (res.status === 'success') {
                alert(res.message);
                location.reload();  // This reloads the entire page

                // OR dynamically remove the row without reloading the page
                // selectedRows.closest('tr').remove();  // Uncomment this line to remove rows dynamically
            } else {
                alert(res.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error deleting student:', error);
        }
    });
});

});    