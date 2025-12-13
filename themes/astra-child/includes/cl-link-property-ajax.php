<?php
if (!defined('ABSPATH')) exit;

// ==============================
// 1. AJAX: Smart Property Search
// ==============================
function real_time_property_search() {
    check_ajax_referer('property_search_nonce', 'nonce');

    $search_term = sanitize_text_field($_POST['search'] ?? '');
    $search_term = trim($search_term);
    if (empty($search_term)) wp_send_json_error('Please enter a search term.');

    $api_key = "7a7c73a68ffc46abae4f32d560e54bf2";
    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 5;
    $mode = sanitize_text_field($_POST['mode'] ?? 'properties'); // properties / sale / rental
    $search_type = detect_search_type($search_term);

    try {
        if ($search_type === 'address') {
            $results = search_property_by_address($search_term, $api_key, $limit, $mode);
        } else {
            $results = search_property_general($search_term, $search_type, $api_key, $limit, $mode);
        }

        if ($results['success']) {
            wp_send_json_success([
                'html' => $results['html'],
                'count' => $results['count']
            ]);
        } else {
            wp_send_json_error($results['message'] ?? 'No results found.');
        }
    } catch (Exception $e) {
        wp_send_json_error('Search failed: ' . $e->getMessage());
    }
}
add_action('wp_ajax_real_time_property_search', 'real_time_property_search');
add_action('wp_ajax_nopriv_real_time_property_search', 'real_time_property_search');

// ==============================
// 2. Address-specific search with fallback
// ==============================
function search_property_by_address($address, $api_key, $limit = 5, $mode = 'properties') {

    // Property details
    $property_url = "https://api.rentcast.io/v1/properties?address=" . urlencode($address) . "&limit=1";
    $property_result = make_api_request($property_url, $api_key, $address, 'property');

    // Rental listing
    $rental_url = "https://api.rentcast.io/v1/listings/rental/long-term?address=" . urlencode($address) . "&status=Active&limit=1";
    $rental_result = make_api_request($rental_url, $api_key, $address, 'rental');

    // ✅ BOTH EXIST → SMART MERGE
    if ($property_result['success'] && $rental_result['success']) {

        $merged = smart_merge_property_and_rental(
            $property_result['data'],
            $rental_result['data']
        );

        return [
            'success' => true,
            'html' => render_property_item_custom(
                $merged,
                format_rent_price($merged),
                format_property_value($merged)
            ),
            'count' => 1
        ];
    }

    // Only property
    if ($property_result['success']) {
        return [
            'success' => true,
            'html' => render_property_item_custom(
                $property_result['data'],
                'N/A',
                format_property_value($property_result['data'])
            ),
            'count' => 1
        ];
    }

    // Only rental
    if ($rental_result['success']) {
        return [
            'success' => true,
            'html' => render_property_item_custom(
                $rental_result['data'],
                format_rent_price($rental_result['data']),
                'N/A'
            ),
            'count' => 1
        ];
    }

    return [
        'success' => false,
        'message' => "No property found for this address."
    ];
}


function smart_merge_property_and_rental($property, $rental) {

    foreach ($rental as $key => $value) {

        // ❌ Never override property-only data
        if (in_array($key, [
            'taxAssessments',
            'propertyTaxes',
            'owner',
            'features',
            'assessorID',
            'legalDescription',
            'subdivision'
        ])) {
            continue;
        }

        // Only fill missing or empty fields
        if (
            !isset($property[$key]) ||
            $property[$key] === null ||
            $property[$key] === ''
        ) {
            $property[$key] = $value;
        }
    }

    return $property;
}


// ==============================
// 3. General search by ZIP or city (Fixed)
// ==============================
function search_property_general($search_term, $search_type, $api_key, $limit = 5, $mode = 'properties') {
    $search_config = get_search_endpoint_and_params($search_term, $search_type, $mode, $limit);
    if (!$search_config) return ['success'=>false, 'message'=>'Invalid search term'];

    $query_string = http_build_query($search_config['params']);
    $api_url = "https://api.rentcast.io/v1/{$search_config['endpoint']}?$query_string";

    return make_api_request($api_url, $api_key);
}

function get_search_endpoint_and_params($search_term, $search_type, $mode = 'properties', $limit = 5) {
    $endpoint = ($mode === 'properties') ? 'properties' : "listings/$mode";
    $params = [];

    if ($search_type === 'zipcode') {
        $zip = preg_replace('/\D/', '', $search_term);
        if (!$zip) return false;
        $params['zipCode'] = $zip;
    } elseif ($search_type === 'city') {
        $city_only = trim(preg_replace('/\d{5}/', '', $search_term));
        if (!$city_only) return false;

        // Mode-specific city parameter
        if ($mode === 'properties') {
            $params['cityName'] = $city_only;
        } else { // rental / sale listings
            $params['city'] = $city_only;
            $params['status'] = 'Active'; // optional for listings
        }
    } else {
        // Address search should not go here
        return false;
    }

    $params['limit'] = intval($limit);
    return [
        'endpoint' => $endpoint,
        'params' => $params
    ];
}


// ==============================
// Make API Request (Fixed for 'data' key)
// ==============================
function make_api_request($api_url, $api_key, $search_address = '', $type = 'general') {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            "X-Api-Key: $api_key",
            "Accept: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) return ['success'=>false, 'message'=>$err];
    if ($http_code !== 200) return ['success'=>false, 'message'=>"HTTP $http_code"];

    $data = json_decode($response, true);
    if (!is_array($data)) return ['success'=>false, 'message'=>'Invalid JSON response from API'];

    // Handle API response with 'data' key
    if (isset($data['data']) && is_array($data['data'])) $data = $data['data'];

    if ($type === 'property' || $type === 'rental') {
        if (isset($data[0]) && is_array($data[0])) {
            return ['success' => true, 'data' => $data[0]];
        }
        return ['success' => false, 'message' => "No {$type} found"];
    }

    if (empty($data)) return ['success'=>false,'message'=>'No results found'];

    $html = '';
    foreach ($data as $item) {
        $rent = format_rent_price($item);
        $value = format_property_value($item);
        $html .= render_property_item_custom($item, $rent, $value);
    }

    return ['success' => true, 'html' => $html, 'count' => count($data)];
}


// ==============================
// Format Property Value
// ==============================
function format_property_value($property) {
    if (!empty($property['taxAssessments']) && is_array($property['taxAssessments'])) {
        $years = array_keys($property['taxAssessments']);
        rsort($years);
        foreach ($years as $year) {
            if (!empty($property['taxAssessments'][$year]['value'])) {
                return floatval($property['taxAssessments'][$year]['value']);
            }
        }
    }
    if (!empty($property['estimatedValue']) && is_numeric($property['estimatedValue'])) {
        return floatval($property['estimatedValue']);
    }
    if (!empty($property['lastSalePrice']) && is_numeric($property['lastSalePrice'])) {
        return floatval($property['lastSalePrice']);
    }
    return 0;
}


// ==============================
// Format Rent Price
// ==============================
function format_rent_price($property) {
    if (!empty($property['price']) && is_numeric($property['price'])) {
        return floatval($property['price']);
    }
    return 0;
}

// ==============================
// Render Property Item (Custom with Rent/Value + Debug)
// ==============================
function render_property_item_custom($property, $rent, $value) {
    // Image
    $image = !empty($property['photos'][0]) ? esc_url($property['photos'][0]) : 
             (!empty($property['image_url']) ? esc_url($property['image_url']) : 
             'https://placehold.co/100x80?text=No+Image');

    // Address info
    $address = sanitize_text_field($property['formattedAddress'] ?? $property['address'] ?? '');
    $city = sanitize_text_field($property['city'] ?? '');
    $state = sanitize_text_field($property['state'] ?? '');
    $zip = sanitize_text_field($property['zipCode'] ?? $property['zip'] ?? '');

    // Property stats
    $bedrooms = isset($property['bedrooms']) ? intval($property['bedrooms']) : 0;
    $bathrooms = isset($property['bathrooms']) ? intval($property['bathrooms']) : 0;
    $sqft = isset($property['squareFootage']) ? intval($property['squareFootage']) : 0;

    $bed_display = $bedrooms > 0 ? $bedrooms : 'N/A';
    $bath_display = $bathrooms > 0 ? $bathrooms : 'N/A';
    $sqft_display = $sqft > 0 ? number_format($sqft) : 'N/A';

    // Ensure property_value is always set
    $property['property_value'] = $property['estimatedValue'] 
                                  ?? format_property_value($property) 
                                  ?? 0;

    // Format rent display
    $rent_display = is_numeric($rent) && $rent > 0 ? '$' . number_format($rent) . '/Month' : 'N/A';

    // JSON payload for JS
    $payload_json = esc_attr(wp_json_encode($property));

    ob_start(); ?>
    <div class="property-result-item">
        <img src="<?php echo $image; ?>" alt="Property Image">
        <div class="property-info">
            <h4><?php echo esc_html($address); ?></h4>
            <p><?php echo esc_html($city . ', ' . $state . ' ' . $zip); ?></p>
            <p>Property Rent: <?php echo esc_html($rent_display); ?></p>
            <p>Property Value: $<?php echo number_format($property['property_value']); ?></p>
            <p>Bed: <?php echo $bed_display; ?> | Bath: <?php echo $bath_display; ?> | SqFt: <?php echo $sqft_display; ?></p>
        </div>
        <button class="link-property-btn" data-property-payload='<?php echo $payload_json; ?>'>Link Property</button>
    </div>
    <?php
    return ob_get_clean();
}

// ==============================
// 6. Helpers
// ==============================
function extract_zip_code($address) { preg_match('/\b\d{5}\b/', $address, $matches); return $matches[0] ?? null; }
function extract_city($address) { $parts = explode(',', $address); return count($parts) >= 2 ? trim($parts[1]) : null; }
function detect_search_type($term) {
    $term = trim($term);
    if (is_numeric($term) && strlen($term) === 5) return 'zipcode';
    $lower = strtolower($term);
    $address_indicators = ['st','street','ave','avenue','rd','road','dr','drive','ln','lane','blvd','boulevard','pkwy','parkway','ct','court','way','circle','pl','place'];
    if (preg_match('/\d+/', $lower)) {
        foreach ($address_indicators as $indicator) if (strpos($lower, $indicator) !== false) return 'address';
        if (preg_match('/^\d+\s+[a-z]+\s+[a-z]+/', $lower)) return 'address';
    }
    return 'city';
}

// ==============================
// 7. AJAX: Link Property to User (Updated for property_value)
// ==============================
function simple_link_property() {
    check_ajax_referer('property_link_nonce', 'nonce');

    $user_id = get_current_user_id();
    if (!$user_id) wp_send_json_error('Please login to link properties');

    // Prepare property payload
    $property = [
        'id' => sanitize_text_field($_POST['listing_id'] ?? ''),
        'listingId' => sanitize_text_field($_POST['listing_id'] ?? ''),
        'formattedAddress' => sanitize_text_field($_POST['address'] ?? ''),
        'address' => sanitize_text_field($_POST['address'] ?? ''),
        'city' => sanitize_text_field($_POST['city'] ?? ''),
        'state' => sanitize_text_field($_POST['state'] ?? ''),
        'zipCode' => sanitize_text_field($_POST['zip'] ?? ''),
        'zip' => sanitize_text_field($_POST['zip'] ?? ''),
        'bedrooms' => intval($_POST['bedrooms'] ?? 0),
        'bathrooms' => intval($_POST['bathrooms'] ?? 0),
        'squareFootage' => intval($_POST['sqft'] ?? 0),
        'price' => sanitize_text_field($_POST['price'] ?? ''),                
        'property_value' => sanitize_text_field($_POST['property_value'] ?? ''),
        'photos' => !empty($_POST['image_url']) ? [esc_url($_POST['image_url'])] : []
    ];

    // Call core linking function
    $result = simple_link_property_to_user($user_id, $property);

    if ($result['success']) {
        wp_send_json_success('Property linked successfully');
    }

    wp_send_json_error($result['message']);
}
add_action('wp_ajax_simple_link_property', 'simple_link_property');

// ==============================
// 8. Core Property Linking Function (Updated for rent & property value)
// ==============================
function simple_link_property_to_user($user_id, $property_payload) {
    global $wpdb;
    $properties_table = $wpdb->prefix . 'rentcast_properties';
    $user_properties_table = $wpdb->prefix . 'rentcast_user_properties';

    $listing_id = sanitize_text_field($property_payload['id'] ?? '');
    $address = sanitize_text_field($property_payload['formattedAddress'] ?? '');
    if (!$listing_id || !$address) return ['success'=>false,'message'=>'Invalid property data'];

    // Extract rental price and property value safely
    $rent_price = isset($property_payload['price']) ? floatval(str_replace([',','$'],'',$property_payload['price'])) : 0;
    $property_value = isset($property_payload['property_value']) ? floatval(str_replace([',','$'],'',$property_payload['property_value'])) : 0;

    // Check if property already exists
    $existing = $wpdb->get_row($wpdb->prepare("SELECT id FROM $properties_table WHERE listing_id=%s", $listing_id));

    $data_to_save = [
        'address' => $address,
        'city' => sanitize_text_field($property_payload['city'] ?? ''),
        'state' => sanitize_text_field($property_payload['state'] ?? ''),
        'zip' => sanitize_text_field($property_payload['zip'] ?? ''),
        'bedrooms' => intval($property_payload['bedrooms'] ?? 0),
        'bathrooms' => intval($property_payload['bathrooms'] ?? 0),
        'sqft' => intval($property_payload['squareFootage'] ?? 0),
        'price' => $rent_price,
        'property_value' => $property_value,
        'image_url' => $property_payload['photos'][0] ?? '',
        'linked_user_id' => $user_id,
        'linked_date' => current_time('mysql'),
        'last_updated' => current_time('mysql'),
        'is_linked' => 1
    ];

    if ($existing) {
        $property_id = $existing->id;
        $wpdb->update($properties_table, $data_to_save, ['id' => $property_id]);
    } else {
        $wpdb->insert($properties_table, array_merge($data_to_save, [
            'listing_id' => $listing_id
        ]));
        $property_id = $wpdb->insert_id;
    }

    // Link property to user if not already linked
    $already_linked = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $user_properties_table WHERE user_id=%d AND property_id=%d AND is_active=1",
        $user_id, $property_id
    ));

    if ($already_linked) return ['success'=>false,'message'=>'Property already linked'];

    $wpdb->insert($user_properties_table, [
        'user_id' => $user_id,
        'property_id' => $property_id,
        'listing_id' => $listing_id,
        'linked_date' => current_time('mysql'),
        'is_active' => 1
    ]);

    return ['success' => true];
}


// ==============================
// 9. Shortcode: User's Linked Properties
// ==============================
function my_properties_shortcode() {
    $user_id = get_current_user_id();
    if (!$user_id) return '<p>Please login to view your properties.</p>';

    global $wpdb;
    $properties_table = $wpdb->prefix.'rentcast_properties';
    $user_properties_table = $wpdb->prefix.'rentcast_user_properties';

    $properties = $wpdb->get_results($wpdb->prepare(
        "SELECT p.*, up.linked_date
         FROM $properties_table p
         INNER JOIN $user_properties_table up ON p.id=up.property_id
         WHERE up.user_id=%d AND up.is_active=1
         ORDER BY up.linked_date DESC",
        $user_id
    ));

    ob_start();
    if ($properties) {
        echo '<div class="my-properties-list">';
        foreach($properties as $p) {
            $image_url = !empty($p->image_url)?esc_url($p->image_url):'https://placehold.co/300x200?text=No+Image';
            $rent = is_numeric($p->price)?number_format($p->price):'N/A';
            $last_updated = !empty($p->last_updated)?date('M j, Y', strtotime($p->last_updated)):'Never';
            ?>
            <div class="my-property-item">
                <img src="<?php echo $image_url; ?>" alt="<?php echo esc_attr($p->address); ?>">
                <div class="property-details">
                    <h5><?php echo esc_html($p->address); ?></h5>
                    <p class="location"><?php echo esc_html($p->city . ', ' . $p->state); ?></p>
                    <div class="property-stats">
                        <span>Rent: $<?php echo $rent; ?>/month</span>
                    </div>
                    <div class="property-meta">
                        <small>Linked: <?php echo date('M j, Y', strtotime($p->linked_date)); ?></small>
                        <small>Updated: <?php echo esc_html($last_updated); ?></small>
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
