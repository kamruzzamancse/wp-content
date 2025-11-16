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
    $title        = sanitize_text_field($_POST['title'] ?? '');
    $type_id      = intval($_POST['type_id'] ?? 0);
    $client_id    = intval($_POST['client_id'] ?? 0);
    $property_id  = intval($_POST['property_id'] ?? 0);
    $note         = sanitize_textarea_field($_POST['note'] ?? '');

    if (empty($title) || empty($type_id) || empty($client_id) || empty($property_id)) {
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

    } else {
        wp_send_json_error(['message' => 'Please select a file.']);
        wp_die();
    }

    /*
    ===========================================================
    CHECK IF DOCUMENT EXISTS (client_id + property_id)
    ===========================================================
    */
    $existing_doc = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id FROM $documents_table 
             WHERE client_id=%d AND property_id=%d LIMIT 1",
            $client_id, $property_id
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
                'note'        => $note,
                'updated_at'  => $now,
                'updated_by'  => $current_user
            ],
            ['id' => $existing_doc->id],
            ['%s','%d','%s','%s','%s','%d'],
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
                'property_id'   => $property_id,
                'file_name'     => $file_url,
                'note'          => $note,
                'created_at'    => $now,
                'created_by'    => $current_user
            ],
            ['%s','%d','%d','%d','%s','%s','%s','%d']
        );
        $document_id = $wpdb->insert_id;

        if (!$document_id) {
            wp_send_json_error(['message' => 'Database insert failed.']);
        }
    }

    /*
    ===========================================================
    CHECK IF ASSIGNED TASK EXISTS (client_id + property_id)
    ===========================================================
    */
    $existing_assign = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, deleted_at, deleted_by FROM $assigned_table 
             WHERE client_id=%d AND property_id=%d LIMIT 1",
            $client_id, $property_id
        )
    );

    if ($existing_assign) {
        // UPDATE existing assignment
        $update_data = [
            'document_id' => $document_id,
            'updated_at'  => $now,
            'updated_by'  => $current_user,
        ];

        if (!is_null($existing_assign->deleted_at) || !is_null($existing_assign->deleted_by)) {
            $update_data['deleted_at'] = null;
            $update_data['deleted_by'] = null;
        }

        $wpdb->update(
            $assigned_table,
            $update_data,
            ['id' => $existing_assign->id],
            ['%d','%s','%d','%s','%d'],
            ['%d']
        );

    } else {
        // INSERT new assignment
        $wpdb->insert(
            $assigned_table,
            [
                'client_id'     => $client_id,
                'property_id'   => $property_id,
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
|----------------------------------------------------------------------
| AJAX: Soft Delete Assigned Task
|----------------------------------------------------------------------
*/

add_action('wp_ajax_rt_delete_assignment', 'rt_delete_assignment_handler');

function rt_delete_assignment_handler() {
    global $wpdb;

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rt_ap_assign_task_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
        wp_die();
    }

    $task_id = intval($_POST['task_id'] ?? 0);
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

/*
|----------------------------------------------------------------------
| AJAX: Load Assign Table
|----------------------------------------------------------------------
*/

add_action('wp_ajax_rt_load_assign_table', 'rt_load_assign_table');
function rt_load_assign_table() {
    global $wpdb;

    $paged         = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $items_per_page= 10;
    $offset        = ($paged - 1) * $items_per_page;
    $search_query  = sanitize_text_field($_POST['search'] ?? '');
    $filter_status = sanitize_text_field($_POST['filter_status'] ?? '');

    $clients_table               = $wpdb->prefix . 'clients';
    $rentcast_properties_table   = $wpdb->prefix . 'rentcast_properties';
    $assigned_property_table     = $wpdb->prefix . 'assigned_property';
    $assigned_tasks_table        = $wpdb->prefix . 'assigned_tasks';
    $documents_table             = $wpdb->prefix . 'documents';

    // Build WHERE clause
    $where_clause = ' WHERE a.deleted_at IS NULL ';
    if ($search_query) {
      $where_clause .= $wpdb->prepare(" AND (c.full_name LIKE %s OR p.address LIKE %s) ", "%$search_query%", "%$search_query%");
    }
    if ($filter_status === 'with_docs') {
      $where_clause .= " AND EXISTS (SELECT 1 FROM $assigned_tasks_table t WHERE t.client_id = a.client_id AND t.property_id = a.property_id AND t.deleted_at IS NULL) ";
    }
    if ($filter_status === 'no_docs') {
      $where_clause .= " AND NOT EXISTS (SELECT 1 FROM $assigned_tasks_table t WHERE t.client_id = a.client_id AND t.property_id = a.property_id AND t.deleted_at IS NULL) ";
    }

    // Count total items
    $total_items = $wpdb->get_var("SELECT COUNT(a.id)
                                   FROM {$assigned_property_table} a
                                   LEFT JOIN {$clients_table} c ON a.client_id = c.client_id
                                   LEFT JOIN {$rentcast_properties_table} p ON a.property_id = p.id
                                   $where_clause");

    // Fetch data
    $results = $wpdb->get_results("
      SELECT a.id AS assignment_id, a.client_id, a.property_id, a.created_at, c.full_name, p.address
      FROM {$assigned_property_table} a
      LEFT JOIN {$clients_table} c ON a.client_id = c.client_id
      LEFT JOIN {$rentcast_properties_table} p ON a.property_id = p.id
      $where_clause
      ORDER BY a.created_at DESC
      LIMIT $items_per_page OFFSET $offset
    ");

    ob_start();
    if ($results) {
        echo '<table class="wp-list-table widefat fixed striped">
                <thead>
                  <tr>
                    <th>Client Name</th>
                    <th>Property Address</th>
                    <th>Assigned Docs</th>
                    <th>Assigned Note</th>
                    <th>Reply Docs</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>';
        foreach ($results as $row) {
            $doc_name = $note_text = '';
            $task_id = 0;

            $task = $wpdb->get_row($wpdb->prepare("
                SELECT t.id AS task_id, t.document_id
                FROM {$assigned_tasks_table} t
                WHERE t.client_id=%d AND t.property_id=%d AND t.deleted_at IS NULL
                ORDER BY t.id DESC LIMIT 1
            ", $row->client_id, $row->property_id));

            if ($task) {
                $task_id = $task->task_id;
                if ($task->document_id) {
                    $doc = $wpdb->get_row($wpdb->prepare("
                        SELECT title, file_name, note
                        FROM {$documents_table}
                        WHERE id=%d AND deleted_at IS NULL
                    ", $task->document_id));
                    if ($doc) {
                        $file_short = basename($doc->file_name);
                        $doc_name = '<a href="' . esc_url($doc->file_name) . '" target="_blank">' . esc_html($file_short) . '</a>';
                        $note_text = !empty($doc->note) ? esc_html($doc->note) : '';
                    }
                }
            }

            echo '<tr 
                    data-assignment-id="'.esc_attr($row->assignment_id).'" 
                    data-task-id="'.esc_attr($task_id).'" 
                    data-client-id="'.esc_attr($row->client_id).'" 
                    data-property-id="'.esc_attr($row->property_id).'">
                    <td data-label="Client">'.esc_html($row->full_name).'</td>
                    <td data-label="Address">'.esc_html($row->address).'</td>
                    <td data-label="Assigned Docs">'.$doc_name.'</td>
                    <td data-label="Note">'.$note_text.'</td>
                    <td data-label="Reply Docs"></td>
                    <td data-label="Actions">
                        <button class="button upload-document-trigger"
                            data-assignment-id="'.esc_attr($row->assignment_id).'"
                            data-task-id="'.esc_attr($task_id).'"
                            data-client-id="'.esc_attr($row->client_id).'"
                            data-property-id="'.esc_attr($row->property_id).'">
                            <span class="dashicons dashicons-upload"></span>
                        </button>
                        <button class="button delete-assignment"
                            data-task-id="'.esc_attr($task_id).'">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </td>
                </tr>';
        }
        echo '</tbody></table>';

        // Pagination
        $total_pages = ceil($total_items / $items_per_page);
        if ($total_pages > 1) {
            echo '<div class="pagination">';
            echo paginate_links([
                'base' => '%_%',
                'format' => '',
                'current' => $paged,
                'total' => $total_pages,
                'prev_text' => '« Prev',
                'next_text' => 'Next »'
            ]);
            echo '</div>';
        }
    } else {
        echo '<p>No assignments found.</p>';
    }

    wp_send_json_success(['html' => ob_get_clean()]);
}
