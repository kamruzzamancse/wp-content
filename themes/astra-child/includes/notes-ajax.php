<?php
add_action('wp_ajax_get_note_headers_for_notes', 'get_note_headers_for_notes');
add_action('wp_ajax_get_notes', 'get_notes');
add_action('wp_ajax_save_note', 'save_note');
add_action('wp_ajax_delete_note', 'delete_note');

/* ===== LOAD HEADERS FOR DROPDOWN ===== */
function get_note_headers_for_notes() {

    global $wpdb;
    $table = $wpdb->prefix . 'notes';
    $user_id = get_current_user_id();

    // Only active rows
    $headers = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, note_header 
             FROM $table 
             WHERE created_by=%d AND deleted_at IS NULL
             ORDER BY note_header ASC",
            $user_id
        )
    );

    wp_send_json_success($headers);
}

/* ===== READ NOTES ===== */
function get_notes() {

    global $wpdb;
    $table = $wpdb->prefix . 'notes';
    $user_id = get_current_user_id();

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, note_header, note 
             FROM $table
             WHERE created_by=%d 
               AND deleted_at IS NULL
             ORDER BY id DESC",
            $user_id
        )
    );

    wp_send_json_success($results);
}

/* ===== SAVE NOTE ===== */
function save_note() {

    check_ajax_referer('note_header_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'notes';

    $note_header_id = intval($_POST['note_header_id']); // ID selected in dropdown
    $note = sanitize_textarea_field($_POST['note']);
    $user_id = get_current_user_id();

    if (!$note_header_id || !$note) {
        wp_send_json_error('Please select a Note Header and write your note.');
    }

    // Update existing row
    $wpdb->update(
        $table,
        ['note' => $note],   // only note column
        ['id' => $note_header_id, 'created_by' => $user_id]
    );

    wp_send_json_success();
}

/* ===== DELETE NOTE (soft delete) ===== */
function delete_note() {

    check_ajax_referer('note_header_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'notes';

    $note_header_id = intval($_POST['id']);

    $wpdb->update(
        $table,
        [
            'deleted_at' => current_time('mysql'),
            'deleted_by' => get_current_user_id()
        ],
        ['id' => $note_header_id]
    );

    wp_send_json_success();
}
