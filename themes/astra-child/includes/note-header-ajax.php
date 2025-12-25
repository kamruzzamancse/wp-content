<?php
/**
 * Note Header AJAX handlers
 */

add_action('wp_ajax_get_note_headers', 'cld_get_note_headers');
add_action('wp_ajax_save_note_header', 'cld_save_note_header');
add_action('wp_ajax_delete_note_header', 'cld_delete_note_header');

/* ================= READ NOTE HEADERS ================= */
function cld_get_note_headers() {
    check_ajax_referer('note_header_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'note_header';
    $user_id = get_current_user_id();

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, note_header 
             FROM $table 
             WHERE created_by=%d AND deleted_at IS NULL 
             ORDER BY id DESC",
            $user_id
        ),
        ARRAY_A
    );

    wp_send_json_success($results);
}

/* ================= CREATE / UPDATE NOTE HEADER ================= */
function cld_save_note_header() {
    check_ajax_referer('note_header_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'note_header';
    $user_id = get_current_user_id();

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $header = isset($_POST['note_header']) ? sanitize_text_field($_POST['note_header']) : '';

    if (empty($header)) wp_send_json_error('Note header cannot be empty');

    if ($id) {
        // Update existing header
        $updated = $wpdb->update(
            $table,
            [
                'note_header' => $header,
                'updated_at'  => current_time('mysql'),
                'updated_by'  => $user_id
            ],
            ['id' => $id, 'created_by' => $user_id]
        );

        if ($updated !== false) {
            wp_send_json_success(['message' => 'Note header updated successfully']);
        } else {
            wp_send_json_error('Error updating note header');
        }

    } else {
        // Insert new header
        $inserted = $wpdb->insert(
            $table,
            [
                'note_header' => $header,
                'created_at'  => current_time('mysql'),
                'created_by'  => $user_id
            ]
        );

        if ($inserted) {
            wp_send_json_success(['message' => 'Note header added successfully']);
        } else {
            $wpdb_error = $wpdb->last_error;
            wp_send_json_error("Error saving note header: $wpdb_error");
        }
    }
}

/* ================= DELETE NOTE HEADER ================= */
function cld_delete_note_header() {
    check_ajax_referer('note_header_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'note_header';
    $user_id = get_current_user_id();

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if (!$id) wp_send_json_error('Invalid note header ID');

    $deleted = $wpdb->update(
        $table,
        [
            'deleted_at' => current_time('mysql'),
            'deleted_by' => $user_id
        ],
        ['id' => $id, 'created_by' => $user_id]
    );

    if ($deleted !== false) wp_send_json_success(['message' => 'Note header deleted successfully']);
    else wp_send_json_error('Error deleting note header');
}
