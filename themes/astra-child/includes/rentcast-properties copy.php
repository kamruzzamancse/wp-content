<?php
if (!defined('ABSPATH')) exit;

// ==============================
// Create DB Table
// ==============================
function create_rentcast_properties_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentcast_properties';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        listing_id VARCHAR(255) NOT NULL,
        address VARCHAR(255),
        city VARCHAR(100),
        state VARCHAR(50),
        zip VARCHAR(20),
        bedrooms INT,
        bathrooms INT,
        sqft INT,
        price VARCHAR(50),
        property_value VARCHAR(50),
        image_url VARCHAR(255),
        PRIMARY KEY (id),
        UNIQUE KEY unique_listing (listing_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_setup_theme', 'create_rentcast_properties_table');

// ==============================
// Fetch API & Save to DB with debug logs
// ==============================
function fetch_rentcast_properties_to_db($city = 'Orlando', $limit = 5) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentcast_properties';
    $api_key = "7a7c73a68ffc46abae4f32d560e54bf2"; // Your API key

    // Fetch rental listings
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.rentcast.io/v1/listings/rental/long-term?city=" . urlencode($city) . "&limit=" . $limit,
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
        error_log("RentCast cURL Error: " . $err);
        return false;
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
        error_log("RentCast returned empty or invalid data: " . json_last_error_msg());
        error_log(print_r($data, true));
        return false;
    }

    // Check API structure
    $properties_list = [];
    if (isset($data['listings']) && is_array($data['listings'])) {
        $properties_list = $data['listings'];
    } elseif (is_array($data)) {
        $properties_list = $data;
    } else {
        error_log("Unexpected RentCast API response structure");
        error_log(print_r($data, true));
        return false;
    }

    foreach ($properties_list as $property) {
        $listing_id = sanitize_text_field($property['listingId'] ?? $property['id'] ?? '');
        if (!$listing_id) continue;

        $image_url = '';
        $images = $property['photos'] ?? [];
        if (!empty($images) && isset($images[0])) $image_url = esc_url($images[0]);

        // Clean up address for AVM API
        $address_raw = $property['formattedAddress'] ?? '';
        $address_clean = preg_replace('/(Apt|Unit)\s*\d+/i', '', $address_raw);
        $address = urlencode(trim($address_clean));
        $state   = urlencode($property['state'] ?? '');
        $zip     = urlencode($property['zipCode'] ?? '');
        $cityVal = urlencode($property['city'] ?? '');

        // Fetch property value
        $property_value = '';
        if (!empty($address) && !empty($state) && !empty($zip)) {
            $val_url = "https://api.rentcast.io/v1/avm/value?address=$address&city=$cityVal&state=$state&zip=$zip";
            $val_curl = curl_init();
            curl_setopt_array($val_curl, [
                CURLOPT_URL => $val_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_HTTPHEADER => [
                    "X-Api-Key: $api_key",
                    "accept: application/json"
                ],
            ]);
            $val_response = curl_exec($val_curl);
            $val_err = curl_error($val_curl);
            curl_close($val_curl);

            if ($val_err) {
                error_log("AVM API cURL Error: " . $val_err);
            } elseif ($val_response) {
                $val_data = json_decode($val_response, true);
                if (!empty($val_data['value']) && is_numeric($val_data['value'])) {
                    $property_value = sanitize_text_field($val_data['value']);
                } else {
                    error_log("AVM API returned empty/non-numeric value for listing_id: $listing_id");
                    error_log(print_r($val_data, true));
                }
            }
        }

        // Prepare insert/update data
        $insert_data = [
            'listing_id'      => $listing_id,
            'address'         => sanitize_text_field($property['formattedAddress'] ?? ''),
            'city'            => sanitize_text_field($property['city'] ?? ''),
            'state'           => sanitize_text_field($property['state'] ?? ''),
            'zip'             => sanitize_text_field($property['zipCode'] ?? ''),
            'bedrooms'        => intval($property['bedrooms'] ?? 0),
            'bathrooms'       => intval($property['bathrooms'] ?? 0),
            'sqft'            => intval($property['squareFootage'] ?? 0),
            'price'           => sanitize_text_field($property['price'] ?? ''),
            'property_value'  => $property_value,
            'image_url'       => $image_url
        ];

        // Replace row
        $result = $wpdb->replace(
            $table_name,
            $insert_data,
            ['%s','%s','%s','%s','%s','%d','%d','%d','%s','%s','%s']
        );
        if ($result === false) {
            error_log("DB insert/replace failed for listing_id: $listing_id");
            error_log(print_r($insert_data, true));
        } else {
            error_log("DB insert/replace succeeded for listing_id: $listing_id");
        }
    }

    return true;
}

// ==============================
// AJAX Upload Property Image
// ==============================
function upload_property_image() {
    check_ajax_referer('property_image_nonce', 'nonce');

    if (empty($_POST['listing_id'])) wp_send_json_error('Missing listing ID');
    if (!isset($_FILES['property_image'])) wp_send_json_error('No file uploaded');

    $listing_id = sanitize_text_field($_POST['listing_id']);

    if (!function_exists('wp_handle_upload')) require_once(ABSPATH . 'wp-admin/includes/file.php');

    $file = $_FILES['property_image'];
    $upload_overrides = ['test_form' => false];
    $movefile = wp_handle_upload($file, $upload_overrides);

    if ($movefile && !isset($movefile['error'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rentcast_properties';

        $wpdb->update(
            $table_name,
            ['image_url' => esc_url($movefile['url'])],
            ['listing_id' => $listing_id],
            ['%s'],
            ['%s']
        );

        wp_send_json_success(['url' => esc_url($movefile['url'])]);
    } else {
        wp_send_json_error($movefile['error'] ?? 'Upload error');
    }
}
add_action('wp_ajax_upload_property_image', 'upload_property_image');

// ==============================
// Shortcode: Show All Properties from DB
// ==============================
function rentcast_properties_shortcode($atts) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentcast_properties';

    // Fetch all properties from DB, newest first
    $properties = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");

    if (!$properties) return "<p>No properties found in the database.</p>";

    ob_start();
    foreach ($properties as $property):
        $listing_id = esc_attr($property->listing_id);
        $image_url  = esc_url($property->image_url ?: "https://placehold.co/500x300?text=No+Image");
        $rent_price = !empty($property->price) ? '$' . number_format((float)$property->price) . '/month' : 'N/A';
        $value_price = !empty($property->property_value) ? '$' . number_format((float)$property->property_value) : 'Value not available';
        $location   = esc_html("{$property->city}, {$property->state}");
    ?>
    <div class="pt-property-item">
        <a href="?tab=cl-property-details">
            <img src="<?php echo $image_url; ?>" 
                 id="property-img-<?php echo esc_attr($listing_id); ?>" 
                 class="pt-main-image"
                 alt="<?php echo esc_attr($property->address); ?>">
        </a>

        <!-- Top-right Upload/Edit Icon -->
        <label class="pt-upload-icon" for="file-input-<?php echo esc_attr($listing_id); ?>" title="Upload Image">
            <span class="dashicons dashicons-edit"></span>
        </label>
        <input type="file" id="file-input-<?php echo esc_attr($listing_id); ?>" 
               class="property-image-input" data-listing-id="<?php echo esc_attr($listing_id); ?>">

        <div class="pt-property-details">
            <a href="?tab=cl-property-details">
                <h3 class="pt-property-title"><?php echo esc_html($property->address); ?></h3>
            </a>
            <div class="pt-property-price">Rent: <?php echo $rent_price; ?></div>
            <div class="pt-property-value">Value: <?php echo $value_price; ?></div>
            <div class="pt-property-location">
                <span class="dashicons dashicons-location"></span>
                <span><?php echo $location; ?></span>
            </div>
        </div>
    </div>
    <?php
    endforeach;

    return ob_get_clean();
}
add_shortcode('rentcast_properties', 'rentcast_properties_shortcode');
