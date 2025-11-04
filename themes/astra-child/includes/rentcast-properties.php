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
        property_value FLOAT DEFAULT 0,
        image_url VARCHAR(255),
        description TEXT,
        year_built INT,
        PRIMARY KEY (id),
        UNIQUE KEY unique_listing (listing_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_setup_theme', 'create_rentcast_properties_table');

// ==============================
// Fetch API & Save to DB (Updated)
// ==============================
function fetch_rentcast_properties_to_db($city = 'Orlando', $limit = 5) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentcast_properties';
    $api_key = "7a7c73a68ffc46abae4f32d560e54bf2";

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

    if ($err) { error_log("RentCast API Error: $err"); return false; }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("RentCast JSON Error: " . json_last_error_msg());
        return false;
    }

    $properties_list = $data['listings'] ?? $data;
    if (!is_array($properties_list)) return false;

    foreach ($properties_list as $property) {
        $listing_id = sanitize_text_field($property['listingId'] ?? $property['id'] ?? '');
        if (!$listing_id) continue;

        // Fetch existing row (to keep image_url)
        $existing_row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE listing_id=%s", $listing_id), ARRAY_A);

        $image_url = $existing_row['image_url'] ?? (!empty($property['photos'][0]) ? esc_url($property['photos'][0]) : '');

        // Address for AVM
        $address_raw = $property['formattedAddress'] ?? '';
        $address_clean = preg_replace('/(Apt|Unit)\s*\d+/i', '', $address_raw);
        $address_encoded = urlencode(trim($address_clean));
        $state_encoded = urlencode($property['state'] ?? '');
        $zip_encoded = urlencode($property['zipCode'] ?? '');
        $city_encoded = urlencode($property['city'] ?? '');

        $property_value = 0;
        if ($address_encoded && $state_encoded && $zip_encoded) {
            $val_url = "https://api.rentcast.io/v1/avm/value?address=$address_encoded&city=$city_encoded&state=$state_encoded&zip=$zip_encoded";
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

            if (!$val_err && $val_response) {
                $val_data = json_decode($val_response, true);
                if (!empty($val_data['value']) && is_numeric($val_data['value'])) {
                    $property_value = floatval($val_data['value']);
                }
            }
        }

        $insert_data = [
            'listing_id'     => $listing_id,
            'address'        => sanitize_text_field($property['formattedAddress'] ?? ''),
            'city'           => sanitize_text_field($property['city'] ?? ''),
            'state'          => sanitize_text_field($property['state'] ?? ''),
            'zip'            => sanitize_text_field($property['zipCode'] ?? ''),
            'bedrooms'       => intval($property['bedrooms'] ?? 0),
            'bathrooms'      => intval($property['bathrooms'] ?? 0),
            'sqft'           => intval($property['squareFootage'] ?? 0),
            'price'          => sanitize_text_field($property['price'] ?? ''),
            'property_value' => $property_value,
            'image_url'      => $image_url, // preserve existing image if any
        ];

        if ($existing_row) {
            // Update all except id
            $wpdb->update(
                $table_name,
                $insert_data,
                ['listing_id' => $listing_id],
                ['%s','%s','%s','%s','%s','%d','%d','%d','%s','%f','%s'],
                ['%s']
            );
        } else {
            $wpdb->insert(
                $table_name,
                $insert_data,
                ['%s','%s','%s','%s','%s','%d','%d','%d','%s','%f','%s']
            );
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
    $movefile = wp_handle_upload($file, ['test_form' => false]);

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
// Shortcode: Show All Properties
// ==============================
function rentcast_properties_shortcode($atts) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentcast_properties';
    $properties = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");

    if (!$properties) return "<p>No properties found.</p>";
    ob_start();

    foreach ($properties as $property):
        $property_id = intval($property->id); // <-- database id
        $listing_id  = esc_attr($property->listing_id); // <-- existing listing_id
        $image_url   = esc_url($property->image_url ?: "https://placehold.co/500x300?text=No+Image");
        $rent_price  = !empty($property->price) ? '$' . number_format((float)$property->price) . '/month' : 'N/A';
        $value_price = $property->property_value ? '$' . number_format((float)$property->property_value) : 'Value not available';
        $location    = esc_html("{$property->city}, {$property->state}");
    ?>
    <div class="pt-property-item" 
         id="property-item-<?php echo $listing_id; ?>" 
         data-id="<?php echo $property_id; ?>" 
         data-listing-id="<?php echo $listing_id; ?>">
        
        <!-- Link updated to include both listing_id & id -->
        <a href="?tab=cl-property-details&listing_id=<?php echo $listing_id; ?>&id=<?php echo $property_id; ?>">
            <img src="<?php echo $image_url; ?>" 
                 id="property-img-<?php echo $listing_id; ?>" 
                 class="pt-main-image" 
                 alt="<?php echo esc_attr($property->address); ?>">
        </a>

        <label class="pt-upload-icon" for="file-input-<?php echo $listing_id; ?>" title="Upload Image">
            <span class="dashicons dashicons-edit"></span>
        </label>

        <input type="file" 
               id="file-input-<?php echo $listing_id; ?>" 
               class="property-image-input" 
               data-listing-id="<?php echo $listing_id; ?>" 
               data-id="<?php echo $property_id; ?>">

        <div class="pt-property-details">
            <h3><?php echo esc_html($property->address); ?></h3>
            <div>Rent: <?php echo $rent_price; ?></div>
            <div>Value: <?php echo $value_price; ?></div>
            <div><?php echo $location; ?></div>
        </div>
    </div>
    <?php endforeach;

    return ob_get_clean();
}
add_shortcode('rentcast_properties', 'rentcast_properties_shortcode');

