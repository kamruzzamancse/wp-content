<?php
if (!defined('ABSPATH')) exit;

/**
 * Rentcast Property AJAX Handlers
 * File: includes/rentcast-properties-ajax.php
 */

// Search clients by name
add_action('wp_ajax_search_clients', 'search_clients_ajax');
function search_clients_ajax() {
    global $wpdb;
    $term = sanitize_text_field($_POST['term'] ?? '');
    $table = $wpdb->prefix . 'clients';

    if (!$term) wp_send_json_success([]);

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT client_id, full_name FROM $table WHERE full_name LIKE %s LIMIT 10",
        '%' . $wpdb->esc_like($term) . '%'
    ));

    wp_send_json_success($results);
}

// Search properties by address
add_action('wp_ajax_search_properties', 'search_properties_ajax');
function search_properties_ajax() {
    global $wpdb;
    $term = sanitize_text_field($_POST['term'] ?? '');
    $table = $wpdb->prefix . 'rentcast_properties';

    if (!$term) wp_send_json_success([]);

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT id, address FROM $table WHERE address LIKE %s LIMIT 10",
        '%' . $wpdb->esc_like($term) . '%'
    ));

    wp_send_json_success($results);
}
