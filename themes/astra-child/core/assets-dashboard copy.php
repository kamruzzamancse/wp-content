<?php
if (!defined('ABSPATH')) exit;

/**
 * ==========================
 * Conditional JS & AJAX Scripts
 * ==========================
 */

// ======================
// Reply Docs (Client Dashboard)
// ======================
function enqueue_rt_reply_docs_scripts() {
    if (is_page('client-dashboard') && isset($_GET['tab']) && $_GET['tab'] === 'documents') {
        $script_path = get_stylesheet_directory() . '/assets/js/cl-reply-docs.js';
        $script_uri  = get_stylesheet_directory_uri() . '/assets/js/cl-reply-docs.js';

        wp_enqueue_script(
            'cl-reply-docs-js',
            $script_uri,
            ['jquery'],
            file_exists($script_path) ? filemtime($script_path) : '1.0.0',
            true
        );

        wp_localize_script('cl-reply-docs-js', 'rtReplyDocsAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('cl_reply_docs_nonce'),
        ]);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_rt_reply_docs_scripts');

// ======================
// Assign Task (Realtor Dashboard)
// ======================
function enqueue_rt_ap_assign_task_scripts() {
    if (is_page('realtor-dashboard') && isset($_GET['tab']) && $_GET['tab'] === 'assign-task') {
        $script_path = get_stylesheet_directory() . '/assets/js/rt-ap-assign-task.js';
        $script_uri  = get_stylesheet_directory_uri() . '/assets/js/rt-ap-assign-task.js';

        wp_enqueue_script(
            'rt-ap-assign-task-js',
            $script_uri,
            ['jquery'],
            file_exists($script_path) ? filemtime($script_path) : '1.0.0',
            true
        );

        wp_localize_script('rt-ap-assign-task-js', 'rtAssignTaskAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('rt_ap_assign_task_nonce'),
        ]);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_rt_ap_assign_task_scripts');

// ======================
// Assign Property (Realtor Dashboard)
// ======================
function enqueue_rt_ap_assign_property_scripts() {
    if (is_page('realtor-dashboard') && isset($_GET['tab']) && $_GET['tab'] === 'assign-property') {
        $script_path = get_stylesheet_directory() . '/assets/js/rt-ap-assign-property.js';
        $script_uri  = get_stylesheet_directory_uri() . '/assets/js/rt-ap-assign-property.js';

        wp_enqueue_script(
            'rt-ap-assign-property-js',
            $script_uri,
            ['jquery'],
            file_exists($script_path) ? filemtime($script_path) : '1.0.0',
            true
        );

        wp_localize_script('rt-ap-assign-property-js', 'rtAssignPropertyAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('rt_ap_assign_property_nonce'),
            'debug'    => true,
        ]);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_rt_ap_assign_property_scripts');

// ======================
// Address Book / Clients (Realtor & Admin Dashboards)
// ======================
function rt_enqueue_client_scripts() {
    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
    $is_allowed_page = is_page('realtor-dashboard') || is_page('admin-dashboard');
    $is_allowed_tab = in_array($current_tab, ['address-book', 'clients', '']);

    if ($is_allowed_page && $is_allowed_tab) {
        // SheetJS for XLSX
        wp_enqueue_script(
            'sheetjs',
            'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js',
            [],
            '0.18.5',
            true
        );

        $script_path = get_stylesheet_directory() . '/assets/js/rt-ab-client.js';
        $script_uri  = get_stylesheet_directory_uri() . '/assets/js/rt-ab-client.js';

        wp_enqueue_script(
            'rt-ab-client-js',
            $script_uri,
            ['jquery', 'sheetjs'],
            file_exists($script_path) ? filemtime($script_path) : time(),
            true
        );

        wp_localize_script('rt-ab-client-js', 'rtClientAjax', [
            'ajax_url'               => admin_url('admin-ajax.php'),
            'create_nonce'           => wp_create_nonce('rt_client_create_nonce'),
            'edit_nonce'             => wp_create_nonce('rt_client_edit_nonce'),
            'delete_nonce'           => wp_create_nonce('rt_client_delete_nonce'),
            'export_nonce'           => wp_create_nonce('rt_client_export_nonce'),
            'import_nonce'           => wp_create_nonce('rt_client_import_nonce'),
            'default_avatar'         => get_stylesheet_directory_uri() . '/assets/images/default-avatar.jpg',
            'default_property_image' => get_stylesheet_directory_uri() . '/assets/images/default-property.png',
            'debug'                  => true,
        ]);
    }
}
add_action('wp_enqueue_scripts', 'rt_enqueue_client_scripts');

// ======================
// Realtors Tab
// ======================
function rt_enqueue_realtor_scripts() {
    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';

    if ($current_tab === 'realtors') {
        wp_enqueue_script(
            'sheetjs',
            'https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js',
            [],
            '0.18.5',
            true
        );

        $script_path = get_stylesheet_directory() . '/assets/js/am-rt-realtor.js';
        $script_uri  = get_stylesheet_directory_uri() . '/assets/js/am-rt-realtor.js';

        wp_enqueue_script(
            'am-rt-realtor-js',
            $script_uri,
            ['jquery', 'sheetjs'],
            file_exists($script_path) ? filemtime($script_path) : '1.0.0',
            true
        );

        wp_localize_script('am-rt-realtor-js', 'rtRealtorAjax', [
            'ajax_url'               => admin_url('admin-ajax.php'),
            'create_nonce'           => wp_create_nonce('am_realtor_create_nonce'),
            'edit_nonce'             => wp_create_nonce('am_realtor_edit_nonce'),
            'delete_nonce'           => wp_create_nonce('am_realtor_delete_nonce'),
            'export_nonce'           => wp_create_nonce('am_realtor_export_nonce'),
            'import_nonce'           => wp_create_nonce('am_realtor_import_nonce'),
            'default_avatar'         => get_stylesheet_directory_uri() . '/assets/images/default-avatar.jpg',
            'default_property_image' => get_stylesheet_directory_uri() . '/assets/images/default-property.png',
        ]);
    }
}
add_action('wp_enqueue_scripts', 'rt_enqueue_realtor_scripts');

// ======================
// Active Client Dashboard
// ======================
function rt_enqueue_active_client_dashboard_scripts() {

    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
    $is_dashboard_page = is_page('realtor-dashboard') || is_page('admin-dashboard');
    $is_dashboard_tab  = empty($current_tab) || $current_tab === 'dashboard';

    if ($is_dashboard_page && $is_dashboard_tab) {

        $script_path = get_stylesheet_directory() . '/assets/js/rt-db-active-client.js';

        wp_enqueue_script(
            'rt-db-active-client-js',
            get_stylesheet_directory_uri() . '/assets/js/rt-db-active-client.js',
            ['jquery'],
            filemtime($script_path),
            true
        );

        wp_localize_script('rt-db-active-client-js', 'rtActiveClientAjax', [
            'ajax_url'                => admin_url('admin-ajax.php'),
            
            // Pagination nonce (fetch)
            'pagination_nonce'        => wp_create_nonce('clients_pagination_nonce'),
            
            // Create client nonce
            'create_client_nonce'     => wp_create_nonce('create_dashboard_client_nonce'),

            // NEW: Edit + Delete nonce
            'edit_client_nonce'       => wp_create_nonce('edit_dashboard_client_nonce'),
            'delete_client_nonce'     => wp_create_nonce('delete_dashboard_client_nonce'),

            // Default avatar
            'default_avatar'          => get_stylesheet_directory_uri() . '/assets/images/default-avatar.jpg',
        ]);
    }
}
add_action('wp_enqueue_scripts', 'rt_enqueue_active_client_dashboard_scripts');

// ======================
// Lead Dashboard
// ======================
function rt_enqueue_lead_client_scripts() {
    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
    $is_dashboard_page = is_page('realtor-dashboard') || is_page('admin-dashboard');
    $is_dashboard_tab = empty($current_tab) || $current_tab === 'dashboard';

    if ($is_dashboard_page && $is_dashboard_tab) {
        $script_path = get_stylesheet_directory() . '/assets/js/rt-db-lead-client.js';
        wp_enqueue_script(
            'rt-db-lead-client-js',
            get_stylesheet_directory_uri() . '/assets/js/rt-db-lead-client.js',
            ['jquery'],
            filemtime($script_path),
            true
        );

        wp_localize_script('rt-db-lead-client-js', 'rtDashboardAjax', [
            'ajax_url'       => admin_url('admin-ajax.php'),
            'create_nonce'   => wp_create_nonce('cl_client_create_nonce'),
            'edit_nonce'     => wp_create_nonce('cl_client_edit_nonce'),
            'delete_nonce'   => wp_create_nonce('cl_client_delete_nonce'),
            'convert_nonce'  => wp_create_nonce('cl_client_convert_nonce'),
            'default_avatar' => get_stylesheet_directory_uri() . '/assets/images/default-avatar.jpg'
        ]);
    }
}
add_action('wp_enqueue_scripts', 'rt_enqueue_lead_client_scripts');

// ======================
// Profile Scripts (Realtor & Client)
// ======================
function mdk_enqueue_profile_scripts() {
    if (!isset($_GET['tab'])) return;
    $tab = sanitize_text_field($_GET['tab']);

    // Realtor Profile
    if (in_array($tab, ['rt-settings-pi', 'rt-settings-pi-edit'], true)) {

        if (!wp_script_is('rt-profile-script', 'enqueued')) {
            $script_path = get_stylesheet_directory() . '/assets/js/rt-realtor-profile.js';
            wp_enqueue_script(
                'rt-profile-script',
                get_stylesheet_directory_uri() . '/assets/js/rt-realtor-profile.js',
                ['jquery'],
                filemtime($script_path),
                true
            );
            wp_localize_script('rt-profile-script', 'rt_profile_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('rt_profile_nonce'),
            ]);
        }
    }

    // Client Profile
    if (in_array($tab, ['cl-settings-pi', 'cl-settings-pi-edit'], true)) {

        if (!wp_script_is('cl-profile-script', 'enqueued')) {
            $script_path = get_stylesheet_directory() . '/assets/js/cl-client-profile.js';
            wp_enqueue_script(
                'cl-profile-script',
                get_stylesheet_directory_uri() . '/assets/js/cl-client-profile.js',
                ['jquery'],
                filemtime($script_path),
                true
            );
            wp_localize_script('cl-profile-script', 'cl_profile_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('cl_profile_nonce'),
            ]);
        }
    }

    // Address Book / Documents
    if ((is_page('realtor-dashboard') || is_page('admin-dashboard')) 
    && in_array($tab, ['documents', 'address-book', 'doc-type'])) {

        if (!wp_script_is('rt-document-type-script', 'enqueued')) {
            $doc_type_path = get_stylesheet_directory() . '/assets/js/rt-document-type.js';
            wp_enqueue_script(
                'rt-document-type-script',
                get_stylesheet_directory_uri() . '/assets/js/rt-document-type.js',
                ['jquery'],
                filemtime($doc_type_path),
                true
            );
            wp_localize_script('rt-document-type-script', 'rt_doc_type_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('rt_doc_type_nonce'),
            ]);
        }

        if (!wp_script_is('rt-documents-script', 'enqueued')) {
            $documents_path = get_stylesheet_directory() . '/assets/js/rt-documents.js';
            wp_enqueue_script(
                'rt-documents-script',
                get_stylesheet_directory_uri() . '/assets/js/rt-documents.js',
                ['jquery'],
                filemtime($documents_path),
                true
            );
            wp_localize_script('rt-documents-script', 'rt_doc_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('rt_doc_nonce'),
            ]);
        }

        // Upload Documents Script
        if (!wp_script_is('upload-documents-script', 'enqueued')) {
            $upload_documents_path = get_stylesheet_directory() . '/assets/js/upload-document.js';
            wp_enqueue_script(
                'upload-documents-script',
                get_stylesheet_directory_uri() . '/assets/js/upload-document.js',
                ['jquery'],
                filemtime($upload_documents_path),
                true
            );
            wp_localize_script('upload-documents-script', 'upload_doc_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('upload_doc_nonce'),
            ]);
        }
    }
}
add_action('wp_enqueue_scripts', 'mdk_enqueue_profile_scripts');

// ======================
// Password Scripts
// ======================
function rt_enqueue_password_script() {
    if (!isset($_GET['tab']) || $_GET['tab'] !== 'rt-settings-cp') return;
    $script_path = get_stylesheet_directory() . '/assets/js/rt-settings-password.js';
    wp_enqueue_script(
        'rt-password-script',
        get_stylesheet_directory_uri() . '/assets/js/rt-settings-password.js',
        ['jquery'],
        filemtime($script_path),
        true
    );
    wp_localize_script('rt-password-script', 'rt_password_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('rt_password_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'rt_enqueue_password_script');

function cl_enqueue_password_script() {
    if (!isset($_GET['tab']) || $_GET['tab'] !== 'cl-settings-cp') return;
    $script_path = get_stylesheet_directory() . '/assets/js/cl-settings-password.js';
    wp_enqueue_script(
        'cl-password-script',
        get_stylesheet_directory_uri() . '/assets/js/cl-settings-password.js',
        ['jquery'],
        file_exists($script_path) ? filemtime($script_path) : '1.0.0',
        true
    );
    wp_localize_script('cl-password-script', 'cl_password_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('cl_password_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'cl_enqueue_password_script');

// ======================
// Dashboard Assets (CSS + JS)
// ======================
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
                $uri       = get_stylesheet_directory_uri() . '/' . $path;

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

