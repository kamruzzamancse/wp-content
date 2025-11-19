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

    // Duplicate Check
    $exists = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) FROM $table 
        WHERE type_name = %s AND deleted_at IS NULL
    ", $type_name));

    if ($exists > 0) {
        wp_send_json_error('This document type already exists.');
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

    // Duplicate Check (Except current ID)
    $exists = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) FROM $table 
        WHERE type_name = %s AND id != %d AND deleted_at IS NULL
    ", $type_name, $id));

    if ($exists > 0) {
        wp_send_json_error('This document type already exists.');
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

// ---------------------
// AJAX: Get Paginated Document Types
// ---------------------
add_action('wp_ajax_rt_get_document_types', 'rt_get_document_types_callback');
function rt_get_document_types_callback() {
    check_ajax_referer('rt_doc_type_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'document_types';

    $page = max(1, intval($_POST['page'] ?? 1));
    $per_page = intval($_POST['per_page'] ?? 5);
    $offset = ($page - 1) * $per_page;

    // Total count
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE deleted_at IS NULL");

    // Get paginated results
    $doc_types = $wpdb->get_results($wpdb->prepare("
        SELECT id, type_name FROM $table
        WHERE deleted_at IS NULL
        ORDER BY created_at DESC
        LIMIT %d OFFSET %d
    ", $per_page, $offset));

    wp_send_json_success([
        'data'      => $doc_types,
        'total'     => intval($total),
        'per_page'  => $per_page,
        'current'   => $page,
        'total_pages' => ceil($total / $per_page),
    ]);
}
