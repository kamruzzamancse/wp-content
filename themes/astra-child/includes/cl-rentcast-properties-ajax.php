<?php
if (!defined('ABSPATH')) exit;

// ========================================================
// AJAX: Get Property Data for Modal
// ========================================================

add_action('wp_ajax_get_property_data', function() {
    check_ajax_referer('property_edit_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'rentcast_properties';
    $listing_id = sanitize_text_field($_POST['listing_id']);

    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE listing_id=%s", $listing_id), ARRAY_A);

    if ($row) {
        wp_send_json_success($row);
    } else {
        wp_send_json_error("Property not found");
    }
});

// ========================================================
// AJAX: Update Property Fields from Modal
// ========================================================

add_action('wp_ajax_update_property_fields', function() {
    check_ajax_referer('property_edit_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'rentcast_properties';
    $listing_id = sanitize_text_field($_POST['listing_id']);

    $update = [
        'bedrooms'    => intval($_POST['bedrooms']),
        'bathrooms'   => intval($_POST['bathrooms']),
        'sqft'        => intval($_POST['sqft']),
        'price'       => sanitize_text_field($_POST['price']),
        'year_built'  => intval($_POST['year_built']),
        'description' => sanitize_textarea_field($_POST['description']),
    ];

    $wpdb->update($table, $update, ['listing_id' => $listing_id]);

    wp_send_json_success("Property updated successfully");
});