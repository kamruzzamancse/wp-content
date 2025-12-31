<?php
// ==============================
// AJAX Handler: Get Rentcast Chart Data (UPDATED)
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

    // Query assigned properties WITH rental trend data
    $assigned_table = $wpdb->prefix . 'assigned_property';
    $property_table = $wpdb->prefix . 'rentcast_properties';

    $query = $wpdb->prepare("
        SELECT p.id, p.address, p.price, p.property_value, 
            p.monthly_rental_data, p.historical_rental_prices
        FROM {$property_table} AS p
        INNER JOIN {$assigned_table} AS a
        ON p.id = a.property_id
        WHERE a.client_id = %d
        AND a.deleted_by IS NULL
        ORDER BY a.id ASC
        LIMIT 20
    ", $client_id);

    $properties = $wpdb->get_results($query);

    if (!$properties) {
        wp_send_json(['properties' => []]);
        return;
    }

    $data = [];
    $rentalSum = 0;
    $salesSum  = 0;
    $allValues = [];

    foreach ($properties as $prop) {
        $rental = floatval($prop->price);
        $sales  = floatval($prop->property_value);

        // Decode monthly rental data
        $monthly_data = [];

        if (!empty($prop->monthly_rental_data)) {
            $raw_data = json_decode($prop->monthly_rental_data, true);
            if (is_array($raw_data)) {
                foreach ($raw_data as $date => $entry) {
                    $monthly_data[$date] = [
                        'price' => floatval($entry['price'] ?? 0)
                    ];
                }
            }
        }

        // Fallback to historical_rental_prices if monthly_rental_data is empty
        if (empty($monthly_data) && !empty($prop->historical_rental_prices)) {
            $raw_hist = json_decode($prop->historical_rental_prices, true);
            if (is_array($raw_hist)) {
                foreach ($raw_hist as $date => $entry) {
                    $monthly_data[$date] = [
                        'price' => floatval($entry['price'] ?? 0)
                    ];
                }
            }
        }

        $data[] = [
            'id'      => $prop->id,
            'address' => $prop->address,
            'rental'  => $rental,
            'sales'   => $sales,
            'monthly_rental_data' => $monthly_data
        ];

        $rentalSum += $rental;
        $salesSum  += $sales;
        $allValues[] = $rental;
        $allValues[] = $sales;
    }

    $count = count($properties);
    $avgRental = $count ? round($rentalSum / $count, 2) : 0;
    $avgSales  = $count ? round($salesSum / $count, 2) : 0;

    // Y-axis max calculation
    $yAxisMax = !empty($allValues) ? max($allValues) * 1.25 : 10000;

    wp_send_json([
        'properties' => $data,
        'avg_rental' => $avgRental,
        'avg_sales'  => $avgSales,
        'y_axis_max' => [
            'rental' => $yAxisMax,
            'sale'   => $yAxisMax,
            'both'   => $yAxisMax
        ]
    ]);
}
?>
