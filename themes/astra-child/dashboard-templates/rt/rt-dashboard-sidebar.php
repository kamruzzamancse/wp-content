<?php
/**
 * Dashboard Sidebar Navigation with Logout Confirmation Modal
 */
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
?>
<aside class="dashboard-sidebar" id="dashboard-sidebar">
    <ul class="sidebar-menu">
        <li class="<?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>">
            <a href="?tab=dashboard" title="Dashboard">
                <span class="dashicons dashicons-admin-home"></span>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- <li class="<?php echo $current_tab === 'properties' ? 'active' : ''; ?>">
            <a href="?tab=properties" title="Properties">
                <span class="dashicons dashicons-building"></span>
                <span>Properties</span>
            </a>
        </li> -->

        <li class="<?php echo $current_tab === 'address-book' ? 'active' : ''; ?>">
            <a href="?tab=address-book" title="Address Book">
                <span class="dashicons dashicons-id"></span> <!-- Changed to ID card icon -->
                <span>Address Book</span>
            </a>
        </li>
        <li class="<?php echo $current_tab === 'documents' ? 'active' : ''; ?>">
            <a href="?tab=documents" title="Documents">
                <span class="dashicons dashicons-media-document"></span> <!-- Changed to document icon -->
                <span>Documents</span>
            </a>
        </li>
        <li class="<?php echo $current_tab === 'assign-property' ? 'active' : ''; ?>">
            <a href="?tab=assign-property" title="Assign Property">
                <span class="dashicons dashicons-admin-links"></span>
                <span>Assign Property</span>
            </a>
        </li>
        <li class="<?php echo $current_tab === 'messages' ? 'active' : ''; ?>">
            <a href="?tab=messages" title="Messages">
                <span class="dashicons dashicons-email"></span>
                <span>Messages</span>
            </a>
        </li>
        <li class="<?php echo $current_tab === 'settings' ? 'active' : ''; ?>">
            <a href="?tab=settings" title="Settings">
                <span class="dashicons dashicons-admin-settings"></span>
                <span>Settings</span>
            </a>
        </li>
        <li>
            <a href="#" class="sup-logout-trigger" title="Logout">
                <span class="dashicons dashicons-migrate"></span>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</aside>

<!-- Logout Confirmation Modal -->
<div class="sup-modal sup-logout-modal">
    <div class="sup-modal-content">
        <div class="sup-modal-header">
            <h2 class="sup-modal-title">Confirm Logout</h2>
        </div>
        <div class="sup-modal-body">
            <p class="sup-modal-text">Are you sure you want to logout?</p>
        </div>
        <div class="sup-modal-footer">
            <button type="button" class="sup-modal-button sup-logout-cancel">No</button>
            <a href="<?php echo esc_url(wp_logout_url(home_url('/login/'))); ?>" class="sup-modal-button sup-modal-button-primary sup-logout-confirm">Logout</a>
        </div>
    </div>
</div>

<style>
/* Modal Styles */
.sup-modal {
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

.sup-modal-content {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    width: 100%;
    max-width: 400px;
    overflow: hidden;
}

.sup-modal-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.sup-modal-title {
    font-size: 20px;
    font-weight: 600;
    margin: 0;
    color: #333;
    text-align: center;
}

.sup-modal-body {
    padding: 20px;
}

.sup-modal-text {
    font-size: 16px;
    color: #555;
    margin: 0;
    text-align: center;
}

.sup-modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.sup-modal-button {
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    border: 1px solid #ddd;
    background-color: #f8f9fa;
    color: #333;
    text-decoration: none;
}

.sup-modal-button:hover {
    color: #FFF!important;
}

.sup-modal-button-primary {
    background-color: #e74c3c;
    color: #FFF!important;
    border-color: #e74c3c;
}

.sup-modal-button-primary:hover {
    background-color: #c0392b;
    border-color: #c0392b;
}

/* Sidebar Styles ************************/
.dashboard-sidebar {
    width: 60px;
    transition: width 0.3s ease;
}

.dashboard-sidebar.expanded {
    width: 250px;
}

.sidebar-menu li a {
    display: flex;
    align-items: center;
    padding: 20px 15px;
    color: #fff;
    text-decoration: none;
    transition: background-color 0.3s;
    position: relative;
}

.sidebar-menu li a:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.sidebar-menu li a .dashicons {
    margin-right: 10px;
    flex-shrink: 0;
}

.dashboard-sidebar:not(.expanded) .sidebar-menu li a span:not(.dashicons) {
    display: none;
}

/* Tooltip Styles */
.sidebar-menu li a::after {
    content: attr(title);
    position: absolute;
    left: 100%;
    top: 50%;
    transform: translateY(-50%);
    margin-left: 10px;
    background-color: #333;
    color: #fff;
    padding: 5px 10px;
    border-radius: 4px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
    z-index: 1000;
}

.dashboard-sidebar:not(.expanded) .sidebar-menu li a:hover::after {
    opacity: 1;
}

/* Dashboard Content Layout */
.dashboard-content {
    display: grid;
    grid-template-columns: 60px 1fr;
    transition: grid-template-columns 0.3s ease;
}

.dashboard-content.sidebar-expanded {
    grid-template-columns: 250px 1fr;
}

/* Mobile Responsive Styles */
@media (max-width: 768px) {
    .dashboard-sidebar {
        position: fixed;
        left: 0;
        top: 0;
        height: 100%;
        z-index: 1000;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .dashboard-sidebar.mobile-open {
        transform: translateX(0);
    }
    
    .dashboard-content {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Select modal and buttons
    const logoutModal = $('.sup-logout-modal');
    const logoutTrigger = $('.sup-logout-trigger');
    const logoutCancel = $('.sup-logout-cancel');

    // Show modal on trigger click
    logoutTrigger.on('click', function(e) {
        e.preventDefault();
        logoutModal.css('display', 'flex');
    });

    // Hide modal on cancel
    logoutCancel.on('click', function() {
        logoutModal.css('display', 'none');
    });

    // Close modal when clicking outside
    logoutModal.on('click', function(e) {
        if ($(e.target).hasClass('sup-modal')) {
            logoutModal.css('display', 'none');
        }
    });

    // Close with Escape
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            logoutModal.css('display', 'none');
        }
    });
});
</script>