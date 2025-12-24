<?php
add_action('wp_ajax_get_note_headers', 'get_note_headers');
add_action('wp_ajax_save_note_header', 'save_note_header');
add_action('wp_ajax_delete_note_header', 'delete_note_header');

/* ===== READ ===== */
function get_note_headers() {

    check_ajax_referer('note_header_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'notes';
    $user_id = get_current_user_id();

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, note_header 
             FROM $table 
             WHERE created_by=%d AND deleted_at IS NULL 
             ORDER BY id DESC",
            $user_id
        )
    );

    wp_send_json_success($results);
}

/* ===== CREATE & UPDATE ===== */
function save_note_header() {

    check_ajax_referer('note_header_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'notes';

    $id = intval($_POST['id']);
    $header = sanitize_text_field($_POST['note_header']);
    $user_id = get_current_user_id();

    if (!$header) {
        wp_send_json_error('Empty header');
    }

    if ($id) {
        $wpdb->update(
            $table,
            ['note_header' => $header],
            ['id' => $id]
        );
    } else {
        $wpdb->insert(
            $table,
            [
                'note_header' => $header,
                'created_at'  => current_time('mysql'),
                'created_by'  => $user_id
            ]
        );
    }

    wp_send_json_success();
}

/* ===== DELETE (SOFT DELETE) ===== */
function delete_note_header() {

    check_ajax_referer('note_header_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'notes';

    $wpdb->update(
        $table,
        [
            'deleted_at' => current_time('mysql'),
            'deleted_by' => get_current_user_id()
        ],
        ['id' => intval($_POST['id'])]
    );

    wp_send_json_success();
}
