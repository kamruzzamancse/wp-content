<?php
if (!defined('ABSPATH')) exit;

/**
 * Ensure user is logged in
 */
function rt_client_current_user_required() {
    if (!is_user_logged_in()) wp_send_json_error('Unauthorized', 401);
}

// =====================
// Fetch Clients (Address Book only)
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

    // Check if client with same email already exists
    $existing_client = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE email = %s AND deleted_at IS NULL",
        $email
    ));

    if ($existing_client > 0) {
        wp_send_json_error('A client with this email already exists');
    }

    if (!$full_name || !$email || !$status) {
        wp_send_json_error('Name, Email, Status are required');
    }

    $phone = sanitize_text_field($_POST['realtor_client_phone'] ?? '');
    $note = sanitize_textarea_field($_POST['realtor_client_note'] ?? '');
    $preferred_location = sanitize_text_field($_POST['preferred_location'] ?? '');
    $lead_status = sanitize_text_field($_POST['realtor_lead_status'] ?? 'cold');

    $profile_url = null;
    if (!empty($_FILES['realtor_client_profile_picture']['name'])) {
        require_once(ABSPATH.'wp-admin/includes/file.php');
        require_once(ABSPATH.'wp-admin/includes/media.php');
        require_once(ABSPATH.'wp-admin/includes/image.php');

        $upload = wp_handle_upload($_FILES['realtor_client_profile_picture'], ['test_form'=>false]);
        if (isset($upload['error'])) {
            wp_send_json_error('Upload Error: '.$upload['error']);
        }
        $profile_url = esc_url_raw($upload['url']);
    }

    $data = [
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone,
        'note' => $note,
        'status' => $status,
        'preferred_location' => $preferred_location,
        'lead_status' => $lead_status,
        'profile_picture' => $profile_url,
        'created_at' => current_time('mysql'),
        'created_by' => get_current_user_id()
    ];

    $inserted = $wpdb->insert($table, $data);
    
    if ($inserted) {
        wp_send_json_success([
            'client_id' => $wpdb->insert_id,
            'message' => 'Client created successfully'
        ]);
    }
    
    wp_send_json_error('Could not create client. Database error: ' . $wpdb->last_error);
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

    $data = [
        'full_name'=>sanitize_text_field($_POST['realtor_client_full_name'] ?? ''),
        'email'=>sanitize_email($_POST['realtor_client_email'] ?? ''),
        'phone'=>sanitize_text_field($_POST['realtor_client_phone'] ?? ''),
        'note'=>sanitize_textarea_field($_POST['realtor_client_note'] ?? ''),
        'status'=>sanitize_text_field($_POST['realtor_client_status'] ?? ''),
        'preferred_location'=>sanitize_text_field($_POST['preferred_location'] ?? ''),
        'lead_status'=>sanitize_text_field($_POST['realtor_lead_status'] ?? 'cold'),
        'updated_at'=>current_time('mysql'),
        'updated_by'=>get_current_user_id()
    ];

    if (!empty($_FILES['realtor_client_profile_picture']['name'])) {
        require_once(ABSPATH.'wp-admin/includes/file.php');
        require_once(ABSPATH.'wp-admin/includes/media.php');
        require_once(ABSPATH.'wp-admin/includes/image.php');

        $upload = wp_handle_upload($_FILES['realtor_client_profile_picture'], ['test_form'=>false]);
        if (isset($upload['error'])) wp_send_json_error('Upload Error: '.$upload['error']);
        $data['profile_picture'] = esc_url_raw($upload['url']);
    }

    $updated = $wpdb->update($table,$data,['client_id'=>$client_id]);
    if ($updated !== false) wp_send_json_success();
    wp_send_json_error('Could not update client or nothing changed');
}
add_action('wp_ajax_update_realtor_client_ajax','rt_update_realtor_client_ajax');

// =====================
// Delete Client
// =====================
function rt_delete_realtor_client_ajax() {
    rt_client_current_user_required();
    check_ajax_referer('cl_client_delete_nonce','nonce');

    $client_id = intval($_POST['client_id'] ?? 0);
    if (!$client_id) wp_send_json_error('Missing client ID');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    $deleted = $wpdb->update($table, [
        'deleted_at'=>current_time('mysql'),
        'deleted_by'=>get_current_user_id()
    ], ['client_id'=>$client_id]);

    if ($deleted !== false) wp_send_json_success();
    wp_send_json_error('Could not delete client');
}
add_action('wp_ajax_delete_realtor_client_ajax','rt_delete_realtor_client_ajax');
