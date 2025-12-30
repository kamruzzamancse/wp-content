<?php
/**
 * Dashboard Header Component with Logout Confirmation Modal
 */
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Dashboard URL based on role
$dashboard_url = current_user_can('administrator') ? home_url('/am/admin-dashboard/') : home_url('/');

// Upload directory for profile picture fallback
$upload_dir = wp_upload_dir();
$image_url = $upload_dir['baseurl'];

// Use the WordPress site name for company name
$company_name = get_bloginfo('name');
if (empty($company_name)) {
    $role_names = [
        'realtor' => 'Realtor',
        'client' => 'Client',
        'administrator' => 'Admin'
    ];
    $user_roles = $current_user->roles;
    $company_name = $role_names[$user_roles[0]] ?? ucfirst($user_roles[0]);
}
?>

<!-- Desktop Header -->
<header class="dashboard-header desktop-header">
    <div class="header-row-1">
        <a href="?tab=dashboard" class="logo-link">
            <img src="<?php echo esc_url(content_url('/uploads/2025/12/syncronest-logo.png')); ?>" 
                alt="<?php echo esc_attr(get_bloginfo('name')); ?> Logo" 
                class="site-logo">
        </a>

        <div class="user-info">
            <a href="?tab=notifications" class="notification-icon" aria-label="Notifications">
                <span class="dashicons dashicons-bell"></span>
                <?php echo bm_get_unread_count_badge(); ?>
            </a>

            <div class="profile-header">
                <div class="profile-pic">
                    <img class="realtor-avatar" src="<?php echo esc_url($image_url . '/2025/08/client-photo.jpg'); ?>" alt="Realtor Profile Pic">
                </div>
            </div>

            <div class="user-details">
                <span class="user-name"><?php echo esc_html($current_user->display_name); ?></span>
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

    <?php $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard'; ?>

    <!-- Modal sidebar -->
    <div class="modal-sidebar" id="modal-sidebar" aria-hidden="true" role="dialog" aria-label="Sidebar menu">
        <button class="close-sidebar" id="close-sidebar" aria-label="Close menu">&times;</button>
        <nav class="sidebar-nav">
            <ul>
                <li class="<?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>">
                    <a href="?tab=dashboard"><span class="dashicons dashicons-admin-home"></span> Dashboard Overview</a>
                </li>
                <!-- <li class="<?php echo $current_tab === 'properties' ? 'active' : ''; ?>">
                    <a href="?tab=properties"><span class="dashicons dashicons-building"></span> Properties</a>
                </li> -->
                <li class="<?php echo $current_tab === 'realtors' ? 'active' : ''; ?>">
                    <a href="?tab=realtors"><span class="dashicons dashicons-groups"></span> Realtors</a>
                </li>
                <li class="<?php echo $current_tab === 'clients' ? 'active' : ''; ?>">
                    <a href="?tab=clients"><span class="dashicons dashicons-buddicons-buddypress-logo"></span> Clients</a>
                </li>
                <!-- <li class="<?php echo $current_tab === 'documents' ? 'active' : ''; ?>">
                    <a href="?tab=documents"><span class="dashicons dashicons-media-document"></span> Documents</a>
                </li> -->
                <li class="<?php echo $current_tab === 'docs' ? 'active' : ''; ?>">
                    <a href="?tab=docs"><span class="dashicons dashicons-media-document"></span> Documents</a>
                </li>
                <li class="<?php echo $current_tab === 'messages' ? 'active' : ''; ?>">
                    <a href="?tab=messages"><span class="dashicons dashicons-email"></span> Messages</a>
                </li>
                <li class="<?php echo $current_tab === 'settings' ? 'active' : ''; ?>">
                    <a href="?tab=settings"><span class="dashicons dashicons-admin-settings"></span> Settings</a>
                </li>
                <!-- Logout with modal -->
                <li>
                    <a href="#" id="am-logout-trigger">
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
<div class="am-modal" id="am-logout-modal">
    <div class="am-modal-content">
        <div class="am-modal-header">
            <h2 class="am-modal-title">⚠️ Confirm Logout</h2>
        </div>
        <div class="am-modal-body">
            <p class="am-modal-text">Are you sure you want to logout?</p>
        </div>
        <div class="am-modal-footer">
            <button type="button" class="am-modal-button" id="am-logout-cancel">No</button>
            <a href="<?php echo esc_url(wp_logout_url(home_url('/login/'))); ?>" class="am-modal-button am-modal-button-primary" id="am-logout-confirm">Logout</a>
        </div>
    </div>
</div>

<style>
/* Modal Styles */
.am-modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.5);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}
.am-modal-content {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    max-width: 400px;
    width: 100%;
}
.am-modal-header, .am-modal-footer { padding: 20px; }
.am-modal-title { margin: 0; text-align: center; font-size: 18px; font-weight: 600; }
.am-modal-body { padding: 20px; text-align: center; }
.am-modal-button {
    padding: 8px 16px;
    border-radius: 4px;
    border: 1px solid #ddd;
    background: #f8f9fa;
    cursor: pointer;
    text-decoration: none;
}
.am-modal-button-primary {
    background: #e74c3c;
    color: #fff;
    border-color: #e74c3c;
}
.am-modal-button-primary:hover { background: #c0392b; }
.am-modal-footer { display: flex; justify-content: flex-end; gap: 10px; }

.notif-count {
    background: red;
    color: #fff;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
    margin-left: -8px;
}

.notification-icon {
    position: relative;

}

.notif-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ff3b30;
    color: #fff;
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 20px;
    font-weight: bold;
    min-width: 18px;
    text-align: center;
}

@media screen and (max-width: 480px) {
    .am-modal-button {
        color: #000;
    }
}

</style>

<script>
jQuery(document).ready(function($) {
    const logoutModal = $('#am-logout-modal');
    const logoutTrigger = $('#am-logout-trigger');
    const logoutCancel = $('#am-logout-cancel');
    
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
