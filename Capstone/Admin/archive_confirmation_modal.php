<?php
include '../db.php';

// Check if 'archived' parameter exists in the URL and show the success message if it does
if (isset($_GET['archived']) && $_GET['archived'] == 'success') {
    echo '<div class="alert alert-success" id="archiveSuccessAlert">Archived Successfully</div>';
}
?>

<!-- Archive Confirmation Modal -->
<div class="modal fade" id="archiveConfirmationModal" tabindex="-1" aria-labelledby="archiveConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="archiveConfirmationModalLabel">Confirm Archiving</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to archive the selected user(s)?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmArchiveButton">Archive</button>
            </div>
        </div>
    </div>
</div>

<!-- Archive Success Modal -->
<div class="modal fade" id="archiveSuccessModal" tabindex="-1" aria-labelledby="archiveSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="archiveSuccessModalLabel">Success</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                User(s) archived successfully!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery (to handle archiving logic and showing the message) -->
<script>
$(document).ready(function() {
    // Handle Archive Confirmation Button click
    $('#confirmArchiveButton').click(function () {
        var userIds = $(this).data('user-ids'); // Retrieve user IDs

        // Send the selected user IDs to archive in a batch
        $.post('archive_user_admin.php', { ids: userIds }, function (response) {
            if (response.success) {
                // Hide the confirmation modal
                $('#archiveConfirmationModal').modal('hide');

                // Show the success modal
                $('#archiveSuccessModal').modal('show');

                // Optionally, remove the archived rows from the table
                $('.select-checkbox:checked').each(function () {
                    table.row($(this).parents('tr')).remove().draw(); // Remove each row
                });

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
});
</script>
