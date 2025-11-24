<?php
if (!defined('ABSPATH')) exit;

// ======================
// Unified Dashboard Shortcodes
// ======================
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
