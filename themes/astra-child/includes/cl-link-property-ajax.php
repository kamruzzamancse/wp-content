<?php
if (!defined('ABSPATH')) exit;

// ==============================
// AJAX: Real-time Property Search
// ==============================
function real_time_property_search() {
    check_ajax_referer('property_search_nonce', 'nonce');
    
    $search_term = sanitize_text_field($_POST['search']);
    $api_key = "7a7c73a68ffc46abae4f32d560e54bf2";
    
    // Determine search type
    if (is_numeric($search_term) && strlen($search_term) === 5) {
        $api_url = "https://api.rentcast.io/v1/listings/rental/long-term?zipCode=$search_term&limit=10";
    } else {
        $api_url = "https://api.rentcast.io/v1/listings/rental/long-term?city=" . urlencode($search_term) . "&limit=10";
    }
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            "X-Api-Key: $api_key",
            "accept: application/json"
        ],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    
    if ($err) {
        wp_send_json_error("Search error: $err");
    }
    
    $data = json_decode($response, true);
    $properties_list = $data['listings'] ?? $data;
    
    if (!is_array($properties_list) || empty($properties_list)) {
        wp_send_json_error("No properties found for: $search_term");
    }
    
    // Save to database and display results
    $results = save_and_display_search_results($properties_list);
    wp_send_json_success($results);
}
add_action('wp_ajax_real_time_property_search', 'real_time_property_search');

// ==============================
// AJAX: Link Property to User
// ==============================
function simple_link_property() {
    check_ajax_referer('property_link_nonce', 'nonce');
    
    $user_id = get_current_user_id();
    $property_id = intval($_POST['property_id']);
    
    if (!$user_id) {
        wp_send_json_error('Please login to link properties');
    }
    
    $result = simple_link_property_to_user($user_id, $property_id);
    
    if ($result['success']) {
        wp_send_json_success('Property linked successfully');
    } else {
        wp_send_json_error($result['message']);
    }
}
add_action('wp_ajax_simple_link_property', 'simple_link_property');