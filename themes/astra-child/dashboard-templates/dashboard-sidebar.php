<?php
/**
 * Dashboard Sidebar Navigation
 */
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
?>

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