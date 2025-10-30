<?php
if (!defined('ABSPATH')) exit;

/**
 * Ensure the user is logged in
 */
function rt_realtor_current_user_required() {
    if (!is_user_logged_in()) wp_send_json_error('Unauthorized', 401);
}

// =====================
// Fetch Realtors (List)
// =====================
function rt_fetch_realtors_ajax() {
    rt_realtor_current_user_required();
    check_ajax_referer('am_realtor_edit_nonce','nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'realtors';

    $search = sanitize_text_field($_POST['search'] ?? '');
    $page = max(1, intval($_POST['page'] ?? 1));
    $rows = max(1, intval($_POST['rows'] ?? 10));
    $offset = ($page - 1) * $rows;

    $where = "WHERE deleted_at IS NULL";
    $params = [];
    if ($search !== '') {
        $like = "%{$wpdb->esc_like($search)}%";
        $where .= " AND (full_name LIKE %s OR email LIKE %s OR phone LIKE %s OR agency_name LIKE %s OR license_number LIKE %s)";
        $params = [$like, $like, $like, $like, $like];
    }

    $count_query = !empty($params)
        ? $wpdb->prepare("SELECT COUNT(*) FROM {$table} {$where}", $params)
        : "SELECT COUNT(*) FROM {$table} {$where}";

    $total = intval($wpdb->get_var($count_query));

    $sql = "SELECT * FROM {$table} {$where} ORDER BY created_at DESC LIMIT %d,%d";
    if (!empty($params)) {
        $params[] = $offset;
        $params[] = $rows;
        $prepared = $wpdb->prepare($sql, $params);
    } else {
        $prepared = $wpdb->prepare($sql, $offset, $rows);
    }

    $realtors = $wpdb->get_results($prepared);
    $total_pages = ceil($total / $rows);

    wp_send_json_success([
        'realtors' => $realtors,
        'total' => $total,
        'total_pages' => $total_pages,
        'page' => $page
    ]);
}
add_action('wp_ajax_fetch_realtors_ajax','rt_fetch_realtors_ajax');

// =====================
// Fetch Single Realtor
// =====================
function rt_fetch_single_realtor_ajax() {
    rt_realtor_current_user_required();
    check_ajax_referer('am_realtor_edit_nonce','nonce');

    $realtor_id = intval($_POST['realtor_id'] ?? 0);
    if (!$realtor_id) wp_send_json_error('Missing realtor ID');

    global $wpdb;
    $table = $wpdb->prefix . 'realtors';
    $realtor = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE realtor_id=%d AND deleted_at IS NULL", $realtor_id), ARRAY_A);

    if (!$realtor) wp_send_json_error('Realtor not found');
    wp_send_json_success($realtor);
}
add_action('wp_ajax_fetch_single_realtor_ajax','rt_fetch_single_realtor_ajax');

// =====================
// Create Realtor
// =====================
function rt_create_realtor_ajax() {
    rt_realtor_current_user_required();
    check_ajax_referer('am_realtor_create_nonce','nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'realtors';

    $full_name = sanitize_text_field($_POST['full_name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    if (!$full_name || !$email) wp_send_json_error('Full Name and Email are required');

    // Check if email already exists in Realtors
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE email = %s AND deleted_at IS NULL",
        $email
    ));
    if ($existing > 0) wp_send_json_error('A realtor with this email already exists');

    if (email_exists($email)) wp_send_json_error('A WordPress user with this email already exists');

    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $agency_name = sanitize_text_field($_POST['agency_name'] ?? '');
    $license_number = sanitize_text_field($_POST['license_number'] ?? '');
    $rating_avg = floatval($_POST['rating_avg'] ?? 0);

    // Upload profile picture
    $profile_url = null;
    if (!empty($_FILES['profile_picture']['name'])) {
        require_once(ABSPATH.'wp-admin/includes/file.php');
        require_once(ABSPATH.'wp-admin/includes/media.php');
        require_once(ABSPATH.'wp-admin/includes/image.php');
        $upload = wp_handle_upload($_FILES['profile_picture'], ['test_form'=>false]);
        if (isset($upload['error'])) wp_send_json_error('Upload Error: '.$upload['error']);
        $profile_url = esc_url_raw($upload['url']);
    }

    $wpdb->query('START TRANSACTION');
    try {
        $password = wp_generate_password(12, false);
        $user_id = wp_create_user($email, $password, $email);
        if (is_wp_error($user_id)) throw new Exception('WP User creation failed: '.$user_id->get_error_message());

        wp_update_user([
            'ID' => $user_id,
            'display_name' => $full_name,
            'first_name' => $full_name
        ]);

        $wp_user = new WP_User($user_id);
        $wp_user->set_role('realtor');

        $data = [
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'agency_name' => $agency_name,
            'license_number' => $license_number,
            'rating_avg' => $rating_avg,
            'profile_picture' => $profile_url,
            'user_id' => $user_id,
            'created_at' => current_time('mysql'),
            'created_by' => get_current_user_id()
        ];

        $inserted = $wpdb->insert($table, $data);
        if (!$inserted) throw new Exception('Could not create realtor. DB error: ' . $wpdb->last_error);

        $wpdb->query('COMMIT');
        wp_send_json_success(['realtor_id'=>$wpdb->insert_id, 'message'=>'Realtor created successfully']);
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        if (isset($user_id) && $user_id) wp_delete_user($user_id);
        wp_send_json_error($e->getMessage());
    }
}
add_action('wp_ajax_create_realtor_ajax','rt_create_realtor_ajax');

// =====================
// Update Realtor
// =====================
function rt_update_realtor_ajax() {
    rt_realtor_current_user_required();
    check_ajax_referer('am_realtor_edit_nonce','nonce');

    $realtor_id = intval($_POST['realtor_id'] ?? 0);
    if (!$realtor_id) wp_send_json_error('Missing realtor ID');

    global $wpdb;
    $table = $wpdb->prefix . 'realtors';
    $realtor = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE realtor_id=%d", $realtor_id));
    if (!$realtor) wp_send_json_error('Realtor not found');

    $full_name = sanitize_text_field($_POST['full_name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $agency_name = sanitize_text_field($_POST['agency_name'] ?? '');
    $license_number = sanitize_text_field($_POST['license_number'] ?? '');
    $rating_avg = floatval($_POST['rating_avg'] ?? 0);

    $profile_url = $realtor->profile_picture;
    if (!empty($_FILES['profile_picture']['name'])) {
        require_once(ABSPATH.'wp-admin/includes/file.php');
        require_once(ABSPATH.'wp-admin/includes/media.php');
        require_once(ABSPATH.'wp-admin/includes/image.php');
        $upload = wp_handle_upload($_FILES['profile_picture'], ['test_form'=>false]);
        if (isset($upload['error'])) wp_send_json_error('Upload Error: '.$upload['error']);
        $profile_url = esc_url_raw($upload['url']);
    }

    if (!empty($realtor->user_id)) {
        $user_data = [
            'ID' => $realtor->user_id,
            'user_email' => $email,
            'user_login' => $email,
            'display_name' => $full_name
        ];
        wp_update_user($user_data);
    }

    $data = [
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone,
        'agency_name' => $agency_name,
        'license_number' => $license_number,
        'rating_avg' => $rating_avg,
        'profile_picture' => $profile_url,
        'updated_at' => current_time('mysql'),
        'updated_by' => get_current_user_id()
    ];

    $updated = $wpdb->update($table, $data, ['realtor_id'=>$realtor_id]);
    if ($updated !== false) wp_send_json_success('Realtor updated successfully');
    wp_send_json_error('Could not update realtor or nothing changed');
}
add_action('wp_ajax_update_realtor_ajax','rt_update_realtor_ajax');

// =====================
// Delete Realtor (Soft Delete)
// =====================
function rt_delete_realtor_ajax() {
    rt_realtor_current_user_required();
    check_ajax_referer('am_realtor_delete_nonce','nonce');

    $realtor_id = intval($_POST['realtor_id'] ?? 0);
    if (!$realtor_id) wp_send_json_error('Missing realtor ID');

    global $wpdb;
    $table = $wpdb->prefix . 'realtors';
    $realtor = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE realtor_id=%d",$realtor_id));
    if (!$realtor) wp_send_json_error('Realtor not found');

    $deleted = $wpdb->update($table, [
        'deleted_at' => current_time('mysql'),
        'deleted_by' => get_current_user_id()
    ], ['realtor_id'=>$realtor_id]);

    if ($realtor->user_id) {
        wp_update_user([
            'ID' => $realtor->user_id,
            'user_status' => 1
        ]);
    }

    if ($deleted !== false) wp_send_json_success('Realtor deleted successfully');
    wp_send_json_error('Could not delete realtor');
}
add_action('wp_ajax_delete_realtor_ajax','rt_delete_realtor_ajax');

// =====================
// Export Realtors
// =====================
function rt_export_realtors_ajax() {
    rt_realtor_current_user_required();
    check_ajax_referer('am_realtor_export_nonce','nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'realtors';

    $allowed_cols = ['full_name','email','phone','agency_name','license_number','rating_avg','profile_picture','created_at'];
    $columns = json_decode(stripslashes($_POST['columns'] ?? '[]'), true);
    $columns = array_intersect($allowed_cols, $columns);
    if (empty($columns)) $columns = $allowed_cols;

    $scope = sanitize_text_field($_POST['scope'] ?? 'all');
    $where = "WHERE deleted_at IS NULL";
    $params = [];

    if ($scope === 'current' && !empty($_POST['current_ids'])) {
        $current_ids = array_map('intval', $_POST['current_ids']);
        if (!empty($current_ids)) {
            $placeholders = implode(',', array_fill(0, count($current_ids), '%d'));
            $where .= " AND realtor_id IN ($placeholders)";
            $params = $current_ids;
        }
    }

    $select = implode(',', array_map('esc_sql', $columns));
    $sql = !empty($params)
        ? $wpdb->prepare("SELECT {$select} FROM {$table} {$where}", $params)
        : "SELECT {$select} FROM {$table} {$where}";
    $results = $wpdb->get_results($sql, ARRAY_A);

    wp_send_json_success(['realtors' => $results]);
}
add_action('wp_ajax_export_realtors_ajax','rt_export_realtors_ajax');

// =====================
// Import Realtors (CSV only, skip/update/create)
// =====================
function rt_import_realtors_ajax() {
    rt_realtor_current_user_required();

    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'am_realtor_import_nonce')) {
        wp_send_json_error('Security verification failed');
    }

    if (!isset($_FILES['realtors_file']) || empty($_FILES['realtors_file']['tmp_name'])) {
        wp_send_json_error('No file uploaded or file is empty');
    }

    $file = $_FILES['realtors_file']['tmp_name'];
    $filename = $_FILES['realtors_file']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if ($ext !== 'csv') {
        wp_send_json_error('Only CSV files are supported. Please convert Excel to CSV.');
    }

    $realtors = rt_parse_realtor_csv_file($file);
    if (empty($realtors)) wp_send_json_error('No valid data found in CSV file.');

    global $wpdb;
    $table = $wpdb->prefix . 'realtors';
    $current_user_id = get_current_user_id();
    $duplicate_handling = sanitize_text_field($_POST['duplicate_handling'] ?? 'skip');

    $inserted = 0;
    $updated = 0;
    $errors = [];

    foreach ($realtors as $index => $r) {
        try {
            $full_name = sanitize_text_field($r['full_name'] ?? '');
            $email = sanitize_email($r['email'] ?? '');
            $phone = sanitize_text_field($r['phone'] ?? '');
            $agency_name = sanitize_text_field($r['agency_name'] ?? '');
            $license_number = sanitize_text_field($r['license_number'] ?? '');
            $rating_avg = floatval($r['rating_avg'] ?? 0);

            if (!$full_name || !$email || !is_email($email)) {
                $errors[] = "Row ".($index+1).": Missing or invalid full name/email";
                continue;
            }

            $existing_id = $wpdb->get_var($wpdb->prepare(
                "SELECT realtor_id FROM {$table} WHERE email = %s AND deleted_at IS NULL",
                $email
            ));

            if ($existing_id) {
                if ($duplicate_handling === 'update') {
                    $result = $wpdb->update(
                        $table,
                        [
                            'full_name'=>$full_name,
                            'phone'=>$phone,
                            'agency_name'=>$agency_name,
                            'license_number'=>$license_number,
                            'rating_avg'=>$rating_avg,
                            'updated_at'=>current_time('mysql'),
                            'updated_by'=>$current_user_id
                        ],
                        ['realtor_id'=>$existing_id],
                        ['%s','%s','%s','%s','%f','%s','%d'],
                        ['%d']
                    );
                    if ($result !== false) $updated++;
                }
                continue;
            }

            $result = $wpdb->insert(
                $table,
                [
                    'full_name'=>$full_name,
                    'email'=>$email,
                    'phone'=>$phone,
                    'agency_name'=>$agency_name,
                    'license_number'=>$license_number,
                    'rating_avg'=>$rating_avg,
                    'created_at'=>current_time('mysql'),
                    'created_by'=>$current_user_id
                ],
                ['%s','%s','%s','%s','%s','%f','%s','%d']
            );

            if ($result !== false) {
                $inserted++;
                $new_realtor_id = $wpdb->insert_id;

                // Create WP user
                if (!email_exists($email)) {
                    $password = wp_generate_password(12, false);
                    $user_id = wp_create_user($email, $password, $email);
                    if (!is_wp_error($user_id)) {
                        wp_update_user([
                            'ID'=>$user_id,
                            'display_name'=>$full_name,
                            'first_name'=>$full_name
                        ]);
                        $wp_user = new WP_User($user_id);
                        $wp_user->set_role('realtor');

                        $wpdb->update($table, ['user_id'=>$user_id], ['realtor_id'=>$new_realtor_id], ['%d'], ['%d']);
                    }
                }
            } else {
                $errors[] = "Row ".($index+1).": Failed to insert realtor";
            }

        } catch (Exception $e) {
            $errors[] = "Row ".($index+1).": ".$e->getMessage();
        }
    }

    $message = "Import completed. {$inserted} new, {$updated} updated.";
    if (!empty($errors)) $message .= " ".count($errors)." errors occurred.";

    wp_send_json_success([
        'message'=>$message,
        'inserted'=>$inserted,
        'updated'=>$updated,
        'total_processed'=>count($realtors),
        'error_count'=>count($errors),
        'errors'=>array_slice($errors,0,10)
    ]);
}

// =====================
// CSV Parser for Realtors
// =====================
function rt_parse_realtor_csv_file($file_path) {
    $realtors = [];
    if (!file_exists($file_path)) return $realtors;

    if (($handle = fopen($file_path,"r")) !== false) {
        $headers = fgetcsv($handle);
        if ($headers === false) return $realtors;
        $headers = array_map(function($h){ return strtolower(trim(preg_replace('/^\xEF\xBB\xBF/','',$h))); }, $headers);

        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row))) continue;
            if (count($row) !== count($headers)) {
                if (count($row) < count($headers)) $row = array_pad($row, count($headers), '');
                else $row = array_slice($row, 0, count($headers));
            }
            $data = array_combine($headers,$row);
            $data = array_map('trim',$data);
            if (!empty($data['full_name']) && !empty($data['email'])) $realtors[]=$data;
        }
        fclose($handle);
    }
    return $realtors;
}

// =====================
// Register AJAX handler
// =====================
add_action('wp_ajax_import_realtors_ajax','rt_import_realtors_ajax');
