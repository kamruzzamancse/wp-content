jQuery(document).ready(function($) {
    const modal = $('#cl-add-doc-type-modal');
    const form = $('#add-doc-type-form');

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
    modal.on('click', e => { if (e.target === modal[0]) modal.removeClass('show'); });

    // -------------------------
    // CREATE
    // -------------------------
    form.on('submit', function(e) {
        e.preventDefault();
        const type_name = form.find('[name="type_name"]').val().trim();
        let slug = form.find('[name="slug"]').val().trim();

        if (!type_name) return alert('Please enter a document type name.');

        // If slug is empty, send empty string so PHP can auto-generate
        slug = slug || '';

        $.post(rt_doc_type_ajax.ajax_url, {
            action: 'rt_add_document_type',
            nonce: rt_doc_type_ajax.nonce,
            type_name,
            slug
        }, function(res) {
            if (res.success) {
                const tbody = $('.doc-types-table tbody');

                // Prepend new row
                tbody.prepend(`
                    <tr data-id="${res.data.id}">
                        <td>1</td>
                        <td>${res.data.type_name}</td>
                        <td>${res.data.slug}</td>
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
    $(document).on('click', '.delete-doc-type', function() {
        if (!confirm('Are you sure you want to delete this document type?')) return;

        const row = $(this).closest('tr');
        const id = row.data('id');

        $.post(rt_doc_type_ajax.ajax_url, {
            action: 'rt_delete_document_type',
            nonce: rt_doc_type_ajax.nonce,
            id
        }, function(res) {
            if (res.success) {
                row.remove();
                // Re-number remaining rows
                $('.doc-types-table tbody tr').each((i, row) => $(row).find('td:first').text(i + 1));
            } else alert(res.data || 'Failed to delete document type.');
        });
    });

    // -------------------------
    // UPDATE
    // -------------------------
    $(document).on('click', '.edit-doc-type', function() {
        const row = $(this).closest('tr');
        const id = row.data('id');
        const currentName = row.find('td:eq(1)').text();
        const currentSlug = row.find('td:eq(2)').text();

        const newName = prompt('Edit Type Name:', currentName);
        if (!newName) return;

        let newSlug = prompt('Edit Slug (leave empty for auto):', currentSlug);
        newSlug = newSlug || '';

        $.post(rt_doc_type_ajax.ajax_url, {
            action: 'rt_update_document_type',
            nonce: rt_doc_type_ajax.nonce,
            id,
            type_name: newName,
            slug: newSlug
        }, function(res) {
            if (res.success) {
                row.find('td:eq(1)').text(res.data?.type_name || newName);
                row.find('td:eq(2)').text(res.data?.slug || newSlug);
            } else alert(res.data || 'Failed to update document type.');
        });
    });
});
