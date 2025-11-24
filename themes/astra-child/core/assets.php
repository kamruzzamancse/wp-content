<?php
if (!defined('ABSPATH')) exit;

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
// General CSS/JS Enqueues
// ======================
function astra_child_enqueue_assets() {

    // -----------------------------
    // CSS Files
    // -----------------------------
    $styles = [
        'property-management-css' => 'assets/css/rt-property-management.css',
        'address-book-css'        => 'assets/css/rt-address-book.css',
        'realtor-settings-css'    => 'assets/css/rt-realtor-settings.css',
        'cl-dashboard-css'        => 'assets/css/cl-dashboard.css',
        'all-sticky-notes-css'    => 'assets/css/all-sticky-notes.css',
    ];

    foreach ($styles as $handle => $path) {
        $file = get_stylesheet_directory() . '/' . $path;
        $uri  = get_stylesheet_directory_uri() . '/' . $path;

        if (file_exists($file)) {
            wp_enqueue_style($handle, $uri, [], filemtime($file));
        }
    }

    // -----------------------------
    // JS Files
    // -----------------------------
    $scripts = [
        'property-management-js' => 'assets/js/rt-property-management.js',
        'address-book-js'        => 'assets/js/rt-address-book.js',
        'realtor-settings-js'    => 'assets/js/rt-realtor-settings.js',
        'all-sticky-notes-js'    => 'assets/js/all-sticky-notes.js',
        'property-upload-js'     => 'assets/js/property-upload.js',
    ];

    foreach ($scripts as $handle => $path) {
        $file = get_stylesheet_directory() . '/' . $path;
        $uri  = get_stylesheet_directory_uri() . '/' . $path;

        if (file_exists($file)) {
            wp_enqueue_script($handle, $uri, ['jquery'], filemtime($file), true);
        }
    }

    // -----------------------------
    // Localize Scripts
    // -----------------------------
    if (wp_script_is('address-book-js', 'enqueued')) {
        wp_localize_script('address-book-js', 'propertyDetailsAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);
    }

    if (wp_script_is('property-upload-js', 'enqueued')) {
        wp_localize_script('property-upload-js', 'property_upload_vars', [
            'edit' => [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('property_edit_nonce'),
            ],
            'image' => [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('property_image_nonce'),
            ],
        ]);
    }

    if (wp_script_is('all-sticky-notes-js', 'enqueued')) {
        wp_localize_script('all-sticky-notes-js', 'stickyNotesData', [
            'userId'  => get_current_user_id(),
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);
    }
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_assets');
