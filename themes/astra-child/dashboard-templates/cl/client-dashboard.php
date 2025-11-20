<?php
/**
 * Realtor Dashboard Main Template
 */

if (!defined('ABSPATH')) exit;

// Authentication check
if (!is_user_logged_in() || !current_user_can('client')) {
    wp_redirect(home_url('/login/'));
    exit;
}

// Current tab
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';

// Load dashboard data (define function if missing)
if (!function_exists('load_client_dashboard_data')) {
    function load_client_dashboard_data($tab) {
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
                return get_posts([
                    'post_type' => 'property',
                    'author' => $user_id,
                    'post_status' => 'publish',
                ]);
            case 'documents':
            case 'messages':
            case 'settings':
            case 'notifications':
            case 'cl-property-details':
            case 'cl-settings-pi':
            case 'cl-settings-cp':
            case 'cl-settings-pi-edit':
            case 'cl-settings-support':
                return []; // Placeholder, replace with actual data queries
            default:
                return null;
        }
    }
}

$dashboard_data = load_client_dashboard_data($current_tab);

if ($dashboard_data === null) {
    echo '<div class="error">Failed to load dashboard data.</div>';
    get_footer();
    exit;
}

get_header();
?>

<div class="dashboard-container">
    <?php include locate_template('dashboard-templates/cl/cl-dashboard-header.php'); ?>
    
    <div class="dashboard-content">
        <?php include locate_template('dashboard-templates/cl/cl-dashboard-sidebar.php'); ?>
        
        <main class="dashboard-main">
            <?php
             switch($current_tab) {
                case 'dashboard':
                    include locate_template('dashboard-templates/cl/cl-tab-dashboard.php');
                    break;
                case 'notifications':
                    include locate_template('dashboard-templates/cl/cl-tab-notifications.php');
                    break;
                case 'link-property':
                    include locate_template('dashboard-templates/cl/cl-tab-link-property.php');
                    break;
                case 'properties':
                    include locate_template('dashboard-templates/cl/cl-tab-properties.php');
                    break;
                case 'cl-property-details':
                    include locate_template('dashboard-templates/cl/cl-property-details.php');
                    break;
                case 'documents':
                    include locate_template('dashboard-templates/cl/cl-tab-documents.php');
                    break;
                case 'messages':
                    include locate_template('dashboard-templates/cl/cl-tab-messages.php');
                    break;
                case 'settings':
                    include locate_template('dashboard-templates/cl/cl-tab-settings.php');
                    break;
                case 'cl-settings-pi':
                    include locate_template('dashboard-templates/cl/cl-settings-pi.php');
                    break;
                case 'cl-settings-cp':
                    include locate_template('dashboard-templates/cl/cl-settings-cp.php');
                    break;
                case 'cl-settings-pi-edit':
                    include locate_template('dashboard-templates/cl/cl-settings-pi-edit.php');
                    break;
                case 'cl-settings-support':
                    include locate_template('dashboard-templates/cl/cl-settings-support.php');
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
    include locate_template('dashboard-templates/cl/cl-profile-modal.php');
get_footer(); 
?>
