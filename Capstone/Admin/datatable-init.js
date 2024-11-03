$(document).ready(function() {
    // Initialize DataTables on all tables with the 'data-table' class
    $('.data-table').each(function() {
        if (!$.fn.DataTable.isDataTable($(this))) {
            $(this).DataTable({
                paging: true,
                ordering: true,
                info: true
            });
        }
    });
});
