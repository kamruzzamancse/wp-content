jQuery(document).ready(function ($) {

    const noteModal = $('#noteModal');
    const allNotesModal = $('#allNotesModal');

    function loadHeaderDropdown() {
        $.post(noteHeaderAjax.ajax_url, {
            action: 'get_note_headers',
            nonce: noteHeaderAjax.nonce
        }, function (res) {
            if (!res.success) return;
            let opts = '<option value="">Select Note Header</option>';
            res.data.forEach(h => { opts += `<option value="${h.id}">${h.note_header}</option>`; });
            $('#note_header_select').html(opts);
        });
    }

    function loadNotes() {
        $.post(notesAjax.ajax_url, {
            action: 'get_notes',
            nonce: notesAjax.nonce
        }, function (res) {
            if (!res.success) return;
            let html = '';
            let allHtml = '';
            const maxPreview = 3;

            if (res.data.length === 0) {
                html = '<li class="empty">No notes found</li>';
                $('#view-all-notes-btn').hide();
            } else {
                res.data.forEach((n, index) => {
                    let liHtml = `
                        <li data-id="${n.id}">
                            <div class="note-header-title">${n.note_header}</div>
                            <div class="note-text">${n.note || ''}</div>
                            <div class="note-actions">
                                <span class="edit-note">✏️ Edit</span>
                                <span class="delete-note">🗑️ Delete</span>
                            </div>
                        </li>`;
                    allHtml += liHtml;
                    if(index < maxPreview) html += liHtml;
                });

                if(res.data.length > maxPreview) $('#view-all-notes-btn').show();
                else $('#view-all-notes-btn').hide();
            }

            $('#notes-list').html(html);
            $('#all-notes-list').html(allHtml);
        });
    }

    loadHeaderDropdown();
    loadNotes();

    // Add note
    $('#add-note-btn').on('click', function () {
        $('#note_row_id').val('');
        $('#note_header_select').val('');
        $('#note_text').val('');
        $('#note-modal-title').text('Add Note');
        loadHeaderDropdown();
        noteModal.addClass('show');
    });

    // Edit note
    $(document).on('click', '.edit-note', function () {
        const id = $(this).closest('li').data('id');

        // Close All Notes modal if open
        allNotesModal.removeClass('show');

        $.post(notesAjax.ajax_url, {
            action: 'get_single_note',
            nonce: notesAjax.nonce,
            note_id: id
        }, function (res) {
            if (!res.success) return;
            $('#note_row_id').val(id);
            $('#note_header_select').val(res.data.note_header_id);
            $('#note_text').val(res.data.note);
            $('#note-modal-title').text('Edit Note');
            noteModal.addClass('show');
        });
    });

    // Save note
    $('#save_note').on('click', function () {
        const headerId = $('#note_header_select').val();
        const text = $('#note_text').val().trim();
        const id = $('#note_row_id').val();
        if (!headerId || !text) { alert('Select header and write note'); return; }

        $.post(notesAjax.ajax_url, {
            action: 'save_note',
            nonce: notesAjax.nonce,
            note_id: id,
            note_header_id: headerId,
            note: text
        }, function (res) {
            if (res.success) { noteModal.removeClass('show'); loadNotes(); alert(res.data.message); }
            else { alert(res.data); }
        });
    });

    // Delete note
    $(document).on('click', '.delete-note', function () {
        if (!confirm('Delete this note?')) return;
        const id = $(this).closest('li').data('id');
        $.post(notesAjax.ajax_url, {
            action: 'delete_note',
            nonce: notesAjax.nonce,
            id: id
        }, function (res) {
            if (res.success) { loadNotes(); alert('Note deleted'); }
            else { alert('Error deleting note'); }
        });
    });

    // View All Notes
    $('#view-all-notes-btn').on('click', function(){
        allNotesModal.addClass('show');
    });

    // Close modals
    $('[data-modal="noteModal"], [data-modal="allNotesModal"]').on('click', function () {
        $(`#${$(this).data('modal')}`).removeClass('show');
    });

    // Close modal by clicking outside content
    $(window).on('click', function (e) {
        if ($(e.target).hasClass('note-modal')) {
            $('.note-modal').removeClass('show');
        }
    });

});
