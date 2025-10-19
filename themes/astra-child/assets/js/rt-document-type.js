jQuery(document).ready(function ($) {
    console.log('✅ rt-document-type.js loaded');

    const modal = $('#cl-add-doc-type-modal');
    const form = $('#add-doc-type-form');

    // -------------------------
    // Prevent multiple bindings
    // -------------------------
    $(document).off('submit', '#add-doc-type-form');
    $(document).off('click', '.delete-doc-type');
    $(document).off('click', '.edit-doc-type');

    // -------------------------
    // Open Modal
    // -------------------------
    $('#addDocTypeBtn').on('click', () => {
        modal.addClass('show');
        form[0].reset();
    });

    // -------------------------
    // Close Modal
    // -------------------------
    modal.find('.clup-close-btn, .clup-cancel').on('click', () => modal.removeClass('show'));
    modal.on('click', (e) => { if (e.target === modal[0]) modal.removeClass('show'); });

    // -------------------------
    // CREATE (Add New)
    // -------------------------
    $(document).on('submit', '#add-doc-type-form', function (e) {
        e.preventDefault();
        console.log('Submitting form...');

        const type_name = $(this).find('[name="type_name"]').val().trim();
        if (!type_name) return alert('Please enter a document type name.');

        $.post(rt_doc_type_ajax.ajax_url, {
            action: 'rt_add_document_type',
            nonce: rt_doc_type_ajax.nonce,
            type_name
        }, function (res) {
            console.log('AJAX response:', res);

            if (res.success) {
                const tbody = $('.doc-types-table tbody');

                // Prepend new row
                tbody.prepend(`
                    <tr data-id="${res.data.id}">
                        <td>1</td>
                        <td>${res.data.type_name}</td>
                        <td>
                            <span class="edit-doc-type" title="Edit">✏️</span>
                            <span class="delete-doc-type" title="Delete">🗑️</span>
                        </td>
                    </tr>
                `);

                // Re-number all rows
                tbody.children('tr').each((i, row) => $(row).find('td:first').text(i + 1));

                form[0].reset();
                modal.removeClass('show');
            } else {
                alert(res.data || 'Failed to add document type.');
            }
        });
    });

    // -------------------------
    // DELETE
    // -------------------------
    $(document).on('click', '.delete-doc-type', function () {
        if (!confirm('Are you sure you want to delete this document type?')) return;

        const row = $(this).closest('tr');
        const id = row.data('id');

        $.post(rt_doc_type_ajax.ajax_url, {
            action: 'rt_delete_document_type',
            nonce: rt_doc_type_ajax.nonce,
            id
        }, function (res) {
            console.log('Delete response:', res);

            if (res.success) {
                row.remove();
                $('.doc-types-table tbody tr').each((i, row) =>
                    $(row).find('td:first').text(i + 1)
                );
            } else {
                alert(res.data || 'Failed to delete document type.');
            }
        });
    });

    // -------------------------
    // UPDATE (Edit)
    // -------------------------
    $(document).on('click', '.edit-doc-type', function () {
        const row = $(this).closest('tr');
        const id = row.data('id');
        const currentName = row.find('td:eq(1)').text();

        const newName = prompt('Edit Type Name:', currentName);
        if (!newName) return;

        $.post(rt_doc_type_ajax.ajax_url, {
            action: 'rt_update_document_type',
            nonce: rt_doc_type_ajax.nonce,
            id,
            type_name: newName
        }, function (res) {
            console.log('Update response:', res);

            if (res.success) {
                row.find('td:eq(1)').text(res.data?.type_name || newName);
            } else {
                alert(res.data || 'Failed to update document type.');
            }
        });
    });
});
