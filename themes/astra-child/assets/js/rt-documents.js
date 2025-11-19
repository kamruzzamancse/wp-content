jQuery(document).ready(function($){
    const ajaxObj = rt_doc_ajax;

    // --- Pagination & Filtering Variables ---
    const tbody = $('#documents-tbody');
    const pagination = $('#documents-pagination');
    const perPageSelector = $('#documentsPerPage');
    let per_page = parseInt(perPageSelector.val()) || 5;
    let currentType = 'all';
    let searchQuery = '';

    // --- Load Documents with AJAX ---
    function loadDocuments(page = 1) {
        $.post(ajaxObj.ajax_url, {
            action: 'rt_get_documents',
            nonce: ajaxObj.nonce,
            page: page,
            per_page: per_page,
            type_id: currentType,
            search: searchQuery
        }, function(res){
            if(!res.success) return tbody.html('<tr><td colspan="6" style="text-align:center;">Failed to load documents.</td></tr>');

            const data = res.data.data;
            const total_pages = res.data.total_pages;

            tbody.empty();
            if(data.length === 0){
                tbody.append('<tr><td colspan="6" style="text-align:center;">No Documents Found</td></tr>');
            } else {
                data.forEach((doc, i) => {
                    const fileLink = doc.file_exists ? `<a href="${doc.file_url}" target="_blank">${doc.file_name}</a>` : `<span style="color:red;">File missing</span>`;
                    const assignedStatus = doc.assigned_status;
                    tbody.append(`
                        <tr data-id="${doc.id}" data-type-id="${doc.type_id}">
                            <td>${(page-1)*per_page + i + 1}</td>
                            <td>${doc.title}</td>
                            <td data-type-id="${doc.type_id}">${doc.type_name}</td>
                            <td>${fileLink}</td>
                            <td>${assignedStatus}</td>
                            <td>
                                ${doc.file_exists ? `<a href="${doc.file_url}" download="${doc.file_name}" class="doc-action download-doc" title="Download">⬇️</a>` : ''}
                                <span class="edit-doc" title="Edit">✏️</span>
                                <span class="delete-doc" title="Delete">🗑️</span>
                            </td>
                        </tr>
                    `);
                });
            }

            renderPagination(total_pages, page);
        });
    }

    function renderPagination(total, current){
        pagination.empty();
        if(total <= 1) return;
        for(let i=1; i<=total; i++){
            const btn = $('<button>').text(i).css({
                padding:'5px 10px', border:'1px solid #ddd', cursor:'pointer',
                background:i===current?'#2271b1':'#fff', color:i===current?'#fff':'#333', borderRadius:'4px'
            }).on('click', () => loadDocuments(i));
            pagination.append(btn);
        }
    }

    // --- Tabs ---
    $(document).on('click', '.doc-type-tab', function(){
        $('.doc-type-tab').removeClass('active');
        $(this).addClass('active');
        currentType = $(this).data('type');
        loadDocuments(1);
    });

    // --- Search ---
    $('#documentSearchInput').on('keyup', function(){
        searchQuery = $(this).val();
        loadDocuments(1);
    });

    // --- Per-page change ---
    perPageSelector.on('change', function(){
        per_page = parseInt($(this).val());
        loadDocuments(1);
    });

    // --- Initial load ---
    loadDocuments();

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
                    loadDocuments(1); // Reload paginated table without full page refresh
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
                loadDocuments(1); // Reload table without full page refresh
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


