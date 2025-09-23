<?php
/**
 * Astra Child Theme Functions
 * Optimized version with consolidated enqueues, dashboard handling, and security
 */

/**
 * Load parent + child theme styles
 */
function astra_child_enqueue_theme_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', ['parent-style']);
    wp_enqueue_style('dashicons');
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_theme_styles');

/**
 * Enqueue general child theme assets (CSS + JS)
 */
function astra_child_enqueue_assets() {
    $assets = [
        // CSS
        'todo-calendar-css'       => 'assets/css/rt-todo-calendar.css',
        'property-management-css' => 'assets/css/rt-property-management.css',
        'address-book-css'        => 'assets/css/rt-address-book.css',
        'realtor-settings-css'    => 'assets/css/rt-realtor-settings.css',
        'cl-dashboard-css'        => 'assets/css/cl-dashboard.css',
        'all-sticky-notes-css'    => 'assets/css/all-sticky-notes.css',

        // JS
        'todo-calendar-js'        => 'assets/js/rt-todo-calendar.js',
        'property-management-js'  => 'assets/js/rt-property-management.js',
        'address-book-js'         => 'assets/js/rt-address-book.js',
        'realtor-settings-js'     => 'assets/js/rt-realtor-settings.js',
        'all-sticky-notes-js'     => 'assets/js/all-sticky-notes.js',
        'property-upload-js'      => 'assets/js/property-upload.js',
    ];

    foreach ($assets as $handle => $path) {
        $full_path = get_stylesheet_directory() . '/' . $path;
        $uri = get_stylesheet_directory_uri() . '/' . $path;

        if (file_exists($full_path)) {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if ($ext === 'css') {
                wp_enqueue_style($handle, $uri, [], filemtime($full_path));
            } elseif ($ext === 'js') {
                wp_enqueue_script($handle, $uri, ['jquery'], filemtime($full_path), true);
            }
        }
    }

    // Localize scripts
    if (wp_script_is('todo-calendar-js', 'enqueued')) {
        wp_localize_script('todo-calendar-js', 'todoCalendarVars', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('todo_calendar_nonce'),
            'i18n'    => [
                'confirmDelete' => __('Are you sure you want to delete this todo?', 'astra-child'),
                'saving'        => __('Saving...', 'astra-child'),
            ]
        ]);
    }

    if (wp_script_is('address-book-js', 'enqueued')) {
        wp_localize_script('address-book-js', 'propertyDetailsAjax', [
            'ajaxurl' => admin_url('admin-ajax.php')
        ]);
    }

    // Localize property upload script
    if (wp_script_is('property-upload-js', 'enqueued')) {
        wp_localize_script('property-upload-js', 'property_image_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('property_image_nonce'),
        ]);
    }
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_assets');

/**
 * Conditionally enqueue dashboard assets
 */
function mdk_enqueue_dashboard_assets() {
    global $post;
    if (!is_admin() && is_a($post, 'WP_Post')) {
        $dashboard_slugs = ['realtor-dashboard', 'admin-dashboard', 'client-dashboard'];

        if (in_array($post->post_name, $dashboard_slugs)) {
            $assets = [
                'mdk-dashboard-style'  => 'assets/css/rt-dashboard.css',
                'mdk-dashboard-script' => 'assets/js/rt-dashboard.js',
            ];

            foreach ($assets as $handle => $path) {
                $full_path = get_stylesheet_directory() . '/' . $path;
                $uri = get_stylesheet_directory_uri() . '/' . $path;

                if (file_exists($full_path)) {
                    $ext = pathinfo($path, PATHINFO_EXTENSION);
                    if ($ext === 'css') {
                        wp_enqueue_style($handle, $uri, [], filemtime($full_path));
                    } else {
                        wp_enqueue_script($handle, $uri, ['jquery'], filemtime($full_path), true);
                    }
                }
            }
        }
    }
}
add_action('wp_enqueue_scripts', 'mdk_enqueue_dashboard_assets');

/**
 * Ajax: Load property details (modal)
 */
add_action('wp_ajax_get_property_details', 'load_property_details');
add_action('wp_ajax_nopriv_get_property_details', 'load_property_details');
function load_property_details() {
    $template = locate_template('dashboard-templates/rt/rt-property-details-modal.php');
    if ($template) {
        include $template;
    }
    wp_die();
}

/**
 * Include classes
 */
require_once get_stylesheet_directory() . '/includes/class-todo-calendar.php';

/**
 * Initialize Todo Calendar
 */
function astra_child_init_todo_calendar() {
    new Todo_Calendar();
}
add_action('init', 'astra_child_init_todo_calendar');

/**
 * Create dashboard pages (runs on theme activation)
 */
function mdk_create_dashboard_pages() {
    $pages = [
        'admin-dashboard'   => ['title' => 'Admin Dashboard',   'content' => '[mdk_admin_dashboard]'],
        'realtor-dashboard' => ['title' => 'Realtor Dashboard', 'content' => '[mdk_realtor_dashboard]'],
        'client-dashboard'  => ['title' => 'Client Dashboard',  'content' => '[mdk_client_dashboard]'],
    ];

    foreach ($pages as $slug => $page) {
        if (!get_page_by_path($slug)) {
            wp_insert_post([
                'post_title'   => $page['title'],
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => $page['content'],
            ]);
        }
    }
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'mdk_create_dashboard_pages');

/**
 * Secure dashboard template loader
 */
function mdk_load_dashboard_template($template) {
    global $post;
    if (!$post || !is_page()) return $template;

    $dashboard_map = [
        'admin-dashboard'   => ['template' => 'admin',   'capability' => 'manage_options'],
        'realtor-dashboard' => ['template' => 'realtor', 'capability' => 'edit_properties'],
        'client-dashboard'  => ['template' => 'client',  'capability' => 'read_properties'],
    ];

    if (isset($dashboard_map[$post->post_name])) {
        $dashboard = $dashboard_map[$post->post_name];

        if (!current_user_can($dashboard['capability'])) {
            wp_redirect(wp_login_url(get_permalink()));
            exit;
        }

        $template_path = locate_template([
            "dashboard-templates/{$dashboard['template']}-dashboard.php",
            "dashboard-templates/default-dashboard.php"
        ]);

        if ($template_path) return $template_path;
    }

    return $template;
}
add_filter('template_include', 'mdk_load_dashboard_template', 99);

/**
 * Unified dashboard shortcode handler
 */
function mdk_dashboard_shortcode($atts, $content = null, $tag = '') {
    $role = str_replace(['mdk_', '_dashboard'], '', $tag);

    $capabilities = [
        'admin'   => 'manage_options',
        'realtor' => 'edit_properties',
        'client'  => 'read_properties',
    ];

    if (!isset($capabilities[$role])) {
        return '<div class="mdk-alert">Invalid dashboard role.</div>';
    }

    if (!is_user_logged_in() || !current_user_can($capabilities[$role])) {
        return mdk_dashboard_login_message(ucfirst($role));
    }

    ob_start();

    if ($role === 'client') {
        $template = locate_template("dashboard-templates/cl/{$role}-dashboard.php");
    } 
    elseif ($role === 'realtor') {
        $template = locate_template("dashboard-templates/rt/{$role}-dashboard.php");
    }
    elseif ($role === 'admin') {
        $template = locate_template("dashboard-templates/am/admin-dashboard.php");
    }

    if ($template) {
        include $template;
    } else {
        echo '<div class="mdk-alert">Dashboard template not found.</div>';
    }

    return ob_get_clean();
}
add_shortcode('mdk_realtor_dashboard', 'mdk_dashboard_shortcode');
add_shortcode('mdk_client_dashboard', 'mdk_dashboard_shortcode');
add_shortcode('mdk_admin_dashboard', 'mdk_dashboard_shortcode');

/**
 * Login message helper
 */
function mdk_dashboard_login_message($role) {
    return sprintf(
        '<div class="mdk-alert">%s <a href="%s">%s</a></div>',
        sprintf(__('You need to be logged in as a %s to view this page.', 'astra-child'), esc_html($role)),
        esc_url(wp_login_url()),
        __('Login here', 'astra-child')
    );
}

/**
 * Redirect users after login based on role
 */
function mdk_login_redirect($redirect_to, $request, $user) {
    if (!isset($user->roles) || !is_array($user->roles)) {
        return $redirect_to;
    }

    $role = $user->roles[0];

    $dashboards = [
        'admin'   => home_url('/am/admin-dashboard/'),
        'realtor' => home_url('/rt/realtor-dashboard/'),
        'client'  => home_url('/cl/client-dashboard/'),
    ];

    if (isset($dashboards[$role])) {
        return $dashboards[$role];
    }

    return $redirect_to;
}
add_filter('login_redirect', 'mdk_login_redirect', 10, 3);

/**
 * Add body class for hiding header/footer for certain roles
 */
function add_user_role_body_class($classes) {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        if (array_intersect(['realtor', 'client', 'admin'], $user->roles)) {
            $classes[] = 'hide-header-footer';
        }
    }
    return $classes;
}
add_filter('body_class', 'add_user_role_body_class');

/**
 * Add custom user roles
 */
function mdk_add_user_roles() {
    add_role('admin', 'Admin', [
        'read'            => true,
        'manage_options'  => true,
    ]);

    add_role('realtor', 'Realtor', [
        'read'            => true,
        'edit_properties' => true,
        'edit_clients'    => true,
    ]);

    add_role('client', 'Client', [
        'read'            => true,
        'read_properties' => true,
    ]);
}
add_action('init', 'mdk_add_user_roles');

/**
 * Deactivation cleanup
 */
function mdk_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'mdk_deactivate');

/* ===========================
   Profile Updating Section
   =========================== */

// Enqueue scripts and styles
function enqueue_profile_scripts() {
    wp_enqueue_script('profile-ajax', get_template_directory_uri() . '/js/profile-ajax.js', array('jquery'), null, true);
    wp_localize_script('profile-ajax', 'profile_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('profile_ajax_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_profile_scripts');

// Handle profile data loading
function load_profile_data() {
    if (!wp_verify_nonce($_POST['nonce'], 'profile_ajax_nonce')) {
        wp_die('Security check failed');
    }
    
    $user_id = get_current_user_id();
    if ($user_id == 0) {
        wp_send_json_error('User not logged in');
    }
    
    $user_info = get_userdata($user_id);
    $broker_number = get_user_meta($user_id, 'broker_number', true);
    $company_name = get_user_meta($user_id, 'company_name', true);
    $profile_picture = get_user_meta($user_id, 'profile_picture', true);
    $default_picture = get_template_directory_uri() . '/images/default-avatar.jpg';
    
    $response = array(
        'full_name' => $user_info->display_name,
        'email' => $user_info->user_email,
        'broker_number' => $broker_number,
        'company_name' => $company_name,
        'profile_picture' => $profile_picture ? $profile_picture : $default_picture
    );
    
    wp_send_json_success($response);
}
add_action('wp_ajax_load_profile_data', 'load_profile_data');

// Handle profile data saving
function save_profile_data() {
    if (!wp_verify_nonce($_POST['nonce'], 'profile_ajax_nonce')) {
        wp_die('Security check failed');
    }
    
    $user_id = get_current_user_id();
    if ($user_id == 0) {
        wp_send_json_error('User not logged in');
    }
    
    if (isset($_POST['broker_number'])) {
        update_user_meta($user_id, 'broker_number', sanitize_text_field($_POST['broker_number']));
    }
    
    if (isset($_POST['company_name'])) {
        update_user_meta($user_id, 'company_name', sanitize_text_field($_POST['company_name']));
    }
    
    wp_send_json_success('Profile updated successfully');
}
add_action('wp_ajax_save_profile_data', 'save_profile_data');

// Handle profile picture upload
function upload_profile_picture() {
    if (!wp_verify_nonce($_POST['nonce'], 'profile_ajax_nonce')) {
        wp_die('Security check failed');
    }
    
    $user_id = get_current_user_id();
    if ($user_id == 0) {
        wp_send_json_error('User not logged in');
    }
    
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    
    $uploadedfile = $_FILES['profile_picture'];
    $upload_overrides = array('test_form' => false);
    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
    
    if ($movefile && !isset($movefile['error'])) {
        update_user_meta($user_id, 'profile_picture', $movefile['url']);
        wp_send_json_success(array('url' => $movefile['url']));
    } else {
        wp_send_json_error($movefile['error']);
    }
}
add_action('wp_ajax_upload_profile_picture', 'upload_profile_picture');

/**
 * Add custom user fields
 */
function add_custom_user_fields($user) {
    ?>
    <h3>Broker Information</h3>
    <table class="form-table">
        <tr>
            <th><label for="broker_number">Broker Number</label></th>
            <td>
                <input type="text" name="broker_number" id="broker_number" value="<?php echo esc_attr(get_user_meta($user->ID, 'broker_number', true)); ?>" class="regular-text" /><br />
                <span class="description">Enter your broker license number.</span>
            </td>
        </tr>
        <tr>
            <th><label for="company_name">Company Name</label></th>
            <td>
                <input type="text" name="company_name" id="company_name" value="<?php echo esc_attr(get_user_meta($user->ID, 'company_name', true)); ?>" class="regular-text" /><br />
                <span class="description">Enter your company name.</span>
            </td>
        </tr>
        <tr>
            <th><label for="profile_picture">Profile Picture URL</label></th>
            <td>
                <input type="text" name="profile_picture" id="profile_picture" value="<?php echo esc_attr(get_user_meta($user->ID, 'profile_picture', true)); ?>" class="regular-text" /><br />
                <span class="description">Enter the URL of your profile picture.</span>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'add_custom_user_fields');
add_action('edit_user_profile', 'add_custom_user_fields');

/**
 * Save custom user fields
 */
function save_custom_user_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    if (isset($_POST['broker_number'])) {
        update_user_meta($user_id, 'broker_number', sanitize_text_field($_POST['broker_number']));
    }
    
    if (isset($_POST['company_name'])) {
        update_user_meta($user_id, 'company_name', sanitize_text_field($_POST['company_name']));
    }
    
    if (isset($_POST['profile_picture'])) {
        update_user_meta($user_id, 'profile_picture', esc_url_raw($_POST['profile_picture']));
    }
}
add_action('personal_options_update', 'save_custom_user_fields');
add_action('edit_user_profile_update', 'save_custom_user_fields');

add_action('init', function() {
    if(isset($_GET['export_addressbook']) && $_GET['export_addressbook'] == '1') {
        $format = isset($_GET['format']) ? $_GET['format'] : 'csv';

        // Example data — replace with your dynamic table data
        $clients = [
            ['sl'=>'01','name'=>'Afsana Hamid Mim','email'=>'Support.info@gmail.com','phone'=>'999-888-666','address'=>'New York'],
            ['sl'=>'02','name'=>'John D. Smith','email'=>'john.smith@business.com','phone'=>'555-123-4567','address'=>'Los Angeles, CA'],
            ['sl'=>'03','name'=>'Emily Carter','email'=>'emily.carter@example.com','phone'=>'777-222-9999','address'=>'Chicago, IL'],
            ['sl'=>'04','name'=>'Michael Johnson','email'=>'michael.johnson@example.com','phone'=>'888-333-4444','address'=>'Houston, TX'],
            ['sl'=>'05','name'=>'Sophia Williams','email'=>'sophia.williams@example.com','phone'=>'999-555-1111','address'=>'San Francisco, CA'],
            ['sl'=>'06','name'=>'David Brown','email'=>'david.brown@example.com','phone'=>'444-777-2222','address'=>'Miami, FL'],
            ['sl'=>'07','name'=>'Olivia Martinez','email'=>'olivia.martinez@example.com','phone'=>'333-666-8888','address'=>'Seattle, WA'],
            ['sl'=>'08','name'=>'James Lee','email'=>'james.lee@example.com','phone'=>'222-444-5555','address'=>'Boston, MA'],
            ['sl'=>'09','name'=>'Isabella Thompson','email'=>'isabella.thompson@example.com','phone'=>'555-888-9999','address'=>'Denver, CO'],
            ['sl'=>'10','name'=>'William Garcia','email'=>'william.garcia@example.com','phone'=>'777-999-0000','address'=>'Phoenix, AZ'],
        ];

        // CSV Export
        if($format == 'csv'){
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="address_book.csv"');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['#SL', 'Client Name', 'Email', 'Phone Number', 'Address']);
            foreach($clients as $c){
                fputcsv($output, [$c['sl'],$c['name'],$c['email'],$c['phone'],$c['address']]);
            }
            fclose($output);
            exit;
        }

        // JSON Export
        if($format == 'json'){
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="address_book.json"');
            echo json_encode($clients, JSON_PRETTY_PRINT);
            exit;
        }
    }
});

// Redirect wp-login.php to custom login page
add_action('init', function() {
    $login_page = home_url('/login/');
    $request_uri = $_SERVER['REQUEST_URI'];

    if (strpos($request_uri, 'wp-login.php') !== false && !is_user_logged_in()) {
        wp_redirect($login_page);
        exit;
    }
});

// WordPress dashboard access block
function restrict_specific_users_from_wpadmin() {
    // Get current user
    $user = wp_get_current_user();

    // Redirect based on username
    if ( is_admin() && !defined('DOING_AJAX') ) {
        switch ( $user->user_login ) {
            case 'admin':
                wp_redirect(site_url('/am/admin-dashboard/'));
                exit;
            case 'anis':
                wp_redirect(site_url('/rt/realtor-dashboard/'));
                exit;
            case 'sadi':
                wp_redirect(site_url('/cl/client-dashboard/'));
                exit;
        }
    }
}
add_action('admin_init', 'restrict_specific_users_from_wpadmin');

// ==============================
// 2️⃣ Create DB Table
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
// 3️⃣ Fetch API & Save to DB
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
// 4️⃣ AJAX Upload Property Image
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
// 5️⃣ Shortcode: Property Cards with Upload Icon
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





