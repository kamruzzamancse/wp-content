<?php
if (!defined('ABSPATH')) exit;

/**
 * Ensure user is logged in
 */
function rt_client_current_user_required() {
    if (!is_user_logged_in()) wp_send_json_error('Unauthorized', 401);
}

// =====================
// Fetch Clients (List)
// =====================
function rt_fetch_clients_ajax() {
    rt_client_current_user_required();
    check_ajax_referer('rt_client_edit_nonce', 'nonce'); // FIXED

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    $search = sanitize_text_field($_POST['search'] ?? '');
    $page = max(1, intval($_POST['page'] ?? 1));
    $rows = max(1, intval($_POST['rows'] ?? 10));
    $offset = ($page - 1) * $rows;

    // Add condition user_id IS NOT NULL
    $where = "WHERE deleted_at IS NULL AND user_id IS NOT NULL";
    $params = [];
    if ($search !== '') {
        $like = "%{$wpdb->esc_like($search)}%";
        $where .= " AND (full_name LIKE %s OR email LIKE %s OR phone LIKE %s)";
        $params = [$like, $like, $like];
    }

    $count_query = !empty($params) 
        ? $wpdb->prepare("SELECT COUNT(*) FROM {$table} {$where}", $params) 
        : "SELECT COUNT(*) FROM {$table} {$where}";
    $total = intval($wpdb->get_var($count_query));

    $sql = "SELECT * FROM {$table} {$where} ORDER BY created_at DESC LIMIT %d, %d";
    if (!empty($params)) {
        $params[] = $offset;
        $params[] = $rows;
        $prepared = $wpdb->prepare($sql, $params);
    } else {
        $prepared = $wpdb->prepare($sql, $offset, $rows);
    }

    $clients = $wpdb->get_results($prepared);
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
// Register AJAX Handlers
// ======================
add_action('wp_ajax_fetch_clients_ajax', 'rt_fetch_clients_ajax');
add_action('wp_ajax_create_realtor_client_ajax', 'rt_create_realtor_client_ajax');
add_action('wp_ajax_update_realtor_client_ajax', 'rt_update_realtor_client_ajax');
add_action('wp_ajax_delete_realtor_client_ajax', 'rt_delete_realtor_client_ajax');
add_action('wp_ajax_search_properties','rt_search_properties_ajax');


// ======================
// Fetch single client with property info - IMPROVED VERSION
// ======================
function rt_fetch_realtor_client_ajax() {
    check_ajax_referer('rt_client_edit_nonce', 'nonce');
    global $wpdb;

    $client_id = intval($_POST['client_id']);
    if (!$client_id) wp_send_json_error('Invalid client ID');

    $table_clients = $wpdb->prefix . 'clients';
    $table_properties = $wpdb->prefix . 'rentcast_properties';

    $client = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT c.*, 
                    p.id AS property_id,
                    p.listing_id AS property_listing_id,
                    p.address AS property_title,
                    p.image_url AS property_image_url,
                    p.price AS property_price,
                    p.bedrooms,
                    p.bathrooms, 
                    p.sqft,
                    CONCAT(p.address, ', ', p.city, ', ', p.state, ' ', p.zip) AS property_location
             FROM $table_clients c
             LEFT JOIN $table_properties p ON c.property_id = p.id
             WHERE c.client_id = %d AND c.deleted_at IS NULL",
            $client_id
        ),
        ARRAY_A
    );

    if (!$client) {
        wp_send_json_error('Client not found');
    }

    // Add additional property data if needed
    if ($client['property_id']) {
        $client['property_gallery'] = $client['property_image_url'] ? [$client['property_image_url']] : [];
    }

    wp_send_json_success($client);
}
add_action('wp_ajax_fetch_realtor_client_ajax', 'rt_fetch_realtor_client_ajax');

// =====================
// Create Client with Property
// =====================
function rt_create_realtor_client_ajax() {
    rt_client_current_user_required();
    check_ajax_referer('rt_client_create_nonce', 'nonce'); // CORRECT

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    // Debug: log all received data
    error_log('CREATE CLIENT - POST DATA: ' . print_r($_POST, true));
    error_log('CREATE CLIENT - FILES DATA: ' . print_r($_FILES, true));

    $full_name = sanitize_text_field($_POST['realtor_client_full_name'] ?? '');
    $email = sanitize_email($_POST['realtor_client_email'] ?? '');
    $status = sanitize_text_field($_POST['realtor_client_status'] ?? '');
    
    // Get property_id from both possible field names
    $property_id = intval($_POST['property_id'] ?? $_POST['realtor_client_property_id'] ?? 0);
    
    error_log('Extracted Property ID: ' . $property_id);

    if (!$full_name || !$email || !$status) {
        wp_send_json_error('Name, Email, Status are required');
    }

    // Check existing client
    $existing_client = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE email = %s AND deleted_at IS NULL",
        $email
    ));

    if ($existing_client > 0) {
        wp_send_json_error('A client with this email already exists');
    }

    $phone = sanitize_text_field($_POST['realtor_client_phone'] ?? '');
    $note = sanitize_textarea_field($_POST['realtor_client_note'] ?? '');
    $preferred_location = sanitize_text_field($_POST['preferred_location'] ?? '');

    // Upload profile picture
    $profile_url = null;
    if (!empty($_FILES['realtor_client_profile_picture']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $upload = wp_handle_upload($_FILES['realtor_client_profile_picture'], ['test_form' => false]);
        if (isset($upload['error'])) {
            wp_send_json_error('Upload Error: ' . $upload['error']);
        }
        $profile_url = esc_url_raw($upload['url']);
    }

    $wpdb->query('START TRANSACTION');

    try {
        $password = wp_generate_password(12, false);
        $user_id = wp_create_user($email, $password, $email);
        
        if (is_wp_error($user_id)) {
            throw new Exception('WP User creation failed: ' . $user_id->get_error_message());
        }

        wp_update_user([
            'ID' => $user_id,
            'display_name' => $full_name,
            'first_name' => $full_name
        ]);

        $wp_user = new WP_User($user_id);
        $wp_user->set_role('client');

        $data = [
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'preferred_location' => $preferred_location,
            'note' => $note,
            'status' => $status,
            'profile_picture' => $profile_url,
            'user_id' => $user_id,
            'property_id' => $property_id,
            'created_at' => current_time('mysql'),
            'created_by' => get_current_user_id()
        ];

        error_log('Inserting client data: ' . print_r($data, true));

        $inserted = $wpdb->insert($table, $data);
        
        if (!$inserted) {
            throw new Exception('Could not create client. DB error: ' . $wpdb->last_error);
        }

        $client_id = $wpdb->insert_id;
        error_log('Client created successfully with ID: ' . $client_id . ', Property ID: ' . $property_id);

        $wpdb->query('COMMIT');

        wp_send_json_success([
            'client_id' => $client_id,
            'property_id' => $property_id,
            'message' => 'Client created successfully'
        ]);

    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        if (isset($user_id) && $user_id) {
            wp_delete_user($user_id);
        }
        error_log('Client creation failed: ' . $e->getMessage());
        wp_send_json_error($e->getMessage());
    }
}
add_action('wp_ajax_create_realtor_client_ajax', 'rt_create_realtor_client_ajax');

// =====================
// Search Properties by Address
// =====================
function rt_search_properties_ajax() {
    rt_client_current_user_required();

    $nonce = $_POST['nonce'] ?? '';
    if ( !wp_verify_nonce($nonce, 'rt_client_create_nonce') && !wp_verify_nonce($nonce, 'rt_client_edit_nonce') ) {
        wp_send_json_error('Invalid nonce');
    }

    global $wpdb;
    $keyword = sanitize_text_field($_POST['keyword'] ?? '');
    $table = $wpdb->prefix . 'rentcast_properties';

    if (!$keyword) wp_send_json_error('No keyword provided');

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT id, address FROM $table WHERE address LIKE %s LIMIT 10",
        '%' . $wpdb->esc_like($keyword) . '%'
    ));

    $html = '';
    if ($results) {
        foreach ($results as $row) {
            $html .= '<div class="property-suggestion" data-id="' . esc_attr($row->id) . '">' . esc_html($row->address) . '</div>';
        }
    } else {
        $html = '<div class="property-suggestion">No results found</div>';
    }

    wp_send_json_success(['html' => $html]);
}
add_action('wp_ajax_search_properties', 'rt_search_properties_ajax');


// ======================
// Update Client - COMPLETELY UPDATED VERSION
// ======================
function rt_update_realtor_client_ajax() {
    // Verify nonce
    check_ajax_referer('rt_client_edit_nonce', 'nonce');
    global $wpdb;

    // Get client ID
    $client_id = intval($_POST['realtor_client_id'] ?? 0);
    if (!$client_id) {
        error_log("❌ UPDATE CLIENT - Missing client ID");
        wp_send_json_error('Missing client ID');
    }

    $table_clients = $wpdb->prefix . 'clients';

    // Debug: Log all received data
    error_log("=== UPDATE CLIENT DEBUG START ===");
    error_log("📋 Client ID: " . $client_id);
    error_log("📝 POST Data: " . print_r($_POST, true));
    error_log("📁 FILES Data: " . print_r($_FILES, true));

    // Get property_id from the correct field name - UPDATED
    $property_id = null;
    
    // Try multiple possible field names for property_id
    if (isset($_POST['property_id']) && $_POST['property_id'] !== '') {
        $property_id = intval($_POST['property_id']);
        error_log("✅ Properties ID from 'property_id' field: " . $property_id);
    } elseif (isset($_POST['realtor_client_property_id']) && $_POST['realtor_client_property_id'] !== '') {
        $property_id = intval($_POST['realtor_client_property_id']);
        error_log("✅ Properties ID from 'realtor_client_property_id' field: " . $property_id);
    } else {
        // If no new property ID submitted, get existing one from database
        $existing_client = $wpdb->get_row($wpdb->prepare(
            "SELECT property_id FROM {$table_clients} WHERE client_id=%d",
            $client_id
        ));
        $property_id = $existing_client->property_id ?? null;
        error_log("ℹ️ Using existing Properties ID from DB: " . ($property_id ?? 'NULL'));
    }

    // Prepare data array - UPDATED FIELD NAMES
    $data = [
        'full_name'      => sanitize_text_field($_POST['realtor_client_full_name'] ?? ''),
        'email'          => sanitize_email($_POST['realtor_client_email'] ?? ''),
        'phone'          => sanitize_text_field($_POST['realtor_client_phone'] ?? ''),
        'note'           => sanitize_textarea_field($_POST['realtor_client_note'] ?? $_POST['realtor_client_notes'] ?? ''),
        'status'         => sanitize_text_field($_POST['realtor_client_status'] ?? ''),
        'lead_status'    => sanitize_text_field($_POST['realtor_lead_status'] ?? ''),
        'property_id'  => $property_id,
        'updated_at'     => current_time('mysql'),
        'updated_by'     => get_current_user_id()
    ];

    // Validate required fields
    if (empty($data['full_name']) || empty($data['email']) || empty($data['status'])) {
        error_log("❌ UPDATE CLIENT - Missing required fields");
        wp_send_json_error('Name, Email, and Status are required fields.');
    }

    // Check for duplicate email (excluding current client)
    $existing_email = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table_clients} WHERE email = %s AND client_id != %d AND deleted_at IS NULL",
        $data['email'], $client_id
    ));

    if ($existing_email > 0) {
        error_log("❌ UPDATE CLIENT - Duplicate email: " . $data['email']);
        wp_send_json_error('A client with this email already exists.');
    }

    $format = ['%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d'];

    // Handle profile picture upload - IMPROVED VERSION
    if (!empty($_FILES['realtor_client_profile_picture']['name']) && $_FILES['realtor_client_profile_picture']['error'] === UPLOAD_ERR_OK) {
        error_log("🖼️ Profile picture upload detected for client: $client_id");
        
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        // Validate file type
        $file_type = wp_check_filetype($_FILES['realtor_client_profile_picture']['name']);
        $allowed_types = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'];
        
        if (!in_array($file_type['type'], $allowed_types)) {
            error_log("❌ Invalid file type: " . $file_type['type']);
            wp_send_json_error('Please upload a valid image file (JPEG, PNG, GIF).');
        }

        // Validate file size (max 5MB)
        if ($_FILES['realtor_client_profile_picture']['size'] > 5 * 1024 * 1024) {
            error_log("❌ File too large: " . $_FILES['realtor_client_profile_picture']['size']);
            wp_send_json_error('Profile picture must be less than 5MB.');
        }

        $upload = wp_handle_upload($_FILES['realtor_client_profile_picture'], ['test_form' => false]);
        
        if (isset($upload['error'])) {
            error_log("❌ Profile picture upload error: " . $upload['error']);
            wp_send_json_error('Profile picture upload failed: ' . $upload['error']);
        }
        
        $data['profile_picture'] = esc_url_raw($upload['url']);
        $format[] = '%s';
        error_log("✅ Profile picture uploaded successfully: " . $data['profile_picture']);
    } else {
        if (isset($_FILES['realtor_client_profile_picture']['error']) && $_FILES['realtor_client_profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
            error_log("❌ File upload error code: " . $_FILES['realtor_client_profile_picture']['error']);
        }
        error_log("ℹ️ No new profile picture uploaded for client: $client_id");
    }

    error_log("📦 Final data to update: " . print_r($data, true));
    error_log("🔧 Format array: " . print_r($format, true));

    // Update the client
    $wpdb->query('START TRANSACTION');

    try {
        $updated = $wpdb->update(
            $table_clients,
            $data,
            ['client_id' => $client_id],
            $format,
            ['%d']
        );

        if ($updated === false) {
            throw new Exception('Database update failed: ' . $wpdb->last_error);
        }

        // Also update WordPress user if exists
        $client_data = $wpdb->get_row($wpdb->prepare(
            "SELECT user_id, email FROM {$table_clients} WHERE client_id = %d",
            $client_id
        ));

        if ($client_data && $client_data->user_id) {
            $user_update_data = [
                'ID' => $client_data->user_id,
                'display_name' => $data['full_name'],
                'user_email' => $data['email']
            ];

            $user_updated = wp_update_user($user_update_data);
            
            if (is_wp_error($user_updated)) {
                error_log("⚠️ WP User update warning: " . $user_updated->get_error_message());
            } else {
                error_log("WordPress user updated successfully");
            }
        }

        $wpdb->query('COMMIT');

        // Fetch updated property title for frontend
        $property_title = '';
        if ($property_id) {
            $property = $wpdb->get_row(
                $wpdb->prepare("SELECT address FROM {$wpdb->prefix}rentcast_properties WHERE id=%d", $property_id),
                ARRAY_A
            );
            if ($property) {
                $property_title = $property['address'];
                error_log("🏠 Property title fetched: " . $property_title);
            }
        }

        error_log("Client updated successfully: $client_id");
        error_log("Properties ID after update: " . ($property_id ?? 'NULL'));
        error_log("=== UPDATE CLIENT DEBUG END ===");

        // Return success response
        $response_data = [
            'message' => 'Client updated successfully!',
            'property_title' => $property_title,
            'property_id' => $property_id
        ];

        if (isset($data['profile_picture'])) {
            $response_data['profile_picture'] = $data['profile_picture'];
        }

        wp_send_json_success($response_data);

    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        error_log("❌ UPDATE CLIENT - Transaction failed: " . $e->getMessage());
        error_log("=== UPDATE CLIENT DEBUG END ===");
        wp_send_json_error('Update failed: ' . $e->getMessage());
    }
}
add_action('wp_ajax_update_realtor_client_ajax', 'rt_update_realtor_client_ajax');

// =====================
// Delete Client (Soft Delete)
// =====================
function rt_delete_realtor_client_ajax() {
    rt_client_current_user_required();
    check_ajax_referer('rt_client_delete_nonce', 'nonce'); // CORRECT

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
        wp_update_user([
            'ID' => $client->user_id,
            'user_status' => 1
        ]);
    }

    if ($deleted !== false) wp_send_json_success('Client deleted successfully');
    wp_send_json_error('Could not delete client');
}
add_action('wp_ajax_delete_realtor_client_ajax', 'rt_delete_realtor_client_ajax');

// =====================
// Export Clients (JSON for frontend CSV/XLSX)
// =====================
function rt_export_clients_ajax() {
    rt_client_current_user_required();
    check_ajax_referer('rt_client_export_nonce', 'nonce'); // CORRECT

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    // Allowed columns
    $allowed_cols = ['full_name', 'email', 'phone', 'note', 'status', 'profile_picture', 'created_at'];
    $columns = json_decode(stripslashes($_POST['columns'] ?? '[]'), true);
    $columns = array_intersect($allowed_cols, $columns);
    if (empty($columns)) $columns = $allowed_cols;

    // Scope
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
    $sql = !empty($params) ? $wpdb->prepare("SELECT {$select} FROM {$table} {$where}", $params) : "SELECT {$select} FROM {$table} {$where}";
    $results = $wpdb->get_results($sql, ARRAY_A);

    wp_send_json_success(['clients' => $results]);
}
add_action('wp_ajax_export_clients_ajax', 'rt_export_clients_ajax');

// =====================
// Import Clients (CSV/XLSX) with WP User creation
// =====================
function rt_import_clients_ajax() {

    // =====================
    // DEBUG LOGS
    // =====================
    error_log('=== IMPORT CLIENTS DEBUG START ===');
    error_log('Import started - File: ' . ($_FILES['clients_file']['name'] ?? 'No file'));
    error_log('File size: ' . ($_FILES['clients_file']['size'] ?? '0'));
    error_log('File error: ' . ($_FILES['clients_file']['error'] ?? 'No error code'));
    error_log('POST data: ' . print_r($_POST, true));

    // Check if user is logged in
    if (!is_user_logged_in()) {
        error_log('User not logged in');
        wp_send_json_error('You must be logged in');
    }
    
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'rt_client_import_nonce')) {
        error_log('Nonce verification failed');
        wp_send_json_error('Security verification failed');
    }

    // Check if file was uploaded
    if (!isset($_FILES['clients_file']) || empty($_FILES['clients_file']['tmp_name'])) {
        error_log('No file uploaded or file is empty');
        wp_send_json_error('No file uploaded or file is empty');
    }

    $file = $_FILES['clients_file']['tmp_name'];
    $filename = $_FILES['clients_file']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    error_log('File extension: ' . $ext);
    error_log('File temp path: ' . $file);

    $clients = [];
    $inserted = 0;
    $updated = 0;
    $errors = [];

    // Parse file with detailed logging
    if ($ext === 'csv') {
        error_log('Processing as CSV file');
        $clients = rt_parse_csv_file($file);
        
        if (empty($clients)) {
            wp_send_json_error('No valid data found in CSV file. Please check the file format.');
        }
    } 
    // Parse XLSX file - Show user-friendly message
    elseif ($ext === 'xlsx') {
        error_log('XLSX file detected - showing conversion message');
        wp_send_json_error('Please convert your Excel file to CSV format. In Excel: File → Save As → CSV (Comma delimited). Then upload the CSV file.');
    } else {
        error_log('Unsupported file type: ' . $ext);
        wp_send_json_error('Unsupported file type. Please use CSV format.');
    }

    error_log('Total clients parsed: ' . count($clients));

    if (empty($clients)) {
        error_log('No valid client data found in file');
        wp_send_json_error('No valid client data found in file. Please check the file format.');
    }

    // Insert into database
    global $wpdb;
    $table = $wpdb->prefix . 'clients';
    $current_user_id = get_current_user_id();
    $duplicate_handling = sanitize_text_field($_POST['duplicate_handling'] ?? 'skip');

    foreach ($clients as $index => $client) {
        try {
            // Clean and validate data with multiple possible field names
            $full_name = sanitize_text_field($client['full_name'] ?? $client['client_name'] ?? $client['name'] ?? '');
            $email = sanitize_email($client['email'] ?? '');
            $status = sanitize_text_field($client['status'] ?? 'active');
            $phone = sanitize_text_field($client['phone'] ?? $client['phone_number'] ?? $client['mobile'] ?? '');
            $note = sanitize_textarea_field($client['note'] ?? $client['notes'] ?? $client['description'] ?? '');

            // Validate required fields
            if (empty($full_name) || empty($email)) {
                $errors[] = "Row " . ($index + 1) . ": Missing required fields (name or email)";
                continue;
            }

            if (!is_email($email)) {
                $errors[] = "Row " . ($index + 1) . ": Invalid email format: " . $email;
                continue;
            }

            // Check if client already exists
            $existing_id = $wpdb->get_var($wpdb->prepare(
                "SELECT client_id FROM {$table} WHERE email = %s AND deleted_at IS NULL",
                $email
            ));

            if ($existing_id) {
                if ($duplicate_handling === 'update') {
                    $result = $wpdb->update(
                        $table, 
                        [
                            'full_name' => $full_name,
                            'status' => $status,
                            'phone' => $phone,
                            'note' => $note,
                            'updated_at' => current_time('mysql'),
                            'updated_by' => $current_user_id
                        ],
                        ['client_id' => $existing_id],
                        ['%s', '%s', '%s', '%s', '%s', '%d'],
                        ['%d']
                    );
                    
                    if ($result !== false) {
                        $updated++;
                        error_log('Updated client: ' . $email);
                    } else {
                        $errors[] = "Row " . ($index + 1) . ": Failed to update client " . $email;
                    }
                } else {
                    error_log('Skipped existing client: ' . $email);
                }
            } 
            
            // Create new client if doesn't exist or duplicate handling is 'create'
            if (!$existing_id || $duplicate_handling === 'create') {
                $result = $wpdb->insert(
                    $table,
                    [
                        'full_name' => $full_name,
                        'email' => $email,
                        'status' => $status,
                        'phone' => $phone,
                        'note' => $note,
                        'created_at' => current_time('mysql'),
                        'created_by' => $current_user_id
                    ],
                    ['%s', '%s', '%s', '%s', '%s', '%s', '%d']
                );

                if ($result !== false) {
                    $inserted++;
                    error_log('Inserted new client: ' . $email);
                    
                    // Create WP user for new clients
                    $new_client_id = $wpdb->insert_id;
                    
                    // Check if user already exists
                    $existing_user_id = email_exists($email);
                    if (!$existing_user_id) {
                        $password = wp_generate_password(12, false);
                        $user_id = wp_create_user($email, $password, $email);
                        
                        if (!is_wp_error($user_id)) {
                            wp_update_user([
                                'ID' => $user_id,
                                'display_name' => $full_name,
                                'first_name' => $full_name
                            ]);
                            
                            // Update client record with user_id
                            $wpdb->update(
                                $table,
                                ['user_id' => $user_id],
                                ['client_id' => $new_client_id],
                                ['%d'],
                                ['%d']
                            );
                            error_log('Created WP user for: ' . $email);
                        } else {
                            error_log('Failed to create WP user for: ' . $email . ' - ' . $user_id->get_error_message());
                        }
                    } else {
                        error_log('WP user already exists for: ' . $email);
                    }
                } else {
                    $errors[] = "Row " . ($index + 1) . ": Failed to insert client " . $email;
                }
            }

        } catch (Exception $e) {
            $error_msg = "Row " . ($index + 1) . ": " . $e->getMessage();
            $errors[] = $error_msg;
            error_log($error_msg);
        }
    }

    $message = "Import completed successfully! {$inserted} new clients added, {$updated} clients updated";
    if (!empty($errors)) {
        $message .= ". " . count($errors) . " errors occurred during import.";
    }

    error_log('Import completed - Inserted: ' . $inserted . ', Updated: ' . $updated);
    error_log('=== IMPORT CLIENTS DEBUG END ===');

    wp_send_json_success([
        'message' => $message,
        'inserted' => $inserted,
        'updated' => $updated,
        'total_processed' => count($clients),
        'error_count' => count($errors),
        'errors' => array_slice($errors, 0, 10) // Return first 10 errors only
    ]);
}

// =====================
// CSV Parser Function
// =====================
function rt_parse_csv_file($file_path) {
    $clients = [];
    
    error_log('Parsing CSV file: ' . $file_path);
    
    if (!file_exists($file_path)) {
        error_log('CSV file not found: ' . $file_path);
        return $clients;
    }

    if (($handle = fopen($file_path, "r")) !== false) {
        // Read headers
        $headers = fgetcsv($handle);
        if ($headers === false) {
            error_log('Failed to read CSV headers');
            fclose($handle);
            return $clients;
        }
        
        // Clean headers - remove BOM and trim
        $headers = array_map(function($header) {
            // Remove UTF-8 BOM if present
            $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);
            return trim($header);
        }, $headers);
        
        // Convert headers to lowercase for consistency
        $headers = array_map('strtolower', $headers);
        
        error_log('CSV Headers found: ' . implode(', ', $headers));
        
        $row_count = 0;
        $valid_rows = 0;
        
        while (($row = fgetcsv($handle)) !== false) {
            $row_count++;
            
            // Skip empty rows
            if (empty(array_filter($row))) {
                error_log('Skipping empty row ' . $row_count);
                continue;
            }
            
            // Handle column count mismatch
            if (count($row) !== count($headers)) {
                error_log('Row ' . $row_count . ': Column count mismatch. Expected: ' . count($headers) . ', Got: ' . count($row));
                
                // Pad or truncate row to match header count
                if (count($row) < count($headers)) {
                    $row = array_pad($row, count($headers), '');
                } else {
                    $row = array_slice($row, 0, count($headers));
                }
            }
            
            $client_data = array_combine($headers, $row);
            
            // Clean all values
            foreach ($client_data as $key => $value) {
                $client_data[$key] = trim($value);
            }
            
            // Check if this row has at least one required field
            if (!empty($client_data['full_name']) || !empty($client_data['email'])) {
                $clients[] = $client_data;
                $valid_rows++;
                error_log('Valid row ' . $row_count . ': ' . json_encode($client_data));
            } else {
                error_log('Skipping row ' . $row_count . ': No name or email found');
            }
        }
        
        fclose($handle);
        error_log('CSV processing completed. Total rows: ' . $row_count . ', Valid rows: ' . $valid_rows);
        
    } else {
        error_log('Failed to open CSV file: ' . $file_path);
    }
    
    return $clients;
}

// =====================
// Register AJAX handler
// =====================
add_action('wp_ajax_import_clients_ajax', 'rt_import_clients_ajax');