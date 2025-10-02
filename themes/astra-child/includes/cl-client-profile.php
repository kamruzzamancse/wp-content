<?php
if (!defined('ABSPATH')) exit;

/**
 * ======================
 * Load Client Profile Data
 * ======================
 */
add_action('wp_ajax_load_cl_profile_data', 'load_cl_profile_data');
function load_cl_profile_data() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cl_profile_nonce')) {
        wp_send_json_error('Security verification failed');
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('Not logged in');
    }

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    // Ensure client record exists
    $client = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d", $user_id), ARRAY_A);
    
    if (!$client) {
        $current_user = wp_get_current_user();
        $wpdb->insert($table, [
            'user_id'     => $user_id,
            'full_name'   => $current_user->display_name,
            'email'       => $current_user->user_email,
            'created_by'  => $user_id, // Set created_by to current user
            'created_at'  => current_time('mysql'),
        ], ['%d', '%s', '%s', '%d', '%s']);
        
        $client = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d", $user_id), ARRAY_A);
    }

    $user = wp_get_current_user();
    $profile_picture = get_user_meta($user_id, 'profile_picture', true);
    
    // Default profile picture
    if (!$profile_picture) {
        $upload_dir = wp_upload_dir();
        $profile_picture = $upload_dir['baseurl'] . '/2025/08/client-photo.jpg';
    }

    wp_send_json_success([
        'full_name'          => $user->display_name,
        'email'              => $user->user_email,
        'phone'              => $client['phone'] ?? '',
        'budget'             => $client['budget'] ?? '',
        'preferred_location' => $client['preferred_location'] ?? '',
        'profile_picture'    => $profile_picture,
        'created_by'         => $client['created_by'] ?? $user_id,
    ]);
}

/**
 * ======================
 * Save Client Profile Data (full_name and email are readonly)
 * ======================
 */
add_action('wp_ajax_save_cl_profile_data', 'save_cl_profile_data');
function save_cl_profile_data() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cl_profile_nonce')) {
        wp_send_json_error('Security verification failed');
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('Not logged in');
    }

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    // Get current user data
    $current_user = wp_get_current_user();
    $current_profile_picture = get_user_meta($user_id, 'profile_picture', true);

    // Prepare data for wp_clients table
    $client_data = [
        'full_name'          => $current_user->display_name, // Readonly - keep original
        'email'              => $current_user->user_email,   // Readonly - keep original
        'phone'              => sanitize_text_field($_POST['phone'] ?? ''),
        'budget'             => sanitize_text_field($_POST['budget'] ?? ''), // Keep as string for flexibility
        'preferred_location' => sanitize_text_field($_POST['preferred_location'] ?? ''),
        'profile_picture'    => $current_profile_picture ?: '',
        'created_by'         => $user_id, // Always set created_by to current user
    ];

    // Check if client record exists, if not create it
    $existing_client = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE user_id = %d", $user_id));
    
    if (!$existing_client) {
        $client_data['user_id'] = $user_id;
        $client_data['created_at'] = current_time('mysql');
        
        $inserted = $wpdb->insert($table, $client_data, 
            ['%s','%s','%s','%s','%s','%s','%d','%d','%s'] // Added %d for created_by
        );
        
        if ($inserted === false) {
            wp_send_json_error('Failed to create client record: ' . $wpdb->last_error);
        }
    } else {
        // Update existing record
        $updated = $wpdb->update(
            $table,
            $client_data,
            ['user_id' => $user_id],
            ['%s','%s','%s','%s','%s','%s','%d'], // Added %d for created_by
            ['%d']
        );

        if ($updated === false) {
            wp_send_json_error('Database update failed: ' . $wpdb->last_error);
        }
    }

    wp_send_json_success('Profile updated successfully');
}

/**
 * ======================
 * Upload Client Profile Picture (Update both user meta and clients table)
 * ======================
 */
add_action('wp_ajax_upload_cl_profile_picture', 'upload_cl_profile_picture');
function upload_cl_profile_picture() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cl_profile_nonce')) {
        wp_send_json_error('Security verification failed');
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('Not logged in');
    }

    if (empty($_FILES['profile_picture']['name'])) {
        wp_send_json_error('No file uploaded');
    }

    require_once(ABSPATH . 'wp-admin/includes/file.php');
    
    $upload_overrides = ['test_form' => false];
    $uploaded = wp_handle_upload($_FILES['profile_picture'], $upload_overrides);

    if (isset($uploaded['error'])) {
        wp_send_json_error('Upload error: ' . $uploaded['error']);
    }

    if (!isset($uploaded['url'])) {
        wp_send_json_error('Upload failed - no URL returned');
    }

    $profile_picture_url = esc_url($uploaded['url']);
    
    // Update user meta
    update_user_meta($user_id, 'profile_picture', $profile_picture_url);
    
    // Also update wp_clients table
    global $wpdb;
    $table = $wpdb->prefix . 'clients';
    
    $wpdb->update(
        $table,
        ['profile_picture' => $profile_picture_url],
        ['user_id' => $user_id],
        ['%s'],
        ['%d']
    );
    
    wp_send_json_success(['url' => $profile_picture_url]);
}

/**
 * ======================
 * Update created_by field for existing clients
 * ======================
 */
add_action('wp_ajax_update_client_created_by', 'update_client_created_by');
function update_client_created_by() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cl_profile_nonce')) {
        wp_send_json_error('Security verification failed');
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('Not logged in');
    }

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    // Update created_by field with current user ID for all client records of this user
    $updated = $wpdb->update(
        $table,
        ['created_by' => $user_id],
        ['user_id' => $user_id],
        ['%d'], // created_by format
        ['%d']  // user_id format
    );

    if ($updated === false) {
        wp_send_json_error('Failed to update created_by field: ' . $wpdb->last_error);
    }

    wp_send_json_success('created_by field updated successfully');
}

/**
 * ======================
 * Save Realtor Client (Create Client) - Redirect to new system
 * ======================
 */
add_action('wp_ajax_save_rt_client_data', 'save_rt_client_data');
function save_rt_client_data() {
    wp_send_json_error('Client creation is now handled by the new system. Please use the create client form.');
}