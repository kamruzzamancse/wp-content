<?php
if (!defined('ABSPATH')) exit;

// ---------------------------
// Ensure logged-in
// ---------------------------
if (!function_exists('rt_active_client_user_required')) {
    function rt_active_client_user_required() {
        if (!is_user_logged_in()) {
            wp_send_json_error('Unauthorized', 401);
        }
    }
}

// ---------------------------
// Fetch Active Clients (Pagination) - DEBUG VERSION
// ---------------------------
function get_active_clients_page() {
    // Check nonce first
    if (!check_ajax_referer('clients_pagination_nonce', 'nonce', false)) {
        wp_send_json_error('Security check failed', 403);
    }

    global $wpdb;
    
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $rows_per_page = isset($_POST['rows_per_page']) ? intval($_POST['rows_per_page']) : 10;
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $offset = ($page - 1) * $rows_per_page;

    // Build WHERE clause
    $where = "WHERE (deleted_at IS NULL OR deleted_at = '') AND (status = 'active' OR status IS NULL OR status = '')";
    $params = array();
    
    if(!empty($search)) {
        $where .= " AND (full_name LIKE %s OR email LIKE %s OR phone LIKE %s)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    // Get total count
    $count_query = "SELECT COUNT(*) FROM {$wpdb->prefix}clients {$where}";
    if (!empty($params)) {
        $count_query = $wpdb->prepare($count_query, $params);
    }
    
    $total = intval($wpdb->get_var($count_query));

    // Get clients data
    $sql = "SELECT client_id, full_name, email, phone, note 
            FROM {$wpdb->prefix}clients 
            {$where} 
            ORDER BY created_at DESC 
            LIMIT %d OFFSET %d";
    
    $params[] = $rows_per_page;
    $params[] = $offset;
    
    $prepared_sql = $wpdb->prepare($sql, $params);
    $clients = $wpdb->get_results($prepared_sql);

    // Debug output
    error_log("Active Clients Query: " . $prepared_sql);
    error_log("Total Clients Found: " . $total);
    error_log("Clients Data: " . print_r($clients, true));

    ob_start();
    if($clients && count($clients) > 0):
        foreach($clients as $client):
            ?>
            <tr data-client-id="<?php echo esc_attr($client->client_id); ?>">
                <td data-label="Client Name"><?php echo esc_html($client->full_name); ?></td>
                <td data-label="Email"><?php echo esc_html($client->email ?: '—'); ?></td>
                <td data-label="Phone"><?php echo esc_html($client->phone ?: '—'); ?></td>
                <td data-label="Notes"><?php echo esc_html($client->note ?: '—'); ?></td>
                <td data-label="Actions" class="action-cell">
                    <span class="delete-client-btn" title="Delete" style="cursor:pointer;">🗑️</span>
                </td>
            </tr>
            <?php
        endforeach;
    else:
        echo '<tr><td colspan="5" style="text-align:center;">No Active Clients Found</td></tr>';
    endif;
    $html = ob_get_clean();

    wp_send_json_success([
        'html' => $html,
        'total_pages' => ceil($total / $rows_per_page),
        'current_page' => $page,
        'total' => $total,
        'debug' => [
            'query' => $prepared_sql,
            'total_found' => $total
        ]
    ]);
}
add_action('wp_ajax_get_active_clients_page', 'get_active_clients_page');
add_action('wp_ajax_nopriv_get_active_clients_page', 'get_active_clients_page');

// ---------------------------
// Create Active Client
// ---------------------------
function rt_create_active_client_ajax() {
    rt_active_client_user_required();
    
    if (!check_ajax_referer('cl_active_client_create_nonce', 'nonce', false)) {
        wp_send_json_error('Security check failed', 403);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    $full_name = sanitize_text_field($_POST['full_name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $note = sanitize_textarea_field($_POST['note'] ?? '');

    if (!$full_name || !$email) {
        wp_send_json_error('Name and Email are required');
    }

    // Check for existing client with same email
    $existing_client = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE email = %s AND (deleted_at IS NULL OR deleted_at = '')",
        $email
    ));
    
    if ($existing_client > 0) {
        wp_send_json_error('A client with this email already exists');
    }

    // Upload profile picture
    $profile_url = null;
    if (!empty($_FILES['profile_picture']['name'])) {
        require_once(ABSPATH.'wp-admin/includes/file.php');
        require_once(ABSPATH.'wp-admin/includes/media.php');
        require_once(ABSPATH.'wp-admin/includes/image.php');

        $upload = wp_handle_upload($_FILES['profile_picture'], ['test_form' => false]);
        if (isset($upload['error'])) {
            wp_send_json_error('Upload Error: ' . $upload['error']);
        }
        $profile_url = esc_url_raw($upload['url']);
    }

    // Insert client into custom table
    $data = [
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone,
        'note' => $note,
        'status' => 'active',
        'profile_picture' => $profile_url,
        'created_at' => current_time('mysql'),
        'created_by' => get_current_user_id()
    ];

    $inserted = $wpdb->insert($table, $data);
    
    if ($inserted) {
        wp_send_json_success([
            'client_id' => $wpdb->insert_id,
            'message' => 'Active client created successfully'
        ]);
    } else {
        wp_send_json_error('Could not create client. Database error: ' . $wpdb->last_error);
    }
}
add_action('wp_ajax_create_active_client_ajax', 'rt_create_active_client_ajax');
add_action('wp_ajax_nopriv_create_active_client_ajax', 'rt_create_active_client_ajax');

// ---------------------------
// Delete Active Client
// ---------------------------
function rt_delete_active_client_ajax() {
    rt_active_client_user_required();
    
    if (!check_ajax_referer('cl_active_client_delete_nonce', 'nonce', false)) {
        wp_send_json_error('Security check failed', 403);
    }

    $client_id = intval($_POST['client_id'] ?? 0);
    if (!$client_id) wp_send_json_error('Missing client ID');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    $deleted = $wpdb->update($table, [
        'deleted_at' => current_time('mysql'),
        'deleted_by' => get_current_user_id()
    ], ['client_id' => $client_id]);

    if ($deleted !== false) {
        wp_send_json_success('Client deleted successfully');
    } else {
        wp_send_json_error('Could not delete client');
    }
}
add_action('wp_ajax_delete_active_client_ajax', 'rt_delete_active_client_ajax');
add_action('wp_ajax_nopriv_delete_active_client_ajax', 'rt_delete_active_client_ajax');