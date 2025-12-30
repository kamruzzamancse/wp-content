<?php
/**
 * Dashboard Sidebar Navigation with Logout Confirmation Modal (Client Version)
 */
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
?>
<aside class="dashboard-sidebar" id="dashboard-sidebar-cl">
    <ul class="sidebar-menu">
        <li class="<?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>">
            <a href="?tab=dashboard" title="Dashboard">
                <span class="dashicons dashicons-admin-home"></span>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="<?php echo $current_tab === 'link-property' ? 'active' : ''; ?>">
            <a href="?tab=link-property" title="Link Property">
                <span class="dashicons dashicons-admin-links"></span>
                <span>Link Property</span>
            </a>
        </li>
        <li class="<?php echo $current_tab === 'properties' ? 'active' : ''; ?>">
            <a href="?tab=properties" title="Properties">
                <span class="dashicons dashicons-building"></span>
                <span>Properties</span>
            </a>
        </li>
        <!-- <li class="<?php echo $current_tab === 'doc-type' ? 'active' : ''; ?>">
            <a href="?tab=doc-type" title="Document Types">
                <span class="dashicons dashicons-tag"></span>
                <span>Document Types</span>
            </a>
        </li> -->
        <li class="<?php echo $current_tab === 'docs' ? 'active' : ''; ?>">
            <a href="?tab=docs" title="Documents">
                <span class="dashicons dashicons-media-text"></span>
                <span>Documents</span>
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
            <a href="#" id="sup-cl-logout-trigger" title="Logout">
                <span class="dashicons dashicons-migrate"></span>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</aside>

<!-- Logout Confirmation Modal -->
<div class="sup-cl-modal" id="sup-cl-logout-modal">
    <div class="sup-cl-modal-content">
        <div class="sup-cl-modal-header">
            <h2 class="sup-cl-modal-title">⚠️ Confirm Logout</h2>
        </div>
        <div class="sup-cl-modal-body">
            <p class="sup-cl-modal-text">Are you sure you want to logout?</p>
        </div>
        <div class="sup-cl-modal-footer">
            <button type="button" class="sup-cl-modal-button" id="sup-cl-logout-cancel">No</button>
            <a href="<?php echo esc_url(wp_logout_url(home_url('/login/'))); ?>" class="sup-cl-modal-button sup-cl-modal-button-primary" id="sup-cl-logout-confirm">Logout</a>
        </div>
    </div>
</div>

<style>
/* ------------------------------
   Sidebar Styles with Variables
------------------------------ */
:root {
    --sidebar-collapsed-width: 60px;
    --sidebar-expanded-width: 250px;
    --sidebar-bg: #1e1e2d;
    --sidebar-text: #fff;
    --tooltip-bg: #333;
    --tooltip-text: #fff;
}

/* Sidebar */
.dashboard-sidebar {
    width: var(--sidebar-collapsed-width);
    background: #1F5597;
    transition: width 0.3s ease, transform 0.3s ease;
    color: var(--sidebar-text);
}

.dashboard-sidebar.expanded {
    width: var(--sidebar-expanded-width);
}

.sidebar-menu li a {
    display: flex;
    align-items: center;
    padding: 20px 15px;
    color: var(--sidebar-text);
    text-decoration: none;
    transition: background-color 0.3s, color 0.3s;
    position: relative;
    outline: none;
}

.sidebar-menu li a:hover,
.sidebar-menu li a:focus-visible {
    background-color: rgba(255, 255, 255, 0.1);
}

.sidebar-menu li a .dashicons {
    margin-right: 10px;
    flex-shrink: 0;
}

/* Hide text when collapsed */
.dashboard-sidebar:not(.expanded) .sidebar-menu li a span:not(.dashicons) {
    display: none;
}

/* Tooltip */
.sidebar-menu li a::after {
    content: attr(title);
    position: absolute;
    left: 100%;
    top: 50%;
    transform: translateY(-50%);
    margin-left: 10px;
    background-color: var(--tooltip-bg);
    color: var(--tooltip-text);
    padding: 5px 10px;
    border-radius: 4px;
    max-width: 200px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
    z-index: 1000;
    font-size: 13px;
}

.dashboard-sidebar:not(.expanded) .sidebar-menu li a:hover::after,
.dashboard-sidebar:not(.expanded) .sidebar-menu li a:focus-visible::after {
    opacity: 1;
}

/* Layout */
.dashboard-content {
    display: grid;
    grid-template-columns: var(--sidebar-collapsed-width) 1fr;
    transition: grid-template-columns 0.3s ease;
}

.dashboard-content.sidebar-expanded {
    grid-template-columns: var(--sidebar-expanded-width) 1fr;
}

/* Mobile */
@media (max-width: 768px) {
    .dashboard-sidebar {
        position: fixed;
        left: 0;
        top: 0;
        height: 100%;
        transform: translateX(-100%);
        z-index: 1000;
    }
    .dashboard-sidebar.mobile-open {
        transform: translateX(0);
    }
    .dashboard-content {
        grid-template-columns: 1fr;
    }
}

/* ------------------------------
   Modal Styles (Client Version)
------------------------------ */
.sup-cl-modal {
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

.sup-cl-modal-content {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    width: 100%;
    max-width: 400px;
    overflow: hidden;
}

.sup-cl-modal-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.sup-cl-modal-title {
    font-size: 20px;
    font-weight: 600;
    margin: 0;
    color: #333;
    text-align: center;
}

.sup-cl-modal-body {
    padding: 20px;
}

.sup-cl-modal-text {
    font-size: 16px;
    color: #555;
    margin: 0;
    text-align: center;
}

.sup-cl-modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.sup-cl-modal-button {
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

.sup-cl-modal-button:hover {
    color: #fff !important;
}

.sup-cl-modal-button-primary {
    background-color: #e74c3c;
    color: #fff !important;
    border-color: #e74c3c;
}

.sup-cl-modal-button-primary:hover {
    background-color: #c0392b;
    border-color: #c0392b;
}
</style>

<script>
jQuery(document).ready(function($) {
    const logoutModal = $('#sup-cl-logout-modal');
    const logoutTrigger = $('#sup-cl-logout-trigger');
    const logoutCancel = $('#sup-cl-logout-cancel');

    logoutTrigger.on('click', function(e) {
        e.preventDefault();
        logoutModal.css('display', 'flex');
    });

    logoutCancel.on('click', function() {
        logoutModal.css('display', 'none');
    });

    logoutModal.on('click', function(e) {
        if (e.target === this) {
            $(this).css('display', 'none');
        }
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            logoutModal.css('display', 'none');
        }
    });
});
</script>
