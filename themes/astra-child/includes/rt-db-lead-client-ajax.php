<?php
if (!defined('ABSPATH')) exit;

/**
 * Ensure user is logged in for dashboard actions
 */
function rt_dashboard_current_user_required() {
    if (!is_user_logged_in()) wp_send_json_error('Unauthorized', 401);
}

/**
 * Fetch Leads (List)
 */
function rt_fetch_dashboard_leads_ajax() {
    rt_dashboard_current_user_required();
    check_ajax_referer('cl_client_edit_nonce','nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    $search = sanitize_text_field($_POST['search'] ?? '');
    $page   = max(1, intval($_POST['page'] ?? 1));
    $rows   = max(1, intval($_POST['rows'] ?? 10));
    $offset = ($page-1)*$rows;

    $where = "WHERE deleted_at IS NULL AND status='lead'";
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

    $sql = "SELECT * FROM {$table} {$where} ORDER BY created_at DESC LIMIT %d,%d";
    if (!empty($params)) {
        $params[] = $offset;
        $params[] = $rows;
        $prepared = $wpdb->prepare($sql, $params);
    } else {
        $prepared = $wpdb->prepare($sql, $offset, $rows);
    }

    $leads = $wpdb->get_results($prepared, ARRAY_A);
    $total_pages = ceil($total/$rows);

    wp_send_json_success([
        'leads' => $leads,
        'total' => $total,
        'total_pages' => $total_pages,
        'page' => $page
    ]);
}
add_action('wp_ajax_fetch_dashboard_leads_ajax','rt_fetch_dashboard_leads_ajax');

/**
 * Fetch Single Lead
 */
function rt_fetch_dashboard_lead_ajax() {
    rt_dashboard_current_user_required();
    check_ajax_referer('cl_client_edit_nonce','nonce');

    $client_id = intval($_POST['client_id'] ?? 0);
    if (!$client_id) wp_send_json_error('Missing client ID');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';
    
    $lead = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT client_id, full_name, email, phone, address, note, status, lead_status, profile_picture 
             FROM {$table} 
             WHERE client_id=%d AND deleted_at IS NULL",
            $client_id
        ), 
        ARRAY_A
    );

    if (!$lead) wp_send_json_error('Lead not found');

    // Ensure profile picture URL is always valid
    if (!empty($lead['profile_picture'])) {
        // If it's already a full URL, use it
        if (strpos($lead['profile_picture'], 'http') === 0) {
            $lead['profile_picture'] = esc_url($lead['profile_picture']);
        } else {
            // If stored as relative path, convert to full URL
            $lead['profile_picture'] = site_url($lead['profile_picture']);
        }
    } else {
        // Use default avatar if none exists
        $lead['profile_picture'] = get_stylesheet_directory_uri() . '/assets/images/default-avatar.jpg';
    }

    wp_send_json_success($lead);
}
add_action('wp_ajax_fetch_dashboard_lead_ajax','rt_fetch_dashboard_lead_ajax');


/**
 * Create Lead (with WP user)
 */
function rt_create_dashboard_lead_ajax() {
    rt_dashboard_current_user_required();
    check_ajax_referer('cl_client_create_nonce','nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    $full_name = sanitize_text_field($_POST['rt_db_lead_full_name'] ?? '');
    $email     = sanitize_email($_POST['rt_db_lead_email'] ?? '');
    $phone     = sanitize_text_field($_POST['rt_db_lead_phone'] ?? '');
    $note      = sanitize_textarea_field($_POST['rt_db_lead_note'] ?? '');
    $address = sanitize_text_field($_POST['rt_db_lead_address'] ?? '');

    if (!$full_name || !$email) wp_send_json_error('Name & Email required');

    if (email_exists($email)) wp_send_json_error('A WordPress user with this email already exists');

    $profile_url = null;
    if (!empty($_FILES['rt_db_lead_profile_picture']['name'])) {
        require_once(ABSPATH.'wp-admin/includes/file.php');
        require_once(ABSPATH.'wp-admin/includes/media.php');
        require_once(ABSPATH.'wp-admin/includes/image.php');

        $upload = wp_handle_upload($_FILES['rt_db_lead_profile_picture'], ['test_form'=>false]);
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
            'address' => $address,
            'status' => 'lead',
            'profile_picture' => $profile_url,
            'user_id' => $user_id,
            'created_at' => current_time('mysql'),
            'created_by' => get_current_user_id()
        ];

        $inserted = $wpdb->insert($table, $data);
        if (!$inserted) throw new Exception('Could not create lead. DB error: '.$wpdb->last_error);

        $wpdb->query('COMMIT');

        wp_send_json_success([
            'client_id' => $wpdb->insert_id,
            'message' => 'Lead created successfully'
        ]);

    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        if (isset($user_id) && $user_id) wp_delete_user($user_id);
        wp_send_json_error($e->getMessage());
    }
}
add_action('wp_ajax_create_dashboard_lead_ajax','rt_create_dashboard_lead_ajax');

/**
 * Update Lead
 */
function rt_update_dashboard_lead_ajax() {
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
        'address'=>sanitize_text_field($_POST['address'] ?? ''),
        'note'=>sanitize_textarea_field($_POST['note'] ?? ''),
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

    $updated = $wpdb->update($table, $data, ['client_id'=>$client_id]);
    if ($updated !== false) wp_send_json_success();
    wp_send_json_error('Could not update lead or nothing changed');
}
add_action('wp_ajax_update_dashboard_lead_ajax','rt_update_dashboard_lead_ajax');

/**
 * Delete Lead
 */
function rt_delete_dashboard_lead_ajax() {
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
    wp_send_json_error('Could not delete lead');
}
add_action('wp_ajax_delete_dashboard_lead_ajax','rt_delete_dashboard_lead_ajax');

/**
 * Convert Lead to Client
 */
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
