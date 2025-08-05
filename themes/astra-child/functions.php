<?php
/**
 * Child Theme Functions
 * Enhanced with robust dashboard functionality and security
 */

// Enqueue Parent and Child Theme Styles
function astra_child_style() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'));
    
    // Load Dashicons for dashboard
    wp_enqueue_style('dashicons');
}
add_action('wp_enqueue_scripts', 'astra_child_style');

/**
 * Enqueue Todo Calendar assets
 */
function astra_child_enqueue_todo_calendar() {
    // CSS
    wp_enqueue_style(
        'todo-calendar-css',
        get_stylesheet_directory_uri() . '/assets/css/todo-calendar.css',
        array(),
        filemtime(get_stylesheet_directory() . '/assets/css/todo-calendar.css')
    );
    
    // JavaScript
    wp_enqueue_script(
        'todo-calendar-js',
        get_stylesheet_directory_uri() . '/assets/js/todo-calendar.js',
        array('jquery'),
        filemtime(get_stylesheet_directory() . '/assets/js/todo-calendar.js'),
        true
    );
    
    // Localize script
    wp_localize_script(
        'todo-calendar-js',
        'todoCalendarVars',
        array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('todo_calendar_nonce'),
            'i18n' => array(
                'confirmDelete' => __('Are you sure you want to delete this todo?', 'astra-child'),
                'saving' => __('Saving...', 'astra-child')
            )
        )
    );
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_todo_calendar');

/**
 * Enqueue Property Management CSS
 */
function astra_child_enqueue_property_management_css() {
    wp_enqueue_style(
        'property-management-css',
        get_stylesheet_directory_uri() . '/assets/css/property-management.css',
        array(),
        filemtime(get_stylesheet_directory() . '/assets/css/property-management.css')
    );
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_property_management_css');

/**
 * Enqueue Property Management JavaScript
 */
function astra_child_enqueue_property_management_js() {
    wp_enqueue_script(
        'property-management-js',
        get_stylesheet_directory_uri() . '/assets/js/property-management.js',
        array(),
        filemtime(get_stylesheet_directory() . '/assets/js/property-management.js'),
        true
    );
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_property_management_js');


/**
 * Include Todo Calendar class
 */
require_once get_stylesheet_directory() . '/includes/class-todo-calendar.php';
//require_once get_stylesheet_directory() . '/includes/class-upload-document.php';

/**
 * Initialize Todo Calendar
 */
function astra_child_init_todo_calendar() {
    new Todo_Calendar();
}
add_action('init', 'astra_child_init_todo_calendar');

// Conditionally Enqueue Dashboard Assets
function mdk_enqueue_dashboard_assets() {
    global $post;

    if (!is_admin() && is_a($post, 'WP_Post')) {
        $dashboard_slugs = ['realtor-dashboard', 'admin-dashboard', 'client-dashboard'];

        if (in_array($post->post_name, $dashboard_slugs)) {
            // Dashboard CSS - Check if file exists first
            $css_path = get_stylesheet_directory() . '/assets/css/dashboard.css';
            if (file_exists($css_path)) {
                // Simplified version without filemtime
                wp_enqueue_style(
                    'mdk-dashboard-style',
                    get_stylesheet_directory_uri() . '/assets/css/dashboard.css',
                    array(),
                    '1.0', // Static version number
                    'all'
                );
            }

            // Dashboard JS - Check if file exists first
            $js_path = get_stylesheet_directory() . '/assets/js/dashboard.js';
            if (file_exists($js_path)) {
                wp_enqueue_script(
                    'mdk-dashboard-script',
                    get_stylesheet_directory_uri() . '/assets/js/dashboard.js',
                    array('jquery'),
                    filemtime($js_path),
                    true
                );
            }
        }
    }
}
add_action('wp_enqueue_scripts', 'mdk_enqueue_dashboard_assets');

// Dashboard Page Creator
function mdk_create_dashboard_pages() {
    $pages = array(
        'admin-dashboard' => array(
            'title' => 'Admin Dashboard',
            'content' => '[mdk_admin_dashboard]'
        ),
        'realtor-dashboard' => array(
            'title' => 'Realtor Dashboard',
            'content' => '[mdk_realtor_dashboard]'
        ),
        'client-dashboard' => array(
            'title' => 'Client Dashboard',
            'content' => '[mdk_client_dashboard]'
        )
    );

    foreach ($pages as $slug => $page) {
        if (!get_page_by_path($slug)) {
            wp_insert_post(array(
                'post_title'   => $page['title'],
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => $page['content']
            ));
        }
    }
    
    // Flush rewrite rules after creation
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'mdk_create_dashboard_pages');

// Template Loader with Enhanced Security
function mdk_load_dashboard_template($template) {
    global $post;

    if (!$post || !is_page()) {
        return $template;
    }

    $dashboard_map = array(
        'admin-dashboard'   => array('template' => 'admin', 'capability' => 'manage_options'),
        'realtor-dashboard' => array('template' => 'realtor', 'capability' => 'edit_properties'),
        'client-dashboard'  => array('template' => 'client', 'capability' => 'read_properties')
    );

    if (array_key_exists($post->post_name, $dashboard_map)) {
        $dashboard = $dashboard_map[$post->post_name];
        
        // Check user capabilities
        if (!current_user_can($dashboard['capability'])) {
            wp_redirect(wp_login_url(get_permalink()));
            exit;
        }

        // Locate template file
        $template_path = locate_template(array(
            "dashboard-templates/{$dashboard['template']}-dashboard.php",
            "dashboard-templates/default-dashboard.php"
        ));

        if ($template_path) {
            return $template_path;
        }
    }

    return $template;
}
add_filter('template_include', 'mdk_load_dashboard_template', 99);

// Dashboard Shortcodes with Caching
function mdk_realtor_dashboard_shortcode($atts = array()) {
    // Check user capabilities
    if (!is_user_logged_in() || !current_user_can('edit_properties')) {
        return mdk_dashboard_login_message('Realtor');
    }

    // Start output buffering
    ob_start();
    
    // Include template file
    $template = locate_template('dashboard-templates/realtor-dashboard.php');
    if ($template) {
        include $template;
    } else {
        echo '<div class="mdk-alert">Dashboard template not found.</div>';
    }
    
    return ob_get_clean();
}
add_shortcode('mdk_realtor_dashboard', 'mdk_realtor_dashboard_shortcode');

function mdk_client_dashboard_shortcode($atts = array()) {
    // Check user capabilities
    if (!is_user_logged_in() || !current_user_can('read_properties')) {
        return mdk_dashboard_login_message('Client');
    }

    // Start output buffering
    ob_start();
    
    // Include template file
    $template = locate_template('dashboard-templates/client-dashboard.php');
    if ($template) {
        include $template;
    } else {
        echo '<div class="mdk-alert">Dashboard template not found.</div>';
    }
    
    return ob_get_clean();
}
add_shortcode('mdk_client_dashboard', 'mdk_client_dashboard_shortcode');

// Helper function for login messages
function mdk_dashboard_login_message($role) {
    return sprintf(
        '<div class="mdk-alert">%s <a href="%s">%s</a></div>',
        sprintf(__('You need to be logged in as a %s to view this page.', 'astra-child'), $role),
        esc_url(wp_login_url()),
        __('Login here', 'astra-child')
    );
}

// Dashboard Data Helper
function mdk_get_dashboard_data($user_id, $role) {
    $data = array(
        'user' => wp_get_current_user(),
        'stats' => array()
    );

    switch ($role) {
        case 'realtor':
            $data['stats'] = array(
                'active_properties' => get_user_meta($user_id, 'active_properties', true) ?: 0,
                'tasks_sent' => get_user_meta($user_id, 'tasks_sent', true) ?: 0,
                'awaiting_response' => get_user_meta($user_id, 'awaiting_response', true) ?: 0,
                'clients' => get_posts(array(
                    'post_type' => 'client',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => 'assigned_realtor',
                            'value' => $user_id,
                            'compare' => '='
                        )
                    )
                ))
            );
            break;
            
        case 'client':
            // Add client-specific data
            break;
            
        case 'administrator':
            // Add admin-specific data
            break;
    }

    return apply_filters('mdk_dashboard_data', $data, $user_id, $role);
}

// Flush rewrite rules on deactivation
function mdk_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'mdk_deactivate');


/**
 * Load dashboard data for the current user
 */
function load_realtor_dashboard_data() {
    if (!is_user_logged_in()) {
        return false;
    }

    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    return array(
        'active_properties' => get_user_meta($user_id, 'active_properties', true) ?: 0,
        'tasks_sent' => get_user_meta($user_id, 'tasks_sent', true) ?: 0,
        'awaiting_response' => get_user_meta($user_id, 'awaiting_response', true) ?: 0,
        'active_clients' => get_posts(array(
            'post_type' => 'client',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'assigned_realtor',
                    'value' => $user_id,
                    'compare' => '='
                )
            )
        )),
        'current_user' => $current_user,
        'user_id' => $user_id
    );
}

function mdk_add_user_roles() {
    add_role('realtor', 'Realtor', array(
        'read' => true,
        'edit_properties' => true,
        'edit_clients' => true
    ));
}
add_action('init', 'mdk_add_user_roles');

// for header and foother disply/hide
function add_user_role_body_class($classes) {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        if (in_array('realtor', $user->roles) || in_array('client', $user->roles)) {
            $classes[] = 'hide-header-footer';
        }
    }
    return $classes;
}
add_filter('body_class', 'add_user_role_body_class');
