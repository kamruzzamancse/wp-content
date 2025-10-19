<?php
if (!defined('ABSPATH')) exit;

// Helper function: handle upload inside "realtor-documents"
function rt_handle_document_upload($file_field) {
    if (empty($_FILES[$file_field]['name'])) {
        return new WP_Error('no_file', 'No file uploaded');
    }

    $upload_dir = wp_upload_dir();
    $target_dir = trailingslashit($upload_dir['basedir']) . 'realtor-documents/';

    // Create directory if missing
    if (!file_exists($target_dir)) {
        wp_mkdir_p($target_dir);
    }

    $file = $_FILES[$file_field];
    $file_name = sanitize_file_name($file['name']);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // Return relative path for storage
        return 'realtor-documents/' . $file_name;
    }

    return new WP_Error('upload_failed', 'File upload failed');
}

// ---------------------
// Add Document
// ---------------------
add_action('wp_ajax_rt_add_document', 'rt_add_document_callback');
function rt_add_document_callback() {
    check_ajax_referer('rt_doc_nonce', 'nonce');
    global $wpdb;
    $table = $wpdb->prefix . 'documents';

    $title = sanitize_text_field($_POST['title'] ?? '');
    $type_id = intval($_POST['type_id'] ?? 0);

    if (!$title || !$type_id || empty($_FILES['file_name']['name'])) {
        wp_send_json_error('All fields are required.');
    }

    $uploaded_path = rt_handle_document_upload('file_name');
    if (is_wp_error($uploaded_path)) wp_send_json_error($uploaded_path->get_error_message());

    $wpdb->insert($table, [
        'title'      => $title,
        'type_id'    => $type_id,
        'file_name'  => $uploaded_path,
        'created_at' => current_time('mysql'),
        'created_by' => get_current_user_id()
    ]);

    if ($wpdb->insert_id) wp_send_json_success();
    else wp_send_json_error('Failed to add document.');
}

// ---------------------
// Update Document
// ---------------------
add_action('wp_ajax_rt_update_document', 'rt_update_document_callback');
function rt_update_document_callback() {
    check_ajax_referer('rt_doc_nonce', 'nonce');
    global $wpdb;
    $table = $wpdb->prefix . 'documents';

    $id = intval($_POST['document_id'] ?? 0);
    $title = sanitize_text_field($_POST['title'] ?? '');
    $type_id = intval($_POST['type_id'] ?? 0);

    if (!$id || !$title || !$type_id) {
        wp_send_json_error('Invalid data.');
    }

    $data = [
        'title'      => $title,
        'type_id'    => $type_id,
        'updated_at' => current_time('mysql'),
        'updated_by' => get_current_user_id()
    ];

    if (!empty($_FILES['file_name']['name'])) {
        $uploaded_path = rt_handle_document_upload('file_name');
        if (is_wp_error($uploaded_path)) wp_send_json_error($uploaded_path->get_error_message());
        $data['file_name'] = $uploaded_path;
    }

    $updated = $wpdb->update($table, $data, ['id' => $id]);

    if ($updated !== false) wp_send_json_success();
    else wp_send_json_error('Failed to update document.');
}

// ---------------------
// Delete Document (Soft Delete)
// ---------------------
add_action('wp_ajax_rt_delete_document', 'rt_delete_document_callback');
function rt_delete_document_callback() {
    check_ajax_referer('rt_doc_nonce', 'nonce');
    global $wpdb;
    $table = $wpdb->prefix . 'documents';

    $id = intval($_POST['id'] ?? 0);
    if (!$id) wp_send_json_error('Invalid ID.');

    $deleted = $wpdb->update($table, [
        'deleted_at' => current_time('mysql'),
        'deleted_by' => get_current_user_id()
    ], ['id' => $id]);

    if ($deleted !== false) wp_send_json_success();
    else wp_send_json_error('Failed to delete document.');
}
