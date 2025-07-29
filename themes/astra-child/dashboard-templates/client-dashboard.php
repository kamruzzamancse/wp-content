<div class="client-dashboard">
    <header class="dashboard-header">
        <h1>Client Dashboard</h1>
        <div class="user-info">
            <span>Welcome, <?php echo esc_html(wp_get_current_user()->display_name); ?></span>
            <a href="<?php echo wp_logout_url(); ?>" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="task-overview">
        <div class="stat-card">
            <h3>Tasks Received</h3>
            <p>5</p>
        </div>
        <div class="stat-card">
            <h3>In Progress</h3>
            <p>2</p>
        </div>
        <div class="stat-card">
            <h3>Completed</h3>
            <p>3</p>
        </div>
    </div>

    <div class="dashboard-sections">
        <section class="task-list">
            <h2>Your Tasks</h2>
            <table class="task-table">
                <thead>
                    <tr>
                        <th>Task Name</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Property Review Form</td>
                        <td><span class="status pending">Pending</span></td>
                        <td>30 July</td>
                        <td><button class="complete-task">Complete</button></td>
                    </tr>
                    <!-- More task rows -->
                </tbody>
            </table>
        </section>

        <section class="assigned-properties">
            <h2>Your Properties</h2>
            <div class="property-grid">
                <!-- Property cards would go here -->
            </div>
        </section>
    </div>
</div>