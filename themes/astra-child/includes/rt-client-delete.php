<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_delete_realtor_client_ajax', 'delete_realtor_client_ajax');
function delete_realtor_client_ajax() {
    check_ajax_referer('cl_client_delete_nonce', 'nonce');

    if(empty($_POST['client_id'])) wp_send_json_error('Invalid client ID.');

    global $wpdb;
    $client_id = intval($_POST['client_id']);
    $user_id = get_current_user_id();

    $updated = $wpdb->update(
        "{$wpdb->prefix}clients",
        [
            'deleted_at' => current_time('mysql'),
            'deleted_by' => $user_id,
        ],
        ['client_id' => $client_id],
        ['%s','%d'],
        ['%d']
    );

    if($updated !== false){
        wp_send_json_success('Client marked as deleted.');
    } else {
        wp_send_json_error('Failed to delete client.');
    }
}

