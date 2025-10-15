<?php

add_action('wp_ajax_convert_lead_to_client', 'convert_lead_to_client_ajax');

function convert_lead_to_client_ajax() {
    // Security check
    check_ajax_referer('convert_lead_nonce', 'nonce');

    if (empty($_POST['client_id'])) {
        wp_send_json_error('Client ID is missing');
    }

    global $wpdb;
    $client_id = intval($_POST['client_id']);
    $table = $wpdb->prefix . 'clients';

    $updated = $wpdb->update(
        $table,
        ['status' => 'active'], // New status
        ['client_id' => $client_id], // Where condition
        ['%s'],
        ['%d']
    );

    if ($updated !== false) {
        wp_send_json_success('Lead converted successfully');
    } else {
        wp_send_json_error('Failed to update client status');
    }
}
