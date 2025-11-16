<?php
add_action('wp_ajax_get_rentcast_chart_data', 'get_rentcast_chart_data');
add_action('wp_ajax_nopriv_get_rentcast_chart_data', 'get_rentcast_chart_data');

function get_rentcast_chart_data() {
    if (!is_user_logged_in()) {
        wp_send_json([]);
        return;
    }

    global $wpdb;
    $user_id = get_current_user_id();

    $assigned_table = $wpdb->prefix . 'assigned_property';
    $property_table = $wpdb->prefix . 'rentcast_properties';

    // Debug logs
    error_log("Current user ID: " . $user_id);

    // Fetch assigned properties (even if price or property_value is NULL)
    $query = $wpdb->prepare("
        SELECT p.id, p.listing_id, p.address, p.price, p.property_value
        FROM {$property_table} AS p
        INNER JOIN {$assigned_table} AS a
        ON p.id = a.property_id
        WHERE a.client_id = %d
        ORDER BY a.created_at DESC
        LIMIT 6
    ", $user_id);

    error_log("Query: " . $query);

    $properties = $wpdb->get_results($query, ARRAY_A);

    error_log("Number of properties fetched: " . count($properties));
    error_log("Properties: " . print_r($properties, true));

    $data = [];
    foreach ($properties as $prop) {
        $data[$prop['id']] = [
            'listing_id' => $prop['listing_id'],
            'address'    => $prop['address'],
            'rental'     => floatval($prop['price'] ?? 0),
            'sales'      => floatval($prop['property_value'] ?? 0)
        ];
    }

    wp_send_json($data);
}
