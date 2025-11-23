<?php
if (!defined('ABSPATH')) exit;

// ==============================
// AJAX: Smart Property Search (DB save skipped)
// ==============================
function real_time_property_search() {
    check_ajax_referer('property_search_nonce', 'nonce');
    
    $search_term = sanitize_text_field($_POST['search']);
    $search_term = ucwords(strtolower($search_term));
    $api_key = "7a7c73a68ffc46abae4f32d560e54bf2";
    
    // Smart search type detection
    $search_type = detect_search_type($search_term);
    
    // For address searches, try multiple approaches
    if ($search_type === 'address') {
        $results = search_property_by_address($search_term, $api_key);
    } else {
        $results = search_property_general($search_term, $search_type, $api_key);
    }
    
    if ($results['success']) {
        wp_send_json_success(['html' => $results['html'], 'count' => $results['count']]);
    } else {
        wp_send_json_error($results['message']);
    }
}
add_action('wp_ajax_real_time_property_search', 'real_time_property_search');
add_action('wp_ajax_nopriv_real_time_property_search', 'real_time_property_search');

// ==============================
// Address-specific search with fallbacks
// ==============================
function search_property_by_address($address, $api_key) {
    // Try 1: Exact address search
    $api_url = "https://api.rentcast.io/v1/listings/rental/long-term?address=" . urlencode($address) . "&limit=2";
    $result = make_api_request($api_url, $api_key);
    
    if ($result['success']) {
        return $result;
    }
    
    // Try 2: Extract ZIP code and search by ZIP
    $zip_code = extract_zip_code($address);
    if ($zip_code) {
        $api_url = "https://api.rentcast.io/v1/listings/rental/long-term?zipCode=$zip_code&limit=2";
        $result = make_api_request($api_url, $api_key);
        if ($result['success'] && $result['count'] > 0) {
            $result['message'] = "Exact address not found. Showing properties in ZIP code $zip_code";
            return $result;
        }
    }
    
    // Try 3: Extract city and search by city
    $city = extract_city($address);
    if ($city) {
        $api_url = "https://api.rentcast.io/v1/listings/rental/long-term?city=" . urlencode($city) . "&limit=2";
        $result = make_api_request($api_url, $api_key);
        if ($result['success'] && $result['count'] > 0) {
            $result['message'] = "Exact address not found. Showing properties in $city";
            return $result;
        }
    }
    
    return [
        'success' => false,
        'message' => "No properties found for '$address'. Try searching by ZIP code or city name only."
    ];
}

// ==============================
// General search function
// ==============================
function search_property_general($search_term, $search_type, $api_key) {
    switch($search_type) {
        case 'zipcode':
            $api_url = "https://api.rentcast.io/v1/listings/rental/long-term?zipCode=$search_term&limit=2";
            break;
        default:
            $api_url = "https://api.rentcast.io/v1/listings/rental/long-term?city=" . urlencode($search_term) . "&limit=2";
    }
    
    return make_api_request($api_url, $api_key);
}

// ==============================
// Make API request with proper error handling
// ==============================
function make_api_request($api_url, $api_key) {
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
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);
    curl_close($curl);
    
    // Check for cURL error
    if ($err) {
        return ['success' => false, 'message' => "Network error: $err"];
    }
    
    // Check HTTP status code
    if ($http_code === 404) {
        return ['success' => false, 'message' => "No properties found at this location."];
    }
    
    if ($http_code !== 200) {
        return ['success' => false, 'message' => "API returned HTTP $http_code. Please try a different search."];
    }
    
    $data = json_decode($response, true);
    
    // Check if JSON decoding failed
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['success' => false, 'message' => "Invalid response from API."];
    }
    
    // Check for API errors
    if (isset($data['error']) || isset($data['message'])) {
        $error_msg = $data['message'] ?? $data['error'] ?? 'API error';
        return ['success' => false, 'message' => $error_msg];
    }
    
    // Check if we have valid listings
    $properties_list = $data['listings'] ?? $data;
    
    if (!is_array($properties_list) || empty($properties_list)) {
        return ['success' => false, 'message' => "No properties found. Try a different search term."];
    }
    
    // Validate properties
    $valid_properties = array_filter($properties_list, function($property) {
        return is_array($property) && 
               !isset($property['error']) && 
               !isset($property['message']) &&
               (!empty($property['id']) || !empty($property['listingId']));
    });
    
    if (empty($valid_properties)) {
        return ['success' => false, 'message' => "No valid property data found."];
    }
    
    // Generate HTML
    $html = '';
    foreach ($valid_properties as $property) {
        $html .= render_property_item($property);
    }
    
    return [
        'success' => true,
        'html' => $html,
        'count' => count($valid_properties)
    ];
}

// ==============================
// Helper functions
// ==============================
function extract_zip_code($address) {
    preg_match('/\b\d{5}\b/', $address, $matches);
    return $matches[0] ?? null;
}

function extract_city($address) {
    // Simple city extraction - you might want to improve this
    $parts = explode(',', $address);
    return count($parts) >= 2 ? trim($parts[1]) : null;
}

// ==============================
// Detect Search Type
// ==============================
function detect_search_type($search_term) {
    $clean_term = trim($search_term);
    
    // ZIP code (5 digits)
    if (is_numeric($clean_term) && strlen($clean_term) === 5) {
        return 'zipcode';
    }
    
    $lower_term = strtolower($clean_term);
    
    $address_indicators = [
        'st', 'street', 'ave', 'avenue', 'rd', 'road', 'dr', 'drive', 
        'ln', 'lane', 'blvd', 'boulevard', 'pkwy', 'parkway', 'ct', 'court',
        'way', 'circle', 'pl', 'place'
    ];
    
    if (preg_match('/\d+/', $lower_term)) {
        foreach ($address_indicators as $indicator) {
            if (strpos($lower_term, $indicator) !== false) {
                return 'address';
            }
        }
        if (preg_match('/^\d+\s+[a-z]+\s+[a-z]+/', $lower_term)) {
            return 'address';
        }
    }
    
    return 'city';
}

// ==============================
// Render Property Item (HTML for search results)
// ==============================
function render_property_item($property) {
    $image = !empty($property['photos'][0]) ? esc_url($property['photos'][0]) : 'https://placehold.co/100x80?text=No+Image';
    $address = sanitize_text_field($property['formattedAddress'] ?? $property['address'] ?? '');
    $city = sanitize_text_field($property['city'] ?? '');
    $state = sanitize_text_field($property['state'] ?? '');
    $zip = sanitize_text_field($property['zipCode'] ?? $property['zip'] ?? '');
    $price = sanitize_text_field($property['price'] ?? $property['rent'] ?? '');
    $bedrooms = intval($property['bedrooms'] ?? $property['bed'] ?? 0);
    $bathrooms = intval($property['bathrooms'] ?? $property['bath'] ?? 0);
    $sqft = intval($property['squareFootage'] ?? $property['sqft'] ?? 0);
    $listing_id = sanitize_text_field($property['id'] ?? $property['listingId'] ?? '');
    
    // Store full property payload in button for later DB insert
    $payload_json = esc_attr(wp_json_encode($property));
    
    ob_start();
    ?>
    <div class="property-result-item">
        <img src="<?php echo $image; ?>" alt="Property Image">
        <div class="property-info">
            <h4><?php echo esc_html($address); ?></h4>
            <p><?php echo esc_html($city . ', ' . $state . ' ' . $zip); ?></p>
            <p>Rent: $<?php echo number_format((float)$price); ?>/month</p>
            <p>Bed: <?php echo $bedrooms; ?> | Bath: <?php echo $bathrooms; ?> | SqFt: <?php echo number_format($sqft); ?></p>
        </div>
        <button class="link-property-btn" data-property-payload='<?php echo $payload_json; ?>'>
            Link Property
        </button>
    </div>
    <?php
    return ob_get_clean();
}

// ==============================
// AJAX: Link Property to User (DB insert happens here)
// ==============================
function simple_link_property() {
    check_ajax_referer('property_link_nonce', 'nonce');
    
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('Please login to link properties');
    }
    
    // Get individual property fields from POST data
    $listing_id = sanitize_text_field($_POST['listing_id'] ?? '');
    $address = sanitize_text_field($_POST['address'] ?? '');
    $city = sanitize_text_field($_POST['city'] ?? '');
    $state = sanitize_text_field($_POST['state'] ?? '');
    $zip = sanitize_text_field($_POST['zip'] ?? '');
    $bedrooms = intval($_POST['bedrooms'] ?? 0);
    $bathrooms = intval($_POST['bathrooms'] ?? 0);
    $sqft = intval($_POST['sqft'] ?? 0);
    $price = sanitize_text_field($_POST['price'] ?? '');
    $image_url = esc_url($_POST['image_url'] ?? '');
    
    // Create property array from individual fields
    $property = [
        'id' => $listing_id,
        'listingId' => $listing_id,
        'formattedAddress' => $address,
        'address' => $address,
        'city' => $city,
        'state' => $state,
        'zipCode' => $zip,
        'zip' => $zip,
        'bedrooms' => $bedrooms,
        'bed' => $bedrooms,
        'bathrooms' => $bathrooms,
        'bath' => $bathrooms,
        'squareFootage' => $sqft,
        'sqft' => $sqft,
        'price' => $price,
        'rent' => $price,
        'photos' => $image_url ? [$image_url] : []
    ];
    
    $result = simple_link_property_to_user($user_id, $property);
    
    if ($result['success']) {
        wp_send_json_success('Property linked successfully');
    } else {
        wp_send_json_error($result['message']);
    }
}
add_action('wp_ajax_simple_link_property', 'simple_link_property');

// ==============================
// Core Property Linking Function (insert/update DB)
// ==============================
function simple_link_property_to_user($user_id, $property_payload) {
    global $wpdb;
    $properties_table = $wpdb->prefix . 'rentcast_properties';
    $user_properties_table = $wpdb->prefix . 'rentcast_user_properties';
    
    $listing_id = sanitize_text_field($property_payload['id'] ?? $property_payload['listingId'] ?? '');
    $address = sanitize_text_field($property_payload['formattedAddress'] ?? $property_payload['address'] ?? '');
    $city = sanitize_text_field($property_payload['city'] ?? '');
    $state = sanitize_text_field($property_payload['state'] ?? '');
    $zip = sanitize_text_field($property_payload['zipCode'] ?? $property_payload['zip'] ?? '');
    $bedrooms = intval($property_payload['bedrooms'] ?? $property_payload['bed'] ?? 0);
    $bathrooms = intval($property_payload['bathrooms'] ?? $property_payload['bath'] ?? 0);
    $sqft = intval($property_payload['squareFootage'] ?? $property_payload['sqft'] ?? 0);
    $price = sanitize_text_field($property_payload['price'] ?? $property_payload['rent'] ?? '');
    $image_url = !empty($property_payload['photos'][0]) ? esc_url($property_payload['photos'][0]) : '';
    
    // Validate required fields
    if (empty($listing_id) || empty($address)) {
        return ['success' => false, 'message' => 'Invalid property data: missing listing ID or address'];
    }
    
    // 1) Check if property exists
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $properties_table WHERE listing_id = %s",
        $listing_id
    ));
    
    if ($existing) {
        $property_id = $existing->id;
        $wpdb->update($properties_table, [
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'zip' => $zip,
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'sqft' => $sqft,
            'price' => $price,
            'image_url' => $image_url,
            'linked_user_id' => $user_id,
            'linked_date' => current_time('mysql'),
            'is_linked' => 1
        ], ['id' => $property_id]);
    } else {
        $wpdb->insert($properties_table, [
            'listing_id' => $listing_id,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'zip' => $zip,
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'sqft' => $sqft,
            'price' => $price,
            'image_url' => $image_url,
            'linked_user_id' => $user_id,
            'linked_date' => current_time('mysql'),
            'is_linked' => 1
        ]);
        $property_id = $wpdb->insert_id;
    }
    
    // 2) Link property to user
    $already_linked = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $user_properties_table WHERE user_id = %d AND property_id = %d AND is_active = 1",
        $user_id, $property_id
    ));
    
    if ($already_linked) {
        return ['success' => false, 'message' => 'You have already linked this property'];
    }
    
    $insert_link = $wpdb->insert($user_properties_table, [
        'user_id' => $user_id,
        'property_id' => $property_id,
        'listing_id' => $listing_id,
        'linked_date' => current_time('mysql'),
        'is_active' => 1
    ]);
    
    if ($insert_link === false) {
        return ['success' => false, 'message' => 'Failed to link property to user'];
    }
    
    return ['success' => true];
}

// ==============================
// Shortcode: User's Linked Properties
// ==============================
function my_properties_shortcode() {
    $user_id = get_current_user_id();
    if (!$user_id) return '<p>Please login to view your properties.</p>';
    
    global $wpdb;
    $properties_table = $wpdb->prefix . 'rentcast_properties';
    $user_properties_table = $wpdb->prefix . 'rentcast_user_properties';
    
    $properties = $wpdb->get_results($wpdb->prepare("
        SELECT p.*, up.linked_date 
        FROM $properties_table p
        INNER JOIN $user_properties_table up ON p.id = up.property_id
        WHERE up.user_id = %d AND up.is_active = 1
        ORDER BY up.linked_date DESC
    ", $user_id));
    
    ob_start();
    if ($properties) {
        echo '<div class="my-properties-list">';
        foreach ($properties as $property) {
            $image_url = $property->image_url ?: 'https://placehold.co/300x200?text=No+Image';
            $last_updated = $property->last_updated ? date('M j, Y', strtotime($property->last_updated)) : 'Never';
            ?>
            <div class="my-property-item">
                <img src="<?php echo $image_url; ?>" alt="<?php echo esc_attr($property->address); ?>">
                <div class="property-details">
                    <h5><?php echo esc_html($property->address); ?></h5>
                    <p class="location"><?php echo esc_html($property->city . ', ' . $property->state); ?></p>
                    <div class="property-stats">
                        <span>Rent: $<?php echo number_format($property->price); ?>/month</span>
                        <span>Value: $<?php echo number_format($property->property_value); ?></span>
                    </div>
                    <div class="property-meta">
                        <small>Linked: <?php echo date('M j, Y', strtotime($property->linked_date)); ?></small>
                        <small>Updated: <?php echo $last_updated; ?></small>
                    </div>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    } else {
        echo '<div class="no-properties-message">';
        echo '<p>You have no linked properties yet.</p>';
        echo '<p>Use the search above to link your first property!</p>';
        echo '</div>';
    }
    
    return ob_get_clean();
}
add_shortcode('my_properties', 'my_properties_shortcode');