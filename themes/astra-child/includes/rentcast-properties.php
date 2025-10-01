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
        image_url VARCHAR(255),
        PRIMARY KEY (id),
        UNIQUE KEY unique_listing (listing_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_setup_theme', 'create_rentcast_properties_table');

// ==============================
// Fetch API & Save to DB
// ==============================
function fetch_rentcast_properties_to_db($city = 'Orlando', $limit = 2) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentcast_properties';

    $api_key = "YOUR_RENTCAST_API_KEY"; // Replace with your actual key

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
        error_log("RentCast returned empty or invalid data.");
        return false;
    }

    foreach ($data as $property) {
        $listing_id = sanitize_text_field($property['listingId'] ?? $property['id'] ?? '');
        if (!$listing_id) continue;

        $image_url = '';
        $images = $property['photos'] ?? [];
        if (!empty($images)) $image_url = esc_url($images[0]);

        $insert_data = [
            'listing_id' => $listing_id,
            'address'    => sanitize_text_field($property['formattedAddress'] ?? ''),
            'city'       => sanitize_text_field($property['city'] ?? ''),
            'state'      => sanitize_text_field($property['state'] ?? ''),
            'zip'        => sanitize_text_field($property['zipCode'] ?? ''),
            'bedrooms'   => intval($property['bedrooms'] ?? 0),
            'bathrooms'  => intval($property['bathrooms'] ?? 0),
            'sqft'       => intval($property['squareFootage'] ?? 0),
            'price'      => sanitize_text_field($property['price'] ?? ''),
            'image_url'  => $image_url
        ];

        $wpdb->replace($table_name, $insert_data, ['%s','%s','%s','%s','%s','%d','%d','%d','%s','%s']);
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
// Shortcode: Property Cards with Upload Icon
// ==============================
function rentcast_properties_shortcode($atts) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentcast_properties';

    $atts = shortcode_atts(['city'=>'Orlando','limit'=>2], $atts, 'rentcast_properties');
    $city  = sanitize_text_field($atts['city']);
    $limit = intval($atts['limit']);

    fetch_rentcast_properties_to_db($city, $limit);

    $properties = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE city=%s ORDER BY id DESC LIMIT %d",
        $city, $limit
    ));

    if (!$properties) return "<p>No properties found for " . esc_html($city) . ".</p>";

    ob_start();
    foreach ($properties as $property):
        $listing_id = esc_attr($property->listing_id);
        $image_url  = esc_url($property->image_url ?: "https://placehold.co/500x300?text=No+Image");
        $price      = !empty($property->price) ? '$' . number_format((float)$property->price) : 'N/A';
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
            <div class="pt-property-price"><?php echo $price; ?></div>
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
