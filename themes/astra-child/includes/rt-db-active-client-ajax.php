<?php
if (!defined('ABSPATH')) exit;

// ======================
// Ensure user is logged in
// ======================
function rt_active_current_user_required() {
    if (!is_user_logged_in()) wp_send_json_error('Unauthorized', 401);
}

// ======================
// Fetch Active Clients AJAX
// ======================
function rt_active_fetch_dashboard_clients_ajax() {
    rt_active_current_user_required();
    check_ajax_referer('clients_pagination_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    // Get request data
    $search = sanitize_text_field($_POST['search'] ?? '');
    $page   = max(1, intval($_POST['page'] ?? 1));
    $rows   = max(1, intval($_POST['rows'] ?? 10));
    $offset = ($page - 1) * $rows;

    // Base query
    $where = "WHERE deleted_at IS NULL AND status='active'";
    $params = [];

    if ($search !== '') {
        $like = "%{$wpdb->esc_like($search)}%";
        $where .= " AND (full_name LIKE %s OR email LIKE %s OR phone LIKE %s)";
        $params = [$like, $like, $like];
    }

    // Count total records
    $count_query = !empty($params)
        ? $wpdb->prepare("SELECT COUNT(*) FROM {$table} {$where}", $params)
        : "SELECT COUNT(*) FROM {$table} {$where}";
    $total = intval($wpdb->get_var($count_query));

    // Fetch data
    $sql = "SELECT * FROM {$table} {$where} ORDER BY created_at DESC LIMIT %d, %d";
    if (!empty($params)) {
        $params[] = $offset;
        $params[] = $rows;
        $prepared = $wpdb->prepare($sql, $params);
    } else {
        $prepared = $wpdb->prepare($sql, $offset, $rows);
    }

    $clients = $wpdb->get_results($prepared, ARRAY_A);
    $total_pages = ceil($total / $rows);

    wp_send_json_success([
        'clients' => $clients,
        'total' => $total,
        'total_pages' => $total_pages,
        'page' => $page
    ]);
}
add_action('wp_ajax_fetch_dashboard_active_clients_ajax', 'rt_active_fetch_dashboard_clients_ajax');
