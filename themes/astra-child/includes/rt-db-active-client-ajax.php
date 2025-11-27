<?php
if (!defined('ABSPATH')) exit;

/**
 * Ensure logged-in user
 */
function rt_active_current_user_required() {
    if (!is_user_logged_in()) wp_send_json_error('Unauthorized', 401);
}

/**
 * =============================
 * FETCH ACTIVE CLIENTS (AJAX)
 * =============================
 */
function rt_active_fetch_dashboard_clients_ajax() {
    rt_active_current_user_required();
    check_ajax_referer('clients_pagination_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    $search = sanitize_text_field($_POST['search'] ?? '');
    $page   = max(1, intval($_POST['page'] ?? 1));
    $rows   = max(1, intval($_POST['rows'] ?? 10));
    $offset = ($page - 1) * $rows;

    $where = "WHERE deleted_at IS NULL AND status='active'";
    $params = [];

    if ($search !== '') {
        $like = "%{$wpdb->esc_like($search)}%";
        $where .= " AND (full_name LIKE %s OR email LIKE %s OR phone LIKE %s)";
        $params = [$like, $like, $like];
    }

    // Count
    $count_query = (!empty($params))
        ? $wpdb->prepare("SELECT COUNT(*) FROM {$table} {$where}", $params)
        : "SELECT COUNT(*) FROM {$table} {$where}";
    $total = intval($wpdb->get_var($count_query));

    // Fetch
    $sql = "SELECT * FROM {$table} {$where} ORDER BY created_at DESC LIMIT %d, %d";
    if (!empty($params)) {
        $params[] = $offset;
        $params[] = $rows;
        $prepared = $wpdb->prepare($sql, $params);
    } else {
        $prepared = $wpdb->prepare($sql, $offset, $rows);
    }

    $clients = $wpdb->get_results($prepared, ARRAY_A);

    wp_send_json_success([
        'clients' => $clients,
        'total' => $total,
        'total_pages' => ceil($total / $rows),
        'page' => $page
    ]);
}
add_action('wp_ajax_fetch_dashboard_active_clients_ajax', 'rt_active_fetch_dashboard_clients_ajax');

/**
 * =============================
 * CREATE ACTIVE CLIENT (AJAX)
 * =============================
 */
function rt_create_dashboard_client_ajax() {

    rt_active_current_user_required();

    /***
     * FIXED:
     * — NONCE must come from FormData (multipart)
     * — JSON double response STOPPED
     */
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'create_dashboard_client_nonce')) {
        wp_send_json_error('Invalid nonce', 403);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    // Sanitize
    $full_name = sanitize_text_field($_POST['rt_db_client_full_name'] ?? '');
    $email     = sanitize_email($_POST['rt_db_client_email'] ?? '');
    $phone     = sanitize_text_field($_POST['rt_db_client_phone'] ?? '');
    $address   = sanitize_text_field($_POST['rt_db_client_address'] ?? '');
    $note      = sanitize_textarea_field($_POST['rt_db_client_note'] ?? '');

    if (empty($full_name) || empty($email)) {
        wp_send_json_error('Full Name and Email are required');
    }

    // Optional WP user create
    $user_id = null;
    if (!email_exists($email)) {
        $password = wp_generate_password(12, false);
        $user_id = wp_create_user($email, $password, $email);
        if (is_wp_error($user_id)) {
            wp_send_json_error('WP User creation failed: ' . $user_id->get_error_message());
        }

        wp_update_user([
            'ID' => $user_id,
            'display_name' => $full_name,
            'first_name' => $full_name
        ]);
        (new WP_User($user_id))->set_role('client');
    }

    // Upload picture (Fixed: multipart upload support)
    $profile_picture = '';
    if (!empty($_FILES['rt_db_client_profile_picture']['name'])) {

        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $attachment_id = media_handle_upload('rt_db_client_profile_picture', 0);

        if (is_wp_error($attachment_id)) {
            wp_send_json_error('Failed to upload profile picture');
        }

        $profile_picture = wp_get_attachment_url($attachment_id);
    }

    // DB insert (Fixed: PERFECT formatting)
    $inserted = $wpdb->insert(
        $table,
        [
            'full_name'       => $full_name,
            'email'           => $email,
            'phone'           => $phone,
            'address'         => $address,
            'note'            => $note,
            'status'          => 'active',
            'profile_picture' => $profile_picture,
            'user_id'         => $user_id,
            'created_at'      => current_time('mysql'),
            'created_by'      => get_current_user_id()
        ],
        ['%s','%s','%s','%s','%s','%s','%s','%d','%s','%d']
    );

    if ($inserted) {
        wp_send_json_success('Client created successfully');
    } else {
        wp_send_json_error('Failed to create client');
    }
}
add_action('wp_ajax_create_dashboard_client_ajax', 'rt_create_dashboard_client_ajax');


add_action('wp_ajax_fetch_dashboard_client_ajax', 'rt_fetch_single_dashboard_client_ajax');

function rt_fetch_single_dashboard_client_ajax() {
    rt_active_current_user_required();

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'edit_dashboard_client_nonce')) {
        wp_send_json_error('Invalid nonce', 403);
    }

    $client_id = intval($_POST['client_id'] ?? 0);
    if (!$client_id) wp_send_json_error('Client ID missing');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';
    $client = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE client_id = %d AND deleted_at IS NULL", $client_id), ARRAY_A);

    if ($client) {
        wp_send_json_success($client);
    } else {
        wp_send_json_error('Client not found');
    }
}

/**
 * =============================
 * UPDATE ACTIVE CLIENT (AJAX)
 * =============================
 */
function rt_update_dashboard_client_ajax() {
    rt_active_current_user_required();

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'edit_dashboard_client_nonce')) {
        wp_send_json_error('Invalid nonce', 403);
    }

    $client_id = intval($_POST['client_id'] ?? 0);
    if (!$client_id) wp_send_json_error('Client ID missing');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    // Sanitize inputs
    $full_name = sanitize_text_field($_POST['full_name'] ?? '');
    $email     = sanitize_email($_POST['email'] ?? '');
    $phone     = sanitize_text_field($_POST['phone'] ?? '');
    $address   = sanitize_text_field($_POST['address'] ?? '');
    $note      = sanitize_textarea_field($_POST['note'] ?? '');

    // Optional profile upload
    $profile_picture = '';
    if (!empty($_FILES['profile_picture']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $attachment_id = media_handle_upload('profile_picture', 0);
        if (is_wp_error($attachment_id)) {
            wp_send_json_error('Failed to upload profile picture');
        }
        $profile_picture = wp_get_attachment_url($attachment_id);
    }

    $update_data = [
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'note' => $note
    ];

    if ($profile_picture) {
        $update_data['profile_picture'] = $profile_picture;
    }

    $updated = $wpdb->update(
        $table,
        $update_data,
        ['client_id' => $client_id],
        ['%s','%s','%s','%s','%s', isset($profile_picture) ? '%s' : ''],
        ['%d']
    );

    if ($updated !== false) {
        $client = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE client_id=%d", $client_id), ARRAY_A);
        wp_send_json_success($client);
    } else {
        wp_send_json_error('Failed to update client');
    }
}
add_action('wp_ajax_update_dashboard_client_ajax', 'rt_update_dashboard_client_ajax');

/**
 * =============================
 * DELETE ACTIVE CLIENT (AJAX)
 * =============================
 */
function rt_delete_dashboard_client_ajax() {

    rt_active_current_user_required();

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'delete_dashboard_client_nonce')) {
        wp_send_json_error('Invalid nonce', 403);
    }

    $client_id = intval($_POST['client_id'] ?? 0);
    if (!$client_id) wp_send_json_error('Client ID missing');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    // Soft delete: set deleted_at timestamp
    $deleted = $wpdb->update(
        $table,
        [
            'deleted_at' => current_time('mysql'),
            'deleted_by' => get_current_user_id()
        ],
        ['client_id' => $client_id],
        ['%s','%d'],
        ['%d']
    );

    if ($deleted !== false) {
        wp_send_json_success('Client deleted successfully');
    } else {
        wp_send_json_error('Failed to delete client');
    }
}
add_action('wp_ajax_delete_dashboard_client_ajax', 'rt_delete_dashboard_client_ajax');


