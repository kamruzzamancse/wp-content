<?php
if (!defined('ABSPATH')) exit;

// ======================
// Load Realtor Profile Data
// ======================
add_action('wp_ajax_load_rt_profile_data', 'load_rt_profile_data');
function load_rt_profile_data() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rt_profile_nonce')) {
        wp_send_json_error('Security verification failed');
    }

    $user_id = get_current_user_id();
    if (!$user_id) wp_send_json_error('Not logged in');

    global $wpdb;
    $table = $wpdb->prefix . 'realtors';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
        wp_send_json_error('Realtors table does not exist.');
    }

    $realtor = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d", $user_id), ARRAY_A);

    if (!$realtor) {
        // Insert default row if not exists
        $inserted = $wpdb->insert($table, [
            'user_id'   => $user_id,
            'full_name' => wp_get_current_user()->display_name,
            'created_at'=> current_time('mysql'),
        ]);

        if ($inserted === false) wp_send_json_error('Failed to create profile');

        $realtor = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d", $user_id), ARRAY_A);
    }

    $user = wp_get_current_user();
    $profile_picture = get_user_meta($user_id, 'profile_picture', true);
    if (!$profile_picture) {
        $upload_dir = wp_upload_dir();
        $profile_picture = $upload_dir['baseurl'] . '/2025/08/client-photo.jpg';
    }

    wp_send_json_success([
        'full_name'      => $realtor['full_name'] ?? $user->display_name,
        'email'          => $user->user_email,
        'phone'          => $realtor['phone'] ?? '',
        'agency_name'    => $realtor['agency_name'] ?? '',
        'license_number' => $realtor['license_number'] ?? '',
        'rating_avg'     => $realtor['rating_avg'] ?? 0,
        'profile_picture'=> $profile_picture,
    ]);
}

// ======================
// Save Realtor Profile Data
// ======================
add_action('wp_ajax_save_rt_profile_data', 'save_rt_profile_data');
function save_rt_profile_data() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rt_profile_nonce')) {
        wp_send_json_error('Security verification failed');
    }

    $user_id = get_current_user_id();
    if (!$user_id) wp_send_json_error('Not logged in');

    global $wpdb;
    $table = $wpdb->prefix . 'realtors';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
        wp_send_json_error('Realtors table does not exist.');
    }

    $data = [
        'full_name'      => sanitize_text_field($_POST['full_name'] ?? ''),
        'phone'          => sanitize_text_field($_POST['phone'] ?? ''),
        'agency_name'    => sanitize_text_field($_POST['agency_name'] ?? ''),
        'license_number' => sanitize_text_field($_POST['license_number'] ?? ''),
        'rating_avg'     => floatval($_POST['rating_avg'] ?? 0),
    ];

    $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE user_id = %d", $user_id));

    if ($exists) {
        $updated = $wpdb->update(
            $table,
            $data,
            ['user_id' => $user_id],
            ['%s','%s','%s','%s','%f'],
            ['%d']
        );
        if ($updated === false) wp_send_json_error('Failed to update profile');
        wp_send_json_success('Profile updated successfully');
    } else {
        $data['user_id'] = $user_id;
        $data['created_at'] = current_time('mysql');

        $inserted = $wpdb->insert(
            $table,
            $data,
            ['%d','%s','%s','%s','%s','%f']
        );
        if ($inserted === false) wp_send_json_error('Failed to create profile');
        wp_send_json_success('Profile created successfully');
    }
}

// ======================
// Upload Profile Picture
// ======================
add_action('wp_ajax_upload_rt_profile_picture', 'upload_rt_profile_picture');
function upload_rt_profile_picture() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rt_profile_nonce')) {
        wp_send_json_error('Security verification failed');
    }

    $user_id = get_current_user_id();
    if (!$user_id) wp_send_json_error('Not logged in');

    if (!empty($_FILES['profile_picture']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $uploaded = wp_handle_upload($_FILES['profile_picture'], ['test_form' => false]);

        if (isset($uploaded['error'])) wp_send_json_error('Upload error: ' . $uploaded['error']);
        if (isset($uploaded['url'])) {
            update_user_meta($user_id, 'profile_picture', esc_url($uploaded['url']));
            wp_send_json_success(['url' => esc_url($uploaded['url'])]);
        } else {
            wp_send_json_error('Upload failed - no URL returned');
        }
    }

    wp_send_json_error('No file uploaded');
}
