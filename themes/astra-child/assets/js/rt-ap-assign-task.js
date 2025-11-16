jQuery(document).ready(function($){

    // ===== Modal & File Upload =====
    $(document).on('click', '.clup-browse', function(){
        $(this).siblings('.clup-file-input').click();
    });

    $(document).on('change', '.clup-file-input', function(){
        var fileName = $(this).val().split('\\').pop();
        $('#selected-file-name').text(fileName);
    });

    $(document).on('click', '.upload-document-trigger', function(){
        var clientId = $(this).data('client-id');
        var propertyId = $(this).data('property-id');

        $('#rt-upload-document-modal').addClass('show');
        $('#upload-document-form input[name="client_id"]').val(clientId);
        $('#upload-document-form input[name="property_id"]').val(propertyId);

        $('#upload-document-form')[0].reset();
        $('#selected-file-name').text('');
    });

    $(document).on('click', '.clup-close-btn', function(){
        closeUploadModal();
    });

    function closeUploadModal() {
        $('#rt-upload-document-modal').removeClass('show');
        $('#upload-document-form')[0].reset();
        $('#selected-file-name').text('');
    }

    $('#upload-document-form').on('submit', function(e){
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'upload_document');
        formData.append('nonce', rtAssignTaskAjax.nonce);

        $.ajax({
            url: rtAssignTaskAjax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: async function(response){
                if (response.success) {
                    alert(response.data.message);
                    closeUploadModal();
                    if (typeof refreshAssignedTaskTable === 'function') {
                        await refreshAssignedTaskTable();
                    } else {
                        $('#assigned-list').load(location.href + ' #assigned-list > *');
                    }
                } else {
                    alert(response.data.message || 'Something went wrong.');
                }
            },
            error: function(xhr){
                console.log(xhr.responseText);
                alert('AJAX error occurred.');
            }
        });
    });

    // ===== Delete Assignment =====
    $(document).on('click', '.delete-assignment', function() {
        var taskId = $(this).data('task-id');
        if (!taskId) { alert("Invalid task ID"); return; }
        if (!confirm("Are you sure you want to delete this assignment?")) return;

        $.ajax({
            url: rtAssignTaskAjax.ajax_url,
            type: 'POST',
            data: { action:'rt_delete_assignment', nonce: rtAssignTaskAjax.nonce, task_id: taskId },
            success: async function(response) {
                if (response.success) {
                    alert(response.data.message);
                    $('button[data-task-id="' + taskId + '"]').closest('tr').fadeOut(300, function(){ $(this).remove(); });
                    if (typeof refreshAssignedTaskTable === 'function') {
                        await refreshAssignedTaskTable();
                    } else {
                        $('#assigned-list').load(location.href + ' #assigned-list > *');
                    }
                } else {
                    alert(response.data.message || "Assignment could not be deleted.");
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("AJAX error occurred.");
            }
        });
    });

    // ===== Helper to refresh table =====
    window.refreshAssignedTaskTable = async function() {
        $('#assigned-list').addClass('loading');
        await $('#assigned-list').load(location.href + ' #assigned-list > *');
        $('#assigned-list').removeClass('loading');
    };

    // ===== AJAX Table Load & Filter =====
    function loadAssignTable(paged = 1) {
        let search = $('#assign-search').val();
        let filter_status = $('#assign-filter-status').val();

        $.ajax({
            url: rtAssignTaskAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'rt_load_assign_table',
                nonce: rtAssignTaskAjax.nonce,
                search: search,
                filter_status: filter_status,
                paged: paged
            },
            success: function(response){
                if(response.success) {
                    $('#assign-table-wrapper').html(response.data.html);
                } else {
                    $('#assign-table-wrapper').html('<p>Failed to load table.</p>');
                }
            },
            error: function() {
                $('#assign-table-wrapper').html('<p>AJAX error occurred.</p>');
            }
        });
    }

    // Initial load
    loadAssignTable();

    // Filter button
    $('#assign-filter-btn').on('click', function(e){
        e.preventDefault();
        loadAssignTable();
    });

    // Pagination click (delegated)
    $(document).on('click', '.pagination a', function(e){
        e.preventDefault();
        let page = $(this).attr('href').split('paged=')[1];
        if(page) loadAssignTable(page);
    });

});
