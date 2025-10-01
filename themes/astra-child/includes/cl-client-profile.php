<?php
if (!defined('ABSPATH')) exit;

/**
 * ======================
 * Load Client Profile Data
 * ======================
 */
add_action('wp_ajax_load_cl_profile_data', 'load_cl_profile_data');
function load_cl_profile_data() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cl_profile_nonce')) {
        wp_send_json_error('Security verification failed');
    }

    $user_id = get_current_user_id();
    if (!$user_id) wp_send_json_error('Not logged in');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    // Ensure row exists
    $client = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d", $user_id), ARRAY_A);
    if (!$client) {
        $wpdb->insert($table, [
            'user_id'   => $user_id,
            'full_name' => wp_get_current_user()->display_name,
            'created_at'=> current_time('mysql'),
        ]);
        $client = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d", $user_id), ARRAY_A);
    }

    $user = wp_get_current_user();
    $profile_picture = get_user_meta($user_id, 'profile_picture', true);
    if (!$profile_picture) {
        $upload_dir = wp_upload_dir();
        $profile_picture = $upload_dir['baseurl'] . '/2025/08/client-photo.jpg';
    }

    wp_send_json_success([
        'full_name'          => $user->display_name,
        'email'              => $user->user_email,
        'phone'              => $client['phone'] ?? '',
        'budget'             => $client['budget'] ?? 0,
        'preferred_location' => $client['preferred_location'] ?? '',
        'profile_picture'    => $profile_picture,
    ]);
}

/**
 * ======================
 * Save Client Profile Data (including email)
 * ======================
 */
add_action('wp_ajax_save_cl_profile_data', 'save_cl_profile_data');
function save_cl_profile_data() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cl_profile_nonce')) {
        wp_send_json_error('Security verification failed');
    }

    $user_id = get_current_user_id();
    if (!$user_id) wp_send_json_error('Not logged in');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    // Ensure row exists
    $client = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d", $user_id));
    if (!$client) {
        $wpdb->insert($table, [
            'user_id'   => $user_id,
            'full_name' => wp_get_current_user()->display_name,
            'email'     => wp_get_current_user()->user_email,
            'created_at'=> current_time('mysql'),
        ]);
    }

    $data = [
        'full_name'          => wp_get_current_user()->display_name, // always sync name
        'email'              => sanitize_email($_POST['email'] ?? wp_get_current_user()->user_email),
        'phone'              => sanitize_text_field($_POST['phone'] ?? ''),
        'budget'             => floatval($_POST['budget'] ?? 0),
        'preferred_location' => sanitize_text_field($_POST['preferred_location'] ?? ''),
    ];

    $updated = $wpdb->update(
        $table,
        $data,
        ['user_id' => $user_id],
        ['%s','%s','%s','%f','%s'],
        ['%d']
    );

    if ($updated === false) {
        wp_send_json_error('Database update failed: ' . $wpdb->last_error);
    }

    wp_send_json_success('Profile updated successfully');
}

/**
 * ======================
 * Upload Client Profile Picture
 * ======================
 */
add_action('wp_ajax_upload_cl_profile_picture', 'upload_cl_profile_picture');
function upload_cl_profile_picture() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cl_profile_nonce')) {
        wp_send_json_error('Security verification failed');
    }

    $user_id = get_current_user_id();
    if (!$user_id) wp_send_json_error('Not logged in');

    if (!empty($_FILES['profile_picture']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $upload_overrides = ['test_form' => false];
        $uploaded = wp_handle_upload($_FILES['profile_picture'], $upload_overrides);

        if (isset($uploaded['error'])) wp_send_json_error('Upload error: ' . $uploaded['error']);
        if (isset($uploaded['url'])) {
            update_user_meta($user_id, 'profile_picture', esc_url($uploaded['url']));
            wp_send_json_success(['url' => esc_url($uploaded['url'])]);
        }
    }

    wp_send_json_error('No file uploaded');
}

/**
 * ======================
 * Save Realtor Client (Create Client)
 * ======================
 */
add_action('wp_ajax_save_rt_client_data', 'save_rt_client_data');
function save_rt_client_data() {
    // Nonce check
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cl_profile_nonce')) {
        wp_send_json_error('Security verification failed');
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('Not logged in');
    }

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    $data = [
        'user_id'            => $user_id,
        'full_name'          => sanitize_text_field($_POST['realtor_client_full_name'] ?? ''),
        'email'              => sanitize_email($_POST['realtor_client_email'] ?? ''),
        'phone'              => sanitize_text_field($_POST['realtor_client_phone'] ?? ''),
        'preferred_location' => sanitize_text_field($_POST['preferred_location'] ?? ''),
        'note'               => sanitize_textarea_field($_POST['realtor_client_note'] ?? ''),
        'status'             => sanitize_text_field($_POST['realtor_client_status'] ?? ''),
        'created_at'         => current_time('mysql'),
    ];

    $inserted = $wpdb->insert(
        $table,
        $data,
        ['%d','%s','%s','%s','%s','%s','%s','%s'] // user_id (%d), rest (%s)
    );

    if ($inserted === false) {
        wp_send_json_error('Insert failed: ' . $wpdb->last_error);
    }

    wp_send_json_success('Client created successfully');
}
