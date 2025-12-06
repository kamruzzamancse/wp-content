<?php
if (!defined('ABSPATH')) exit;

// -----------------------------
// Add / Upload Document
// -----------------------------
add_action('wp_ajax_rt_upload_document', function() {
    check_ajax_referer('upload_doc_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'docs';

    $title   = sanitize_text_field($_POST['title'] ?? '');
    $type_id = intval($_POST['type_id'] ?? 0);
    $note    = sanitize_textarea_field($_POST['note'] ?? '');

    if (empty($title)) wp_send_json_error('Please enter document title.');
    if ($type_id <= 0) wp_send_json_error('Please select a valid document type.');
    if (empty($_FILES['file_name']['name'])) wp_send_json_error('Please select a file to upload.');

    $file = $_FILES['file_name'];

    // Validate file size (max 25MB)
    $max_size = 25 * 1024 * 1024;
    if ($file['size'] > $max_size) wp_send_json_error('File size must be less than 25MB.');

    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    $file_type = wp_check_filetype($file['name']);
    if (!in_array($file_type['type'], $allowed_types)) wp_send_json_error('Only JPG, PNG, and PDF files are allowed.');

    // Upload file
    $upload = wp_handle_upload($file, ['test_form' => false]);
    if (isset($upload['error'])) wp_send_json_error('File upload failed: ' . $upload['error']);

    $file_full_path = $upload['file']; // Full server path
    $current_user_id = get_current_user_id();

    $inserted = $wpdb->insert($table, [
        'type_id'    => $type_id,
        'title'      => $title,
        'file_name'  => $file_full_path,
        'note'       => $note,
        'created_at' => current_time('mysql'),
        'created_by' => $current_user_id,
    ]);

    if ($inserted) {
        $type_name = $wpdb->get_var($wpdb->prepare(
            "SELECT type_name FROM {$wpdb->prefix}document_types WHERE id=%d",
            $type_id
        ));

        // Convert to front-end accessible URL
        $upload_dir = wp_upload_dir();
        $file_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_full_path);
        $file_url = str_replace('\\', '/', $file_url); // Windows fix

        wp_send_json_success([
            'id'        => $wpdb->insert_id,
            'title'     => $title,
            'type_id'   => $type_id,
            'type_name' => $type_name,
            'file_name' => $file_full_path,
            'note'      => $note,
            'file_url'  => $file_url
        ]);
    } else {
        wp_send_json_error('Database error: Failed to save document.');
    }
});

// -----------------------------
// Delete Document
// -----------------------------
add_action('wp_ajax_rt_delete_document', function() {
    check_ajax_referer('upload_doc_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'docs';
    $id    = intval($_POST['id'] ?? 0);
    if ($id <= 0) wp_send_json_error('Invalid document ID.');

    $document = $wpdb->get_row($wpdb->prepare(
        "SELECT file_name FROM $table WHERE id = %d AND deleted_at IS NULL",
        $id
    ));
    if (!$document) wp_send_json_error('Document not found.');

    // Delete file from server
    if (!empty($document->file_name) && file_exists($document->file_name)) {
        @unlink($document->file_name);
    }

    $deleted = $wpdb->update($table, [
        'deleted_at' => current_time('mysql'),
        'deleted_by' => get_current_user_id()
    ], ['id' => $id]);

    if ($deleted !== false) {
        wp_send_json_success('Document deleted successfully.');
    } else {
        wp_send_json_error('Failed to delete document from database.');
    }
});

// -----------------------------
// Update Document (Title / Type / Note / Optional File)
// -----------------------------
add_action('wp_ajax_rt_update_document', function() {
    check_ajax_referer('upload_doc_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'docs';
    $id    = intval($_POST['id'] ?? 0);
    $title = sanitize_text_field($_POST['title'] ?? '');
    $type_id = intval($_POST['type_id'] ?? 0);
    $note  = sanitize_textarea_field($_POST['note'] ?? '');

    if ($id <= 0) wp_send_json_error('Invalid document ID.');
    if (empty($title)) wp_send_json_error('Please enter document title.');
    if ($type_id <= 0) wp_send_json_error('Please select a valid document type.');

    // Check if document exists
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d AND deleted_at IS NULL",
        $id
    ));
    if (!$existing) wp_send_json_error('Document not found.');

    $data = [
        'title'      => $title,
        'type_id'    => $type_id,
        'note'       => $note,
        'updated_at' => current_time('mysql'),
        'updated_by' => get_current_user_id()
    ];

    // Handle optional file replacement
    if (!empty($_FILES['file_name']['name'])) {
        $file = $_FILES['file_name'];

        $max_size = 25 * 1024 * 1024;
        if ($file['size'] > $max_size) wp_send_json_error('File size must be less than 25MB.');

        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        $file_type = wp_check_filetype($file['name']);
        if (!in_array($file_type['type'], $allowed_types)) wp_send_json_error('Only JPG, PNG, and PDF files are allowed.');

        // Delete old file if exists
        if (!empty($existing->file_name) && file_exists($existing->file_name)) {
            @unlink($existing->file_name);
        }

        // Upload new file
        $upload = wp_handle_upload($file, ['test_form' => false]);
        if (isset($upload['error'])) wp_send_json_error('File upload failed: ' . $upload['error']);

        $file_full_path = $upload['file'];
        $data['file_name'] = $file_full_path;
    }

    $updated = $wpdb->update($table, $data, ['id' => $id]);
    if ($updated === false) wp_send_json_error('Failed to update document in database.');

    // Get type name for response
    $type_name = $wpdb->get_var($wpdb->prepare(
        "SELECT type_name FROM {$wpdb->prefix}document_types WHERE id=%d",
        $type_id
    ));

    // Generate front-end file URL
    $upload_dir = wp_upload_dir();
    $file_path_to_use = $data['file_name'] ?? $existing->file_name;
    $file_url = !empty($file_path_to_use) ? str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path_to_use) : '';
    $file_url = str_replace('\\', '/', $file_url);

    wp_send_json_success([
        'id'        => $id,
        'title'     => $title,
        'type_id'   => $type_id,
        'type_name' => $type_name,
        'note'      => $note,
        'file_name' => $file_path_to_use,
        'file_url'  => $file_url
    ]);
});

// -----------------------------
// Fetch Documents (Paginated)
// -----------------------------
add_action('wp_ajax_rt_get_documents', function() {
    check_ajax_referer('upload_doc_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'docs';
    $current_user_id = get_current_user_id();

    $page = max(1, intval($_POST['page'] ?? 1));
    $per_page = intval($_POST['per_page'] ?? 5);
    $offset = ($page - 1) * $per_page;

    $total = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE deleted_at IS NULL AND created_by = %d",
        $current_user_id
    ));

    $documents = $wpdb->get_results($wpdb->prepare("
        SELECT d.*, t.type_name
        FROM $table d
        LEFT JOIN {$wpdb->prefix}document_types t ON d.type_id = t.id
        WHERE d.deleted_at IS NULL AND d.created_by = %d
        ORDER BY d.created_at DESC
        LIMIT %d OFFSET %d
    ", $current_user_id, $per_page, $offset));

    $upload_dir = wp_upload_dir();
    foreach($documents as &$doc){
        if (!empty($doc->file_name)) {
            $doc->file_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $doc->file_name);
            $doc->file_url = str_replace('\\', '/', $doc->file_url); // Windows fix
        } else {
            $doc->file_url = '';
        }
    }

    wp_send_json_success([
        'data'        => $documents,
        'current'     => $page,
        'per_page'    => $per_page,
        'total'       => intval($total),
        'total_pages' => $per_page > 0 ? ceil($total / $per_page) : 0
    ]);
});
