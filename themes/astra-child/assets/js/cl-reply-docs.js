jQuery(document).ready(function($){
    // Open modal
    $(document).on('click', '.upload-document-trigger', function(){
        var clientId        = $(this).data('client-id');
        var propertyId      = $(this).data('property-id');
        var assignedTaskId  = $(this).data('assigned-task-id');

        $('#cl-upload-document-modal').addClass('show');
        $('#cl-upload-document-form input[name="client_id"]').val(clientId);
        $('#cl-upload-document-form input[name="property_id"]').val(propertyId);
        $('#cl-upload-document-form input[name="assigned_task_id"]').val(assignedTaskId);

        $('#cl-selected-file-name').text('');
        $('#upload-message').remove();
    });

    // Close modal
    $(document).on('click', '.clup-close-btn', function(){
        $('#cl-upload-document-modal').removeClass('show');
        $('#cl-upload-document-form')[0].reset();
        $('#cl-selected-file-name').text('');
        $('#upload-message').remove();
    });

    // Browse file
    $(document).on('click', '.clup-browse', function(){
        $(this).siblings('.clup-file-input').click();
    });

    $(document).on('change', '.clup-file-input', function(){
        var fileName = $(this).val().split('\\').pop();
        $('#cl-selected-file-name').text(fileName);
    });

    // Submit form via AJAX
    $('#cl-upload-document-form').on('submit', function(e){
        e.preventDefault();

        if (!$('input[name="file_name"]').val()) {
            alert("Please select a file.");
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
            success: function(response){
                $('#upload-message').remove();
                var html = '<div id="upload-message" style="margin-top:10px; padding:10px; border-radius:5px; font-weight:bold;"></div>';
                $('#cl-upload-document-form').prepend(html);

                if(response.success){
                    $('#upload-message').css({
                        'background':'#d4edda',
                        'color':'#155724',
                        'border':'1px solid #c3e6cb'
                    }).text(response.data.message);

                    $('#cl-upload-document-form')[0].reset();
                    $('#cl-selected-file-name').text('');
                } else {
                    $('#upload-message').css({
                        'background':'#f8d7da',
                        'color':'#721c24',
                        'border':'1px solid #f5c6cb'
                    }).text(response.data.message || 'Something went wrong.');
                }
            },
            error: function(xhr){
                console.log(xhr.responseText);
                $('#upload-message').remove();
                $('#cl-upload-document-form').prepend('<div id="upload-message" style="margin-top:10px; padding:10px; border-radius:5px; font-weight:bold; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb;">AJAX error occurred.</div>');
            }
        });
    });

    // Delete reply document via AJAX
    $(document).on('click', '.delete-assignment', function(){
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
            success: function(response){
                if(response.success){
                    button.closest('tr').find('td:nth-child(4), td:nth-child(5)').html('');
                    button.removeAttr('data-reply-doc-id');
                    alert(response.data.message);
                } else {
                    alert(response.data.message || 'Delete failed.');
                }
            },
            error: function(xhr){
                console.log(xhr.responseText);
                alert('AJAX error occurred.');
            }
        });
    });
});
