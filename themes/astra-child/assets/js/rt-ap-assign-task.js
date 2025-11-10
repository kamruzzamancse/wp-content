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

        $('#cl-upload-document-modal').addClass('show');
        $('#upload-document-form input[name="client_id"]').val(clientId);
        $('#upload-document-form input[name="properties_id"]').val(propertyId);

        // Clear previous messages
        $('#upload-message').remove();
    });

    // Close modal
    $(document).on('click', '.clup-close-btn', function(){
        $('#cl-upload-document-modal').removeClass('show');
        $('#upload-document-form')[0].reset();
        $('#selected-file-name').text('');
        $('#upload-message').remove();
    });

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
            success: function(response){
                console.log(response); // Debugging

                // Remove old message if exists
                $('#upload-message').remove();

                var messageHTML = '<div id="upload-message" style="margin-top:10px; padding:10px; border-radius:5px; font-weight:bold;"></div>';
                $('#upload-document-form').prepend(messageHTML);

                if(response.success){
                    $('#upload-message').css({
                        'background': '#d4edda',
                        'color': '#155724',
                        'border': '1px solid #c3e6cb'
                    }).text(response.data.message);

                    // Optionally reset form
                    $('#upload-document-form')[0].reset();
                    $('#selected-file-name').text('');
                } else {
                    $('#upload-message').css({
                        'background': '#f8d7da',
                        'color': '#721c24',
                        'border': '1px solid #f5c6cb'
                    }).text(response.data.message || 'Something went wrong.');
                }
            },
            error: function(xhr){
                console.log(xhr.responseText);

                $('#upload-message').remove();
                var messageHTML = '<div id="upload-message" style="margin-top:10px; padding:10px; border-radius:5px; font-weight:bold; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb;">AJAX error occurred.</div>';
                $('#upload-document-form').prepend(messageHTML);
            }
        });
    });
});
