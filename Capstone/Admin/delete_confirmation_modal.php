<?php
include '../db.php';

?>

<?php
include '../db.php';

// Check if 'deleted' parameter exists in the URL and show the success message if it does
if (isset($_GET['deleted']) && $_GET['deleted'] == 'success') {
    echo '<div class="alert alert-success" id="deleteSuccessAlert">Deleted Successfully</div>';
}
?>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the selected user(s)?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteButton">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Success Modal -->
<div class="modal fade" id="deleteSuccessModal" tabindex="-1" aria-labelledby="deleteSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSuccessModalLabel">Success</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                User(s) deleted successfully!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>


<!-- jQuery (to handle deletion logic and showing the message) -->
<script>
$(document).ready(function() {
    // Handle Delete Confirmation Button click
    $('#confirmDeleteButton').click(function () {
        var userIds = $(this).data('user-ids'); // Retrieve user IDs

        // Send the selected user IDs to delete in a batch
        $.post('delete_user_admin.php', { ids: userIds }, function (response) {
            if (response.success) {
                // Hide the confirmation modal
                $('#deleteConfirmationModal').modal('hide');

                // Show the success modal
                $('#deleteSuccessModal').modal('show');

                // Optionally, remove the deleted rows from the table
                $('.select-checkbox:checked').each(function () {
                    table.row($(this).parents('tr')).remove().draw(); // Remove each row
                });

            } else {
                alert("Error deleting users: " + response.error);
            }
        }, 'json').fail(function (xhr, status, error) {
            alert("An error occurred: " + error); // Improved error handling
            console.error(xhr.responseText);
        });
    });

    // Automatically hide the success alert if it's used elsewhere (non-modal)
    setTimeout(function() {
        $('#deleteSuccessAlert').fadeOut('slow');
    }, 5000); // 5000 milliseconds = 5 seconds
});

</script>

