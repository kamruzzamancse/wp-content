<div class="admin-dashboard">
    <header class="dashboard-header">
        <h1>Admin Dashboard</h1>
        <div class="user-info">
            <span>Welcome, <?php echo esc_html(wp_get_current_user()->display_name); ?></span>
            <a href="<?php echo wp_logout_url(); ?>" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="dashboard-stats">
        <div class="stat-card">
            <h3>Total Realtors</h3>
            <p><?php echo count_users()['avail_roles']['realtor'] ?? 0; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Clients</h3>
            <p><?php echo count_users()['avail_roles']['client'] ?? 0; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Properties</h3>
            <p><?php echo wp_count_posts('property')->publish ?? 0; ?></p>
        </div>
    </div>

    <div class="dashboard-sections">
        <section class="tasks-overview">
            <h2>Task Status Overview</h2>
            <div class="task-stats">
                <div class="task-stat pending">Pending: 12</div>
                <div class="task-stat in-progress">In Progress: 8</div>
                <div class="task-stat completed">Completed: 24</div>
            </div>
        </section>

        <section class="user-management">
            <h2>User Management</h2>
            <div class="user-actions">
                <button class="add-user">Add New User</button>
                <button class="assign-users">Assign Realtors to Clients</button>
            </div>
        </section>

        <section class="recent-activity">
            <h2>System Activity Logs</h2>
            <div class="activity-list">
                <!-- Activity logs would be populated here -->
            </div>
        </section>
    </div>
</div>