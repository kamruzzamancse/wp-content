<?php
// ======================
// FETCH CLIENT DATA (for Edit Modal)
// ======================
add_action('wp_ajax_fetch_realtor_client_ajax', 'fetch_realtor_client_ajax');
add_action('wp_ajax_nopriv_fetch_realtor_client_ajax', 'fetch_realtor_client_ajax');

function fetch_realtor_client_ajax() {
    check_ajax_referer('cl_client_edit_nonce', 'nonce');

    global $wpdb;
    $client_id = intval($_POST['client_id']);

    $client = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$wpdb->prefix}clients WHERE client_id = %d", $client_id),
        ARRAY_A
    );

    if ($client) {
        wp_send_json_success($client);
    } else {
        wp_send_json_error('Client not found.');
    }
}

// ======================
// UPDATE CLIENT DATA
// ======================
add_action('wp_ajax_update_realtor_client_ajax', 'update_realtor_client_ajax');
add_action('wp_ajax_nopriv_update_realtor_client_ajax', 'update_realtor_client_ajax');

function update_realtor_client_ajax() {
    check_ajax_referer('cl_client_edit_nonce', 'nonce');

    global $wpdb;

    $client_id = intval($_POST['realtor_client_id']);
    if (!$client_id) wp_send_json_error('Invalid client ID.');

    $full_name = sanitize_text_field($_POST['realtor_client_full_name']);
    $email = sanitize_email($_POST['realtor_client_email']);
    $phone = sanitize_text_field($_POST['realtor_client_phone']);
    $preferred_location = sanitize_text_field($_POST['preferred_location']);
    $note = sanitize_textarea_field($_POST['realtor_client_note']);
    $status = sanitize_text_field($_POST['realtor_client_status']);

    // Handle optional image upload
    $profile_picture = '';
    if (!empty($_FILES['realtor_client_profile_picture']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $uploaded = wp_handle_upload($_FILES['realtor_client_profile_picture'], ['test_form' => false]);
        if (isset($uploaded['url'])) {
            $profile_picture = esc_url_raw($uploaded['url']);
        }
    }

    $update_data = [
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone,
        'preferred_location' => $preferred_location,
        'note' => $note,
        'status' => $status,
    ];

    if ($profile_picture) {
        $update_data['profile_picture'] = $profile_picture;
    }

    $updated = $wpdb->update(
        "{$wpdb->prefix}clients",
        $update_data,
        ['client_id' => $client_id],
        null,
        ['%d']
    );

    if ($updated !== false) {
        wp_send_json_success('Client updated successfully!');
    } else {
        wp_send_json_error('Failed to update client.');
    }
}
