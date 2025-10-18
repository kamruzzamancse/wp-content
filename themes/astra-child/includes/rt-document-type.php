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
    $type_name = sanitize_text_field($_POST['type_name']);
    $slug = sanitize_title($_POST['slug'] ?: $type_name);

    if (!$type_name) wp_send_json_error('Type name is required.');

    $wpdb->insert($table, [
        'type_name' => $type_name,
        'slug' => $slug,
        'created_at' => current_time('mysql')
    ]);

    wp_send_json_success([
        'id' => $wpdb->insert_id,
        'type_name' => $type_name,
        'slug' => $slug
    ]);
}

// ---------------------
// Update Document Type
// ---------------------
add_action('wp_ajax_rt_update_document_type', function() {
    check_ajax_referer('rt_doc_type_nonce', 'nonce');

    global $wpdb;
    $id = intval($_POST['id'] ?? 0);
    $type_name = sanitize_text_field($_POST['type_name'] ?? '');
    $slug = sanitize_title($_POST['slug'] ?? $type_name);

    if (!$id || !$type_name) wp_send_json_error('Invalid data.');

    $table = $wpdb->prefix . 'document_types';
    $updated = $wpdb->update($table, [
        'type_name' => $type_name,
        'slug'      => $slug,
    ], ['id' => $id]);

    if ($updated !== false) wp_send_json_success();
    else wp_send_json_error('Failed to update.');
});

// ---------------------
// Delete Document Type
// ---------------------
add_action('wp_ajax_rt_delete_document_type', function() {
    check_ajax_referer('rt_doc_type_nonce', 'nonce');

    global $wpdb;
    $id = intval($_POST['id'] ?? 0);
    if (!$id) wp_send_json_error('Invalid ID.');

    $table = $wpdb->prefix . 'document_types';
    $deleted = $wpdb->update($table, ['deleted_at' => current_time('mysql')], ['id' => $id]);

    if ($deleted !== false) wp_send_json_success();
    else wp_send_json_error('Failed to delete.');
});
