<?php
if (!defined('ABSPATH')) exit;

/**
 * ==========================
 * Conditional JS & AJAX Scripts (Updated & De-duplicated)
 * ==========================
 *
 * Changes made:
 * - Unified SheetJS URL & ensured single enqueue.
 * - Wrapped filemtime() with file_exists() checks everywhere to avoid warnings.
 * - Added wp_script_is() guards to avoid duplicate enqueues.
 * - Fixed dashboard tab detection (empty tab won't trigger both dashboard scripts).
 * - Prevented documents/address-book scripts from loading on client-dashboard (no overlap).
 * - Consolidated small improvements to avoid name collisions in localized objects.
 */

/**
 * Utility: safe_filemtime()
 * Returns filemtime if file exists, otherwise a fallback version string.
 */
if (!function_exists('mdk_safe_filemtime')) {
    function mdk_safe_filemtime($path, $fallback = '1.0.0') {
        return file_exists($path) ? filemtime($path) : $fallback;
    }
}

/**
 * Enqueue Note Header JS
 */
function cld_note_header_assets() {

    wp_enqueue_script(
        'cld-note-header-js',
        get_stylesheet_directory_uri() . '/assets/js/note-header.js',
        ['jquery'],
        filemtime(get_stylesheet_directory() . '/assets/js/note-header.js'),
        true
    );

    wp_localize_script(
        'cld-note-header-js',
        'noteHeaderAjax',
        [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('note_header_nonce'),
        ]
    );
}
add_action('wp_enqueue_scripts', 'cld_note_header_assets');

/**
 * Enqueue & Localize – Notes
 */
function cld_notes_assets() {

    wp_enqueue_script(
        'cld-notes-js',
        get_stylesheet_directory_uri() . '/assets/js/notes.js',
        ['jquery'],
        filemtime(get_stylesheet_directory() . '/assets/js/notes.js'),
        true
    );

    wp_localize_script(
        'cld-notes-js',
        'notesAjax',
        [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('notes_nonce'), // Separate nonce for Notes
        ]
    );
}
add_action('wp_enqueue_scripts', 'cld_notes_assets');

/**
 * Enqueue & Localize Notifications Script
 */
add_action('wp_enqueue_scripts', function() {

    // Register a dummy script so we can localize data
    if (!wp_script_is('am-notifications', 'enqueued')) {
        wp_register_script('am-notifications', '', [], null, true);

        wp_localize_script('am-notifications', 'AM_NOTIF', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('am_notif_nonce'),
        ]);

        wp_enqueue_script('am-notifications');
    }

});

/**
 * AJAX: Mark all notifications as read
 */
function am_mark_all_read() {
    check_ajax_referer('am_notif_nonce', 'nonce');

    if (!is_user_logged_in()) wp_send_json_error('not_logged_in');

    global $wpdb;
    $user_id = get_current_user_id();
    $table = $wpdb->prefix . 'bm_message_recipients';

    $wpdb->update($table, ['unread_count' => 0], ['user_id' => $user_id]);

    wp_send_json_success();
}
add_action('wp_ajax_am_mark_all_read', 'am_mark_all_read');

/**
 * AJAX: Mark single thread as read
 */
function am_mark_single_read() {
    check_ajax_referer('am_notif_nonce', 'nonce');

    if (!is_user_logged_in()) wp_send_json_error('not_logged_in');

    global $wpdb;
    $user_id = get_current_user_id();
    $thread_id = isset($_POST['thread_id']) ? intval($_POST['thread_id']) : 0;

    if (!$thread_id) wp_send_json_error('invalid_thread');

    $table = $wpdb->prefix . 'bm_message_recipients';
    $wpdb->update($table, ['unread_count' => 0], ['user_id' => $user_id, 'thread_id' => $thread_id]);

    wp_send_json_success();
}
add_action('wp_ajax_am_mark_single_read', 'am_mark_single_read');

/**
 * Optional: Auto-refresh unread badge count every 5 seconds
 * Returns JSON: { unread_count: X }
 */
function am_get_unread_count() {
    check_ajax_referer('am_notif_nonce', 'nonce');

    if (!is_user_logged_in()) wp_send_json_error('not_logged_in');

    global $wpdb;
    $user_id = get_current_user_id();
    $table = $wpdb->prefix . 'bm_message_recipients';

    $unread_count = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT SUM(unread_count) FROM {$table} WHERE user_id = %d",
            $user_id
        )
    );

    wp_send_json_success(['unread_count' => $unread_count]);
}
add_action('wp_ajax_am_get_unread_count', 'am_get_unread_count');

// ======================
// Reply Docs (Client Dashboard)
// ======================
function enqueue_rt_reply_docs_scripts() {
    if (is_page('client-dashboard') && isset($_GET['tab']) && sanitize_text_field($_GET['tab']) === 'documents') {
        $script_path = get_stylesheet_directory() . '/assets/js/cl-reply-docs.js';
        $script_uri  = get_stylesheet_directory_uri() . '/assets/js/cl-reply-docs.js';

        if (!wp_script_is('cl-reply-docs-js', 'enqueued')) {
            wp_enqueue_script(
                'cl-reply-docs-js',
                $script_uri,
                ['jquery'],
                mdk_safe_filemtime($script_path),
                true
            );
        }

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
    if (is_page('realtor-dashboard') && isset($_GET['tab']) && sanitize_text_field($_GET['tab']) === 'assign-task') {
        $script_path = get_stylesheet_directory() . '/assets/js/rt-ap-assign-task.js';
        $script_uri  = get_stylesheet_directory_uri() . '/assets/js/rt-ap-assign-task.js';

        if (!wp_script_is('rt-ap-assign-task-js', 'enqueued')) {
            wp_enqueue_script(
                'rt-ap-assign-task-js',
                $script_uri,
                ['jquery'],
                mdk_safe_filemtime($script_path),
                true
            );
        }

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
    if (is_page('realtor-dashboard') && isset($_GET['tab']) && sanitize_text_field($_GET['tab']) === 'assign-property') {
        $script_path = get_stylesheet_directory() . '/assets/js/rt-ap-assign-property.js';
        $script_uri  = get_stylesheet_directory_uri() . '/assets/js/rt-ap-assign-property.js';

        if (!wp_script_is('rt-ap-assign-property-js', 'enqueued')) {
            wp_enqueue_script(
                'rt-ap-assign-property-js',
                $script_uri,
                ['jquery'],
                mdk_safe_filemtime($script_path),
                true
            );
        }

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
    $is_allowed_tab = in_array($current_tab, ['address-book', 'clients', ''], true);

    if ($is_allowed_page && $is_allowed_tab) {
        // SheetJS for XLSX (single source, enqueue only once)
        if (!wp_script_is('sheetjs', 'enqueued')) {
            wp_enqueue_script(
                'sheetjs',
                'https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js',
                [],
                '0.18.5',
                true
            );
        }

        $script_path = get_stylesheet_directory() . '/assets/js/rt-ab-client.js';
        $script_uri  = get_stylesheet_directory_uri() . '/assets/js/rt-ab-client.js';

        if (!wp_script_is('rt-ab-client-js', 'enqueued')) {
            wp_enqueue_script(
                'rt-ab-client-js',
                $script_uri,
                ['jquery', 'sheetjs'],
                mdk_safe_filemtime($script_path, time()),
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
}
add_action('wp_enqueue_scripts', 'rt_enqueue_client_scripts');

// ======================
// Realtors Tab
// ======================
function rt_enqueue_realtor_scripts() {
    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';

    if ($current_tab === 'realtors') {
        // Ensure SheetJS not enqueued twice
        if (!wp_script_is('sheetjs', 'enqueued')) {
            wp_enqueue_script(
                'sheetjs',
                'https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js',
                [],
                '0.18.5',
                true
            );
        }

        $script_path = get_stylesheet_directory() . '/assets/js/am-rt-realtor.js';
        $script_uri  = get_stylesheet_directory_uri() . '/assets/js/am-rt-realtor.js';

        if (!wp_script_is('am-rt-realtor-js', 'enqueued')) {
            wp_enqueue_script(
                'am-rt-realtor-js',
                $script_uri,
                ['jquery', 'sheetjs'],
                mdk_safe_filemtime($script_path),
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
}
add_action('wp_enqueue_scripts', 'rt_enqueue_realtor_scripts');

// ======================
// Active Client Dashboard
// ======================
function rt_enqueue_active_client_dashboard_scripts() {
    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
    $is_dashboard_page = is_page('realtor-dashboard') || is_page('admin-dashboard');

    // Only treat explicit dashboard tab (don't treat empty as dashboard to avoid duplicate loads)
    $is_dashboard_tab  = ($current_tab === 'dashboard');

    if ($is_dashboard_page && $is_dashboard_tab) {
        $script_path = get_stylesheet_directory() . '/assets/js/rt-db-active-client.js';

        if (!wp_script_is('rt-db-active-client-js', 'enqueued')) {
            wp_enqueue_script(
                'rt-db-active-client-js',
                get_stylesheet_directory_uri() . '/assets/js/rt-db-active-client.js',
                ['jquery'],
                mdk_safe_filemtime($script_path),
                true
            );
        }

        wp_localize_script('rt-db-active-client-js', 'rtActiveClientAjax', [
            'ajax_url'                => admin_url('admin-ajax.php'),
            'pagination_nonce'        => wp_create_nonce('clients_pagination_nonce'),
            'create_client_nonce'     => wp_create_nonce('create_dashboard_client_nonce'),
            'edit_client_nonce'       => wp_create_nonce('edit_dashboard_client_nonce'),
            'delete_client_nonce'     => wp_create_nonce('delete_dashboard_client_nonce'),
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

    // Only explicit dashboard tab; avoid empty tab match to prevent duplicate loads
    $is_dashboard_tab = ($current_tab === 'dashboard');

    if ($is_dashboard_page && $is_dashboard_tab) {
        $script_path = get_stylesheet_directory() . '/assets/js/rt-db-lead-client.js';
        if (!wp_script_is('rt-db-lead-client-js', 'enqueued')) {
            wp_enqueue_script(
                'rt-db-lead-client-js',
                get_stylesheet_directory_uri() . '/assets/js/rt-db-lead-client.js',
                ['jquery'],
                mdk_safe_filemtime($script_path),
                true
            );
        }

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
                mdk_safe_filemtime($script_path),
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
                mdk_safe_filemtime($script_path),
                true
            );
            wp_localize_script('cl-profile-script', 'cl_profile_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('cl_profile_nonce'),
            ]);
        }
    }

    // doc type and docs
    if ((is_page('realtor-dashboard') || is_page('admin-dashboard') || is_page('client-dashboard')) 
        && in_array($tab, ['doc-type', 'docs'], true)) {

        if (!wp_script_is('document-type-script', 'enqueued')) {
            $doc_type_path = get_stylesheet_directory() . '/assets/js/document-type.js';
            wp_enqueue_script(
                'document-type-script',
                get_stylesheet_directory_uri() . '/assets/js/document-type.js',
                ['jquery'],
                mdk_safe_filemtime($doc_type_path),
                true
            );
            wp_localize_script('document-type-script', 'rt_doc_type_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('rt_doc_type_nonce'),
            ]);
        }

        // Generate a single nonce for both upload & update
        $upload_doc_nonce = wp_create_nonce('upload_doc_nonce');

        // Upload Documents Script
        if (!wp_script_is('upload-documents-script', 'enqueued')) {
            $upload_documents_path = get_stylesheet_directory() . '/assets/js/upload-document.js';
            wp_enqueue_script(
                'upload-documents-script',
                get_stylesheet_directory_uri() . '/assets/js/upload-document.js',
                ['jquery'],
                mdk_safe_filemtime($upload_documents_path),
                true
            );
            wp_localize_script('upload-documents-script', 'upload_doc_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => $upload_doc_nonce,
            ]);
        }

    }
}
add_action('wp_enqueue_scripts', 'mdk_enqueue_profile_scripts');

// ======================
// Password Scripts
// ======================
function rt_enqueue_password_script() {
    if (!isset($_GET['tab']) || sanitize_text_field($_GET['tab']) !== 'rt-settings-cp') return;
    $script_path = get_stylesheet_directory() . '/assets/js/rt-settings-password.js';
    if (!wp_script_is('rt-password-script', 'enqueued')) {
        wp_enqueue_script(
            'rt-password-script',
            get_stylesheet_directory_uri() . '/assets/js/rt-settings-password.js',
            ['jquery'],
            mdk_safe_filemtime($script_path),
            true
        );
    }
    wp_localize_script('rt-password-script', 'rt_password_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('rt_password_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'rt_enqueue_password_script');

function cl_enqueue_password_script() {
    if (!isset($_GET['tab']) || sanitize_text_field($_GET['tab']) !== 'cl-settings-cp') return;
    $script_path = get_stylesheet_directory() . '/assets/js/cl-settings-password.js';
    if (!wp_script_is('cl-password-script', 'enqueued')) {
        wp_enqueue_script(
            'cl-password-script',
            get_stylesheet_directory_uri() . '/assets/js/cl-settings-password.js',
            ['jquery'],
            mdk_safe_filemtime($script_path),
            true
        );
    }
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
        if (in_array($post->post_name, $dashboard_slugs, true)) {
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
                        if (!wp_style_is($handle, 'enqueued')) {
                            wp_enqueue_style($handle, $uri, [], filemtime($full_path));
                        }
                    } else {
                        if (!wp_script_is($handle, 'enqueued')) {
                            wp_enqueue_script($handle, $uri, ['jquery'], filemtime($full_path), true);
                        }
                    }
                }
            }
        }
    }
}
add_action('wp_enqueue_scripts', 'mdk_enqueue_dashboard_assets');
