<?php
if (!defined('ABSPATH')) exit;

// ==============================
// AJAX: Smart Property Search
// ==============================
function real_time_property_search() {
    check_ajax_referer('property_search_nonce', 'nonce');
    
    $search_term = sanitize_text_field($_POST['search']);
    $search_term = ucwords(strtolower($search_term));
    $api_key = "7a7c73a68ffc46abae4f32d560e54bf2";
    
    // Smart search type detection
    $search_type = detect_search_type($search_term);
    
    switch($search_type) {
        case 'zipcode':
            $api_url = "https://api.rentcast.io/v1/listings/rental/long-term?zipCode=$search_term&limit=2";
            break;
        case 'address':
            $api_url = "https://api.rentcast.io/v1/listings/rental/long-term?address=" . urlencode($search_term) . "&limit=2";
            break;
        default:
            $api_url = "https://api.rentcast.io/v1/listings/rental/long-term?city=" . urlencode($search_term) . "&limit=2";
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
// Detect Search Type
// ==============================
function detect_search_type($search_term) {
    $clean_term = trim($search_term);
    
    // ZIP code (5 digits)
    if (is_numeric($clean_term) && strlen($clean_term) === 5) {
        return 'zipcode';
    }
    
    // Convert to lowercase for pattern matching
    $lower_term = strtolower($clean_term);
    
    // Street address patterns
    $address_indicators = [
        'st', 'street', 'ave', 'avenue', 'rd', 'road', 'dr', 'drive', 
        'ln', 'lane', 'blvd', 'boulevard', 'pkwy', 'parkway', 'ct', 'court',
        'way', 'circle', 'pl', 'place'
    ];
    
    // Check if contains numbers and address indicators
    if (preg_match('/\d+/', $lower_term)) {
        foreach ($address_indicators as $indicator) {
            if (strpos($lower_term, $indicator) !== false) {
                return 'address';
            }
        }
        
        // Pattern: number + word + word (e.g., "123 Main Street")
        if (preg_match('/^\d+\s+[a-z]+\s+[a-z]+/', $lower_term)) {
            return 'address';
        }
    }
    
    // Default to city search
    return 'city';
}

// ==============================
// Save API Results to DB & Display
// ==============================
function save_and_display_search_results($properties_list) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentcast_properties';
    
    ob_start();
    
    foreach ($properties_list as $property) {
        $listing_id = sanitize_text_field($property['id'] ?? '');
        if (!$listing_id) continue;
        
        // Check if already in our database
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE listing_id = %s", $listing_id
        ));
        
        // Prepare property data
        $property_data = [
            'listing_id' => $listing_id,
            'address'    => sanitize_text_field($property['formattedAddress'] ?? ''),
            'city'       => sanitize_text_field($property['city'] ?? ''),
            'state'      => sanitize_text_field($property['state'] ?? ''),
            'zip'        => sanitize_text_field($property['zipCode'] ?? ''),
            'bedrooms'   => intval($property['bedrooms'] ?? 0),
            'bathrooms'  => intval($property['bathrooms'] ?? 0),
            'sqft'       => intval($property['squareFootage'] ?? 0),
            'price'      => sanitize_text_field($property['price'] ?? ''),
            'image_url'  => !empty($property['photos'][0]) ? esc_url($property['photos'][0]) : ''
        ];
        
        // Save to database (insert or update)
        if ($existing) {
            $wpdb->update($table_name, $property_data, ['listing_id' => $listing_id]);
            $property_id = $existing->id;
        } else {
            $wpdb->insert($table_name, $property_data);
            $property_id = $wpdb->insert_id;
        }
        
        // Display the property
        display_property_search_result($property_data, $property_id);
    }
    
    return ob_get_clean();
}

// ==============================
// Display Search Result Item
// ==============================
function display_property_search_result($property, $property_id) {
    $image_url = $property['image_url'] ?: 'https://placehold.co/100x80?text=No+Image';
    ?>
    <div class="property-result-item">
        <img src="<?php echo $image_url; ?>" alt="Property Image">
        <div class="property-info">
            <h4><?php echo esc_html($property['address']); ?></h4>
            <p><?php echo esc_html($property['city'] . ', ' . $property['state'] . ' ' . $property['zip']); ?></p>
            <p>Rent: $<?php echo number_format($property['price']); ?>/month</p>
            <p>Bed: <?php echo $property['bedrooms']; ?> | Bath: <?php echo $property['bathrooms']; ?> | SqFt: <?php echo number_format($property['sqft']); ?></p>
        </div>
        <button class="link-property-btn" 
                data-property-id="<?php echo $property_id; ?>">
            Link Property
        </button>
    </div>
    <?php
}

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

// ==============================
// Core Property Linking Function (UPDATED)
// ==============================
function simple_link_property_to_user($user_id, $property_id) {
    global $wpdb;
    
    $properties_table = $wpdb->prefix . 'rentcast_properties';
    $user_properties_table = $wpdb->prefix . 'rentcast_user_properties';
    
    // Check if property exists
    $property = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $properties_table WHERE id = %d", $property_id
    ));
    
    if (!$property) {
        return ['success' => false, 'message' => 'Property not found'];
    }
    
    // Check if already linked
    $already_linked = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $user_properties_table 
         WHERE user_id = %d AND property_id = %d AND is_active = 1", 
        $user_id, $property_id
    ));
    
    if ($already_linked) {
        return ['success' => false, 'message' => 'You have already linked this property'];
    }
    
    // Link the property
    $result = $wpdb->insert(
        $user_properties_table,
        [
            'user_id' => $user_id,
            'property_id' => $property_id,
            'listing_id' => $property->listing_id,
            'linked_date' => current_time('mysql')
        ],
        ['%d', '%d', '%s', '%s']
    );
    
    if ($result) {
        // Mark property as linked AND set linked_user_id
        $wpdb->update(
            $properties_table,
            [
                'is_linked' => 1,
                'linked_user_id' => $user_id,
                'linked_date' => current_time('mysql')
            ],
            ['id' => $property_id],
            ['%d', '%d', '%s'],
            ['%d']
        );
        
        return ['success' => true, 'message' => 'Property linked successfully'];
    }
    
    return ['success' => false, 'message' => 'Database error'];
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