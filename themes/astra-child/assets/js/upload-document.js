jQuery(document).ready(function($){

    /* ======================================================
       VARIABLES
    ====================================================== */
    const docModal = $('#rt-upload-document-modal');
    const docForm = $('#upload-document-form');
    const fileInput = docForm.find('input[name="file_name"]');
    const browseBtn = docForm.find('.clup-browse');
    const selectedFileName = $('#selected-file-name');
    const tbody = $('.docs-table tbody');
    const pagination = $('#docsPagination');
    
    const editModal = $('#am-edit-document-modal');
    const editForm  = $('#am-edit-document-form');
    const editFileInput = editForm.find('input[name="file_name"]');
    const editSelectedFileName = $('#edit-selected-file-name');
    const currentFileInfo = $('#current-file-info');
    const currentFileLink = $('#current-file-link');

    let isSubmitting = false;

    /* ======================================================
       OPEN / CLOSE MODALS
    ====================================================== */
    function openModal(modal) { modal.addClass('show'); }
    function closeModal(modal) { modal.removeClass('show'); }

    $('#uploadDocBtn').on('click', function(){
        docForm[0].reset();
        selectedFileName.text('');
        fileInput.val('');
        openModal(docModal);
    });

    docModal.find('.clup-close-btn, .clup-cancel').on('click', function(){ closeModal(docModal); });
    editModal.find('.clup-close-btn, .clup-cancel').on('click', function(){ closeModal(editModal); });

    docModal.on('click', function(e){ if(e.target === this) closeModal(docModal); });
    editModal.on('click', function(e){ if(e.target === this) closeModal(editModal); });

    $(document).on('keydown', function(e){
        if(e.key === "Escape") {
            closeModal(docModal);
            closeModal(editModal);
        }
    });

    /* ======================================================
       FILE BROWSE
    ====================================================== */
    browseBtn.on('click', function(e){ e.preventDefault(); fileInput.trigger('click'); });
    fileInput.on('change', function(){ selectedFileName.text(this.files.length ? this.files[0].name : ''); });

    editForm.find('.clup-browse').on('click', function(e){ e.preventDefault(); editFileInput.trigger('click'); });
    editFileInput.on('change', function(){ editSelectedFileName.text(this.files.length ? this.files[0].name : ''); });

    /* ======================================================
       FETCH DOCUMENTS & PAGINATION
    ====================================================== */
    function fetchDocuments(page = 1, perPage = 5){
        $.post(upload_doc_ajax.ajax_url, {
            action: 'rt_get_documents',
            nonce: upload_doc_ajax.nonce,
            page,
            per_page: perPage
        }, function(res){
            tbody.empty();
            pagination.empty();

            if(!res.success || !res.data.data.length){
                tbody.append('<tr><td colspan="6" style="text-align:center;">No Documents Found</td></tr>');
                return;
            }

            const rows = res.data.data;
            rows.forEach((doc, index) => {
                const fileName = doc.file_name ? doc.file_name.split(/[\\/]/).pop() : '';
                const fileUrl  = doc.file_url || '#';
                tbody.append(`
                    <tr data-id="${doc.id}" data-type-id="${doc.type_id}">
                        <td>${index + 1 + ((res.data.current - 1) * res.data.per_page)}</td>
                        <td>${doc.title}</td>
                        <td>${doc.type_name}</td>
                        <td>${fileName ? `<a href="${fileUrl}" target="_blank">${fileName}</a>` : '-'}</td>
                        <td>${doc.note || ''}</td>
                        <td>
                            ${fileName ? `<a href="${fileUrl}" download title="Download ${fileName}">⬇️</a>` : '-'}
                            <span class="edit-document" title="Edit">✏️</span>
                            <span class="delete-document" title="Delete">🗑️</span>
                        </td>
                    </tr>
                `);
            });

            // Pagination buttons
            if(res.data.total_pages > 1){
                for(let i=1; i<=res.data.total_pages; i++){
                    pagination.append(`<button class="page-btn ${i===res.data.current?'active':''}" data-page="${i}">${i}</button>`);
                }
            }
        });
    }

    $(document).on('click', '.page-btn', function(){
        const page = parseInt($(this).data('page'));
        const perPage = parseInt($('#docRowsPerPage').val() || 5);
        fetchDocuments(page, perPage);
    });

    $('#docRowsPerPage').on('change', function(){
        fetchDocuments(1, parseInt($(this).val()));
    });

    /* ======================================================
       UPLOAD DOCUMENT
    ====================================================== */
    docForm.on('submit', function(e){
        e.preventDefault();
        if(isSubmitting) return;

        const title = docForm.find('[name="title"]').val().trim();
        const type_id = docForm.find('[name="type_id"]').val();
        const note = docForm.find('[name="note"]').val();
        const file = fileInput[0].files[0];

        if(!title || !type_id || !file){
            alert('Please fill all required fields.');
            return;
        }

        isSubmitting = true;
        const formData = new FormData();
        formData.append('action','rt_upload_document');
        formData.append('nonce',upload_doc_ajax.nonce);
        formData.append('title',title);
        formData.append('type_id',type_id);
        formData.append('note',note);
        formData.append('file_name',file);

        $.ajax({
            url: upload_doc_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res){
                isSubmitting = false;
                if(!res.success){ alert(res.data || 'Failed to upload.'); return; }

                alert('Document uploaded successfully.');
                docForm[0].reset();
                selectedFileName.text('');
                closeModal(docModal);
                fetchDocuments(1, parseInt($('#docRowsPerPage').val() || 5));
            },
            error: function(){ isSubmitting = false; alert('AJAX Error. Try again.'); }
        });
    });

    /* ======================================================
       DELETE DOCUMENT
    ====================================================== */
    $(document).on('click', '.delete-document', function(){
        if(!confirm('Are you sure you want to delete this document?')) return;

        const row = $(this).closest('tr');
        const id = row.data('id');

        $.post(upload_doc_ajax.ajax_url, {
            action: 'rt_delete_document',
            nonce: upload_doc_ajax.nonce,
            id
        }, function(res){
            if(res.success){
                row.remove();
                alert('Document deleted successfully!');
            } else {
                alert(res.data || 'Failed to delete.');
            }
        });
    });

    /* ======================================================
       EDIT DOCUMENT HANDLER
    ====================================================== */
    $(document).on('click', '.edit-document', function() {
        const row = $(this).closest('tr');
        const id = row.data('id');
        const currentTitle = row.find('td:eq(1)').text();
        const currentTypeId = row.data('type-id');
        const currentNote = row.find('td:eq(4)').text();
        const fileName = row.find('td:eq(3) a').text() || '';
        const fileUrl  = row.find('td:eq(3) a').attr('href') || '#';

        editForm.find('[name="document_id"]').val(id);
        editForm.find('[name="title"]').val(currentTitle);
        editForm.find('[name="type_id"]').val(currentTypeId);
        editForm.find('[name="note"]').val(currentNote);
        editSelectedFileName.text('');
        editFileInput.val('');

        if(fileName){
            currentFileInfo.show();
            currentFileLink.text(fileName).attr('href', fileUrl);
        } else {
            currentFileInfo.hide();
            currentFileLink.text('').attr('href','#');
        }

        openModal(editModal);
    });

    editForm.on('submit', function(e) {
        e.preventDefault();
        if(isSubmitting) return;

        const id = editForm.find('[name="document_id"]').val();
        const title = editForm.find('[name="title"]').val().trim();
        const type_id = editForm.find('[name="type_id"]').val();
        const note = editForm.find('[name="note"]').val();
        const file = editFileInput[0].files[0];

        if(!id || !title || !type_id){
            alert('Please fill all required fields.');
            return;
        }

        isSubmitting = true;
        const formData = new FormData();
        formData.append('action','rt_update_document');
        formData.append('nonce', upload_doc_ajax.nonce);
        formData.append('id', id);
        formData.append('title', title);
        formData.append('type_id', type_id);
        formData.append('note', note);
        if(file) formData.append('file_name', file);

        $.ajax({
            url: upload_doc_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                isSubmitting = false;
                if(!res.success){ 
                    alert(res.data || 'Failed to update.');
                    return;
                }

                const row = $('.docs-table tbody tr[data-id="'+id+'"]');
                row.find('td:eq(1)').text(res.data.title);
                row.find('td:eq(2)').text(res.data.type_name);
                row.find('td:eq(4)').text(res.data.note);
                row.attr('data-type-id', res.data.type_id);

                const fileName = res.data.file_name ? res.data.file_name.split(/[\\/]/).pop() : '';
                const fileUrl  = res.data.file_url || '#';

                // Update file column
                row.find('td:eq(3)').html(fileName ? `<a href="${fileUrl}" target="_blank">${fileName}</a>` : '-');

                // Update action column (download + edit + delete)
                const actionsHtml = `
                    ${fileName ? `<a href="${fileUrl}" download title="Download ${fileName}">⬇️</a>` : '-'}
                    <span class="edit-document" title="Edit">✏️</span>
                    <span class="delete-document" title="Delete">🗑️</span>
                `;
                row.find('td:eq(5)').html(actionsHtml);

                // Update edit modal current file info
                if(fileName){
                    currentFileInfo.show();
                    currentFileLink.text(fileName).attr('href', fileUrl);
                } else {
                    currentFileInfo.hide();
                    currentFileLink.text('').attr('href','#');
                }

                alert('Document updated successfully!');
                closeModal(editModal);
            },
            error: function(){ 
                isSubmitting = false; 
                alert('AJAX Error. Try again.'); 
            }
        });
    });

    /* ======================================================
       INITIAL LOAD
    ====================================================== */
    fetchDocuments();

    /* ======================================================
       CURSOR POINTER FOR ACTIONS
    ====================================================== */
    $('<style>')
    .prop('type', 'text/css')
    .html(`
        .docs-table td span.edit-document,
        .docs-table td span.delete-document {
            cursor: pointer;
        }
    `)
    .appendTo('head');

});
