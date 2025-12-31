<?php
/**
 * Admin Dashboard Main Template
 */

if (!defined('ABSPATH')) exit;

// Authentication check for both default and custom admin roles
$current_user = wp_get_current_user();
$allowed_roles = ['administrator', 'admin'];

if (!is_user_logged_in() || !array_intersect($allowed_roles, $current_user->roles)) {
    wp_redirect(home_url('/login/'));
    exit;
}

// Current tab
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';

// Load dashboard data (define function if missing)
if (!function_exists('load_admin_dashboard_data')) {
    function load_admin_dashboard_data($tab) {
        switch ($tab) {
            case 'dashboard':
                return [
                    'users_count'      => count(get_users()),
                    'properties_count' => count(get_posts([
                        'post_type'   => 'property',
                        'post_status' => 'publish'
                    ])),
                    'messages_count'   => 0, // Placeholder
                ];
            case 'properties':
                return get_posts([
                    'post_type'   => 'property',
                    'post_status' => 'publish',
                ]);
            case 'messages':
            case 'settings':
            case 'notifications':
            case 'realtors':
            case 'clients':
            case 'task-status':
            case 'user-management':
            //case 'doc-type':
            case 'docs':
            case 'am-property-details':
            case 'am-settings-pi':
            case 'am-settings-cp':
            case 'am-settings-pi-edit':
            case 'am-settings-support':
            case 'am-settings-nh':
            case 'am-settings-dt':
                return []; // Placeholder for future implementation
            default:
                return null;
        }
    }
}

$dashboard_data = load_admin_dashboard_data($current_tab);

if ($dashboard_data === null) {
    echo '<div class="error">Failed to load admin dashboard data.</div>';
    get_footer();
    exit;
}

get_header();
?>

<div class="dashboard-container">
    <?php include locate_template('dashboard-templates/am/am-dashboard-header.php'); ?>
    
    <div class="dashboard-content">
        <?php include locate_template('dashboard-templates/am/am-dashboard-sidebar.php'); ?>
        
        <main class="dashboard-main">
            <?php
            switch($current_tab) {
                case 'dashboard':
                    include locate_template('dashboard-templates/am/am-tab-dashboard.php');
                    break;
                case 'notifications':
                    include locate_template('dashboard-templates/am/am-tab-notifications.php');
                    break;
                case 'am-settings-pi':
                    include locate_template('dashboard-templates/am/am-settings-pi.php');
                    break;
                case 'am-settings-pi-edit':
                    include locate_template('dashboard-templates/am/am-settings-pi-edit.php');
                    break;
                case 'properties':
                    include locate_template('dashboard-templates/am/am-tab-properties.php');
                    break;
                case 'am-property-details':
                    include locate_template('dashboard-templates/am/am-property-details.php');
                    break;
                case 'realtors':
                    include locate_template('dashboard-templates/am/am-tab-realtors.php');
                    break;
                case 'clients':
                    include locate_template('dashboard-templates/am/am-tab-clients.php');
                    break;
                case 'messages':
                    include locate_template('dashboard-templates/am/am-tab-messages.php');
                    break;
                case 'task-status':
                    include locate_template('dashboard-templates/am/am-tab-task-status.php');
                    break;
                case 'user-management':
                    include locate_template('dashboard-templates/am/am-tab-user-management.php');
                    break;
                /* case 'doc-type':
                    include locate_template('dashboard-templates/am/am-tab-doc-type.php');
                    break; */
                case 'docs':
                    include locate_template('dashboard-templates/am/am-tab-docs.php');
                    break;
                case 'settings':
                    include locate_template('dashboard-templates/am/am-tab-settings.php');
                    break;
                case 'am-settings-nh':
                    include locate_template('dashboard-templates/am/am-settings-nh.php');
                    break;
                case 'am-settings-dt':
                    include locate_template('dashboard-templates/am/am-settings-dt.php');
                    break;
                default:
                    wp_redirect(add_query_arg('tab', 'dashboard'));
                    exit;
            }
            ?>
        </main>
    </div>
</div>

<?php 
include locate_template('dashboard-templates/am/am-profile-modal.php');
get_footer(); 
?>
