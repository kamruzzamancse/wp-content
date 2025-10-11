<?php

add_action('wp_ajax_edit_realtor_client_ajax', 'handle_edit_realtor_client_ajax');
function handle_edit_realtor_client_ajax(){
    global $wpdb;

    if(!is_user_logged_in()){
        wp_send_json_error('User not logged in');
    }

    if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cl_client_edit_nonce')){
        wp_send_json_error('Security verification failed');
    }

    $client_id = intval($_POST['realtor_client_id']);
    $full_name = sanitize_text_field($_POST['realtor_client_full_name']);
    $email     = sanitize_email($_POST['realtor_client_email']);
    $phone     = sanitize_text_field($_POST['realtor_client_phone']);
    $note      = sanitize_textarea_field($_POST['realtor_client_note']);
    $status    = sanitize_text_field($_POST['realtor_client_status']);
    $profile_picture_url = '';

    // Handle profile picture upload if any
    if(!empty($_FILES['realtor_client_profile_picture']['name'])){
        require_once(ABSPATH.'wp-admin/includes/file.php');
        $uploadedfile = $_FILES['realtor_client_profile_picture'];
        $upload_overrides = ['test_form' => false];
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        if($movefile && !isset($movefile['error'])){
            $profile_picture_url = $movefile['url'];
            update_user_meta($client_id, 'profile_picture', $profile_picture_url);
        }
    }

    // Update clients table
    $updated = $wpdb->update(
        $wpdb->prefix.'clients',
        [
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'note' => $note,
            'status' => $status,
            'profile_picture' => $profile_picture_url ?: $wpdb->get_var($wpdb->prepare("SELECT profile_picture FROM {$wpdb->prefix}clients WHERE client_id=%d", $client_id))
        ],
        ['client_id' => $client_id],
        ['%s','%s','%s','%s','%s','%s'],
        ['%d']
    );

    if($updated !== false){
        wp_send_json_success('Client updated successfully');
    } else {
        wp_send_json_error('Failed to update client');
    }
}
