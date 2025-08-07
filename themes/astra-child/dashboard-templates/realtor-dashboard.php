<?php
/**
 * Realtor Dashboard Main Template
 */

if (!defined('ABSPATH')) exit;

// Authentication check
if (!is_user_logged_in() || !current_user_can('realtor')) {
    wp_redirect(home_url('/login/'));
    exit;
}

$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
$dashboard_data = load_realtor_dashboard_data($current_tab);

if (!$dashboard_data) {
    echo '<div class="error">Failed to load dashboard data.</div>';
    get_footer();
    exit;
}

get_header();
?>

<div class="dashboard-container">
    <?php include locate_template('dashboard-templates/dashboard-header.php'); ?>
    
    <div class="dashboard-content">
        <?php include locate_template('dashboard-templates/dashboard-sidebar.php'); ?>
        
        <main class="dashboard-main">
            <?php
            switch($current_tab) {
                case 'dashboard':
                    include locate_template('dashboard-templates/dashboard-tab.php');
                    break;
                    
                case 'properties':
                    include locate_template('dashboard-templates/properties-tab.php');
                    break;
                    
                case 'address-book':
                    echo '<div class="dashboard-section"><h2>Address Book</h2></div>';
                    break;
                    
                case 'messages':
                    echo '<div class="dashboard-section"><h2>Messages</h2></div>';
                    break;
                    
                case 'settings':
                    echo '<div class="dashboard-section"><h2>Settings</h2></div>';
                    break;

                case 'notifications':
                    include locate_template('dashboard-templates/notifications-tab.php');
                    break;

                case 'property-details':
                    include locate_template('dashboard-templates/property-details.php');
                    break;
                    
                default:
                    wp_redirect(add_query_arg('tab', 'dashboard'));
                    exit;
            }
            
            if (!in_array($current_tab, ['settings', 'notifications', 'properties', 'property-details'])) {
                include locate_template('dashboard-templates/leads-section.php');
            }
            ?>
        </main>
    </div>
</div>

<?php 
include locate_template('dashboard-templates/profile-modal.php');
get_footer(); 
?>