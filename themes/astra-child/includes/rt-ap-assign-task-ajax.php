<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_upload_document', 'rt_upload_document_handler');
add_action('wp_ajax_nopriv_upload_document', 'rt_upload_document_handler');

function rt_upload_document_handler() {
    global $wpdb;

    ob_start();

    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rt_ap_assign_task_nonce')) {
        wp_send_json_error(['message' => 'Security check failed.']);
        wp_die();
    }

    // DB tables
    $documents_table = $wpdb->prefix . 'documents';
    $assigned_table  = $wpdb->prefix . 'assigned_tasks';

    // Sanitize fields
    $title          = sanitize_text_field($_POST['title'] ?? '');
    $type_id        = intval($_POST['type_id'] ?? 0);
    $client_id      = intval($_POST['client_id'] ?? 0);
    $properties_id  = intval($_POST['properties_id'] ?? 0);

    if (empty($title) || empty($type_id) || empty($client_id) || empty($properties_id)) {
        wp_send_json_error(['message' => 'Please fill all required fields.']);
    }

    // Handle file upload
    if (isset($_FILES['file_name']) && $_FILES['file_name']['error'] === 0) {

        $uploaded_file = $_FILES['file_name'];

        // Custom folder "realtor-documents"
        add_filter('upload_dir', function ($dirs) {
            $custom_dir = 'realtor-documents';
            $dirs['path']     = $dirs['basedir'] . '/' . $custom_dir;
            $dirs['url']      = $dirs['baseurl'] . '/' . $custom_dir;
            $dirs['basedir']  = $dirs['path'];
            $dirs['baseurl']  = $dirs['url'];
            return $dirs;
        });

        $upload = wp_handle_upload($uploaded_file, ['test_form' => false]);

        if (isset($upload['error'])) {
            wp_send_json_error(['message' => 'File upload error: ' . $upload['error'], 'debug' => $upload]);
            wp_die();
        }

        $file_url = $upload['url'];

        remove_all_filters('upload_dir');

        if (isset($upload['error'])) {
            wp_send_json_error(['message' => 'File upload error: ' . $upload['error']]);
            wp_die();
        }

        $file_url = $upload['url'];

    } else {
        wp_send_json_error(['message' => 'Please select a file.']);
        wp_die();
    }

    /*
    ===========================================================
    CHECK IF DOCUMENT EXISTS (client_id + properties_id)
    ===========================================================
    */
    $existing_doc = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id FROM $documents_table 
             WHERE client_id=%d AND properties_id=%d LIMIT 1",
            $client_id, $properties_id
        )
    );

    $current_user = get_current_user_id();
    $now = current_time('mysql');

    if ($existing_doc) {
        // UPDATE existing document
        $wpdb->update(
            $documents_table,
            [
                'title'       => $title,
                'type_id'     => $type_id,
                'file_name'   => $file_url,
                'updated_at'  => $now,
                'updated_by'  => $current_user
            ],
            ['id' => $existing_doc->id],
            ['%s','%d','%s','%s','%d'],
            ['%d']
        );
        $document_id = $existing_doc->id;

    } else {
        // INSERT new document
        $wpdb->insert(
            $documents_table,
            [
                'title'         => $title,
                'type_id'       => $type_id,
                'client_id'     => $client_id,
                'properties_id' => $properties_id,
                'file_name'     => $file_url,
                'created_at'    => $now,
                'created_by'    => $current_user
            ],
            ['%s','%d','%d','%d','%s','%s','%d']
        );
        $document_id = $wpdb->insert_id;

        if (!$document_id) {
            wp_send_json_error(['message' => 'Database insert failed.']);
        }
    }

    /*
    ===========================================================
    CHECK IF ASSIGNED TASK EXISTS (client_id + properties_id)
    ===========================================================
    */
    $existing_assign = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id FROM $assigned_table 
             WHERE client_id=%d AND properties_id=%d LIMIT 1",
            $client_id, $properties_id
        )
    );

    if ($existing_assign) {
        // UPDATE existing assignment
        $wpdb->update(
            $assigned_table,
            [
                'document_id' => $document_id,
                'updated_at'  => $now,
                'updated_by'  => $current_user
            ],
            ['id' => $existing_assign->id],
            ['%d','%s','%d'],
            ['%d']
        );
    } else {
        // INSERT new assignment
        $wpdb->insert(
            $assigned_table,
            [
                'client_id'     => $client_id,
                'properties_id' => $properties_id,
                'document_id'   => $document_id,
                'created_at'    => $now,
                'created_by'    => $current_user
            ],
            ['%d','%d','%d','%s','%d']
        );
    }

    ob_end_clean();
    wp_send_json_success(['message' => 'Document uploaded successfully.']);
    wp_die();
}

/*
|--------------------------------------------------------------------------
| AJAX: Soft Delete Assigned Task
| Table: wp_assigned_tasks
|--------------------------------------------------------------------------
*/

// Register AJAX handler
add_action('wp_ajax_rt_delete_assignment', 'rt_delete_assignment_handler');

function rt_delete_assignment_handler() {
    global $wpdb;

    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rt_ap_assign_task_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
        wp_die();
    }

    $task_id = intval($_POST['task_id'] ?? 0); // <-- use task_id
    if (!$task_id) {
        wp_send_json_error(['message' => 'Invalid task ID']);
        wp_die();
    }

    $assigned_table = $wpdb->prefix . 'assigned_tasks';

    $row = $wpdb->get_row(
        $wpdb->prepare("SELECT id FROM $assigned_table WHERE id=%d AND deleted_at IS NULL LIMIT 1", $task_id)
    );

    if (!$row) {
        wp_send_json_error(['message' => 'Assignment not found or already deleted']);
        wp_die();
    }

    $now  = current_time('mysql');
    $user = get_current_user_id();

    $updated = $wpdb->update(
        $assigned_table,
        ['deleted_at' => $now, 'deleted_by' => $user],
        ['id' => $task_id],
        ['%s', '%d'],
        ['%d']
    );

    if ($updated === false) {
        wp_send_json_error(['message' => 'Failed to delete assignment']);
        wp_die();
    }

    wp_send_json_success(['message' => 'Assignment deleted successfully']);
    wp_die();
}
