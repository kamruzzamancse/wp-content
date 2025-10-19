jQuery(document).ready(function($){
    const ajaxObj = rt_doc_ajax;

    // ----------------------
    // Open Modal for New Document
    // ----------------------
    $(document).on('click', '.cld-upload-btn', function(){
        const form = $('#upload-document-form')[0];
        form.reset();
        $('[name="document_id"]').val('');
        $('#selected-file-name').text('');
        $('#cl-upload-document-modal').addClass('show');
    });

    // ----------------------
    // Close Modal
    // ----------------------
    $(document).on('click', '.clup-close-btn, .clup-cancel', function(){
        $(this).closest('.clup-modal-overlay').removeClass('show');
    });

    $(document).on('click', '.clup-modal-overlay', function(e){
        if (e.target === this) $(this).removeClass('show');
    });

    // ----------------------
    // Add / Update Document
    // ----------------------
    $(document).on('submit', '#upload-document-form', function(e){
        e.preventDefault();

        const formData = new FormData(this);
        const docId = $('[name="document_id"]').val();
        const action = docId ? 'rt_update_document' : 'rt_add_document';

        formData.append('action', action);
        formData.append('nonce', ajaxObj.nonce);

        $.ajax({
            url: ajaxObj.ajax_url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res){
                if (res.success) {
                    alert('Document saved successfully!');
                    location.reload();
                } else {
                    alert(res.data);
                }
            }
        });
    });

    // ----------------------
    // Edit Document
    // ----------------------
    $(document).on('click', '.edit-doc', function(){
        const row = $(this).closest('tr');
        const docId = row.data('id');
        const title = row.find('td:eq(1)').text();
        const typeId = row.find('td:eq(2)').data('type-id');
        const fileName = row.find('td:eq(3) a').text();

        $('[name="document_id"]').val(docId);
        $('[name="title"]').val(title);
        $('[name="type_id"]').val(typeId);
        $('#selected-file-name').text(fileName);

        $('#cl-upload-document-modal').addClass('show');
    });

    // ----------------------
    // Delete Document
    // ----------------------
    $(document).on('click', '.delete-doc', function(){
        if (!confirm('Are you sure you want to delete this document?')) return;

        const id = $(this).closest('tr').data('id');

        $.post(ajaxObj.ajax_url, {
            action: 'rt_delete_document',
            nonce: ajaxObj.nonce,
            id: id
        }, function(res){
            if (res.success) {
                alert('Deleted successfully!');
                location.reload();
            } else {
                alert(res.data);
            }
        });
    });

    // ----------------------
    // Download Document
    // ----------------------
    $(document).on('click', '.download-doc', function(){
        // handled by <a download> itself
    });
});
