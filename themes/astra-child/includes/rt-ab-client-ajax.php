<?php
if (!defined('ABSPATH')) exit;

/**
 * Ensure user is logged in
 */
function rt_client_current_user_required() {
    if (!is_user_logged_in()) wp_send_json_error('Unauthorized', 401);
}

// =====================
// Fetch Clients (List) - Updated with Address
// =====================
function rt_fetch_clients_ajax() {
    rt_client_current_user_required();
    check_ajax_referer('rt_client_edit_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    $search = sanitize_text_field($_POST['search'] ?? '');
    $page   = max(1, intval($_POST['page'] ?? 1));
    $rows   = max(1, intval($_POST['rows'] ?? 10));
    $offset = ($page - 1) * $rows;

    $where = "WHERE deleted_at IS NULL AND user_id IS NOT NULL";
    $params = [];

    if ($search !== '') {
        $like = "%{$wpdb->esc_like($search)}%";
        $where .= " AND (
            first_name LIKE %s OR
            second_name LIKE %s OR
            first_email LIKE %s OR
            second_email LIKE %s OR
            first_phone LIKE %s OR
            second_phone LIKE %s OR
            address LIKE %s
        )";
        $params = [$like, $like, $like, $like, $like, $like, $like];
    }

    // Sorting
    $allowed_sort_columns = ['first_name','second_name','first_email','second_email','first_phone','second_phone','address','status','created_at'];
    $sort_by = in_array($_POST['sort_by'] ?? '', $allowed_sort_columns) ? $_POST['sort_by'] : 'created_at';
    $sort_order = strtoupper($_POST['sort_order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

    // Total count
    $count_query = !empty($params) 
        ? $wpdb->prepare("SELECT COUNT(*) FROM {$table} {$where}", $params) 
        : "SELECT COUNT(*) FROM {$table} {$where}";
    $total = intval($wpdb->get_var($count_query));

    // Select columns
    $select = "client_id, first_name, second_name, first_email, second_email, first_phone, second_phone, address, note, status, profile_picture, created_at";

    $sql = "SELECT {$select} FROM {$table} {$where} ORDER BY {$sort_by} {$sort_order} LIMIT %d, %d";
    if (!empty($params)) {
        $params[] = $offset;
        $params[] = $rows;
        $prepared = $wpdb->prepare($sql, $params);
    } else {
        $prepared = $wpdb->prepare($sql, $offset, $rows);
    }

    $clients = $wpdb->get_results($prepared, ARRAY_A);
    $total_pages = ceil($total / $rows);

    wp_send_json_success([
        'clients' => $clients,
        'total' => $total,
        'total_pages' => $total_pages,
        'page' => $page
    ]);
}
add_action('wp_ajax_fetch_clients_ajax', 'rt_fetch_clients_ajax');

// ======================
// Fetch single client (without property)
// ======================
function rt_fetch_realtor_client_ajax() {
    check_ajax_referer('rt_client_edit_nonce', 'nonce');
    global $wpdb;

    $client_id = intval($_POST['client_id'] ?? 0);
    if (!$client_id) wp_send_json_error('Invalid client ID');

    $table_clients = $wpdb->prefix . 'clients';

    $client = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_clients WHERE client_id = %d AND deleted_at IS NULL",
            $client_id
        ),
        ARRAY_A
    );

    if (!$client) {
        wp_send_json_error('Client not found');
    }

    wp_send_json_success($client);
}
add_action('wp_ajax_fetch_realtor_client_ajax', 'rt_fetch_realtor_client_ajax');

// ======================
// Fetch Realtor Client with Assigned Property & Property Details
// ======================
function rt_fetch_realtor_client_full_ajax() {
    check_ajax_referer('rt_client_edit_nonce', 'nonce');
    rt_client_current_user_required();
    
    global $wpdb;

    $client_id = intval($_POST['client_id'] ?? 0);
    if (!$client_id) wp_send_json_error('Invalid client ID');

    $table_clients = $wpdb->prefix . 'clients';
    $table_assigned = $wpdb->prefix . 'assigned_property';
    $table_properties = $wpdb->prefix . 'rentcast_properties';

    $client = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_clients WHERE client_id = %d AND deleted_at IS NULL",
            $client_id
        ),
        ARRAY_A
    );

    if (!$client) wp_send_json_error('Client not found');

    $assigned_props = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT ap.property_id, rp.listing_id, rp.address, rp.city, rp.state, rp.zip, 
                    rp.bedrooms, rp.bathrooms, rp.sqft, rp.price, rp.image_url, rp.description, rp.year_built, rp.property_value
             FROM $table_assigned ap
             LEFT JOIN $table_properties rp ON ap.property_id = rp.id
             WHERE ap.client_id = %d AND ap.deleted_at IS NULL",
            $client_id
        ),
        ARRAY_A
    );

    $client['assigned_properties'] = $assigned_props;

    wp_send_json_success($client);
}
add_action('wp_ajax_fetch_realtor_client_full_ajax', 'rt_fetch_realtor_client_full_ajax');

// =====================
// Create Client
// =====================
function rt_create_realtor_client_ajax() {
    rt_client_current_user_required();
    check_ajax_referer('rt_client_create_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    // Get and sanitize fields
    $first_name   = sanitize_text_field($_POST['first_name'] ?? '');
    $second_name  = sanitize_text_field($_POST['second_name'] ?? '');
    $first_email  = sanitize_email($_POST['first_email'] ?? '');
    $second_email = sanitize_email($_POST['second_email'] ?? '');
    $first_phone  = sanitize_text_field($_POST['first_phone'] ?? '');
    $second_phone = sanitize_text_field($_POST['second_phone'] ?? '');
    $address      = sanitize_text_field($_POST['address'] ?? '');
    $note         = sanitize_textarea_field($_POST['note'] ?? '');
    $status       = sanitize_text_field($_POST['status'] ?? '');

    if (!$first_name || !$first_email) {
        wp_send_json_error('First Name and Primary Email are required.');
    }

    // Check duplicate email
    $existing_client = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE first_email = %s AND deleted_at IS NULL",
        $first_email
    ));
    if ($existing_client > 0) wp_send_json_error('A client with this primary email already exists.');

    // Handle profile picture upload
    $profile_url = null;
    if (!empty($_FILES['realtor_client_profile_picture']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $upload = wp_handle_upload($_FILES['realtor_client_profile_picture'], ['test_form' => false]);
        if (isset($upload['error'])) wp_send_json_error('Upload Error: ' . $upload['error']);
        $profile_url = esc_url_raw($upload['url']);
    }

    $wpdb->query('START TRANSACTION');

    try {
        // Create WP user
        $password = wp_generate_password(12, false);
        $user_id = wp_create_user($first_email, $password, $first_email);
        if (is_wp_error($user_id)) throw new Exception('WP User creation failed: ' . $user_id->get_error_message());

        wp_update_user([
            'ID' => $user_id,
            'display_name' => trim($first_name . ' ' . $second_name),
            'first_name'   => $first_name,
            'last_name'    => $second_name
        ]);

        $wp_user = new WP_User($user_id);
        $wp_user->set_role('client');

        // Insert into clients table (lead_status excluded)
        $data = [
            'first_name'      => $first_name,
            'second_name'     => $second_name,
            'first_email'     => $first_email,
            'second_email'    => $second_email,
            'first_phone'     => $first_phone,
            'second_phone'    => $second_phone,
            'address'         => $address,
            'note'            => $note,
            'status'          => $status,
            'profile_picture' => $profile_url,
            'user_id'         => $user_id,
            'created_at'      => current_time('mysql'),
            'created_by'      => get_current_user_id()
        ];

        $inserted = $wpdb->insert($table, $data);
        if (!$inserted) throw new Exception('Could not create client. DB error: ' . $wpdb->last_error);

        $client_id = $wpdb->insert_id;
        $wpdb->query('COMMIT');

        wp_send_json_success([
            'client_id' => $client_id,
            'message' => 'Client created successfully'
        ]);

    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        if (isset($user_id) && $user_id) wp_delete_user($user_id);
        wp_send_json_error($e->getMessage());
    }
}
add_action('wp_ajax_create_realtor_client_ajax', 'rt_create_realtor_client_ajax');


// =====================
// Update Client
// =====================
function rt_update_realtor_client_ajax() {
    check_ajax_referer('rt_client_edit_nonce', 'nonce');
    rt_client_current_user_required();
    global $wpdb;

    $client_id = intval($_POST['realtor_client_id'] ?? 0);
    if (!$client_id) wp_send_json_error('Missing client ID');

    $table_clients = $wpdb->prefix . 'clients';

    // Build data array from modal fields (lead_status excluded)
    $data = [
        'first_name'   => sanitize_text_field($_POST['first_name'] ?? ''),
        'second_name'  => sanitize_text_field($_POST['second_name'] ?? ''),
        'first_email'  => sanitize_email($_POST['first_email'] ?? ''),
        'second_email' => sanitize_email($_POST['second_email'] ?? ''),
        'first_phone'  => sanitize_text_field($_POST['first_phone'] ?? ''),
        'second_phone' => sanitize_text_field($_POST['second_phone'] ?? ''),
        'address'      => sanitize_text_field($_POST['address'] ?? ''),
        'note'         => sanitize_textarea_field($_POST['note'] ?? ''),
        'status'       => sanitize_text_field($_POST['status'] ?? ''),
        'updated_at'   => current_time('mysql'),
        'updated_by'   => get_current_user_id(),
    ];

    // Required fields validation
    if (empty($data['first_name']) || empty($data['first_email'])) {
        wp_send_json_error('First Name and Primary Email are required.');
    }

    // Duplicate primary email check
    $existing_email = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table_clients} WHERE first_email = %s AND client_id != %d AND deleted_at IS NULL",
        $data['first_email'],
        $client_id
    ));
    if ($existing_email > 0) wp_send_json_error('A client with this primary email already exists.');

    // Handle profile picture
    $format = ['%s','%s','%s','%s','%s','%s','%s','%s','%s','%s']; // no lead_status
    if (!empty($_FILES['realtor_client_profile_picture']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $upload = wp_handle_upload($_FILES['realtor_client_profile_picture'], ['test_form' => false]);
        if (isset($upload['error'])) wp_send_json_error('Profile picture upload failed: ' . $upload['error']);

        $data['profile_picture'] = esc_url_raw($upload['url']);
        $format[] = '%s';
    }

    $wpdb->query('START TRANSACTION');
    try {
        $updated = $wpdb->update(
            $table_clients,
            $data,
            ['client_id' => $client_id],
            $format,
            ['%d']
        );
        if ($updated === false) throw new Exception('Database update failed: ' . $wpdb->last_error);

        // Update WP user safely
        $client_data = $wpdb->get_row($wpdb->prepare(
            "SELECT user_id FROM {$table_clients} WHERE client_id = %d",
            $client_id
        ));

        if ($client_data && $client_data->user_id) {
            $user_id = intval($client_data->user_id);
            wp_update_user([
                'ID'           => $user_id,
                'user_email'   => $data['first_email'],
                'display_name' => $data['first_name'],
            ]);
        }

        $wpdb->query('COMMIT');
        wp_send_json_success([
            'message' => 'Client updated successfully!',
            'client_id' => $client_id,
            'profile_picture' => $data['profile_picture'] ?? null
        ]);

    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        wp_send_json_error('Update failed: ' . $e->getMessage());
    }
}
add_action('wp_ajax_realtor_update_client', 'rt_update_realtor_client_ajax');

// =====================
// Delete Client
// =====================
function rt_delete_realtor_client_ajax() {
    rt_client_current_user_required();
    check_ajax_referer('rt_client_delete_nonce', 'nonce');

    $client_id = intval($_POST['client_id'] ?? 0);
    if (!$client_id) wp_send_json_error('Missing client ID');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';
    $client = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE client_id=%d", $client_id));
    if (!$client) wp_send_json_error('Client not found');

    $deleted = $wpdb->update($table, [
        'deleted_at' => current_time('mysql'),
        'deleted_by' => get_current_user_id()
    ], ['client_id' => $client_id]);

    if ($client->user_id) {
        wp_update_user(['ID' => $client->user_id, 'user_status' => 1]);
    }

    if ($deleted !== false) wp_send_json_success('Client deleted successfully');
    wp_send_json_error('Could not delete client');
}
add_action('wp_ajax_delete_realtor_client_ajax', 'rt_delete_realtor_client_ajax');


// =====================
// Export Clients
// =====================
function rt_export_clients_ajax() {
    rt_client_current_user_required();
    check_ajax_referer('rt_client_export_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    // Allowed columns based on table structure
    $allowed_cols = [
        'client_id', 'user_id', 'first_name', 'second_name', 'first_email', 'second_email',
        'first_phone', 'second_phone', 'address', 'note', 'status', 'lead_status',
        'profile_picture', 'created_at', 'created_by', 'updated_at', 'updated_by', 
        'deleted_at', 'deleted_by'
    ];

    $columns = json_decode(stripslashes($_POST['columns'] ?? '[]'), true);
    $columns = array_intersect($allowed_cols, $columns);
    if (empty($columns)) $columns = $allowed_cols;

    $scope = sanitize_text_field($_POST['scope'] ?? 'all');
    $where = "WHERE deleted_at IS NULL";
    $params = [];

    if ($scope === 'current' && !empty($_POST['current_ids'])) {
        $current_ids = array_map('intval', $_POST['current_ids']);
        if (!empty($current_ids)) {
            $placeholders = implode(',', array_fill(0, count($current_ids), '%d'));
            $where .= " AND client_id IN ($placeholders)";
            $params = $current_ids;
        }
    }

    $select = implode(',', array_map('esc_sql', $columns));
    $sql = !empty($params) 
        ? $wpdb->prepare("SELECT {$select} FROM {$table} {$where}", $params) 
        : "SELECT {$select} FROM {$table} {$where}";

    $results = $wpdb->get_results($sql, ARRAY_A);

    wp_send_json_success(['clients' => $results]);
}
add_action('wp_ajax_export_clients_ajax', 'rt_export_clients_ajax');


// =====================
// Import Clients
// =====================
function rt_import_clients_ajax() {
    rt_client_current_user_required();

    if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rt_client_import_nonce')) {
        wp_send_json_error('Security verification failed');
    }

    if (!isset($_FILES['clients_file']) || empty($_FILES['clients_file']['tmp_name'])) {
        wp_send_json_error('No file uploaded or file is empty');
    }

    $file = $_FILES['clients_file']['tmp_name'];
    $filename = $_FILES['clients_file']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $clients = [];

    // =====================
    // Parse CSV
    // =====================
    if ($ext === 'csv') {
        $clients = rt_parse_csv_file($file);
    }
    // =====================
    // Parse XLSX
    // =====================
    elseif ($ext === 'xlsx') {
        if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once WP_CONTENT_DIR . '/vendor/autoload.php';
        }
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, true, true);

            $headers = array_map('strtolower', array_map('trim', $data[1] ?? []));
            unset($data[1]);

            foreach ($data as $row) {
                $client = [];
                foreach ($headers as $key => $header) {
                    if (!empty($header)) $client[$header] = trim($row[$key] ?? '');
                }
                if (!empty($client)) $clients[] = $client;
            }
        } catch (Exception $e) {
            wp_send_json_error('Failed to read Excel file: ' . $e->getMessage());
        }
    } else {
        wp_send_json_error('Unsupported file type. Please use CSV or Excel (.xlsx) format.');
    }

    if (empty($clients)) {
        wp_send_json_error('No valid data found in uploaded file.');
    }

    global $wpdb;
    $table = $wpdb->prefix . 'clients';
    $current_user_id = get_current_user_id();
    $duplicate_handling = sanitize_text_field($_POST['duplicate_handling'] ?? 'skip');

    $inserted = 0;
    $updated = 0;
    $skipped = 0;
    $errors = [];

    foreach ($clients as $index => $row) {
        $row_num = $index + 1;

        // Sanitize all fields
        $first_name = sanitize_text_field($row['first_name'] ?? '');
        $second_name = sanitize_text_field($row['second_name'] ?? '');
        $first_email = sanitize_email($row['first_email'] ?? '');
        $second_email = sanitize_email($row['second_email'] ?? '');
        $first_phone = sanitize_text_field($row['first_phone'] ?? '');
        $second_phone = sanitize_text_field($row['second_phone'] ?? '');
        $address = sanitize_text_field($row['address'] ?? '');
        $note = sanitize_textarea_field($row['note'] ?? '');
        $status = sanitize_text_field($row['status'] ?? 'active');
        $lead_status = in_array($row['lead_status'] ?? '', ['hot','warm','cold']) ? $row['lead_status'] : 'cold';
        $profile_picture = sanitize_text_field($row['profile_picture'] ?? '');

        // Required fields
        if (empty($first_name) || empty($first_email)) {
            $errors[] = "Row {$row_num}: Missing required fields (first_name or first_email).";
            $skipped++;
            continue;
        }
        if (!is_email($first_email)) {
            $errors[] = "Row {$row_num}: Invalid email format ({$first_email}).";
            $skipped++;
            continue;
        }

        // Check existing WP user
        $existing_wp_user_id = email_exists($first_email);
        if ($existing_wp_user_id) {
            $errors[] = "Row {$row_num}: WP user already exists ({$first_email}).";
            $skipped++;
            continue;
        }

        // Check existing client
        $existing_client_id = $wpdb->get_var($wpdb->prepare(
            "SELECT client_id FROM {$table} WHERE first_email = %s AND deleted_at IS NULL",
            $first_email
        ));

        if ($existing_client_id && $duplicate_handling === 'update') {
            $update_data = [
                'first_name' => $first_name,
                'second_name' => $second_name,
                'second_email' => $second_email,
                'first_phone' => $first_phone,
                'second_phone' => $second_phone,
                'address' => $address,
                'note' => $note,
                'status' => $status,
                'lead_status' => $lead_status,
                'profile_picture' => $profile_picture,
                'updated_at' => current_time('mysql'),
                'updated_by' => $current_user_id
            ];
            $res = $wpdb->update($table, $update_data, ['client_id' => $existing_client_id]);
            if ($res !== false) $updated++;
            else $errors[] = "Row {$row_num}: Failed to update existing client ({$first_email}).";
            continue;
        }

        if ($existing_client_id && $duplicate_handling !== 'update') {
            $errors[] = "Row {$row_num}: Client already exists ({$first_email}), skipped.";
            $skipped++;
            continue;
        }

        // Create WP user
        $password = wp_generate_password(12, false);
        $user_id = wp_create_user($first_email, $password, $first_email);
        if (is_wp_error($user_id)) {
            $errors[] = "Row {$row_num}: WP user creation failed ({$first_email}).";
            $skipped++;
            continue;
        }

        wp_update_user([
            'ID' => $user_id,
            'display_name' => trim($first_name . ' ' . $second_name),
            'first_name' => $first_name,
            'last_name' => $second_name
        ]);
        $wp_user = new WP_User($user_id);
        $wp_user->set_role('client');

        // Insert into clients table
        $insert_data = [
            'first_name' => $first_name,
            'second_name' => $second_name,
            'first_email' => $first_email,
            'second_email' => $second_email,
            'first_phone' => $first_phone,
            'second_phone' => $second_phone,
            'address' => $address,
            'note' => $note,
            'status' => $status,
            'lead_status' => $lead_status,
            'profile_picture' => $profile_picture,
            'user_id' => $user_id,
            'created_at' => current_time('mysql'),
            'created_by' => $current_user_id
        ];

        $insert_res = $wpdb->insert($table, $insert_data);
        if ($insert_res === false) {
            wp_delete_user($user_id);
            $errors[] = "Row {$row_num}: DB insert failed ({$first_email}).";
            $skipped++;
            continue;
        }

        $inserted++;
    }

    $message = "Import finished: {$inserted} inserted, {$updated} updated, {$skipped} skipped.";
    if (!empty($errors)) $message .= ' ' . count($errors) . ' errors.';

    wp_send_json_success([
        'message' => $message,
        'inserted' => $inserted,
        'updated' => $updated,
        'skipped' => $skipped,
        'errors' => array_slice($errors, 0, 50)
    ]);
}
add_action('wp_ajax_import_clients_ajax', 'rt_import_clients_ajax');


// =====================
// CSV Parser (Updated)
// =====================
function rt_parse_csv_file($file) {
    $clients = [];
    if (!file_exists($file) || !is_readable($file)) return $clients;

    if (($handle = fopen($file, 'r')) !== false) {
        $header = null;
        while (($row = fgetcsv($handle, 0, ",")) !== false) {
            // Skip empty rows
            if (count(array_filter($row)) === 0) continue;

            if (!$header) {
                $header = array_map('strtolower', array_map('trim', $row));
                continue;
            }

            // Map row values to headers
            $client = [];
            foreach ($header as $i => $key) {
                if (isset($row[$i]) && $key !== '') $client[$key] = trim($row[$i]);
            }

            if (!empty($client)) $clients[] = $client;
        }
        fclose($handle);
    }

    return $clients;
}
