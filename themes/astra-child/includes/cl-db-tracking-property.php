<?php
// ==============================
// AJAX Handler: Get Rentcast Chart Data
// ==============================
add_action('wp_ajax_get_rentcast_chart_data', 'get_rentcast_chart_data');
add_action('wp_ajax_nopriv_get_rentcast_chart_data', 'get_rentcast_chart_data');

function get_rentcast_chart_data() {
    if (!is_user_logged_in()) {
        wp_send_json(['properties' => []]);
        return;
    }

    global $wpdb;
    $user_id = get_current_user_id();

    // Get client_id from wp_clients table
    $clients_table = $wpdb->prefix . 'clients';
    $client_id = $wpdb->get_var($wpdb->prepare(
        "SELECT client_id FROM {$clients_table} WHERE user_id = %d LIMIT 1",
        $user_id
    ));

    if (!$client_id) {
        wp_send_json(['properties' => []]);
        return;
    }

    // Query assigned properties
    $assigned_table = $wpdb->prefix . 'assigned_property';
    $property_table = $wpdb->prefix . 'rentcast_properties';

    $query = $wpdb->prepare("
        SELECT p.id, p.address, p.price, p.property_value
        FROM {$property_table} AS p
        INNER JOIN {$assigned_table} AS a
        ON p.id = a.property_id
        WHERE a.client_id = %d
        ORDER BY a.id ASC
        LIMIT 10
    ", $client_id);

    $properties = $wpdb->get_results($query);

    if (!$properties) {
        wp_send_json(['properties' => []]);
        return;
    }

    // Process properties
    $data = [];
    $rentalSum = 0;
    $salesSum = 0;
    $allValues = [];

    foreach ($properties as $prop) {
        $rental = floatval($prop->price);
        $sales  = floatval($prop->property_value);

        $data[] = [
            'id'      => $prop->id,
            'address' => $prop->address,
            'rental'  => $rental,
            'sales'   => $sales
        ];

        $rentalSum += $rental;
        $salesSum  += $sales;
        $allValues[] = $rental;
        $allValues[] = $sales;
    }

    $count = count($properties);
    $avgRental = $count ? round($rentalSum / $count, 2) : 0;
    $avgSales  = $count ? round($salesSum / $count, 2) : 0;

    // Y-axis max = highest value + 25%
    $yAxisMax = max($allValues) * 1.25;

    wp_send_json([
        'properties' => $data,
        'avg_rental' => $avgRental,
        'avg_sales'  => $avgSales,
        'y_axis_max' => $yAxisMax
    ]);
}
?>
