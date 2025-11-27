<?php
if (!defined('ABSPATH')) exit;

// ======================
// Ensure user is logged in
// ======================
function rt_active_current_user_required() {
    if (!is_user_logged_in()) wp_send_json_error('Unauthorized', 401);
}

// ======================
// Fetch Active Clients AJAX
// ======================
function rt_active_fetch_dashboard_clients_ajax() {
    rt_active_current_user_required();
    check_ajax_referer('clients_pagination_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    // Get request data
    $search = sanitize_text_field($_POST['search'] ?? '');
    $page   = max(1, intval($_POST['page'] ?? 1));
    $rows   = max(1, intval($_POST['rows'] ?? 10));
    $offset = ($page - 1) * $rows;

    // Base query
    $where = "WHERE deleted_at IS NULL AND status='active'";
    $params = [];

    if ($search !== '') {
        $like = "%{$wpdb->esc_like($search)}%";
        $where .= " AND (full_name LIKE %s OR email LIKE %s OR phone LIKE %s)";
        $params = [$like, $like, $like];
    }

    // Count total records
    $count_query = !empty($params)
        ? $wpdb->prepare("SELECT COUNT(*) FROM {$table} {$where}", $params)
        : "SELECT COUNT(*) FROM {$table} {$where}";
    $total = intval($wpdb->get_var($count_query));

    // Fetch data
    $sql = "SELECT * FROM {$table} {$where} ORDER BY created_at DESC LIMIT %d, %d";
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
add_action('wp_ajax_fetch_dashboard_active_clients_ajax', 'rt_active_fetch_dashboard_clients_ajax');

// ======================
// Create Active Client AJAX (WP User optional)
// ======================
function rt_create_dashboard_client_ajax() {
    rt_active_current_user_required();

    // Nonce check
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'create_dashboard_client_nonce')) {
        wp_send_json_error('Invalid nonce', 403);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    $full_name = sanitize_text_field($_POST['rt_db_client_full_name'] ?? '');
    $email     = sanitize_email($_POST['rt_db_client_email'] ?? '');
    $phone     = sanitize_text_field($_POST['rt_db_client_phone'] ?? '');
    $address  = sanitize_text_field($_POST['rt_db_client_address'] ?? '');
    $note      = sanitize_textarea_field($_POST['rt_db_client_note'] ?? '');
    $profile_picture = '';
    $user_id = null;

    if (empty($full_name) || empty($email)) {
        wp_send_json_error('Full Name and Email are required');
    }

    // Optional: create WP user if email doesn't exist
    if (!email_exists($email)) {
        $password = wp_generate_password(12, false);
        $user_id = wp_create_user($email, $password, $email);
        if (is_wp_error($user_id)) wp_send_json_error('WP User creation failed: '.$user_id->get_error_message());

        wp_update_user([
            'ID' => $user_id,
            'display_name' => $full_name,
            'first_name' => $full_name
        ]);

        $wp_user = new WP_User($user_id);
        $wp_user->set_role('client');
    }

    // Handle profile picture
    if (!empty($_FILES['rt_db_client_profile_picture']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $upload = media_handle_upload('rt_db_client_profile_picture', 0);
        if (is_wp_error($upload)) {
            wp_send_json_error('Failed to upload profile picture');
        } else {
            $profile_picture = wp_get_attachment_url($upload);
        }
    }

    $result = $wpdb->insert($table, [
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'note' => $note,
        'status' => 'active',
        'profile_picture' => $profile_picture,
        'user_id' => $user_id,
        'created_at' => current_time('mysql'),
        'created_by' => get_current_user_id()
    ], ['%s','%s','%s','%s','%s','%s','%s','%d','%s']);

    if ($result) {
        wp_send_json_success('Client created successfully');
    } else {
        wp_send_json_error('Failed to create client');
    }
}
add_action('wp_ajax_create_dashboard_client_ajax', 'rt_create_dashboard_client_ajax');



