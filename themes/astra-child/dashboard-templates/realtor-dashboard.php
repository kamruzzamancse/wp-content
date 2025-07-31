<?php
/**
 * Realtor Dashboard Template
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Check if user is logged in and has correct role
if (!is_user_logged_in() || !current_user_can('realtor')) {
    wp_redirect(home_url('/login/'));
    exit;
}

// Get current tab from URL or set default
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';

// Load dashboard data for the current tab
$dashboard_data = load_realtor_dashboard_data($current_tab);
if (!$dashboard_data) {
    echo '<div class="error">Failed to load dashboard data.</div>';
    get_footer();
    exit;
}

// Extract variables
$active_properties = $dashboard_data['active_properties'];
$tasks_sent = $dashboard_data['tasks_sent'];
$awaiting_response = $dashboard_data['awaiting_response'];
$active_clients = $dashboard_data['active_clients'];
$current_user = $dashboard_data['current_user'];
$user_id = $dashboard_data['user_id'];

get_header();
?>

<div class="dashboard-container">
    <!-- Header Section -->
    <header class="dashboard-header">
        <img src="<?php echo esc_url(content_url('/uploads/' . date('Y/m') . '/logo.png')); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?> Logo" class="site-logo">
        <div class="user-info">
            <div class="notification-icon">
                <span class="dashicons dashicons-bell"></span>
            </div>
            <div class="profile-pic">
                <img src="<?php echo esc_url(get_avatar_url($current_user->ID)); ?>" alt="Profile Picture">
            </div>
            <div class="user-details">
                <span class="user-name"><?php echo esc_html($current_user->display_name); ?></span>
                <span class="user-role">
                    <?php
                    $role_names = array(
                        'realtor' => 'Realtor',
                        'agent' => 'Agent',
                        'subscriber' => 'Subscriber',
                    );
                    
                    $user_roles = $current_user->roles;
                    if (!empty($user_roles)) {
                        $primary_role = $user_roles[0];
                        echo esc_html($role_names[$primary_role] ?? ucfirst($primary_role));
                    }
                    ?>
                </span>
            </div>
        </div>
    </header>

    <div class="dashboard-content">
        <!-- Sidebar Navigation -->
        <aside class="dashboard-sidebar">
            <ul class="sidebar-menu">
                <li class="<?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>">
                    <a href="?tab=dashboard">
                        <span class="dashicons dashicons-admin-home"></span>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="<?php echo $current_tab === 'properties' ? 'active' : ''; ?>">
                    <a href="?tab=properties">
                        <span class="dashicons dashicons-building"></span>
                        <span>My Properties</span>
                    </a>
                </li>
                <li class="<?php echo $current_tab === 'address-book' ? 'active' : ''; ?>">
                    <a href="?tab=address-book">
                        <span class="dashicons dashicons-book-alt"></span>
                        <span>Address Book</span>
                    </a>
                </li>
                <li class="<?php echo $current_tab === 'messages' ? 'active' : ''; ?>">
                    <a href="?tab=messages">
                        <span class="dashicons dashicons-email"></span>
                        <span>Message</span>
                    </a>
                </li>
                <li class="<?php echo $current_tab === 'settings' ? 'active' : ''; ?>">
                    <a href="?tab=settings">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <span>Setting</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(wp_logout_url(home_url('/login/'))); ?>">
                        <span class="dashicons dashicons-migrate"></span>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="dashboard-main">
            <?php 
            // Load content based on current tab
            switch($current_tab) {
                case 'dashboard':
                    ?>

                    <div class="dashboard-top">
                        <div class="dashboard-top-left">
                            <!-- Stats Cards -->
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <h3>
                                        <span class="dashicons dashicons-admin-home"></span>
                                        Total Properties
                                    </h3>
                                    <p><?php echo esc_html($active_properties); ?></p>
                                </div>
                                <div class="stat-card">
                                    <h3>
                                        <span class="dashicons dashicons-groups"></span>
                                        Total Client
                                    </h3>
                                    <p><?php echo esc_html($tasks_sent); ?></p>
                                </div>
                                <div class="stat-card">
                                    <h3>
                                        <span class="dashicons dashicons-media-document"></span>
                                        Upload Document
                                    </h3>
                                    <h6 style="color: #ccc">(marketing, personal, etc)</h6>
                                </div>
                            </div>
                            <!-- Active Clients Section -->
                            <div class="dashboard-section active-clients-section">
                                <h2>Active Clients</h2>

                                <table class="active-clients-table">
                                    <thead>
                                        <tr>
                                            <th>Client Name</th>
                                            <th>Address</th>
                                            <th>Closing Date</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Insurance</td>
                                            <td>New York</td>
                                            <td>22 July</td>
                                            <td>Just a quick follow-up on documents.</td>
                                        </tr>
                                        <tr>
                                            <td>Insurance</td>
                                            <td>New York</td>
                                            <td>22 July</td>
                                            <td>Just a quick follow-up on documents.</td>
                                        </tr>
                                        <tr>
                                            <td>Insurance</td>
                                            <td>New York</td>
                                            <td>22 July</td>
                                            <td>Just a quick follow-up on documents.</td>
                                        </tr>
                                        <tr>
                                            <td>Insurance</td>
                                            <td>New York</td>
                                            <td>22 July</td>
                                            <td>Just a quick follow-up on documents.</td>
                                        </tr>
                                        <tr>
                                            <td>Insurance</td>
                                            <td>New York</td>
                                            <td>22 July</td>
                                            <td>Just a quick follow-up on documents.</td>
                                        </tr>
                                        <tr>
                                            <td>Insurance</td>
                                            <td>New York</td>
                                            <td>22 July</td>
                                            <td>Just a quick follow-up on documents.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                        <div class="dashboard-top-right">
                            <div id="todo-calendar" class="todo-calendar">
                                <?php echo do_shortcode('[todo_calendar]'); ?>
                            </div>
                        </div>
                    </div>

                    
                    <?php
                    break;

                case 'properties':
                    ?>
                    <!-- Property Management Section -->
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h2>Property Management</h2>
                            <button class="action-btn" id="uploadPropertyBtn">
                                <span class="dashicons dashicons-upload"></span> Upload New Property
                            </button>
                        </div>
                        
                        <div class="property-upload-form" style="display:none;">
                            <?php echo do_shortcode('[property_upload_form]'); ?>
                        </div>
                        
                        <div class="property-grid">
                            <?php 
                            if (function_exists('realtor_properties_shortcode')) {
                                echo realtor_properties_shortcode(array('user_id' => $user_id));
                            } else {
                                echo do_shortcode('[realtor_properties user_id="' . $user_id . '"]');
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                    break;

                case 'address-book':
                    // Address Book content would go here
                    echo '<div class="dashboard-section"><h2>Address Book</h2><p>Address book content will be displayed here.</p></div>';
                    break;

                case 'messages':
                    // Messages content would go here
                    echo '<div class="dashboard-section"><h2>Messages</h2><p>Your messages will be displayed here.</p></div>';
                    break;

                case 'settings':
                    // Settings content would go here
                    echo '<div class="dashboard-section"><h2>Settings</h2><p>Settings form will be displayed here.</p></div>';
                    break;

                default:
                    // Default to dashboard if invalid tab
                    wp_redirect(add_query_arg('tab', 'dashboard'));
                    exit;
            }
            ?>

            <!-- Task Creation Section (Shown on all tabs except settings) -->
            <?php if ($current_tab !== 'settings') : ?>
            <!-- Leads Section -->
            <div class="dashboard-section leads-section">
                <h2>Leads</h2>

                <table class="leads-table">
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>Last Touch</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>John Smith</td>
                            <td>20 July 25, 11pm</td>
                            <td><span class="status-dot status-hot"></span> Hot</td>
                            <td>Just a quick update about contract.</td>
                        </tr>
                        <tr>
                            <td>John Smith</td>
                            <td>20 July 25, 11pm</td>
                            <td><span class="status-dot status-warm"></span> Warm</td>
                            <td>Just a quick update about contract.</td>
                        </tr>
                        <tr>
                            <td>John Smith</td>
                            <td>20 July 25, 11pm</td>
                            <td><span class="status-dot status-cold"></span> Cold</td>
                            <td>Just a quick update about contract.</td>
                        </tr>
                        <tr>
                            <td>John Smith</td>
                            <td>20 July 25, 11pm</td>
                            <td><span class="status-dot status-cold"></span> Cold</td>
                            <td>Just a quick update about contract.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Lead Details Popup -->
            <div class="lead-popup-overlay" id="leadPopupOverlay">
                <div class="lead-popup">
                    <button class="close-popup">&times;</button>
                    <div class="popup-header">
                        <h1 class="popup-heading">Leeds</h1>
                        <h2 class="popup-client-name">John Smith</h2>
                    </div>
                    
                    <div class="popup-grid">
                        <div class="popup-column">
                            <div class="popup-section">
                                <span class="popup-label">Last Touch</span>
                                <span class="popup-value">20 July 25, 11pm</span>
                            </div>
                        </div>
                        
                        <div class="popup-column">
                            <div class="popup-section">
                                <span class="popup-label">Status</span>
                                <div class="status-container">
                                    <span class="status-dot status-hot"></span>
                                    <span class="status-text">Hot</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="popup-fullwidth">
                            <div class="popup-section">
                                <span class="popup-label">Notes</span>
                                <p class="popup-value">Just a quick update about contract.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php endif; ?>
        </main>
    </div>
</div>

<?php get_footer(); ?>