jQuery(document).ready(function ($) {
    // ======================
    // Open Upload Modal
    // ======================
    $(document).on('click', '.upload-document-trigger', function () {
        var clientId = $(this).data('client-id');
        var propertyId = $(this).data('property-id');
        var assignedTaskId = $(this).data('assigned-task-id');

        if (!assignedTaskId || assignedTaskId === 0) {
            alert("No assigned task found for this client/property.");
            return;
        }

        // Show modal
        $('#cl-upload-document-modal').addClass('show');

        // Set hidden inputs
        $('#cl-upload-document-form input[name="client_id"]').val(clientId);
        $('#cl-upload-document-form input[name="property_id"]').val(propertyId);
        $('#cl-upload-document-form input[name="assigned_task_id"]').val(assignedTaskId);

        $('#cl-selected-file-name').text('');
    });

    // ======================
    // Close Modal
    // ======================
    $(document).on('click', '.clup-close-btn', function () {
        $('#cl-upload-document-modal').removeClass('show');
        $('#cl-upload-document-form')[0].reset();
        $('#cl-selected-file-name').text('');
    });

    // ======================
    // Browse File
    // ======================
    $(document).on('click', '.clup-browse', function () {
        $(this).siblings('.clup-file-input').click();
    });

    $(document).on('change', '.clup-file-input', function () {
        var fileName = $(this).val().split('\\').pop();
        $('#cl-selected-file-name').text(fileName);
    });

    // ======================
    // Upload Document via AJAX
    // ======================
    $('#cl-upload-document-form').on('submit', function (e) {
        e.preventDefault();

        var fileInput = $('input[name="file_name"]');
        if (!fileInput.val()) {
            alert("Please select a file before uploading.");
            return;
        }

        var clientId = $('input[name="client_id"]').val();
        var propertyId = $('input[name="property_id"]').val();
        var assignedTaskId = $('input[name="assigned_task_id"]').val();

        if (!clientId || !propertyId || !assignedTaskId) {
            alert("Please fill all required fields.");
            return;
        }

        var formData = new FormData(this);
        formData.append('action', 'cl_upload_reply_doc');
        formData.append('nonce', rtReplyDocsAjax.nonce);

        $.ajax({
            url: rtReplyDocsAjax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $('.clup-upload').prop('disabled', true).text('Uploading...');
            },
            success: function (response) {
                $('.clup-upload').prop('disabled', false).text('Save');

                if (response.success) {
                    alert(response.data.message || 'Document uploaded successfully.');

                    // Close modal
                    $('#cl-upload-document-modal').removeClass('show');
                    $('#cl-upload-document-form')[0].reset();
                    $('#cl-selected-file-name').text('');

                    // Refresh table without reloading page
                    refreshDocumentsTable();
                } else {
                    alert(response.data.message || 'Something went wrong while uploading.');
                }
            },
            error: function (xhr) {
                $('.clup-upload').prop('disabled', false).text('Save');
                console.log(xhr.responseText);
                alert('AJAX error occurred while uploading.');
            }
        });
    });

    // ======================
    // Delete Reply Document via AJAX
    // ======================
    $(document).on('click', '.delete-assignment', function () {
        var button = $(this);
        var replyDocId = button.data('reply-doc-id');

        if (!replyDocId) {
            alert("Reply document ID not found.");
            return;
        }

        if (!confirm("Are you sure you want to delete this reply document?")) return;

        $.ajax({
            url: rtReplyDocsAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'cl_delete_reply_doc',
                reply_doc_id: replyDocId,
                nonce: rtReplyDocsAjax.nonce
            },
            success: function (response) {
                if (response.success) {
                    alert(response.data.message || 'Reply document deleted successfully.');
                    // Refresh table after delete
                    refreshDocumentsTable();
                } else {
                    alert(response.data.message || 'Delete failed.');
                }
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                alert('AJAX error occurred while deleting.');
            }
        });
    });

    // ======================
    // Refresh Table Function
    // ======================
    function refreshDocumentsTable() {
        $.ajax({
            url: rtReplyDocsAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'rt_refresh_documents_table',
                nonce: rtReplyDocsAjax.nonce
            },
            success: function (response) {
                if (response.success && response.data.html) {
                    // Replace only the tbody content
                    $('#assigned-list').html(response.data.html);
                } else {
                    console.error('Failed to refresh table data.');
                }
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                alert('Error refreshing document table.');
            }
        });
    }
});
