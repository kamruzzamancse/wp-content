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
                    <a href="<?php echo esc_url(wp_logout_url()); ?>">
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
                    <!-- Stats Cards -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3>Active Properties</h3>
                            <p><?php echo esc_html($active_properties); ?></p>
                        </div>
                        <div class="stat-card">
                            <h3>Tasks Sent</h3>
                            <p><?php echo esc_html($tasks_sent); ?></p>
                        </div>
                        <div class="stat-card">
                            <h3>Awaiting Response</h3>
                            <p><?php echo esc_html($awaiting_response); ?></p>
                        </div>
                    </div>

                    <!-- Active Clients Section -->
                    <div class="dashboard-section">
                        <h2>Active Clients</h2>
                        <?php if (!empty($active_clients)) : ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Client Name</th>
                                        <th>Address</th>
                                        <th>Closing Date</th>
                                        <th>Notes</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($active_clients as $client) : 
                                        $client_data = array(
                                            'address' => get_post_meta($client->ID, 'property_address', true),
                                            'closing_date' => get_post_meta($client->ID, 'closing_date', true),
                                            'notes' => get_post_meta($client->ID, 'realtor_notes', true),
                                            'status' => get_post_meta($client->ID, 'client_status', true)
                                        );
                                        $client_data = array_map('esc_html', $client_data);
                                    ?>
                                        <tr>
                                            <td><?php echo esc_html($client->post_title); ?></td>
                                            <td><?php echo $client_data['address']; ?></td>
                                            <td><?php echo $client_data['closing_date']; ?></td>
                                            <td><?php echo $client_data['notes']; ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo sanitize_html_class(strtolower($client_data['status'])); ?>">
                                                    <?php echo $client_data['status']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else : ?>
                            <p class="no-data">No active clients found.</p>
                        <?php endif; ?>
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
            <div class="dashboard-section">
                <h2>Create New Task</h2>
                <form class="task-form" id="taskCreationForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="taskClient">Client</label>
                            <select name="taskClient" id="taskClient" required>
                                <option value="">Select Client</option>
                                <?php if (!empty($active_clients)) : ?>
                                    <?php foreach ($active_clients as $client) : ?>
                                        <option value="<?php echo esc_attr($client->ID); ?>">
                                            <?php echo esc_html($client->post_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="taskDeadline">Deadline</label>
                            <input type="date" name="taskDeadline" id="taskDeadline" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="taskTitle">Task Title</label>
                        <input type="text" name="taskTitle" id="taskTitle" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="taskDescription">Description</label>
                        <textarea name="taskDescription" id="taskDescription" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="action-btn">
                            <span class="dashicons dashicons-yes"></span> Create Task
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php get_footer(); ?>