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
        <?php
        // Determine dashboard URL based on user role
        $dashboard_url = home_url('/');
        if (current_user_can('realtor')) {
            $dashboard_url = home_url('/realtor-dashboard');
        } elseif (current_user_can('client')) {
            $dashboard_url = home_url('/client-dashboard');
        }
        ?>
        <a href="<?php echo esc_url($dashboard_url); ?>">
            <img src="<?php echo esc_url(content_url('/uploads/2025/08/mary-logo.png')); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?> Logo" class="site-logo">
        </a>

        <div class="user-info">
            <a href="?tab=notifications">
                <div class="notification-icon">
                    <span class="dashicons dashicons-bell"></span>
                </div>
            </a>

            <div class="profile-header">
                <div class="profile-pic">
                    <img src="<?php echo esc_url(get_avatar_url($current_user->ID)); ?>" alt="Profile Picture">
                </div>
            </div>

            <!-- Profile Pic Modal -->
            <div class="modal-overlay" id="profileModal">
                <div class="modal">
                    <img src="<?php echo esc_url(get_avatar_url($current_user->ID)); ?>" alt="Profile">
                    <h3><?php echo esc_html($current_user->display_name); ?></h3>
                    <p><?php echo esc_html($current_user->user_email); ?></p>

                    <div class="modal-buttons">
                        <button onclick="location.href='/profile'">View Profile</button>
                        <button onclick="location.href='/edit-profile'">Edit Profile</button>
                        <button onclick="location.href='<?php echo esc_url(wp_logout_url(home_url('/login/'))); ?>'">Log out</button>
                    </div>
                </div>
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
                                    <p>50</p>
                                </div>
                                <div class="stat-card">
                                    <h3>
                                        <span class="dashicons dashicons-groups"></span>
                                        Total Client
                                    </h3>
                                    <p>60</p>
                                </div>
                                <div class="stat-card" id="upload-document">
                                    <h3>
                                        <span class="dashicons dashicons-media-document"></span>
                                        Upload Document
                                    </h3>
                                    <div class="upload-icons">
                                        <span class="dashicons dashicons-upload" title="Upload"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Document Upload Modal -->
                            <div id="documentUploadModal" class="document-modal">
                                <div class="document-modal-content">
                                    <div class="document-modal-header">
                                        <h2>Upload Document</h2>
                                    </div>
                                    
                                    <form id="documentUploadForm" class="document-upload-form">
                                        <!-- Document Title -->
                                        <div class="form-row-duo">
                                            <!-- Document Title (Left aligned) -->
                                            <div class="form-group left-align">
                                                <label for="documentTitle">Document Title</label>
                                                <input type="text" id="documentTitle" name="documentTitle" required>
                                            </div>
                                            
                                            <!-- Document Type (Right aligned) -->
                                            <div class="form-group right-align">
                                                <label for="documentType">Document Type</label>
                                                <input type="text" id="documentType" name="documentType" required>
                                            </div>
                                        </div>
                                        
                                        <!-- Task List/Notes (Textarea) -->
                                        <div class="form-group">
                                            <label for="taskNotes">Task list/Notes</label>
                                            <textarea id="taskNotes" name="taskNotes" rows="4" placeholder="Add any notes or task details"></textarea>
                                        </div>
                                        
                                        <!-- File Upload -->
                                        <div class="form-group">
                                            <label>Document/File</label>
                                            <div class="file-upload-wrapper">
                                                <input type="file" id="documentFile" name="documentFile" required>
                                                <label for="documentFile" class="file-upload-label">
                                                    <span class="dashicons dashicons-upload"></span>
                                                    <span>Choose file</span>
                                                </label>
                                                <span class="file-name">No file chosen</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Client and Due Date Row -->
                                        <div class="form-row">
                                            <!-- Client Dropdown (Left aligned) -->
                                            <div class="form-group left-align">
                                                <label for="clientList">Client</label>
                                                <select id="clientList" name="clientList">
                                                    <option value="">Select a client</option>
                                                    <option value="client1">John Smith</option>
                                                    <option value="client2">Sarah Johnson</option>
                                                    <option value="client3">Michael Brown</option>
                                                </select>
                                            </div>
                                            
                                            <!-- Due Date (Right aligned) -->
                                            <div class="form-group right-align">
                                                <label for="dueDate">Due Date</label>
                                                <input type="date" id="dueDate" name="dueDate">
                                            </div>
                                        </div>
                                        
                                        <!-- Form Actions (Centered) -->
                                        <div class="form-actions center-align">
                                            <button type="button" class="cancel-btn">Cancel</button>
                                            <button type="submit" class="upload-btn">Upload</button>
                                        </div>
                                    </form>
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
                    <div class="property-container">
        <div class="property-list">
            <!-- Property 1 -->
            <div class="property-item">
                <img src="http://localhost/mary/wp-content/uploads/2025/08/lakeview-basic.png" alt="Lakeview Basic" class="main-image">
                <div class="property-details">
                    <h3 class="property-title">Lakeview Basic Apartment</h3>
                    <div class="property-price">$1,200/month</div>
                    <div class="property-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Downtown, New York</span>
                    </div>
                    <div class="gallery">
                        <img src="http://localhost/mary/wp-content/uploads/2025/08/lakeview-basic-8.png" alt="Gallery Image 1">
                        <img src="http://localhost/mary/wp-content/uploads/2025/08/lakeview-basic-9.png" alt="Gallery Image 2">
                        <img src="http://localhost/mary/wp-content/uploads/2025/08/lakeview-basic-9-1.png" alt="Gallery Image 3">
                    </div>
                </div>
            </div>
            
            <!-- Property 2 -->
            <div class="property-item">
                <img src="http://localhost/mary/wp-content/uploads/2025/08/lakeview-standard.png" alt="Lakeview Standard" class="main-image">
                <div class="property-details">
                    <h3 class="property-title">Lakeview Standard Apartment</h3>
                    <div class="property-price">$1,800/month</div>
                    <div class="property-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Midtown, New York</span>
                    </div>
                    <div class="gallery">
                        <img src="http://localhost/mary/wp-content/uploads/2025/08/lakeview-standard-4.png" alt="Gallery Image 1">
                        <img src="http://localhost/mary/wp-content/uploads/2025/08/lakeview-standard-5.png" alt="Gallery Image 2">
                        <img src="http://localhost/mary/wp-content/uploads/2025/08/lakeview-standard-6.png" alt="Gallery Image 3">
                    </div>
                </div>
            </div>
            
            <!-- Property 3 -->
            <div class="property-item">
                <img src="http://localhost/mary/wp-content/uploads/2025/08/lakeview-premium.png" alt="Lakeview Premium" class="main-image">
                <div class="property-details">
                    <h3 class="property-title">Lakeview Premium Apartment</h3>
                    <div class="property-price">$2,500/month</div>
                    <div class="property-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Uptown, New York</span>
                    </div>
                    <div class="gallery">
                        <img src="http://localhost/mary/wp-content/uploads/2025/08/lakeview-premium-1.png" alt="Gallery Image 1">
                        <img src="http://localhost/mary/wp-content/uploads/2025/08/lakeview-premium-2.png" alt="Gallery Image 2">
                        <img src="http://localhost/mary/wp-content/uploads/2025/08/lakeview-premium-3.png" alt="Gallery Image 3">
                    </div>
                </div>
            </div>
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

                case 'notifications':
                    ?>
                    <div class="notifications-page">
                        <div class="notifications-header">
                            <h2>Notifications</h2>
                            <button class="mark-all-read">Mark all as read</button>
                        </div>
                        
                        <div class="notifications-container">
                            <div class="notification-card unread">
                                <div class="notification-content">
                                    <div class="notification-icon-before">
                                        <span class="dashicons dashicons-bell"></span>
                                    </div>
                                    <div class="notification-message">
                                        You have a new message from <span class="highlight">New Event</span>
                                    </div>
                                    <div class="notification-meta">
                                        <span class="time">2 min ago</span>
                                        <span class="status-dot"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="notification-card unread">
                                <div class="notification-content">
                                    <div class="notification-icon-before">
                                        <span class="dashicons dashicons-bell"></span>
                                    </div>
                                    <div class="notification-message">
                                        Your listing at <span class="highlight">123 Main St</span> received an offer
                                    </div>
                                    <div class="notification-meta">
                                        <span class="time">15 min ago</span>
                                        <span class="status-dot"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="notification-card">
                                <div class="notification-content">
                                    <div class="notification-icon-before">
                                        <span class="dashicons dashicons-bell"></span>
                                    </div>
                                    <div class="notification-message">
                                        Appointment confirmed with <span class="highlight">Sarah Johnson</span>
                                    </div>
                                    <div class="notification-meta">
                                        <span class="time">1 hour ago</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="notification-card">
                                <div class="notification-content">
                                    <div class="notification-icon-before">
                                        <span class="dashicons dashicons-bell"></span>
                                    </div>
                                    <div class="notification-message">
                                        Document signed by <span class="highlight">Michael Brown</span>
                                    </div>
                                    <div class="notification-meta">
                                        <span class="time">3 hours ago</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="notification-card">
                                <div class="notification-content">
                                    <div class="notification-icon-before">
                                        <span class="dashicons dashicons-bell"></span>
                                    </div>
                                    <div class="notification-message">
                                        System maintenance scheduled for <span class="highlight">tonight at 2 AM</span>
                                    </div>
                                    <div class="notification-meta">
                                        <span class="time">1 day ago</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
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

            <!-- Task Creation Section (Shown on all tabs except settings and notifications) -->
            <?php if ($current_tab !== 'settings' && $current_tab !== 'notifications') : ?>
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