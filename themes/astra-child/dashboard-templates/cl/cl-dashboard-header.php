<?php
/**
 * Dashboard Header Component (Client) with Logout Confirmation Modal
 */
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Dashboard URL based on role
$dashboard_url = in_array('client', (array) $current_user->roles) ? home_url('/cl/client-dashboard/') : home_url('/');

// Upload directory for profile picture fallback
$upload_dir = wp_upload_dir();
$image_url = $upload_dir['baseurl'];

// Get company name from user meta
$company_name = get_user_meta($user_id, 'company_name', true);

// If company name is empty, fallback to role name
if (empty($company_name)) {
    $role_names = [
        'realtor' => 'Realtor',
        'client' => 'Client',
        'administrator' => 'Admin'
    ];
    $user_roles = $current_user->roles;
    $company_name = $role_names[$user_roles[0]] ?? ucfirst($user_roles[0]);
}

// Get current tab
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
?>

<!-- Desktop Header -->
<header class="dashboard-header desktop-header">
    <div class="header-row-1">
        <a href="?tab=dashboard" class="logo-link">
            <img src="<?php echo esc_url(content_url('/uploads/2025/08/mary-logo.png')); ?>" 
                alt="<?php echo esc_attr(get_bloginfo('name')); ?> Logo" 
                class="site-logo">
        </a>

        <div class="user-info">
            <a href="?tab=notifications" class="notification-icon" aria-label="Notifications">
                <span class="dashicons dashicons-bell"></span>
            </a>

            <div class="profile-header">
                <div class="profile-pic">
                    <img class="client-avatar" src="<?php echo esc_url($image_url . '/2025/08/client-photo.jpg'); ?>" alt="Client Profile Pic">
                </div>
            </div>

            <div class="user-details">
                <a href="?tab=cl-settings-pi"><span class="user-name"><?php echo esc_html($current_user->display_name); ?></span></a>
                <span class="user-role-dashboard-header"><?php echo esc_html($company_name); ?></span>
            </div>
        </div>
    </div>

    <!-- Hamburger menu container -->
    <div class="header-row-2">
        <div class="hamburger-menu" id="desktop-hamburger" aria-label="Open menu" role="button" tabindex="0">
            <span class="dashicons dashicons-menu"></span>
        </div>
    </div>

    <!-- Modal sidebar -->
    <div class="modal-sidebar" id="modal-sidebar" aria-hidden="true" role="dialog" aria-label="Sidebar menu">
        <button class="close-sidebar" id="close-sidebar" aria-label="Close menu">&times;</button>
        <nav class="sidebar-nav">
            <ul>
                <li class="<?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>">
                    <a href="?tab=dashboard"><span class="dashicons dashicons-admin-home"></span> Dashboard</a>
                </li>
                <li class="<?php echo $current_tab === 'link-property' ? 'active' : ''; ?>">
                    <a href="?tab=link-property"><span class="dashicons dashicons-admin-links"></span> Link Property</a>
                </li>
                <li class="<?php echo $current_tab === 'properties' ? 'active' : ''; ?>">
                    <a href="?tab=properties"><span class="dashicons dashicons-building"></span> Properties</a>
                </li>
                <li class="<?php echo $current_tab === 'doc-type' ? 'active' : ''; ?>">
                    <a href="?tab=doc-type"><span class="dashicons dashicons-tag"></span> Document Types</a>
                </li>
                <li class="<?php echo $current_tab === 'docs' ? 'active' : ''; ?>">
                    <a href="?tab=docs"><span class="dashicons dashicons-media-text"></span> Documents</a>
                </li>
                <li class="<?php echo $current_tab === 'messages' ? 'active' : ''; ?>">
                    <a href="?tab=messages"><span class="dashicons dashicons-email"></span> Messages</a>
                </li>
                <li class="<?php echo $current_tab === 'settings' ? 'active' : ''; ?>">
                    <a href="?tab=settings"><span class="dashicons dashicons-admin-settings"></span> Settings</a>
                </li>
                <!-- Logout with modal -->
                <li>
                    <a href="#" id="cl-logout-trigger">
                        <span class="dashicons dashicons-migrate"></span> Logout
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Overlay background -->
    <div class="modal-overlay" id="modal-overlay" tabindex="-1"></div>
</header>

<!-- Logout Confirmation Modal -->
<div class="cl-modal" id="cl-logout-modal">
    <div class="cl-modal-content">
        <div class="cl-modal-header">
            <h2 class="cl-modal-title">⚠️ Confirm Logout</h2>
        </div>
        <div class="cl-modal-body">
            <p class="cl-modal-text">Are you sure you want to logout?</p>
        </div>
        <div class="cl-modal-footer">
            <button type="button" class="cl-modal-button" id="cl-logout-cancel">No</button>
            <a href="<?php echo esc_url(wp_logout_url(home_url('/login/'))); ?>" class="cl-modal-button cl-modal-button-primary">Logout</a>
        </div>
    </div>
</div>

<style>
/* Modal Styles */
button { color: #000!important; }
.cl-modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.5);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}
.cl-modal-content {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    max-width: 400px;
    width: 100%;
}
.cl-modal-header, .cl-modal-footer { padding: 20px; }
.cl-modal-title { margin: 0; text-align: center; font-size: 18px; font-weight: 600; }
.cl-modal-body { padding: 20px; text-align: center; }
.cl-modal-button {
    padding: 8px 16px;
    border-radius: 4px;
    border: 1px solid #ddd;
    background: #f8f9fa;
    cursor: pointer;
    text-decoration: none;
}
.cl-modal-button-primary {
    background: #e74c3c;
    color: #fff;
    border-color: #e74c3c;
}
.cl-modal-button-primary:hover { background: #c0392b; }
.cl-modal-footer { display: flex; justify-content: flex-end; gap: 10px; }
</style>

<script>
jQuery(document).ready(function($) {
    const logoutModal = $('#cl-logout-modal');
    const logoutTrigger = $('#cl-logout-trigger');
    const logoutCancel = $('#cl-logout-cancel');

    logoutTrigger.on('click', function(e) {
        e.preventDefault();
        logoutModal.css('display', 'flex');
    });
    logoutCancel.on('click', function() {
        logoutModal.css('display', 'none');
    });
    logoutModal.on('click', function(e) {
        if (e.target === this) $(this).css('display', 'none');
    });
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') logoutModal.css('display', 'none');
    });
});
</script>
