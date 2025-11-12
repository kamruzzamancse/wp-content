jQuery(document).ready(function($){

    // Trigger browse input
    $(document).on('click', '.clup-browse', function(){
        $(this).siblings('.clup-file-input').click();
    });

    // Show selected file name
    $(document).on('change', '.clup-file-input', function(){
        var fileName = $(this).val().split('\\').pop();
        $('#selected-file-name').text(fileName);
    });

    // Open modal
    $(document).on('click', '.upload-document-trigger', function(){
        var clientId = $(this).data('client-id');
        var propertyId = $(this).data('property-id');

        $('#rt-upload-document-modal').addClass('show');
        $('#upload-document-form input[name="client_id"]').val(clientId);
        $('#upload-document-form input[name="property_id"]').val(propertyId);

        // Clear old states
        $('#upload-document-form')[0].reset();
        $('#selected-file-name').text('');
    });

    // Close modal
    $(document).on('click', '.clup-close-btn', function(){
        closeUploadModal();
    });

    function closeUploadModal() {
        $('#rt-upload-document-modal').removeClass('show');
        $('#upload-document-form')[0].reset();
        $('#selected-file-name').text('');
    }

    // Submit form via AJAX
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
                console.log(response);

                if (response.success) {
                    alert(response.data.message); // ✅ Alert shown outside modal
                    closeUploadModal();

                    // ✅ Refresh assigned task table dynamically (without full reload)
                    if (typeof refreshAssignedTaskTable === 'function') {
                        await refreshAssignedTaskTable();
                    } else {
                        // fallback: reload tbody from server via AJAX
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


    // DELETE ASSIGNMENT (soft delete)
    $(document).on('click', '.delete-assignment', function() {

        var taskId = $(this).data('task-id');
        if (!taskId) {
            alert("Invalid task ID");
            return;
        }

        if (!confirm("Are you sure you want to delete this assignment?")) return;

        $.ajax({
            url: rtAssignTaskAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'rt_delete_assignment',
                nonce: rtAssignTaskAjax.nonce,
                task_id: taskId
            },
            success: async function(response) {
                if (response.success) {
                    alert(response.data.message);
                    $('button[data-task-id="' + taskId + '"]').closest('tr').fadeOut(300, function(){
                        $(this).remove();
                    });

                    // Refresh table after deletion
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


    // Optional: helper function to refresh assigned table dynamically
    window.refreshAssignedTaskTable = async function() {
        $('#assigned-list').addClass('loading');
        await $('#assigned-list').load(location.href + ' #assigned-list > *');
        $('#assigned-list').removeClass('loading');
    };

});
