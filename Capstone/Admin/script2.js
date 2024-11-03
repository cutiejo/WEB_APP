

// Initialize DataTables
$(document).ready(function() {
    $('#dataTable').DataTable({
        "paging": true,
        "ordering": true,
        "info": true
    });

});  
    


//

// Initialize DataTables for all tables
$('table').each(function() {
    $(this).DataTable({
        "order": [], // Disable initial ordering
        "paging": true,
        "searching": true,
        "info": true
    });
});



<script>
        // Initialize DataTables for all tables
$('table').each(function() {
    $(this).DataTable({
        "order": [], // Disable initial ordering
        "paging": true,
        "searching": true,
        "info": true
    });
});
</script>