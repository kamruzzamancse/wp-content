<?php
if (!defined('ABSPATH')) exit;

// ---------------------
// Add Document Type
// ---------------------
add_action('wp_ajax_rt_add_document_type', 'rt_add_document_type_callback');
function rt_add_document_type_callback() {
    check_ajax_referer('rt_doc_type_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'document_types';

    $type_name = sanitize_text_field($_POST['type_name'] ?? '');

    if (!$type_name) {
        wp_send_json_error('Type name is required.');
    }

    $wpdb->insert($table, [
        'type_name'  => $type_name,
        'created_at' => current_time('mysql'),
        'created_by' => get_current_user_id()
    ]);

    if ($wpdb->insert_id) {
        wp_send_json_success([
            'id'        => $wpdb->insert_id,
            'type_name' => $type_name
        ]);
    } else {
        wp_send_json_error('Failed to add document type.');
    }
}

// ---------------------
// Update Document Type
// ---------------------
add_action('wp_ajax_rt_update_document_type', function() {
    check_ajax_referer('rt_doc_type_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'document_types';

    $id = intval($_POST['id'] ?? 0);
    $type_name = sanitize_text_field($_POST['type_name'] ?? '');

    if (!$id || !$type_name) {
        wp_send_json_error('Invalid data.');
    }

    $updated = $wpdb->update($table, [
        'type_name'  => $type_name,
        'updated_at' => current_time('mysql'),
        'updated_by' => get_current_user_id()
    ], ['id' => $id]);

    if ($updated !== false) {
        wp_send_json_success([
            'id'        => $id,
            'type_name' => $type_name
        ]);
    } else {
        wp_send_json_error('Failed to update document type.');
    }
});

// ---------------------
// Delete Document Type
// ---------------------
add_action('wp_ajax_rt_delete_document_type', function() {
    check_ajax_referer('rt_doc_type_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'document_types';

    $id = intval($_POST['id'] ?? 0);
    if (!$id) wp_send_json_error('Invalid ID.');

    $deleted = $wpdb->update($table, [
        'deleted_at' => current_time('mysql'),
        'deleted_by' => get_current_user_id()
    ], ['id' => $id]);

    if ($deleted !== false) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Failed to delete document type.');
    }
});
