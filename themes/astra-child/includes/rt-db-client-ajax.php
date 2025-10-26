<?php
if (!defined('ABSPATH')) exit;

/**
 * Ensure user is logged in
 */
function rt_dashboard_current_user_required() {
    if (!is_user_logged_in()) wp_send_json_error('Unauthorized', 401);
}

// =====================
// Fetch Clients (Active / Leads)
// =====================
function rt_fetch_dashboard_clients_ajax() {
    rt_dashboard_current_user_required();
    check_ajax_referer('cl_client_edit_nonce','nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    $search = sanitize_text_field($_POST['search'] ?? '');
    $page   = max(1, intval($_POST['page'] ?? 1));
    $rows   = max(1, intval($_POST['rows'] ?? 10));
    $offset = ($page-1)*$rows;
    $tableType = sanitize_text_field($_POST['table_type'] ?? 'active');

    $where = "WHERE deleted_at IS NULL";
    if ($tableType === 'active') {
        $where .= " AND status='active'";
    } elseif ($tableType === 'leads') {
        $where .= " AND status='lead'";
    }

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
add_action('wp_ajax_fetch_dashboard_clients_ajax','rt_fetch_dashboard_clients_ajax');

// =====================
// Fetch Single Client
// =====================
function rt_fetch_dashboard_client_ajax() {
    rt_dashboard_current_user_required();
    check_ajax_referer('cl_client_edit_nonce','nonce');

    $client_id = intval($_POST['client_id'] ?? 0);
    if (!$client_id) wp_send_json_error('Missing client ID');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';
    $client = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE client_id=%d AND deleted_at IS NULL",$client_id), ARRAY_A);

    if (!$client) wp_send_json_error('Client not found');
    wp_send_json_success($client);
}
add_action('wp_ajax_fetch_dashboard_client_ajax','rt_fetch_dashboard_client_ajax');

// =====================
// Create Client
// =====================
function rt_create_dashboard_client_ajax() {
    rt_dashboard_current_user_required();
    check_ajax_referer('cl_client_create_nonce','nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    $full_name = sanitize_text_field($_POST['full_name'] ?? '');
    $email     = sanitize_email($_POST['email'] ?? '');
    $status    = sanitize_text_field($_POST['status'] ?? '');
    if (!$full_name || !$email || !$status) wp_send_json_error('Name, Email, Status required');

    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $note  = sanitize_textarea_field($_POST['note'] ?? '');
    $lead_status = sanitize_text_field($_POST['lead_status'] ?? 'cold');

    // Profile picture
    $profile_url = null;
    if (!empty($_FILES['profile_picture']['name'])) {
        require_once(ABSPATH.'wp-admin/includes/file.php');
        require_once(ABSPATH.'wp-admin/includes/media.php');
        require_once(ABSPATH.'wp-admin/includes/image.php');

        $upload = wp_handle_upload($_FILES['profile_picture'], ['test_form'=>false]);
        if (isset($upload['error'])) wp_send_json_error('Upload Error: '.$upload['error']);
        $profile_url = esc_url_raw($upload['url']);
    }

    $data = [
        'full_name'=>$full_name,
        'email'=>$email,
        'phone'=>$phone,
        'note'=>$note,
        'status'=>$status,
        'lead_status'=>$lead_status,
        'profile_picture'=>$profile_url,
        'created_at'=>current_time('mysql'),
        'created_by'=>get_current_user_id()
    ];

    $inserted = $wpdb->insert($table,$data);
    if ($inserted) wp_send_json_success(['client_id'=>$wpdb->insert_id]);
    wp_send_json_error('Could not create client');
}
add_action('wp_ajax_create_dashboard_client_ajax','rt_create_dashboard_client_ajax');

// =====================
// Update Client
// =====================
function rt_update_dashboard_client_ajax() {
    rt_dashboard_current_user_required();
    check_ajax_referer('cl_client_edit_nonce','nonce');

    $client_id = intval($_POST['client_id'] ?? 0);
    if (!$client_id) wp_send_json_error('Missing client ID');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    $data = [
        'full_name'=>sanitize_text_field($_POST['full_name'] ?? ''),
        'email'=>sanitize_email($_POST['email'] ?? ''),
        'phone'=>sanitize_text_field($_POST['phone'] ?? ''),
        'note'=>sanitize_textarea_field($_POST['note'] ?? ''),
        'status'=>sanitize_text_field($_POST['status'] ?? ''),
        'lead_status'=>sanitize_text_field($_POST['lead_status'] ?? 'cold'),
        'updated_at'=>current_time('mysql'),
        'updated_by'=>get_current_user_id()
    ];

    if (!empty($_FILES['profile_picture']['name'])) {
        require_once(ABSPATH.'wp-admin/includes/file.php');
        require_once(ABSPATH.'wp-admin/includes/media.php');
        require_once(ABSPATH.'wp-admin/includes/image.php');

        $upload = wp_handle_upload($_FILES['profile_picture'], ['test_form'=>false]);
        if (isset($upload['error'])) wp_send_json_error('Upload Error: '.$upload['error']);
        $data['profile_picture'] = esc_url_raw($upload['url']);
    }

    $updated = $wpdb->update($table,$data,['client_id'=>$client_id]);
    if ($updated !== false) wp_send_json_success();
    wp_send_json_error('Could not update client or nothing changed');
}
add_action('wp_ajax_update_dashboard_client_ajax','rt_update_dashboard_client_ajax');

// =====================
// Delete Client
// =====================
function rt_delete_dashboard_client_ajax() {
    rt_dashboard_current_user_required();
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
add_action('wp_ajax_delete_dashboard_client_ajax','rt_delete_dashboard_client_ajax');

// =====================
// Convert Lead to Active Client
// =====================
function rt_convert_lead_to_client_ajax() {
    rt_dashboard_current_user_required();
    check_ajax_referer('cl_client_convert_nonce','nonce');

    $client_id = intval($_POST['client_id'] ?? 0);
    if (!$client_id) wp_send_json_error('Missing client ID');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    $updated = $wpdb->update($table, [
        'status' => 'active',
        'lead_status' => null,
        'updated_at' => current_time('mysql'),
        'updated_by' => get_current_user_id()
    ], ['client_id'=>$client_id]);

    if ($updated !== false) wp_send_json_success();
    wp_send_json_error('Could not convert lead');
}
add_action('wp_ajax_convert_lead_to_client_ajax','rt_convert_lead_to_client_ajax');
