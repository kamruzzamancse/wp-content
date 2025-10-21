<?php
if (!defined('ABSPATH')) exit; // Direct access block

global $wpdb;

/**
 * -----------------------------
 * Active Clients Pagination AJAX
 * -----------------------------
 */
add_action('wp_ajax_get_active_clients_page', 'get_active_clients_page');
add_action('wp_ajax_nopriv_get_active_clients_page', 'get_active_clients_page');

function get_active_clients_page() {
    check_ajax_referer('clients_pagination_nonce', 'nonce');
    global $wpdb;

    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $rows_per_page = isset($_POST['rows_per_page']) ? intval($_POST['rows_per_page']) : 10;
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $offset = ($page - 1) * $rows_per_page;

    $where = "WHERE (deleted_at IS NULL OR deleted_at = '') AND (LOWER(status) = 'active' OR status IS NULL OR status = '')";
    if(!empty($search)) {
        $where .= $wpdb->prepare(" AND full_name LIKE %s", "%{$search}%");
    }

    $total = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}clients {$where}");
    $clients = $wpdb->get_results("
        SELECT client_id, full_name, email, phone, note
        FROM {$wpdb->prefix}clients
        {$where}
        ORDER BY created_at DESC
        LIMIT {$rows_per_page} OFFSET {$offset}
    ");

    ob_start();
    if($clients):
        foreach($clients as $client):
            ?>
            <tr data-client-id="<?php echo esc_attr($client->client_id); ?>">
                <td data-label="Client Name"><?php echo esc_html($client->full_name); ?></td>
                <td data-label="Email"><?php echo esc_html($client->email ?: '—'); ?></td>
                <td data-label="Phone"><?php echo esc_html($client->phone ?: '—'); ?></td>
                <td data-label="Notes"><?php echo esc_html($client->note ?: '—'); ?></td>
                <td data-label="Actions" class="action-cell">
                    <span class="delete-client-btn" title="Delete">🗑️</span>
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
        'current_page' => $page
    ]);
}

/**
 * -----------------------------
 * Leads Pagination AJAX
 * -----------------------------
 */
add_action('wp_ajax_get_leads_page', 'get_leads_page');
add_action('wp_ajax_nopriv_get_leads_page', 'get_leads_page');

function get_leads_page() {
    check_ajax_referer('leads_pagination_nonce', 'nonce');
    global $wpdb;

    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $rows_per_page = isset($_POST['rows_per_page']) ? intval($_POST['rows_per_page']) : 10;
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $offset = ($page - 1) * $rows_per_page;

    $where = "WHERE (deleted_at IS NULL OR deleted_at = '') AND (LOWER(status) = 'lead' OR status IS NULL OR status = '')";
    if(!empty($search)) {
        $where .= $wpdb->prepare(" AND full_name LIKE %s", "%{$search}%");
    }

    $total = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}clients {$where}");
    $leads = $wpdb->get_results("
        SELECT client_id, full_name, note, lead_status
        FROM {$wpdb->prefix}clients
        {$where}
        ORDER BY created_at DESC
        LIMIT {$rows_per_page} OFFSET {$offset}
    ");

    ob_start();
    if($leads):
        foreach($leads as $lead):
            $lead_status = strtolower($lead->lead_status ?: 'cold');
            $status_label = ucfirst($lead_status);
            $status_color = '';
            switch($lead_status){
                case 'hot': $status_color='background-color:#ff4d4d;'; break;
                case 'warm': $status_color='background-color:#ffc107;'; break;
                case 'cold': default: $status_color='background-color:#4caf50;'; break;
            }
            ?>
            <tr data-client-id="<?php echo esc_attr($lead->client_id); ?>">
                <td data-label="Client Name"><?php echo esc_html($lead->full_name); ?></td>
                <td data-label="Last Touch">—</td>
                <td data-label="Status"><span class="status-dot" style="<?php echo esc_attr($status_color); ?>"></span> <?php echo esc_html($status_label); ?></td>
                <td data-label="Notes"><?php echo esc_html($lead->note ?: '—'); ?></td>
                <td data-label="Actions" class="action-cell">
                    <span class="edit-lead-btn" title="Edit">✏️</span>
                    <span class="convert-lead-btn" title="Convert to Client">🔄</span>
                    <span class="delete-lead-btn" title="Delete">🗑️</span>
                </td>
            </tr>
            <?php
        endforeach;
    else:
        echo '<tr><td colspan="5" style="text-align:center;">No Leads Found</td></tr>';
    endif;
    $html = ob_get_clean();

    wp_send_json_success([
        'html' => $html,
        'total_pages' => ceil($total / $rows_per_page),
        'current_page' => $page
    ]);
}
