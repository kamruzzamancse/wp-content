<div class="dashboard-top">
    <div class="dashboard-top-left">
        <div class="stats-grid">
            <div class="stat-card">
                <h3><span class="dashicons dashicons-admin-home"></span> Total Properties</h3>
                <p>50</p>
            </div>
            <div class="stat-card">
                <h3><span class="dashicons dashicons-groups"></span> Total Client</h3>
                <p>60</p>
            </div>
            <div class="stat-card" id="upload-document">
                <h3><span class="dashicons dashicons-media-document"></span> Upload Document</h3>
                <div class="upload-icons">
                    <span class="dashicons dashicons-upload" title="Upload"></span>
                </div>
            </div>
        </div>

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
                    <?php for ($i = 0; $i < 6; $i++): ?>
                    <tr>
                        <td>Insurance</td>
                        <td>New York</td>
                        <td>22 July</td>
                        <td>Just a quick follow-up on documents.</td>
                    </tr>
                    <?php endfor; ?>
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