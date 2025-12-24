// note-header.js - Updated for 2 tables
jQuery(document).ready(function ($) {
    const noteModal = $('#noteModal');
    let editingNoteId = 0;

    /* ================= LOAD HEADERS FOR DROPDOWN ================= */
    function loadHeaderDropdown() {
        $.post(noteHeaderAjax.ajax_url, {
            action: 'get_note_headers_for_notes'
        }, function (res) {
            if (!res.success) return;
            let opts = '<option value="">Select Note Header</option>';
            res.data.forEach(h => {
                opts += `<option value="${h.id}">${h.note_header}</option>`;
            });
            $('#note_header_select').html(opts);
        });
    }

    /* ================= LOAD NOTES FROM BOTH TABLES ================= */
    function loadNotes() {
        $.post(noteHeaderAjax.ajax_url, {
            action: 'get_notes'
        }, function (res) {
            if (!res.success) return;
            let html = '';
            res.data.forEach(n => {
                html += `
                    <li data-id="${n.id}">
                        <div class="note-header-title">${n.note_header}</div>
                        <div class="note-text">${n.note || ''}</div>
                        <div class="note-meta">Updated: ${n.updated_at || n.created_at}</div>
                        <div class="note-actions">
                            <span class="edit-note">✏️ Edit</span>
                            <span class="delete-note">🗑️ Delete</span>
                        </div>
                    </li>`;
            });
            $('#notes-list').html(html);
        });
    }

    // Initial load
    loadHeaderDropdown();
    loadNotes();

    /* ================= ADD NEW NOTE ================= */
    $(document).on('click', '#add-note-btn', function () {
        editingNoteId = 0;
        $('#note_id').val('');
        $('#note_text').val('');
        $('#save_note').text('Save Note');
        loadHeaderDropdown();
        noteModal.addClass('show');
    });

    /* ================= EDIT NOTE ================= */
    $(document).on('click', '.edit-note', function () {
        editingNoteId = $(this).closest('li').data('id');
        
        $.post(noteHeaderAjax.ajax_url, {
            action: 'get_single_note',
            note_id: editingNoteId
        }, function (res) {
            if (!res.success) return;
            
            $('#note_id').val(editingNoteId);
            $('#note_header_select').val(res.data.note_header_id);
            $('#note_text').val(res.data.note);
            $('#save_note').text('Update Note');
            noteModal.addClass('show');
        });
    });

    /* ================= SAVE/UPDATE NOTE ================= */
    $('#save_note').on('click', function () {
        const note_header_id = $('#note_header_select').val();
        const note_text = $('#note_text').val().trim();
        const note_id = $('#note_id').val();

        if (!note_header_id || !note_text) {
            alert('Please select a Note Header and write your note.');
            return;
        }

        const postData = {
            action: 'save_note',
            nonce: noteHeaderAjax.nonce,
            note_header_id: note_header_id,
            note: note_text
        };

        if (note_id) {
            postData.note_id = note_id;
        }

        $.post(noteHeaderAjax.ajax_url, postData, function (res) {
            if (res.success) {
                noteModal.removeClass('show');
                loadNotes();
                alert(res.data.message);
                // Reset form
                editingNoteId = 0;
                $('#note_id').val('');
                $('#note_text').val('');
                $('#save_note').text('Save Note');
            } else {
                alert(res.data);
            }
        });
    });

    /* ================= DELETE NOTE ================= */
    $(document).on('click', '.delete-note', function () {
        if (!confirm('Delete this note?')) return;

        const id = $(this).closest('li').data('id');

        $.post(noteHeaderAjax.ajax_url, {
            action: 'delete_note',
            nonce: noteHeaderAjax.nonce,
            id: id
        }, function (res) {
            if (res.success) {
                loadNotes();
                alert('Note deleted successfully');
            } else {
                alert('Error deleting note');
            }
        });
    });

    /* ================= CLOSE MODAL ================= */
    $('.note-modal-close').on('click', function () {
        noteModal.removeClass('show');
        editingNoteId = 0;
        $('#note_id').val('');
        $('#note_text').val('');
        $('#save_note').text('Save Note');
    });

    $(window).on('click', function (e) {
        if ($(e.target).hasClass('note-modal')) {
            noteModal.removeClass('show');
            editingNoteId = 0;
            $('#note_id').val('');
            $('#note_text').val('');
            $('#save_note').text('Save Note');
        }
    });
});