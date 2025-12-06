jQuery(document).ready(function ($) {

    const modal = $('#cl-add-doc-type-modal');
    const form = $('#add-doc-type-form');
    const tbody = $('.doc-types-table tbody');
    const pagination = $('#docTypesPagination');
    let currentPage = 1;
    let currentPerPage = 5;

    // -------------------------
    // Fetch Document Types
    // -------------------------
    function fetchDocTypes(page = currentPage, perPage = currentPerPage) {
        // Show loading
        tbody.html('<tr><td colspan="3" style="text-align:center;"><div class="loading-spinner">Loading...</div></td></tr>');
        
        $.post(rt_doc_type_ajax.ajax_url, {
            action: 'rt_get_document_types',
            nonce: rt_doc_type_ajax.nonce,
            page: page,
            per_page: perPage
        }, function (res) {
            if (!res.success) {
                tbody.empty().append('<tr><td colspan="3" style="text-align:center; color:red;">Failed to load document types.</td></tr>');
                pagination.empty();
                return;
            }

            tbody.empty();
            const list = res.data.data;
            currentPage = res.data.current;
            currentPerPage = res.data.per_page;

            if (list.length) {
                const startIndex = (res.data.current - 1) * res.data.per_page;
                list.forEach((type, i) => {
                    tbody.append(`
                        <tr data-id="${type.id}">
                            <td data-label="#">${startIndex + i + 1}</td>
                            <td data-label="Document Type">${type.type_name}</td>
                            <td data-label="Actions">
                                <span class="edit-doc-type" title="Edit">✏️</span>
                                <span class="delete-doc-type" title="Delete">🗑️</span>
                            </td>
                        </tr>
                    `);
                });
            } else {
                tbody.append('<tr><td colspan="3" style="text-align:center;">No Document Types Found</td></tr>');
            }

            renderPagination(res.data.current, res.data.total_pages);
        }).fail(function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            tbody.empty().append('<tr><td colspan="3" style="text-align:center; color:red;">Failed to load document types. Please try again.</td></tr>');
            pagination.empty();
        });
    }

    // -------------------------
    // Render Pagination
    // -------------------------
    function renderPagination(current, total) {
        pagination.empty();
        if (total <= 1) return;

        // Previous button
        if (current > 1) {
            pagination.append(`
                <button class="page-btn" data-page="${current - 1}">
                    &laquo; Previous
                </button>
            `);
        }

        // Calculate page range to show
        let startPage = Math.max(1, current - 2);
        let endPage = Math.min(total, current + 2);

        // Adjust start and end if we're near the edges
        if (current <= 3) {
            endPage = Math.min(5, total);
        }
        if (current >= total - 2) {
            startPage = Math.max(total - 4, 1);
        }

        // Show first page if not in range
        if (startPage > 1) {
            pagination.append(`
                <button class="page-btn" data-page="1">1</button>
            `);
            if (startPage > 2) {
                pagination.append('<span class="page-dots">...</span>');
            }
        }

        // Page numbers
        for (let i = startPage; i <= endPage; i++) {
            pagination.append(`
                <button class="page-btn ${i === current ? 'active' : ''}" data-page="${i}">
                    ${i}
                </button>
            `);
        }

        // Show last page if not in range
        if (endPage < total) {
            if (endPage < total - 1) {
                pagination.append('<span class="page-dots">...</span>');
            }
            pagination.append(`
                <button class="page-btn" data-page="${total}">${total}</button>
            `);
        }

        // Next button
        if (current < total) {
            pagination.append(`
                <button class="page-btn" data-page="${current + 1}">
                    Next &raquo;
                </button>
            `);
        }
    }

    // -------------------------
    // Pagination Click (Event Delegation)
    // -------------------------
    $(document).on('click', '#docTypesPagination .page-btn', function(e) {
        e.preventDefault();
        const page = parseInt($(this).data('page'));
        const perPage = parseInt($('#docTypeRowsPerPage').val()) || 5;
        
        if (!isNaN(page) && page > 0) {
            fetchDocTypes(page, perPage);
        }
    });

    // -------------------------
    // Rows Per Page Change
    // -------------------------
    $('#docTypeRowsPerPage').off('change').on('change', function () {
        const perPage = parseInt($(this).val()) || 5;
        currentPerPage = perPage;
        // Reset to page 1 when changing rows per page
        fetchDocTypes(1, perPage);
    });

    // -------------------------
    // Modal Open & Close
    // -------------------------
    $('#addDocTypeBtn').off('click').on('click', function () {
        modal.addClass('show');
        form[0].reset();
        form.find('input[name="type_name"]').focus();
    });

    modal.find('.clup-close-btn, .clup-cancel').off('click').on('click', function(e) {
        e.preventDefault();
        modal.removeClass('show');
    });

    modal.on('click', function(e) {
        if (e.target === modal[0]) {
            modal.removeClass('show');
        }
    });

    // -------------------------
    // CREATE Document Type
    // -------------------------
    form.off('submit').on('submit', function (e) {
        e.preventDefault();
        const type_name = $(this).find('[name="type_name"]').val().trim();
        
        if (!type_name) {
            alert('Please enter a document type name.');
            return;
        }

        // Show loading state
        const submitBtn = $(this).find('.clup-save');
        const originalText = submitBtn.text();
        submitBtn.text('Adding...').prop('disabled', true);

        $.post(rt_doc_type_ajax.ajax_url, {
            action: 'rt_add_document_type',
            nonce: rt_doc_type_ajax.nonce,
            type_name: type_name
        }, function (res) {
            submitBtn.text(originalText).prop('disabled', false);
            
            if (!res.success) {
                alert(res.data || 'Failed to add document type.');
                return;
            }
            
            alert('Document type added successfully!');
            modal.removeClass('show');
            // Reload to show new type (go to first page for consistency)
            fetchDocTypes(1, currentPerPage);
        }).fail(function() {
            submitBtn.text(originalText).prop('disabled', false);
            alert('Network error. Please try again.');
        });
    });

    // -------------------------
    // DELETE Document Type
    // -------------------------
    $(document).on('click', '.delete-doc-type', function () {
        if (!confirm('Are you sure you want to delete this document type?\n\nThis action cannot be undone.')) {
            return;
        }
        
        const row = $(this).closest('tr');
        const id = row.data('id');
        const typeName = row.find('td:eq(1)').text();

        // Show deleting state
        const deleteBtn = $(this);
        deleteBtn.text('⏳').prop('disabled', true);

        $.post(rt_doc_type_ajax.ajax_url, {
            action: 'rt_delete_document_type',
            nonce: rt_doc_type_ajax.nonce,
            id: id
        }, function (res) {
            deleteBtn.text('🗑️').prop('disabled', false);
            
            if (!res.success) {
                alert(res.data || 'Failed to delete document type.');
                return;
            }
            
            alert(`Document type "${typeName}" deleted successfully!`);
            
            // If this page becomes empty after deletion, go to previous page
            const remainingItems = tbody.find('tr[data-id]').length - 1;
            if (remainingItems <= 0 && currentPage > 1) {
                fetchDocTypes(currentPage - 1, currentPerPage);
            } else {
                fetchDocTypes(currentPage, currentPerPage);
            }
        }).fail(function() {
            deleteBtn.text('🗑️').prop('disabled', false);
            alert('Network error. Please try again.');
        });
    });

    // -------------------------
    // UPDATE Document Type
    // -------------------------
    $(document).on('click', '.edit-doc-type', function () {
        const row = $(this).closest('tr');
        const id = row.data('id');
        const currentName = row.find('td:eq(1)').text();

        // Open modal with current value for editing
        modal.addClass('show');
        form[0].reset();
        form.find('input[name="type_name"]').val(currentName).focus();
        
        // Change form to update mode
        form.off('submit').on('submit', function(e) {
            e.preventDefault();
            const newName = $(this).find('[name="type_name"]').val().trim();
            
            if (!newName) {
                alert('Please enter a document type name.');
                return;
            }
            
            if (newName === currentName) {
                modal.removeClass('show');
                return;
            }

            // Show updating state
            const submitBtn = $(this).find('.clup-save');
            const originalText = submitBtn.text();
            submitBtn.text('Updating...').prop('disabled', true);

            $.post(rt_doc_type_ajax.ajax_url, {
                action: 'rt_update_document_type',
                nonce: rt_doc_type_ajax.nonce,
                id: id,
                type_name: newName
            }, function (res) {
                submitBtn.text(originalText).prop('disabled', false);
                
                if (!res.success) {
                    alert(res.data || 'Failed to update document type.');
                    return;
                }
                
                alert('Document type updated successfully!');
                modal.removeClass('show');
                fetchDocTypes(currentPage, currentPerPage);
            }).fail(function() {
                submitBtn.text(originalText).prop('disabled', false);
                alert('Network error. Please try again.');
            });
        });
        
        // Change modal title and button text
        modal.find('.clup-title').text('Edit Document Type');
        modal.find('.clup-save').text('Update');
    });

    // Reset form to create mode when opening modal for adding
    $('#addDocTypeBtn').on('click', function() {
        modal.find('.clup-title').text('Add New Document Type');
        modal.find('.clup-save').text('Add');
        
        // Reset form to create mode
        form.off('submit').on('submit', function (e) {
            e.preventDefault();
            const type_name = $(this).find('[name="type_name"]').val().trim();
            
            if (!type_name) {
                alert('Please enter a document type name.');
                return;
            }

            // Show loading state
            const submitBtn = $(this).find('.clup-save');
            const originalText = submitBtn.text();
            submitBtn.text('Adding...').prop('disabled', true);

            $.post(rt_doc_type_ajax.ajax_url, {
                action: 'rt_add_document_type',
                nonce: rt_doc_type_ajax.nonce,
                type_name: type_name
            }, function (res) {
                submitBtn.text(originalText).prop('disabled', false);
                
                if (!res.success) {
                    alert(res.data || 'Failed to add document type.');
                    return;
                }
                
                alert('Document type added successfully!');
                modal.removeClass('show');
                // Reload to show new type (go to first page for consistency)
                fetchDocTypes(1, currentPerPage);
            }).fail(function() {
                submitBtn.text(originalText).prop('disabled', false);
                alert('Network error. Please try again.');
            });
        });
    });

    // -------------------------
    // Keyboard Shortcuts
    // -------------------------
    $(document).on('keydown', function(e) {
        // ESC to close modal
        if (e.key === 'Escape' && modal.hasClass('show')) {
            modal.removeClass('show');
        }
        
        // Enter in modal to submit (if not in textarea)
        if (e.key === 'Enter' && modal.hasClass('show') && !$(e.target).is('textarea')) {
            e.preventDefault();
            form.find('.clup-save').click();
        }
    });

    // -------------------------
    // Initial Load
    // -------------------------
    // Set initial rows per page from dropdown
    currentPerPage = parseInt($('#docTypeRowsPerPage').val()) || 5;
    fetchDocTypes(1, currentPerPage);

    // Refresh button (optional - if you want to add one)
    $(document).on('click', '#refreshDocTypes', function() {
        fetchDocTypes(currentPage, currentPerPage);
    });

});