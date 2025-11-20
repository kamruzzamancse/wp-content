<?php
if (!defined('ABSPATH')) exit;

// ===== GET PROPERTY DATA =====
add_action('wp_ajax_get_property_data', function() {
    check_ajax_referer('property_edit_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'rentcast_properties';
    $listing_id = sanitize_text_field($_POST['listing_id']);

    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE listing_id=%s", $listing_id), ARRAY_A);

    if ($row) wp_send_json_success($row);
    else wp_send_json_error("Property not found");
});

// ===== UPDATE PRICE + PROPERTY VALUE =====
add_action('wp_ajax_update_property_fields', function() {
    check_ajax_referer('property_edit_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'rentcast_properties';
    $listing_id = sanitize_text_field($_POST['listing_id']);

    $update = [
        'price'          => sanitize_text_field($_POST['price']),
        'property_value' => floatval($_POST['property_value']),
    ];

    $wpdb->update($table, $update, ['listing_id' => $listing_id]);
    wp_send_json_success("Property updated successfully");
});

// ===== UPLOAD IMAGE =====
add_action('wp_ajax_upload_property_image', function() {
    // Check nonce
    if (!wp_verify_nonce($_POST['nonce'], 'property_image_nonce')) {
        wp_send_json_error('Security verification failed');
    }

    if (empty($_FILES['property_image'])) {
        wp_send_json_error('No file uploaded');
    }

    $listing_id = sanitize_text_field($_POST['listing_id']);
    $file = $_FILES['property_image'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        wp_send_json_error('File upload error: ' . $file['error']);
    }

    // Maximum supported formats
    $allowed_types = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif',
        'image/webp', 'image/bmp', 'image/svg+xml', 'image/tiff',
        'image/x-icon', 'image/vnd.microsoft.icon', 'image/avif',
        'image/heic', 'image/heif'
    ];

    // Also check by extension for broader compatibility
    $file_type = wp_check_filetype($file['name']);
    $allowed_extensions = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 
        'svg', 'tiff', 'tif', 'ico', 'avif', 'heic', 'heif'
    ];

    if (!in_array($file_type['type'], $allowed_types) && 
        !in_array($file_type['ext'], $allowed_extensions)) {
        wp_send_json_error('Invalid file type. Supported formats: JPEG, JPG, PNG, GIF, WEBP, BMP, SVG, TIFF, ICO, AVIF, HEIC, HEIF');
    }

    // Increased file size limit to 10MB
    if ($file['size'] > 10 * 1024 * 1024) {
        wp_send_json_error('Image size should be less than 10MB');
    }

    $upload = wp_handle_upload($file, ['test_form' => false]);
    
    if (isset($upload['error'])) {
        wp_send_json_error($upload['error']);
    }

    $image_url = $upload['url'];

    global $wpdb;
    $table = $wpdb->prefix . 'rentcast_properties';
    $result = $wpdb->update($table, ['image_url' => $image_url], ['listing_id' => $listing_id]);

    if ($result === false) {
        wp_send_json_error('Database update failed');
    }

    wp_send_json_success($image_url);
});