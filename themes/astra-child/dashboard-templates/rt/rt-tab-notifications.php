<div class="notifications-page">
    <div class="notifications-header">
        <h2>Notifications</h2>
        <button class="mark-all-read">Mark all as read</button>
    </div>
    
    <div class="notifications-container">
        <?php
        $notifications = [
            ['message' => 'You have a new message from <span class="highlight">New Event</span>', 'time' => '2 min ago', 'unread' => true],
            ['message' => 'Your listing at <span class="highlight">123 Main St</span> received an offer', 'time' => '15 min ago', 'unread' => true],
            ['message' => 'Appointment confirmed with <span class="highlight">Sarah Johnson</span>', 'time' => '1 hour ago', 'unread' => false]
        ];
        
        foreach ($notifications as $notification): ?>
        <div class="notification-card <?php echo $notification['unread'] ? 'unread' : ''; ?>">
            <div class="notification-content">
                <div class="notification-icon-before">
                    <span class="dashicons dashicons-bell"></span>
                </div>
                <div class="notification-message">
                    <?php echo $notification['message']; ?>
                </div>
                <div class="notification-meta">
                    <span class="time"><?php echo esc_html($notification['time']); ?></span>
                    <?php if ($notification['unread']): ?>
                    <span class="status-dot"></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>