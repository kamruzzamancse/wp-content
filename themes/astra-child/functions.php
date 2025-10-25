<?php
/**
 * Astra Child Theme Functions
 * Updated with proper client edit AJAX enqueue
 */

if (!defined('ABSPATH')) exit; // Prevent direct access

// ======================
// Enqueue Parent + Child Styles
// ======================
function astra_child_enqueue_theme_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', ['parent-style']);
    wp_enqueue_style('dashicons');
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_theme_styles');

// ======================
// Include Required Files
// ======================
require_once get_stylesheet_directory() . '/includes/cl-client-profile.php';
require_once get_stylesheet_directory() . '/includes/rt-realtor-profile.php';
require_once get_stylesheet_directory() . '/includes/rt-client-create.php';
require_once get_stylesheet_directory() . '/includes/rt-client-edit.php';
require_once get_stylesheet_directory() . '/includes/rt-client-delete.php';
require_once get_stylesheet_directory() . '/includes/rt-leads-to-client.php';
require_once get_stylesheet_directory() . '/includes/rt-document-type.php';
require_once get_stylesheet_directory() . '/includes/rt-documents.php';
require_once get_stylesheet_directory() . '/includes/rt-settings-password.php';
require_once get_stylesheet_directory() . '/includes/rentcast-properties.php';
require_once get_stylesheet_directory() . '/includes/all-paginations.php';

// ======================
// Conditional Script Enqueues
// ======================
function mdk_enqueue_profile_scripts() {

    if (!isset($_GET['tab'])) return;

    $tab = sanitize_text_field($_GET['tab']);

    // ------------------------------
    // Realtor Profile (View/Edit)
    // ------------------------------
    if (in_array($tab, ['rt-settings-pi', 'rt-settings-pi-edit'], true)) {
        wp_enqueue_script(
            'rt-profile-script',
            get_stylesheet_directory_uri() . '/assets/js/rt-realtor-profile.js',
            ['jquery'],
            filemtime(get_stylesheet_directory() . '/assets/js/rt-realtor-profile.js'),
            true
        );
        wp_localize_script('rt-profile-script', 'rt_profile_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('rt_profile_nonce'),
        ]);
    }

    // ------------------------------
    // Client Profile (View/Edit)
    // ------------------------------
    if (in_array($tab, ['cl-settings-pi', 'cl-settings-pi-edit'], true)) {
        wp_enqueue_script(
            'cl-profile-script',
            get_stylesheet_directory_uri() . '/assets/js/cl-client-profile.js',
            ['jquery'],
            filemtime(get_stylesheet_directory() . '/assets/js/cl-client-profile.js'),
            true
        );
        wp_localize_script('cl-profile-script', 'cl_profile_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('cl_profile_nonce'),
        ]);
    }

    // ------------------------------
    // Address Book Page (Create + Edit + Delete Clients)
    // ------------------------------
    if ($tab === 'rt-tab-address-book') {

        // Client Create Script
        wp_enqueue_script(
            'cl-client-create-script',
            get_stylesheet_directory_uri() . '/assets/js/rt-client-create.js',
            ['jquery'],
            filemtime(get_stylesheet_directory() . '/assets/js/rt-client-create.js'),
            true
        );
        wp_localize_script('cl-client-create-script', 'cl_client_create_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('cl_client_create_nonce'),
        ]);

        // Client Edit Script
        wp_enqueue_script(
            'cl-client-edit-script',
            get_stylesheet_directory_uri() . '/assets/js/rt-client-edit.js',
            ['jquery'],
            filemtime(get_stylesheet_directory() . '/assets/js/rt-client-edit.js'),
            true
        );
        wp_localize_script('cl-client-edit-script', 'cl_client_edit_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('cl_client_edit_nonce'),
        ]);

    }

    if ($tab === 'documents') {
        // -------------------------
        // Document Type Script
        // -------------------------
        wp_enqueue_script(
            'rt-document-type-script', // handle
            get_stylesheet_directory_uri() . '/assets/js/rt-document-type.js',
            ['jquery'],
            filemtime(get_stylesheet_directory() . '/assets/js/rt-document-type.js'),
            true
        );

        wp_localize_script('rt-document-type-script', 'rt_doc_type_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('rt_doc_type_nonce'),
        ]);

        // -------------------------
        // Documents Script
        // -------------------------
        wp_enqueue_script(
            'rt-documents-script', // new handle
            get_stylesheet_directory_uri() . '/assets/js/rt-documents.js',
            ['jquery'],
            filemtime(get_stylesheet_directory() . '/assets/js/rt-documents.js'),
            true
        );

        wp_localize_script('rt-documents-script', 'rt_doc_ajax', [ // new object
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('rt_doc_nonce'),
        ]);
    }

    wp_enqueue_script(
        'rt-client-actions',
        get_stylesheet_directory_uri() . '/assets/js/rt-client-actions.js',
        ['jquery'],
        filemtime(get_stylesheet_directory() . '/assets/js/rt-client-actions.js'),
        true
    );

    wp_localize_script('rt-client-actions', 'clientActionData', [
        'ajax_url'      => admin_url('admin-ajax.php'),
        'edit_nonce'    => wp_create_nonce('cl_client_edit_nonce'),
        'delete_nonce'  => wp_create_nonce('cl_client_delete_nonce'),
        'convert_nonce' => wp_create_nonce('convert_lead_nonce'),
        'default_avatar'=> esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg')
    ]);

    if ($tab === 'rt-tab-address-book') {
        wp_enqueue_script(
            'address-book-actions',
            get_stylesheet_directory_uri() . '/assets/js/rt-address-book-actions.js',
            ['jquery'],
            filemtime(get_stylesheet_directory() . '/assets/js/rt-address-book-actions.js'),
            true
        );

        wp_localize_script('rt-address-book-actions', 'abClientData', [
            'ajax_url'      => admin_url('admin-ajax.php'),
            'edit_nonce'    => wp_create_nonce('cl_client_edit_nonce'),
            'delete_nonce'  => wp_create_nonce('cl_client_delete_nonce'),
            'default_avatar'=> esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'),
        ]);
    }

}
add_action('wp_enqueue_scripts', 'mdk_enqueue_profile_scripts');

// ======================
// Password Update Script Enqueue
// ======================
function rt_enqueue_password_script() {
    // Load script only on the "Change Password" tab
    if (!isset($_GET['tab']) || $_GET['tab'] !== 'rt-settings-cp') return;

    wp_enqueue_script(
        'rt-password-script',
        get_stylesheet_directory_uri() . '/assets/js/rt-settings-password.js',
        ['jquery'],
        filemtime(get_stylesheet_directory() . '/assets/js/rt-settings-password.js'),
        true
    );

    wp_localize_script('rt-password-script', 'rt_password_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('rt_password_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'rt_enqueue_password_script');

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
        'all-paginations-js'      => 'assets/js/all-paginations.js',
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

    // Todo Calendar
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

    // Address Book
    if (wp_script_is('address-book-js', 'enqueued')) {
        wp_localize_script('address-book-js', 'propertyDetailsAjax', [
            'ajaxurl' => admin_url('admin-ajax.php')
        ]);
    }

    // Property Upload
    if (wp_script_is('property-upload-js', 'enqueued')) {
        wp_localize_script('property-upload-js', 'property_image_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('property_image_nonce'),
        ]);
    }

    // Sticky Notes
    if (wp_script_is('all-sticky-notes-js', 'enqueued')) {
        wp_localize_script('all-sticky-notes-js', 'stickyNotesData', [
            'userId'  => get_current_user_id(),
            'ajaxurl' => admin_url('admin-ajax.php')
        ]);
    }

    // All Paginations JS
    if (wp_script_is('all-paginations-js', 'enqueued')) {
        wp_localize_script('all-paginations-js', 'paginationData', [
            'clients_nonce' => wp_create_nonce('clients_pagination_nonce'),
            'leads_nonce'   => wp_create_nonce('leads_pagination_nonce'),
            'ajaxurl'       => admin_url('admin-ajax.php')
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
