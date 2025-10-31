<?php
if (!defined('ABSPATH')) exit;

/**
 * Update Client Password via AJAX
 *
 * AJAX action: cl_update_password
 */
add_action('wp_ajax_cl_update_password', 'cl_update_password_callback');

function cl_update_password_callback() {
    // Security: verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'cl_password_nonce' ) ) {
        wp_send_json_error( 'Security verification failed.' );
    }

    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        wp_send_json_error( 'User not logged in.' );
    }

    // Get inputs
    $old_pass     = isset($_POST['old_password']) ? $_POST['old_password'] : '';
    $new_pass     = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_pass = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if ( empty($old_pass) || empty($new_pass) || empty($confirm_pass) ) {
        wp_send_json_error( 'All fields are required.' );
    }

    if ( $new_pass !== $confirm_pass ) {
        wp_send_json_error( 'New password and confirm password do not match.' );
    }

    // Get user and verify old password
    $user = wp_get_current_user();
    if ( ! $user || ! $user->ID ) {
        wp_send_json_error( 'User not found.' );
    }

    if ( ! wp_check_password( $old_pass, $user->user_pass, $user_id ) ) {
        wp_send_json_error( 'Old password is incorrect.' );
    }

    // Update password
    wp_set_password( $new_pass, $user_id );

    wp_send_json_success( 'Password updated successfully.' );
}
