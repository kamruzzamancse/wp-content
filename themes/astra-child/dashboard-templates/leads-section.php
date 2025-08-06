<div class="dashboard-section leads-section">
    <h2>Leads</h2>
    <table class="leads-table">
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Last Touch</th>
                <th>Status</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $leads = [
                ['name' => 'John Smith', 'time' => '20 July 25, 11pm', 'status' => 'hot', 'notes' => 'Contract update'],
                ['name' => 'Sarah Johnson', 'time' => '19 July 25, 3pm', 'status' => 'warm', 'notes' => 'Follow up needed']
            ];
            
            foreach ($leads as $lead): ?>
            <tr>
                <td><?php echo esc_html($lead['name']); ?></td>
                <td><?php echo esc_html($lead['time']); ?></td>
                <td>
                    <span class="status-dot status-<?php echo esc_attr($lead['status']); ?>"></span>
                    <?php echo ucfirst($lead['status']); ?>
                </td>
                <td><?php echo esc_html($lead['notes']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="lead-popup-overlay" id="leadPopupOverlay">
    <div class="lead-popup">
        <button class="close-popup">&times;</button>
        <div class="popup-header">
            <h1 class="popup-heading">Leads</h1>
            <h2 class="popup-client-name">John Smith</h2>
        </div>
        
        <div class="popup-grid">
            <div class="popup-column">
                <div class="popup-section">
                    <span class="popup-label">Last Touch</span>
                    <span class="popup-value">20 July 25, 11pm</span>
                </div>
            </div>
            
            <div class="popup-column">
                <div class="popup-section">
                    <span class="popup-label">Status</span>
                    <div class="status-container">
                        <span class="status-dot status-hot"></span>
                        <span class="status-text">Hot</span>
                    </div>
                </div>
            </div>
            
            <div class="popup-fullwidth">
                <div class="popup-section">
                    <span class="popup-label">Notes</span>
                    <p class="popup-value">Just a quick update about contract.</p>
                </div>
            </div>
        </div>
    </div>
</div>