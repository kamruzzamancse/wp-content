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
    check_ajax_referer('cl_client_edit_nonce','nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    $search = sanitize_text_field($_POST['search'] ?? '');
    $page = max(1,intval($_POST['page'] ?? 1));
    $rows = max(1,intval($_POST['rows'] ?? 10));
    $offset = ($page-1)*$rows;

    $where = "WHERE deleted_at IS NULL";
    $params = [];
    if ($search !== '') {
        $like = "%{$wpdb->esc_like($search)}%";
        $where .= " AND (full_name LIKE %s OR email LIKE %s OR phone LIKE %s)";
        $params = [$like,$like,$like];
    }

    $count_query = !empty($params) ? $wpdb->prepare("SELECT COUNT(*) FROM {$table} {$where}", $params) : "SELECT COUNT(*) FROM {$table} {$where}";
    $total = intval($wpdb->get_var($count_query));

    $sql = "SELECT * FROM {$table} {$where} ORDER BY created_at DESC LIMIT %d,%d";
    if (!empty($params)) {
        $params[] = $offset;
        $params[] = $rows;
        $prepared = $wpdb->prepare($sql,$params);
    } else {
        $prepared = $wpdb->prepare($sql,$offset,$rows);
    }

    $clients = $wpdb->get_results($prepared);
    $total_pages = ceil($total/$rows);

    wp_send_json_success([
        'clients'=>$clients,
        'total'=>$total,
        'total_pages'=>$total_pages,
        'page'=>$page
    ]);
}
add_action('wp_ajax_fetch_clients_ajax','rt_fetch_clients_ajax');

// =====================
// Fetch Single Client
// =====================
function rt_fetch_realtor_client_ajax() {
    rt_client_current_user_required();
    check_ajax_referer('cl_client_edit_nonce','nonce');

    $client_id = intval($_POST['client_id'] ?? 0);
    if (!$client_id) wp_send_json_error('Missing client ID');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';
    $client = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE client_id=%d AND deleted_at IS NULL",$client_id), ARRAY_A);

    if (!$client) wp_send_json_error('Client not found');
    wp_send_json_success($client);
}
add_action('wp_ajax_fetch_realtor_client_ajax','rt_fetch_realtor_client_ajax');

// =====================
// Create Client
// =====================
function rt_create_realtor_client_ajax() {
    rt_client_current_user_required();
    check_ajax_referer('cl_client_create_nonce','nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    $full_name = sanitize_text_field($_POST['realtor_client_full_name'] ?? '');
    $email = sanitize_email($_POST['realtor_client_email'] ?? '');
    $status = sanitize_text_field($_POST['realtor_client_status'] ?? '');

    if (!$full_name || !$email || !$status) wp_send_json_error('Name, Email, Status are required');

    $existing_client = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE email = %s AND deleted_at IS NULL",
        $email
    ));
    if ($existing_client > 0) wp_send_json_error('A client with this email already exists');

    if (email_exists($email)) wp_send_json_error('A WordPress user with this email already exists');

    $phone = sanitize_text_field($_POST['realtor_client_phone'] ?? '');
    $note = sanitize_textarea_field($_POST['realtor_client_note'] ?? '');

    // Upload profile picture
    $profile_url = null;
    if (!empty($_FILES['realtor_client_profile_picture']['name'])) {
        require_once(ABSPATH.'wp-admin/includes/file.php');
        require_once(ABSPATH.'wp-admin/includes/media.php');
        require_once(ABSPATH.'wp-admin/includes/image.php');
        $upload = wp_handle_upload($_FILES['realtor_client_profile_picture'], ['test_form'=>false]);
        if (isset($upload['error'])) wp_send_json_error('Upload Error: '.$upload['error']);
        $profile_url = esc_url_raw($upload['url']);
    }

    $wpdb->query('START TRANSACTION');

    try {
        $password = wp_generate_password(12, false);
        $user_id = wp_create_user($email, $password, $email);
        if (is_wp_error($user_id)) throw new Exception('WP User creation failed: '.$user_id->get_error_message());

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
            'note' => $note,
            'status' => $status,
            'profile_picture' => $profile_url,
            'user_id' => $user_id,
            'created_at' => current_time('mysql'),
            'created_by' => get_current_user_id()
        ];

        $inserted = $wpdb->insert($table, $data);
        if (!$inserted) throw new Exception('Could not create client. DB error: ' . $wpdb->last_error);

        $wpdb->query('COMMIT');
        wp_send_json_success(['client_id'=>$wpdb->insert_id,'message'=>'Client created successfully']);
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        if (isset($user_id) && $user_id) wp_delete_user($user_id);
        wp_send_json_error($e->getMessage());
    }
}
add_action('wp_ajax_create_realtor_client_ajax','rt_create_realtor_client_ajax');

// =====================
// Update Client
// =====================
function rt_update_realtor_client_ajax() {
    rt_client_current_user_required();
    check_ajax_referer('cl_client_edit_nonce','nonce');

    $client_id = intval($_POST['realtor_client_id'] ?? 0);
    if (!$client_id) wp_send_json_error('Missing client ID');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';
    $client = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE client_id=%d",$client_id));
    if (!$client) wp_send_json_error('Client not found');

    $full_name = sanitize_text_field($_POST['realtor_client_full_name'] ?? '');
    $email = sanitize_email($_POST['realtor_client_email'] ?? '');
    $status = sanitize_text_field($_POST['realtor_client_status'] ?? '');
    $phone = sanitize_text_field($_POST['realtor_client_phone'] ?? '');
    $note = sanitize_textarea_field($_POST['realtor_client_note'] ?? '');

    $profile_url = $client->profile_picture;
    if (!empty($_FILES['realtor_client_profile_picture']['name'])) {
        require_once(ABSPATH.'wp-admin/includes/file.php');
        require_once(ABSPATH.'wp-admin/includes/media.php');
        require_once(ABSPATH.'wp-admin/includes/image.php');
        $upload = wp_handle_upload($_FILES['realtor_client_profile_picture'], ['test_form'=>false]);
        if (isset($upload['error'])) wp_send_json_error('Upload Error: '.$upload['error']);
        $profile_url = esc_url_raw($upload['url']);
    }

    if (!empty($client->user_id)) {
        $user_data = [
            'ID' => $client->user_id,
            'user_email' => $email,
            'user_login' => $email,
            'display_name' => $full_name
        ];
        if (!empty($_POST['realtor_client_password'])) {
            $user_data['user_pass'] = sanitize_text_field($_POST['realtor_client_password']);
        }
        wp_update_user($user_data);
    }

    $data = [
        'full_name'=>$full_name,
        'email'=>$email,
        'phone'=>$phone,
        'note'=>$note,
        'status'=>$status,
        'profile_picture'=>$profile_url,
        'updated_at'=>current_time('mysql'),
        'updated_by'=>get_current_user_id()
    ];

    $updated = $wpdb->update($table,$data,['client_id'=>$client_id]);
    if ($updated !== false) wp_send_json_success('Client updated successfully');
    wp_send_json_error('Could not update client or nothing changed');
}
add_action('wp_ajax_update_realtor_client_ajax','rt_update_realtor_client_ajax');

// =====================
// Delete Client (Soft Delete)
// =====================
function rt_delete_realtor_client_ajax() {
    rt_client_current_user_required();
    check_ajax_referer('cl_client_delete_nonce','nonce');

    $client_id = intval($_POST['client_id'] ?? 0);
    if (!$client_id) wp_send_json_error('Missing client ID');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';
    $client = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE client_id=%d",$client_id));
    if (!$client) wp_send_json_error('Client not found');

    $deleted = $wpdb->update($table, [
        'deleted_at'=>current_time('mysql'),
        'deleted_by'=>get_current_user_id()
    ], ['client_id'=>$client_id]);

    if ($client->user_id) {
        wp_update_user([
            'ID' => $client->user_id,
            'user_status' => 1
        ]);
    }

    if ($deleted !== false) wp_send_json_success('Client deleted successfully');
    wp_send_json_error('Could not delete client');
}
add_action('wp_ajax_delete_realtor_client_ajax','rt_delete_realtor_client_ajax');

// =====================
// Export Clients (JSON for frontend CSV/XLSX)
// =====================
function rt_export_clients_ajax() {
    rt_client_current_user_required();
    check_ajax_referer('cl_client_export_nonce','nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    // Allowed columns
    $allowed_cols = ['full_name','email','phone','note','status','profile_picture','created_at'];
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
// Import Clients (CSV/XLSX/JSON)
// =====================
function rt_import_clients_ajax() {
    rt_client_current_user_required();
    check_ajax_referer('cl_client_import_nonce','nonce');

    if (!isset($_FILES['clients_file'])) wp_send_json_error('No file uploaded');

    $file = $_FILES['clients_file']['tmp_name'];
    $filename = $_FILES['clients_file']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $clients = [];

    if ($ext === 'json') {
        $content = file_get_contents($file);
        $clients = json_decode($content,true);
        if (!is_array($clients)) wp_send_json_error('Invalid JSON file');
    } elseif ($ext === 'csv') {
        if (($handle = fopen($file, "r")) !== false) {
            $headers = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== false) {
                $clients[] = array_combine($headers,$row);
            }
            fclose($handle);
        }
    } elseif ($ext === 'xlsx') {
        if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
            require_once get_stylesheet_directory() . '/includes/libs/phpoffice/phpspreadsheet/vendor/autoload.php';
        }
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        $headers = array_map('trim', $rows[0]);
        for ($i = 1; $i < count($rows); $i++) {
            $clients[] = array_combine($headers, $rows[$i]);
        }
    } else {
        wp_send_json_error('Unsupported file type');
    }

    $duplicate_handling = sanitize_text_field($_POST['duplicate_handling'] ?? 'skip');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';
    $inserted = 0;
    $updated = 0;

    foreach ($clients as $client) {
        $full_name = sanitize_text_field($client['full_name'] ?? '');
        $email = sanitize_email($client['email'] ?? '');
        if (!$full_name || !$email) continue;

        $existing_id = $wpdb->get_var($wpdb->prepare("SELECT client_id FROM {$table} WHERE email=%s",$email));

        if ($existing_id) {
            if ($duplicate_handling === 'update') {
                $wpdb->update($table, $client, ['client_id'=>$existing_id]);
                $updated++;
            } elseif ($duplicate_handling === 'create') {
                $wpdb->insert($table, $client);
                $inserted++;
            } // skip does nothing
        } else {
            $wpdb->insert($table, $client);
            $inserted++;
        }
    }

    wp_send_json_success(['message'=>"Imported: $inserted new, $updated updated"]);
}
add_action('wp_ajax_import_clients_ajax','rt_import_clients_ajax');
