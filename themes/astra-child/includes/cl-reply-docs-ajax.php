<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_cl_upload_reply_doc', 'cl_upload_reply_doc_handler');
add_action('wp_ajax_nopriv_cl_upload_reply_doc', 'cl_upload_reply_doc_handler');

add_action('wp_ajax_cl_delete_reply_doc', 'cl_delete_reply_doc_handler');

// new ajax action for refreshing table dynamically
add_action('wp_ajax_rt_refresh_documents_table', 'rt_refresh_documents_table_handler');

function cl_upload_reply_doc_handler() {
    global $wpdb;

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cl_reply_docs_nonce')) {
        wp_send_json_error(['message' => 'Security check failed.']);
        wp_die();
    }

    $documents_table   = $wpdb->prefix . 'documents';
    $reply_docs_table  = $wpdb->prefix . 'reply_docs';

    $client_id        = intval($_POST['client_id'] ?? 0);
    $property_id      = intval($_POST['property_id'] ?? 0);
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

    // === Custom Upload Directory ===
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

    // Step 1: Documents টেবিলে ইনসার্ট / আপডেট (with doc_type = 'replied')
    $existing_doc = $wpdb->get_row($wpdb->prepare("
        SELECT id FROM $documents_table 
        WHERE client_id=%d AND property_id=%d AND doc_type='replied' AND deleted_at IS NULL LIMIT 1
    ", $client_id, $property_id));

    if ($existing_doc) {
        $wpdb->update(
            $documents_table,
            [
                'title'      => $title,
                'type_id'    => $type_id,
                'file_name'  => $file_url,
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
                'property_id'  => $property_id,
                'file_name'    => $file_url,
                'doc_type'     => 'replied',
                'created_at'   => $now,
                'created_by'   => $current_user
            ],
            ['%s','%d','%d','%d','%s','%s','%s','%d']
        );
        $document_id = $wpdb->insert_id;
    }

    if (!$document_id) {
        wp_send_json_error(['message' => 'Database insert/update failed.']);
        wp_die();
    }

    // Step 2: Reply Docs টেবিলে ইনসার্ট / আপডেট
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
                'property_id'      => $property_id,
                'document_id'      => $document_id,
                'created_at'       => $now,
                'created_by'       => $current_user
            ],
            ['%d','%d','%d','%d','%s','%d']
        );
    }

    // send success + signal to reload table via JS
    wp_send_json_success(['message' => 'Reply document uploaded successfully.', 'reload_table' => true]);
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
        wp_send_json_success(['message' => 'Reply document deleted successfully.', 'reload_table' => true]);
    } else {
        wp_send_json_error(['message' => 'Delete failed.']);
    }

    wp_die();
}

// New function: reload document table content dynamically
function rt_refresh_documents_table_handler() {
    global $wpdb;

    $clients_table             = $wpdb->prefix . 'clients';
    $rentcast_properties_table = $wpdb->prefix . 'rentcast_properties';
    $assigned_property_table   = $wpdb->prefix . 'assigned_property';
    $assigned_tasks_table      = $wpdb->prefix . 'assigned_tasks';
    $documents_table           = $wpdb->prefix . 'documents';
    $reply_docs_table          = $wpdb->prefix . 'reply_docs';
    $current_user_id           = get_current_user_id();

    ob_start();

    $results = $wpdb->get_results($wpdb->prepare("
        SELECT a.id, a.client_id, a.property_id, a.created_at,
               c.full_name, p.address
        FROM {$assigned_property_table} a
        LEFT JOIN {$clients_table} c ON a.client_id = c.client_id
        LEFT JOIN {$rentcast_properties_table} p ON a.property_id = p.id
        WHERE a.deleted_at IS NULL
          AND c.user_id = %d
        ORDER BY a.created_at DESC
    ", $current_user_id));

    if ($results) {
        foreach ($results as $row) {
            $task = $wpdb->get_row($wpdb->prepare("
                SELECT t.id AS task_id, t.document_id, t.created_at
                FROM {$assigned_tasks_table} t
                WHERE t.client_id = %d AND t.property_id = %d
                ORDER BY t.id DESC LIMIT 1
            ", $row->client_id, $row->property_id));

            $assigned_doc_name = '';
            $assigned_doc_date = '';
            if ($task && $task->document_id) {
                $doc = $wpdb->get_row($wpdb->prepare("
                    SELECT title, file_name FROM {$documents_table} WHERE id = %d
                ", $task->document_id));
                if ($doc) {
                    $short = basename($doc->file_name);
                    $assigned_doc_name = '<a href="'.esc_url($doc->file_name).'" target="_blank">'.$short.'</a>';
                    $assigned_doc_date = esc_html($task->created_at);
                }
            }

            $reply_doc_name = '';
            $reply_doc_date = '';
            $reply_doc_id_attr = '';
            if ($task) {
                $reply = $wpdb->get_row($wpdb->prepare("
                    SELECT r.id, r.document_id, r.created_at
                    FROM {$reply_docs_table} r
                    WHERE r.assigned_task_id = %d AND r.deleted_at IS NULL
                    ORDER BY r.id DESC LIMIT 1
                ", $task->task_id));
                if ($reply && $reply->document_id) {
                    $reply_doc = $wpdb->get_row($wpdb->prepare("
                        SELECT title, file_name FROM {$documents_table} WHERE id = %d
                    ", $reply->document_id));
                    if ($reply_doc) {
                        $short = basename($reply_doc->file_name);
                        $reply_doc_name = '<a href="'.esc_url($reply_doc->file_name).'" target="_blank">'.$short.'</a>';
                        $reply_doc_date = esc_html($reply->created_at);
                    }
                    $reply_doc_id_attr = 'data-reply-doc-id="'.esc_attr($reply->id).'"';
                }
            }

            echo '<tr data-id="'.esc_attr($row->id).'"
                      data-client-id="'.esc_attr($row->client_id).'"
                      data-property-id="'.esc_attr($row->property_id).'">
                    <td>'.$row->address.'</td>
                    <td>'.$assigned_doc_name.'</td>
                    <td>'.$assigned_doc_date.'</td>
                    <td>'.$reply_doc_name.'</td>
                    <td>'.$reply_doc_date.'</td>
                    <td>
                        <button class="button upload-document-trigger"
                            data-assignment-id="'.$row->id.'"
                            data-client-id="'.$row->client_id.'"
                            data-property-id="'.$row->property_id.'"
                            data-assigned-task-id="'.($task ? $task->task_id : 0).'">
                            <span class="dashicons dashicons-upload"></span>
                        </button>
                        <button class="button delete-assignment"
                            data-assignment-id="'.$row->id.'" '.$reply_doc_id_attr.'>
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </td>
                  </tr>';
        }
    } else {
        echo '<tr><td colspan="6">No assignments found.</td></tr>';
    }

    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}
