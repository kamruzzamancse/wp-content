<?php
if (!defined('ABSPATH')) exit;

/**
 * Rentcast Property AJAX Handlers
 */

/* ---------------------------
   1. SEARCH CLIENTS
---------------------------- */
add_action('wp_ajax_search_clients', 'search_clients_ajax');
add_action('wp_ajax_nopriv_search_clients', 'search_clients_ajax');

function search_clients_ajax() {
    global $wpdb;

    if (!check_ajax_referer('rt_ap_assign_property_nonce', '_wpnonce', false)) {
        wp_send_json_error(['message' => 'Security verification failed']);
    }

    $term = sanitize_text_field($_POST['term'] ?? '');
    if (empty($term) || strlen($term) < 2) {
        wp_send_json_success([]);
    }

    $table = $wpdb->prefix . 'clients';
    $search_term = '%' . $wpdb->esc_like($term) . '%';

    $results = $wpdb->get_results(
        $wpdb->prepare("
            SELECT user_id AS client_id, CONCAT(first_name, ' ', second_name) AS full_name
            FROM $table
            WHERE CONCAT(first_name, ' ', second_name) LIKE %s
            AND deleted_at IS NULL
            ORDER BY first_name ASC, second_name ASC
            LIMIT 10
        ", $search_term)
    );

    wp_send_json_success($results ?: []);
}

/* ---------------------------
   2. SEARCH PROPERTIES FOR ASSIGNMENT
---------------------------- */
add_action('wp_ajax_search_properties_for_assignment', 'search_properties_for_assignment_ajax');
add_action('wp_ajax_nopriv_search_properties_for_assignment', 'search_properties_for_assignment_ajax');

function search_properties_for_assignment_ajax() {
    global $wpdb;

    if (!check_ajax_referer('rt_ap_assign_property_nonce', '_wpnonce', false)) {
        wp_send_json_error(['message' => 'Invalid nonce']);
    }

    $term = trim(sanitize_text_field($_POST['term'] ?? ''));
    if (empty($term) || strlen($term) < 2) {
        wp_send_json_success([]);
    }

    $table = $wpdb->prefix . 'rentcast_properties';
    $search_term = '%' . $wpdb->esc_like($term) . '%';
    
    $results = $wpdb->get_results(
        $wpdb->prepare("
            SELECT id, address 
            FROM $table 
            WHERE address IS NOT NULL 
              AND address != '' 
              AND address LIKE %s
            ORDER BY address ASC 
            LIMIT 10
        ", $search_term)
    );

    wp_send_json_success($results ?: []);
}

/* ---------------------------
   3. ASSIGN PROPERTY TO CLIENT
---------------------------- */
add_action('wp_ajax_assign_property_to_client', 'assign_property_to_client_ajax');
add_action('wp_ajax_nopriv_assign_property_to_client', 'assign_property_to_client_ajax');

function assign_property_to_client_ajax() {
    global $wpdb;

    if (!check_ajax_referer('rt_ap_assign_property_nonce', '_wpnonce', false)) {
        wp_send_json_error(['message' => 'Security verification failed']);
    }

    $client_id = intval($_POST['client_id'] ?? 0);
    $property_id = intval($_POST['property_id'] ?? 0);

    if (!$client_id || !$property_id) {
        wp_send_json_error(['message' => 'Client and Property are required']);
    }

    $assigned_table = $wpdb->prefix . 'assigned_property';
    $clients_table = $wpdb->prefix . 'clients';

    // Check if assignment already exists
    $existing_assignment = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $assigned_table 
         WHERE client_id = %d AND property_id = %d AND deleted_at IS NULL",
        $client_id, $property_id
    ));

    if ($existing_assignment) {
        wp_send_json_error(['message' => 'This property is already assigned to the client']);
    }

    // Check if property is assigned to another client
    $property_assigned_to_other = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $assigned_table 
         WHERE property_id = %d AND client_id != %d AND deleted_at IS NULL",
        $property_id, $client_id
    ));

    if ($property_assigned_to_other) {
        $existing_client = $wpdb->get_row($wpdb->prepare(
            "SELECT CONCAT(first_name, ' ', second_name) AS full_name 
             FROM $clients_table c
             LEFT JOIN $assigned_table a ON a.client_id = c.user_id
             WHERE a.property_id = %d AND a.deleted_at IS NULL
             LIMIT 1",
            $property_id
        ));
        
        $client_name = $existing_client ? $existing_client->full_name : 'another client';
        wp_send_json_error(['message' => 'This property is already assigned to: ' . $client_name]);
    }

    $current_user_id = get_current_user_id();
    
    if (!$current_user_id) {
        wp_send_json_error(['message' => 'User not logged in']);
    }

    $result = $wpdb->insert(
        $assigned_table,
        [
            'client_id' => $client_id,
            'property_id' => $property_id,
            'created_by' => $current_user_id,
            'created_at' => current_time('mysql')
        ],
        ['%d', '%d', '%d', '%s']
    );

    if ($result) {
        wp_send_json_success(['message' => 'Property assigned successfully']);
    } else {
        wp_send_json_error(['message' => 'Failed to assign property']);
    }
}

/* ---------------------------
   4. UPDATE ASSIGNMENT
---------------------------- */
add_action('wp_ajax_update_assignment', 'update_assignment_ajax');
add_action('wp_ajax_nopriv_update_assignment', 'update_assignment_ajax');

function update_assignment_ajax() {
    global $wpdb;

    if (!check_ajax_referer('rt_ap_assign_property_nonce', '_wpnonce', false)) {
        wp_send_json_error(['message' => 'Security verification failed']);
    }

    $assignment_id = intval($_POST['assignment_id'] ?? 0);
    $client_id = intval($_POST['client_id'] ?? 0);
    $property_id = intval($_POST['property_id'] ?? 0);

    if (!$assignment_id || !$client_id || !$property_id) {
        wp_send_json_error(['message' => 'All fields are required']);
    }

    $assigned_table = $wpdb->prefix . 'assigned_property';

    // Check if new assignment already exists
    $existing_assignment = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $assigned_table 
         WHERE client_id = %d AND property_id = %d AND id != %d AND deleted_at IS NULL",
        $client_id, $property_id, $assignment_id
    ));

    if ($existing_assignment) {
        wp_send_json_error(['message' => 'This property is already assigned to the client']);
    }

    // Check if property is assigned to another client
    $property_assigned_to_other = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $assigned_table 
         WHERE property_id = %d AND client_id != %d AND id != %d AND deleted_at IS NULL",
        $property_id, $client_id, $assignment_id
    ));

    if ($property_assigned_to_other) {
        wp_send_json_error(['message' => 'This property is already assigned to another client']);
    }

    $current_user_id = get_current_user_id();
    
    if (!$current_user_id) {
        wp_send_json_error(['message' => 'User not logged in']);
    }

    $result = $wpdb->update(
        $assigned_table,
        [
            'client_id' => $client_id,
            'property_id' => $property_id,
            'updated_by' => $current_user_id,
            'updated_at' => current_time('mysql')
        ],
        ['id' => $assignment_id],
        ['%d', '%d', '%d', '%s'],
        ['%d']
    );

    if ($result !== false) {
        wp_send_json_success(['message' => 'Assignment updated successfully']);
    } else {
        wp_send_json_error(['message' => 'Failed to update assignment']);
    }
}

/* ---------------------------
   5. DELETE ASSIGNMENT
---------------------------- */
add_action('wp_ajax_delete_assignment', 'delete_assignment_ajax');
add_action('wp_ajax_nopriv_delete_assignment', 'delete_assignment_ajax');

function delete_assignment_ajax() {
    if (!check_ajax_referer('rt_ap_assign_property_nonce', '_wpnonce', false)) {
        wp_send_json_error(['message' => 'Security verification failed']);
    }

    $assignment_id = intval($_POST['assignment_id'] ?? 0);
    
    if (!$assignment_id) {
        wp_send_json_error(['message' => 'Assignment ID is required']);
    }

    $current_user_id = get_current_user_id();
    
    if (!$current_user_id) {
        wp_send_json_error(['message' => 'User not logged in']);
    }

    global $wpdb;
    $assigned_table = $wpdb->prefix . 'assigned_property';

    $result = $wpdb->update(
        $assigned_table,
        [
            'deleted_at' => current_time('mysql'),
            'deleted_by' => $current_user_id
        ],
        ['id' => $assignment_id],
        ['%s', '%d'],
        ['%d']
    );

    if ($result !== false) {
        wp_send_json_success(['message' => 'Assignment deleted successfully']);
    } else {
        wp_send_json_error(['message' => 'Failed to delete assignment']);
    }
}
