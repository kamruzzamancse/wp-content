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
        $where .= " AND (
            first_name LIKE %s OR
            second_name LIKE %s OR
            first_email LIKE %s OR
            second_email LIKE %s OR
            first_phone LIKE %s OR
            second_phone LIKE %s
        )";
        $params = [$like, $like, $like, $like, $like, $like];
    }

    $count_query = (!empty($params))
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
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'create_dashboard_client_nonce')) {
        wp_send_json_error('Invalid nonce', 403);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    $first_name      = sanitize_text_field($_POST['first_name'] ?? '');
    $second_name     = sanitize_text_field($_POST['second_name'] ?? '');
    $primary_email   = sanitize_email($_POST['first_email'] ?? '');
    $secondary_email = sanitize_email($_POST['second_email'] ?? '');
    $primary_phone   = sanitize_text_field($_POST['first_phone'] ?? '');
    $secondary_phone = sanitize_text_field($_POST['second_phone'] ?? '');
    $address         = sanitize_text_field($_POST['address'] ?? '');
    $note            = sanitize_textarea_field($_POST['note'] ?? '');
    $status          = 'active'; // static value

    if (empty($first_name) || empty($primary_email)) {
        wp_send_json_error('First Name and Primary Email are required');
    }

    // Check duplicate primary email
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE first_email=%s AND deleted_at IS NULL",
        $primary_email
    ));
    if ($existing > 0) wp_send_json_error('A client with this primary email already exists.');

    $user_id = null;
    if (!email_exists($primary_email)) {
        $password = wp_generate_password(12, false);
        $user_id = wp_create_user($primary_email, $password, $primary_email);
        if (is_wp_error($user_id)) wp_send_json_error('WP User creation failed: ' . $user_id->get_error_message());

        wp_update_user([
            'ID' => $user_id,
            'display_name' => $first_name,
            'first_name' => $first_name,
            'last_name' => $second_name
        ]);
        (new WP_User($user_id))->set_role('client');
    }

    $profile_picture = '';
    if (!empty($_FILES['rt_db_client_profile_picture']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $attachment_id = media_handle_upload('rt_db_client_profile_picture', 0);
        if (is_wp_error($attachment_id)) wp_send_json_error('Failed to upload profile picture');
        $profile_picture = wp_get_attachment_url($attachment_id);
    }

    $inserted = $wpdb->insert(
        $table,
        [
            'first_name'       => $first_name,
            'second_name'      => $second_name,
            'first_email'      => $primary_email,
            'second_email'     => $secondary_email,
            'first_phone'      => $primary_phone,
            'second_phone'     => $secondary_phone,
            'address'          => $address,
            'note'             => $note,
            'status'           => $status,
            'profile_picture'  => $profile_picture,
            'user_id'          => $user_id,
            'created_at'       => current_time('mysql'),
            'created_by'       => get_current_user_id()
        ],
        ['%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%d','%d']
    );

    if ($inserted) wp_send_json_success('Client created successfully');
    else wp_send_json_error('Failed to create client');
}
add_action('wp_ajax_create_dashboard_client_ajax', 'rt_create_dashboard_client_ajax');

/**
 * =============================
 * FETCH SINGLE CLIENT (AJAX)
 * =============================
 */
function rt_fetch_single_dashboard_client_ajax() {
    rt_active_current_user_required();
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'edit_dashboard_client_nonce')) {
        wp_send_json_error('Invalid nonce', 403);
    }

    $client_id = intval($_POST['client_id'] ?? 0);
    if (!$client_id) wp_send_json_error('Client ID missing');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';
    $client = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$table} WHERE client_id=%d AND deleted_at IS NULL",
        $client_id
    ), ARRAY_A);

    if ($client) wp_send_json_success($client);
    else wp_send_json_error('Client not found');
}
add_action('wp_ajax_fetch_dashboard_client_ajax', 'rt_fetch_single_dashboard_client_ajax');

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

    $first_name      = sanitize_text_field($_POST['first_name'] ?? '');
    $second_name     = sanitize_text_field($_POST['second_name'] ?? '');
    $primary_email   = sanitize_email($_POST['first_email'] ?? '');
    $secondary_email = sanitize_email($_POST['second_email'] ?? '');
    $primary_phone   = sanitize_text_field($_POST['first_phone'] ?? '');
    $secondary_phone = sanitize_text_field($_POST['second_phone'] ?? '');
    $address         = sanitize_text_field($_POST['address'] ?? '');
    $note            = sanitize_textarea_field($_POST['note'] ?? '');
    $status          = 'active'; // static value

    // Duplicate primary email check
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE first_email=%s AND client_id != %d AND deleted_at IS NULL",
        $primary_email,
        $client_id
    ));
    if ($existing > 0) wp_send_json_error('A client with this primary email already exists.');

    $profile_picture = '';
    if (!empty($_FILES['rt_db_client_profile_picture']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $attachment_id = media_handle_upload('rt_db_client_profile_picture', 0);
        if (is_wp_error($attachment_id)) wp_send_json_error('Failed to upload profile picture');
        $profile_picture = wp_get_attachment_url($attachment_id);
    }

    $update_data = [
        'first_name'      => $first_name,
        'second_name'     => $second_name,
        'first_email'     => $primary_email,
        'second_email'    => $secondary_email,
        'first_phone'     => $primary_phone,
        'second_phone'    => $secondary_phone,
        'address'         => $address,
        'note'            => $note,
        'status'          => $status
    ];
    if ($profile_picture) $update_data['profile_picture'] = $profile_picture;

    $updated = $wpdb->update(
        $table,
        $update_data,
        ['client_id' => $client_id],
        ['%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'],
        ['%d']
    );

    if ($updated !== false) {
        // Update WP user email/display_name
        $client_data = $wpdb->get_row($wpdb->prepare(
            "SELECT user_id FROM {$table} WHERE client_id=%d",
            $client_id
        ));
        if ($client_data && $client_data->user_id) {
            wp_update_user([
                'ID' => $client_data->user_id,
                'user_email' => $primary_email,
                'display_name' => $first_name
            ]);
        }

        $client = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE client_id=%d", $client_id), ARRAY_A);
        wp_send_json_success($client);
    } else wp_send_json_error('Failed to update client');
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

    $client = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE client_id=%d", $client_id));
    if (!$client) wp_send_json_error('Client not found');

    $deleted = $wpdb->update(
        $table,
        ['deleted_at' => current_time('mysql'), 'deleted_by' => get_current_user_id()],
        ['client_id' => $client_id],
        ['%s','%d'],
        ['%d']
    );

    // Deactivate WP user
    if ($client->user_id) wp_update_user(['ID' => $client->user_id, 'user_status' => 1]);

    if ($deleted !== false) wp_send_json_success('Client deleted successfully');
    else wp_send_json_error('Failed to delete client');
}
add_action('wp_ajax_delete_dashboard_client_ajax', 'rt_delete_dashboard_client_ajax');
