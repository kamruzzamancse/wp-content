<?php
if (!defined('ABSPATH')) exit;

// ---------------------
// Update User Password via AJAX
// ---------------------
add_action('wp_ajax_rt_update_password', 'rt_update_password_callback');
function rt_update_password_callback() {
    check_ajax_referer('rt_password_nonce', 'nonce');

    $user_id = get_current_user_id();
    if (!$user_id) wp_send_json_error('User not logged in.');

    $old_pass = $_POST['old_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if (!$old_pass || !$new_pass || !$confirm_pass) {
        wp_send_json_error('All fields are required.');
    }

    if ($new_pass !== $confirm_pass) {
        wp_send_json_error('New password and confirm password do not match.');
    }

    $user = wp_get_current_user();
    if (!wp_check_password($old_pass, $user->user_pass, $user_id)) {
        wp_send_json_error('Old password is incorrect.');
    }

    wp_set_password($new_pass, $user_id);
    wp_send_json_success('Password updated successfully.');
}
