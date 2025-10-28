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

    if (!$full_name || !$email || !$status) {
        wp_send_json_error('Name, Email, Status are required');
    }

    // Check existing client/email in custom table
    $existing_client = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE email = %s AND deleted_at IS NULL",
        $email
    ));
    if ($existing_client > 0) {
        wp_send_json_error('A client with this email already exists in your client list');
    }

    // Check if WordPress user already exists
    if (email_exists($email)) {
        wp_send_json_error('A WordPress user with this email already exists');
    }

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

    // Start transaction to ensure both operations complete or none
    $wpdb->query('START TRANSACTION');

    try {
        // Create WordPress user first
        $password = wp_generate_password(12, false);
        $user_id = wp_create_user($email, $password, $email);
        
        if (is_wp_error($user_id)) {
            throw new Exception('WP User creation failed: '.$user_id->get_error_message());
        }
        
        // Update user details
        wp_update_user([
            'ID' => $user_id,
            'display_name' => $full_name,
            'first_name' => $full_name
        ]);
        
        $wp_user = new WP_User($user_id);
        $wp_user->set_role('client'); // Custom client role

        // Insert client into custom table
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
        
        if (!$inserted) {
            throw new Exception('Could not create client. Database error: ' . $wpdb->last_error);
        }

        // Commit transaction
        $wpdb->query('COMMIT');
        
        wp_send_json_success([
            'client_id' => $wpdb->insert_id,
            'message' => 'Client created successfully'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $wpdb->query('ROLLBACK');
        
        // If WordPress user was created but client insert failed, delete the user
        if (isset($user_id) && $user_id) {
            wp_delete_user($user_id);
        }
        
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

    // Update WP user
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

    // Update client table
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
    if ($updated !== false) {
        wp_send_json_success('Client updated successfully');
    }
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

    // Soft delete in clients table
    $deleted = $wpdb->update($table, [
        'deleted_at'=>current_time('mysql'),
        'deleted_by'=>get_current_user_id()
    ], ['client_id'=>$client_id]);

    // Update WP user status
    if ($client->user_id) {
        wp_update_user([
            'ID' => $client->user_id,
            'user_status' => 1
        ]);
    }

    if ($deleted !== false) {
        wp_send_json_success('Client deleted successfully');
    }
    wp_send_json_error('Could not delete client');
}
add_action('wp_ajax_delete_realtor_client_ajax','rt_delete_realtor_client_ajax');