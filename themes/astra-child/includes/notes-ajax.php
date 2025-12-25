<?php
/**
 * Notes AJAX handlers
 */

add_action('wp_ajax_get_notes', 'cld_get_notes');
add_action('wp_ajax_get_single_note', 'cld_get_single_note');
add_action('wp_ajax_save_note', 'cld_save_note');
add_action('wp_ajax_delete_note', 'cld_delete_note');

/* ================= READ NOTES ================= */
function cld_get_notes() {
    check_ajax_referer('notes_nonce','nonce'); 
    global $wpdb;

    $notes_table = $wpdb->prefix . 'notes';
    $header_table = $wpdb->prefix . 'note_header';
    $user_id = get_current_user_id();

    // Fetch notes with note_header name
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT n.id, n.note_header_id, nh.note_header, n.note, n.created_at, n.updated_at
             FROM $notes_table n
             LEFT JOIN $header_table nh ON n.note_header_id = nh.id
             WHERE n.created_by=%d AND n.deleted_at IS NULL
             ORDER BY n.id DESC",
            $user_id
        ),
        ARRAY_A
    );

    wp_send_json_success($results);
}

/* ================= READ SINGLE NOTE ================= */
function cld_get_single_note() {
    check_ajax_referer('notes_nonce','nonce');
    global $wpdb;
    $table = $wpdb->prefix . 'notes';
    $user_id = get_current_user_id();
    $id = isset($_POST['note_id']) ? intval($_POST['note_id']) : 0;

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, note_header_id, note
             FROM $table
             WHERE id=%d AND created_by=%d",
            $id, $user_id
        ),
        ARRAY_A
    );

    if($row) wp_send_json_success($row);
    else wp_send_json_error('Note not found');
}

/* ================= CREATE / UPDATE NOTE ================= */
function cld_save_note() {
    check_ajax_referer('notes_nonce','nonce');
    global $wpdb;
    $table = $wpdb->prefix . 'notes';
    $user_id = get_current_user_id();

    $id = isset($_POST['note_id']) ? intval($_POST['note_id']) : 0;
    $note_header_id = isset($_POST['note_header_id']) ? intval($_POST['note_header_id']) : 0;
    $note = isset($_POST['note']) ? sanitize_textarea_field($_POST['note']) : '';

    if(!$note_header_id || !$note) wp_send_json_error('Select header and write note');

    if($id){
        // Update existing note
        $updated = $wpdb->update(
            $table,
            [
                'note_header_id' => $note_header_id,
                'note'           => $note,
                'updated_at'     => current_time('mysql'),
                'updated_by'     => $user_id
            ],
            [
                'id'         => $id,
                'created_by' => $user_id
            ]
        );

        if($updated !== false) wp_send_json_success(['message'=>'Note updated successfully']);
        else wp_send_json_error('Error updating note');
    } else {
        // Insert new note
        $inserted = $wpdb->insert(
            $table,
            [
                'note_header_id' => $note_header_id,
                'note'           => $note,
                'created_by'     => $user_id,
                'created_at'     => current_time('mysql')
            ]
        );

        if($inserted) wp_send_json_success(['message'=>'Note added successfully']);
        else wp_send_json_error('Error saving note: '.$wpdb->last_error);
    }
}

/* ================= DELETE NOTE ================= */
function cld_delete_note() {
    check_ajax_referer('notes_nonce','nonce');
    global $wpdb;
    $table = $wpdb->prefix . 'notes';
    $user_id = get_current_user_id();
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if(!$id) wp_send_json_error('Invalid note ID');

    $deleted = $wpdb->update(
        $table,
        [
            'deleted_at' => current_time('mysql'),
            'deleted_by' => $user_id
        ],
        [
            'id'         => $id,
            'created_by' => $user_id
        ]
    );

    if($deleted !== false) wp_send_json_success(['message'=>'Note deleted successfully']);
    else wp_send_json_error('Error deleting note');
}
