<?php
if (!defined('ABSPATH')) exit;

// =======================================================
// ADD DOCUMENT TYPE
// =======================================================
add_action('wp_ajax_rt_add_document_type', 'rt_add_document_type_callback');
function rt_add_document_type_callback() {
    check_ajax_referer('rt_doc_type_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'document_types';

    $user_id   = get_current_user_id();
    $type_name = sanitize_text_field($_POST['type_name'] ?? '');

    if (!$type_name) {
        wp_send_json_error('Type name is required.');
    }

    // Duplicate Check (same user only)
    $exists = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) FROM $table 
        WHERE type_name = %s 
        AND created_by = %d
        AND deleted_at IS NULL
    ", $type_name, $user_id));

    if ($exists > 0) {
        wp_send_json_error('This document type already exists.');
    }

    $wpdb->insert($table, [
        'type_name'  => $type_name,
        'created_at' => current_time('mysql'),
        'created_by' => $user_id
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


// =======================================================
// UPDATE DOCUMENT TYPE
// =======================================================
add_action('wp_ajax_rt_update_document_type', function() {
    check_ajax_referer('rt_doc_type_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'document_types';

    $user_id   = get_current_user_id();
    $id        = intval($_POST['id'] ?? 0);
    $type_name = sanitize_text_field($_POST['type_name'] ?? '');

    if (!$id || !$type_name) {
        wp_send_json_error('Invalid data.');
    }

    // Duplicate Check (same user only, except current ID)
    $exists = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) FROM $table 
        WHERE type_name = %s 
        AND id != %d
        AND created_by = %d
        AND deleted_at IS NULL
    ", $type_name, $id, $user_id));

    if ($exists > 0) {
        wp_send_json_error('This document type already exists.');
    }

    $updated = $wpdb->update($table, [
        'type_name'  => $type_name,
        'updated_at' => current_time('mysql'),
        'updated_by' => $user_id
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


// =======================================================
// DELETE DOCUMENT TYPE
// =======================================================
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


// =======================================================
// GET PAGINATED DOCUMENT TYPES (User-specific)
// =======================================================
add_action('wp_ajax_rt_get_document_types', 'rt_get_document_types_callback');
function rt_get_document_types_callback() {
    check_ajax_referer('rt_doc_type_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'document_types';

    $user_id = get_current_user_id(); // Only load current user's types

    $page     = max(1, intval($_POST['page'] ?? 1));
    $per_page = intval($_POST['per_page'] ?? 5);
    $offset   = ($page - 1) * $per_page;

    // Total count for this user only
    $total = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*)
        FROM $table
        WHERE deleted_at IS NULL
        AND created_by = %d
    ", $user_id));

    // Get paginated list for user only
    $doc_types = $wpdb->get_results($wpdb->prepare("
        SELECT id, type_name
        FROM $table
        WHERE deleted_at IS NULL
        AND created_by = %d
        ORDER BY created_at DESC
        LIMIT %d OFFSET %d
    ", $user_id, $per_page, $offset));

    wp_send_json_success([
        'data'        => $doc_types,
        'total'       => intval($total),
        'per_page'    => $per_page,
        'current'     => $page,
        'total_pages' => ceil($total / $per_page),
    ]);
}
