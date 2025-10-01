<?php
if (!defined('ABSPATH')) exit;

// ==============================
// Save Client Profile Data
// ==============================
function save_client_profile_data() {
    // Security check
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'profile_ajax_nonce')) {
        wp_send_json_error('Security check failed');
    }

    $user_id = get_current_user_id();
    if ($user_id === 0) wp_send_json_error('User not logged in');

    $fields = ['full_name', 'phone', 'budget', 'preferred_location'];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
        }
    }

    wp_send_json_success('Profile updated successfully');
}
add_action('wp_ajax_save_client_profile_data', 'save_client_profile_data');

