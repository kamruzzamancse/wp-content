<?php
/**
 * Dashboard Header Component (Realtor)
 */
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$dashboard_url = in_array('realtor', (array) $current_user->roles) ? home_url('/rt/realtor-dashboard/') : home_url('/');
$upload_dir = wp_upload_dir();
$image_url = $upload_dir['baseurl'];

// Get company name from user meta
$company_name = get_user_meta($user_id, 'company_name', true);
// If company name is empty, fall back to the role name
if (empty($company_name)) {
    $role_names = [
        'realtor' => 'Realtor',
        'client' => 'Client',
        'subscriber' => 'Subscriber'
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
                    <img class="realtor-avatar" src="<?php echo esc_url( $image_url . '/2025/08/client-photo.jpg' ); ?>" alt="Realtor Profile Pic">
                </div>
            </div>
            <div class="user-details">
                <a href="?tab=rt-settings-pi">
                    <span class="user-name"><?php echo esc_html($current_user->display_name); ?></span>
                </a>
                <span class="user-role-dashboard-header">
                    <?php echo esc_html($company_name); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Hamburger menu container -->
    <div class="header-row-2">
        <div class="hamburger-menu" id="desktop-hamburger" aria-label="Open menu" role="button" tabindex="0">
            <span class="dashicons dashicons-menu"></span>
        </div>
    </div>

    <?php
        // Modal Sidebar Navigation
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
    ?>

    <!-- Modal sidebar -->
    <div class="modal-sidebar" id="modal-sidebar" aria-hidden="true" role="dialog" aria-label="Sidebar menu">
        <button class="close-sidebar" id="close-sidebar" aria-label="Close menu">&times;</button>
        <nav class="sidebar-nav">
            <ul>
                <li class="<?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>">
                    <a href="?tab=dashboard"><span class="dashicons dashicons-admin-home"></span> Dashboard</a>
                </li>

                <li class="<?php echo $current_tab === 'address-book' ? 'active' : ''; ?>">
                    <a href="?tab=address-book"><span class="dashicons dashicons-id"></span> Address Book</a>
                </li>

                <li class="<?php echo $current_tab === 'doc-type' ? 'active' : ''; ?>">
                    <a href="?tab=doc-type"><span class="dashicons dashicons-tag"></span> Document Types</a>
                </li>

                <li class="<?php echo $current_tab === 'docs' ? 'active' : ''; ?>">
                    <a href="?tab=docs"><span class="dashicons dashicons-media-text"></span> Documents</a>
                </li>

                <li class="<?php echo $current_tab === 'assign-property' ? 'active' : ''; ?>">
                    <a href="?tab=assign-property"><span class="dashicons dashicons-admin-links"></span> Assign Property</a>
                </li>

                <li class="<?php echo $current_tab === 'messages' ? 'active' : ''; ?>">
                    <a href="?tab=messages"><span class="dashicons dashicons-email"></span> Messages</a>
                </li>

                <li class="<?php echo $current_tab === 'settings' ? 'active' : ''; ?>">
                    <a href="?tab=settings"><span class="dashicons dashicons-admin-settings"></span> Settings</a>
                </li>

                <li>
                    <a href="#" id="rt-logout-trigger"><span class="dashicons dashicons-migrate"></span> Logout</a>
                </li>
            </ul>
        </nav>

    </div>

    <!-- Overlay background -->
    <div class="modal-overlay" id="modal-overlay" tabindex="-1"></div>
</header>

<!-- Logout Confirmation Modal -->
<div class="rt-modal" id="rt-logout-modal">
    <div class="rt-modal-content">
        <div class="rt-modal-header">
            <h2 class="rt-modal-title">⚠️ Confirm Logout</h2>
        </div>
        <div class="rt-modal-body">
            <p class="rt-modal-text">Are you sure you want to logout?</p>
        </div>
        <div class="rt-modal-footer">
            <button type="button" class="rt-modal-button" id="rt-logout-cancel">No</button>
            <a href="<?php echo esc_url(wp_logout_url(home_url('/login/'))); ?>" class="rt-modal-button rt-modal-button-primary">Logout</a>
        </div>
    </div>
</div>

<style>
/* Global Button Styles */
button {
    color: #000 !important;
}

/* Logout Modal Styles */
.rt-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}

.rt-modal-content {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    max-width: 400px;
    width: 100%;
}

.rt-modal-header,
.rt-modal-footer {
    padding: 20px;
}

.rt-modal-title {
    margin: 0;
    text-align: center;
    font-size: 18px;
    font-weight: 600;
}

.rt-modal-body {
    padding: 20px;
    text-align: center;
}

.rt-modal-button {
    padding: 8px 16px;
    border-radius: 4px;
    border: 1px solid #ddd;
    background: #f8f9fa;
    cursor: pointer;
    text-decoration: none;
}

.rt-modal-button-primary {
    background: #e74c3c;
    color: #fff;
    border-color: #e74c3c;
}

.rt-modal-button-primary:hover {
    background: #c0392b;
}

.rt-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

/* Sidebar Toggle Button */
.sidebar-toggle-btn {
    background: none;
    border: none;
    color: #fff;
    font-size: 20px;
    cursor: pointer;
    padding: 8px;
    border-radius: 4px;
    transition: background-color 0.3s;
}

.sidebar-toggle-btn:hover {
    background-color: rgba(255, 255, 255, 0.1);
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

</style>

<script>
jQuery(document).ready(function($) {
    const rtLogoutModal = $('#rt-logout-modal');
    const rtLogoutTrigger = $('#rt-logout-trigger');
    const rtLogoutCancel = $('#rt-logout-cancel');

    // Show modal when logout is clicked
    rtLogoutTrigger.on('click', function(e) {
        e.preventDefault();
        rtLogoutModal.css('display', 'flex');
    });

    // Hide modal when No is clicked
    rtLogoutCancel.on('click', function() {
        rtLogoutModal.css('display', 'none');
    });

    // Close modal when clicking outside content
    rtLogoutModal.on('click', function(e) {
        if (e.target === this) $(this).css('display', 'none');
    });

    // Close modal with Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') rtLogoutModal.css('display', 'none');
    });
});
</script>

