<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_cl_upload_reply_doc', 'cl_upload_reply_doc_handler');
add_action('wp_ajax_nopriv_cl_upload_reply_doc', 'cl_upload_reply_doc_handler');

add_action('wp_ajax_cl_delete_reply_doc', 'cl_delete_reply_doc_handler');

function cl_upload_reply_doc_handler() {
    global $wpdb;

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cl_reply_docs_nonce')) {
        wp_send_json_error(['message' => 'Security check failed.']);
        wp_die();
    }

    $documents_table   = $wpdb->prefix . 'documents';
    $reply_docs_table  = $wpdb->prefix . 'reply_docs';

    $client_id        = intval($_POST['client_id'] ?? 0);
    $property_id    = intval($_POST['property_id'] ?? 0);
    $assigned_task_id = intval($_POST['assigned_task_id'] ?? 0);
    $title            = sanitize_text_field($_POST['title'] ?? '');
    $type_id          = intval($_POST['type_id'] ?? 0);

    if (!$client_id || !$property_id || !$assigned_task_id || !$title || !$type_id) {
        wp_send_json_error(['message' => 'Please fill all required fields.']);
        wp_die();
    }

    if (!isset($_FILES['file_name']) || $_FILES['file_name']['error'] !== 0) {
        wp_send_json_error(['message' => 'Please select a file.']);
        wp_die();
    }

    $uploaded_file = $_FILES['file_name'];

    add_filter('upload_dir', function($dirs){
        $custom_dir = 'client-reply-documents';
        $dirs['path']     = $dirs['basedir'] . '/' . $custom_dir;
        $dirs['url']      = $dirs['baseurl'] . '/' . $custom_dir;
        $dirs['basedir']  = $dirs['path'];
        $dirs['baseurl']  = $dirs['url'];
        return $dirs;
    });

    $upload = wp_handle_upload($uploaded_file, ['test_form' => false]);
    remove_all_filters('upload_dir');

    if (isset($upload['error'])) {
        wp_send_json_error(['message' => 'File upload error: ' . $upload['error']]);
        wp_die();
    }

    $file_url = $upload['url'];
    $now = current_time('mysql');
    $current_user = get_current_user_id();

    // Check existing document (client+property)
    $existing_doc = $wpdb->get_row($wpdb->prepare("
        SELECT id FROM $documents_table WHERE client_id=%d AND property_id=%d AND deleted_at IS NULL LIMIT 1
    ", $client_id, $property_id));

    if ($existing_doc) {
        $wpdb->update(
            $documents_table,
            [
                'title'      => $title,
                'type_id'    => $type_id,
                'file_name'  => $file_url,
                'doc_type'   => 'reply',
                'updated_at' => $now,
                'updated_by' => $current_user
            ],
            ['id' => $existing_doc->id],
            ['%s','%d','%s','%s','%d'],
            ['%d']
        );
        $document_id = $existing_doc->id;
    } else {
        $wpdb->insert(
            $documents_table,
            [
                'title'        => $title,
                'type_id'      => $type_id,
                'client_id'    => $client_id,
                'property_id'=> $property_id,
                'file_name'    => $file_url,
                'doc_type'     => 'reply',
                'created_at'   => $now,
                'created_by'   => $current_user
            ],
            ['%s','%d','%d','%d','%s','%s','%d']
        );
        $document_id = $wpdb->insert_id;
    }

    if (!$document_id) {
        wp_send_json_error(['message' => 'Database insert/update failed.']);
        wp_die();
    }

    // Check existing reply doc
    $existing_reply = $wpdb->get_row($wpdb->prepare("
        SELECT id FROM $reply_docs_table
        WHERE client_id=%d AND property_id=%d AND assigned_task_id=%d AND deleted_at IS NULL LIMIT 1
    ", $client_id, $property_id, $assigned_task_id));

    if ($existing_reply) {
        $wpdb->update(
            $reply_docs_table,
            [
                'document_id' => $document_id,
                'updated_at'  => $now,
                'updated_by'  => $current_user
            ],
            ['id' => $existing_reply->id],
            ['%d','%s','%d'],
            ['%d']
        );
    } else {
        $wpdb->insert(
            $reply_docs_table,
            [
                'assigned_task_id' => $assigned_task_id,
                'client_id'        => $client_id,
                'property_id'    => $property_id,
                'document_id'      => $document_id,
                'created_at'       => $now,
                'created_by'       => $current_user
            ],
            ['%d','%d','%d','%d','%s','%d']
        );
    }

    wp_send_json_success(['message' => 'Reply document uploaded successfully.']);
    wp_die();
}

// Delete Reply Document (soft delete)
function cl_delete_reply_doc_handler() {
    global $wpdb;

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cl_reply_docs_nonce')) {
        wp_send_json_error(['message' => 'Security check failed.']);
        wp_die();
    }

    $reply_docs_table = $wpdb->prefix . 'reply_docs';
    $reply_doc_id = intval($_POST['reply_doc_id'] ?? 0);

    if (!$reply_doc_id) {
        wp_send_json_error(['message' => 'Reply document ID not found.']);
        wp_die();
    }

    $current_user = get_current_user_id();
    $now = current_time('mysql');

    $updated = $wpdb->update(
        $reply_docs_table,
        [
            'deleted_at' => $now,
            'deleted_by' => $current_user
        ],
        ['id' => $reply_doc_id],
        ['%s','%d'],
        ['%d']
    );

    if ($updated !== false) {
        wp_send_json_success(['message' => 'Reply document deleted successfully.']);
    } else {
        wp_send_json_error(['message' => 'Delete failed.']);
    }

    wp_die();
}
