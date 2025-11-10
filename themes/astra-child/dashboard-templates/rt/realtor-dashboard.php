<?php
/**
 * Realtor Dashboard Main Template
 */

if (!defined('ABSPATH')) exit;

// Custom login page URL
$login_url = home_url('/login/');

// Authentication check
if (!is_user_logged_in() || !current_user_can('realtor')) {
    wp_redirect($login_url);
    exit;
}

// Current tab
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';

// Load dashboard data (define function if missing)
if (!function_exists('load_realtor_dashboard_data')) {
    function load_realtor_dashboard_data($tab) {
        $user_id = get_current_user_id();

        // Example data structure; customize as needed
        switch ($tab) {
            case 'dashboard':
                return [
                    'properties_count' => count(get_posts([
                        'post_type' => 'property',
                        'author' => $user_id,
                        'post_status' => 'publish'
                    ])),
                    'messages_count' => 0, // Replace with actual query
                ];
            case 'properties':
            case 'address-book':
            case 'documents':
            case 'assign-property':
            case 'assign-task':
            case 'messages': 
            case 'settings':
            case 'notifications':
            case 'rt-property-details':
            case 'rt-settings-pi':
            case 'rt-settings-cp':
            case 'rt-settings-pi-edit':
            case 'rt-settings-support':
                return []; // Placeholder, replace with actual data queries
            default:
                return null;
        }
    }
}

$dashboard_data = load_realtor_dashboard_data($current_tab);

if ($dashboard_data === null) {
    echo '<div class="error">Failed to load dashboard data.</div>';
    get_footer();
    exit;
}

get_header();
?>

<div class="dashboard-container">
    <?php include locate_template('dashboard-templates/rt/rt-dashboard-header.php'); ?>
    
    <div class="dashboard-content">
        <?php include locate_template('dashboard-templates/rt/rt-dashboard-sidebar.php'); ?>
        
        <main class="dashboard-main">
            <?php
            switch($current_tab) {
                case 'dashboard':
                    include locate_template('dashboard-templates/rt/rt-tab-dashboard.php');
                    break;
                case 'properties':
                    include locate_template('dashboard-templates/rt/rt-tab-properties.php');
                    break;
                case 'address-book':
                    include locate_template('dashboard-templates/rt/rt-tab-address-book.php');
                    break;
                case 'documents':
                    include locate_template('dashboard-templates/rt/rt-tab-documents.php');
                    break;
                case 'assign-property':
                    include locate_template('dashboard-templates/rt/rt-tab-assign-property.php');
                    break;
                case 'assign-task':
                    include locate_template('dashboard-templates/rt/rt-tab-assign-task.php');
                    break;
                case 'messages':
                    include locate_template('dashboard-templates/rt/rt-tab-messages.php');
                    break;
                case 'settings':
                    include locate_template('dashboard-templates/rt/rt-tab-settings.php');
                    break;
                case 'notifications':
                    include locate_template('dashboard-templates/rt/rt-tab-notifications.php');
                    break;
                case 'rt-property-details':
                    include locate_template('dashboard-templates/rt/rt-property-details.php');
                    break;
                case 'rt-settings-pi':
                    include locate_template('dashboard-templates/rt/rt-settings-pi.php');
                    break;
                case 'rt-settings-cp':
                    include locate_template('dashboard-templates/rt/rt-settings-cp.php');
                    break;
                case 'rt-settings-pi-edit':
                    include locate_template('dashboard-templates/rt/rt-settings-pi-edit.php');
                    break;
                case 'rt-settings-support':
                    include locate_template('dashboard-templates/rt/rt-settings-support.php');
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
include locate_template('dashboard-templates/rt/rt-profile-modal.php');
get_footer(); 
?>
