jQuery(document).ready(function ($) {

    // Load all note headers
    function loadNoteHeaders() {
        $.post(noteHeaderAjax.ajax_url, {
            action: 'get_note_headers',
            nonce: noteHeaderAjax.nonce
        }, function (res) {
            if (!res.success) return;

            let html = '';
            if (res.data.length === 0) {
                html = '<li class="empty">No note headers found</li>';
            } else {
                res.data.forEach(h => {
                    html += `
                        <li data-id="${h.id}">
                            <span class="note-header-text">${h.note_header}</span>
                            <span class="edit-header" style="cursor:pointer;">✏️</span>
                            <span class="delete-header" style="cursor:pointer;">🗑️</span>
                        </li>`;
                });
            }

            $('#note-header-list').html(html);
        });
    }

    loadNoteHeaders();

    // Open Add Modal
    $('#add-note-header-btn').on('click', function () {
        $('#note_id').val('');
        $('#note_header_input').val('');
        $('#note-header-modal-title').text('Add Note Header');
        $('#noteHeaderModal').addClass('show');
    });

    // Edit header
    $(document).on('click', '.edit-header', function () {
        const li = $(this).closest('li');
        const id = li.data('id');
        const title = li.find('.note-header-text').text(); // updated selector

        $('#note_id').val(id);
        $('#note_header_input').val(title);
        $('#note-header-modal-title').text('Edit Note Header');
        $('#noteHeaderModal').addClass('show');
    });

    // Save header (Add / Update)
    $('#save_note_header').on('click', function () {
        const header = $('#note_header_input').val().trim();
        const id = $('#note_id').val();

        if (!header) { 
            alert('Please enter a header'); 
            return; 
        }

        $.post(noteHeaderAjax.ajax_url, {
            action: 'save_note_header',
            nonce: noteHeaderAjax.nonce,
            id: id,
            note_header: header
        }, function (res) {
            if (res.success) {
                $('#noteHeaderModal').removeClass('show');
                loadNoteHeaders();
                alert(res.data.message || 'Success');
            } else {
                alert(res.data || 'Error saving note header');
            }
        });
    });

    // Delete header
    $(document).on('click', '.delete-header', function () {
        if (!confirm('Delete this header?')) return;
        const id = $(this).closest('li').data('id');

        $.post(noteHeaderAjax.ajax_url, {
            action: 'delete_note_header',
            nonce: noteHeaderAjax.nonce,
            id: id
        }, function (res) {
            if (res.success) {
                loadNoteHeaders();
                alert(res.data.message || 'Deleted successfully');
            } else {
                alert(res.data || 'Error deleting header');
            }
        });
    });

    // Close modal
    $('[data-modal="noteHeaderModal"]').on('click', function () {
        $('#noteHeaderModal').removeClass('show');
    });

    // Close modal when clicking outside modal content
    $(window).on('click', function (e) {
        if ($(e.target).hasClass('note-modal')) {
            $('#noteHeaderModal').removeClass('show');
        }
    });

});
