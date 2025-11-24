<?php
if (!defined('ABSPATH')) exit;

// ======================
// Helper Functions
// ======================
function mdk_dashboard_login_message($role) {
    return sprintf(
        '<div class="mdk-alert">%s <a href="%s">%s</a></div>',
        sprintf(__('You need to be logged in as a %s to view this page.', 'astra-child'), esc_html($role)),
        esc_url(wp_login_url()),
        __('Login here', 'astra-child')
    );
}
