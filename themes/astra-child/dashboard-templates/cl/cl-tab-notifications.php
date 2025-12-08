<?php
/**
 * Dynamic Notification Page (BP Better Messages Integration)
 */
global $wpdb;
$user_id = get_current_user_id();
?>
<div class="notifications-page">

    <div class="notifications-header">
        <h2 class="header-title">🔔 Notifications</h2>
        <button class="mark-all-read">Mark all as read</button>
    </div>

    <div class="notifications-container">

        <?php
        // Fetch latest message per thread for threads where current user is a recipient
        $messages = $wpdb->get_results(
            $wpdb->prepare("
                SELECT m.id, m.message, m.date_sent, m.thread_id, m.sender_id, u.display_name AS sender_name, r.unread_count
                FROM {$wpdb->prefix}bm_message_messages m
                INNER JOIN (
                    SELECT thread_id, MAX(id) AS max_id
                    FROM {$wpdb->prefix}bm_message_messages
                    GROUP BY thread_id
                ) lm ON lm.thread_id = m.thread_id AND lm.max_id = m.id
                INNER JOIN {$wpdb->prefix}bm_message_recipients r ON r.thread_id = m.thread_id AND r.user_id = %d
                LEFT JOIN {$wpdb->prefix}users u ON u.ID = m.sender_id
                WHERE m.sender_id != %d
                ORDER BY m.date_sent DESC
                LIMIT 100
            ", $user_id, $user_id)
        );

        if ($messages):
            foreach ($messages as $msg):
                $unread = (int) $msg->unread_count;
                $thread_id = (int) $msg->thread_id;
                $excerpt = wp_trim_words( $msg->message, 12, '...' );
                $time_ago = human_time_diff( strtotime($msg->date_sent), current_time('timestamp') ) . ' ago';
                ?>
                <div class="notification-card <?php echo $unread ? 'unread' : ''; ?>" data-thread="<?php echo esc_attr($thread_id); ?>">

                    <a href="?tab=messages&thread=<?php echo esc_attr($thread_id); ?>" class="notification-content mark-read-single" data-thread="<?php echo esc_attr($thread_id); ?>">

                        <div class="notification-icon-before">
                            <span class="dashicons dashicons-email"></span>
                        </div>

                        <div class="notification-message">
                            <strong><?php echo esc_html( $msg->sender_name ?: 'Unknown' ); ?></strong> sent you a message:
                            <div><?php echo esc_html( $excerpt ); ?></div>
                        </div>

                        <div class="notification-meta">
                            <span class="time"><?php echo esc_html( $time_ago ); ?></span>

                            <?php if ( $unread ): ?>
                                <span class="status-dot"></span>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php endforeach;
        else: ?>
            <div class="no-notifications">
                <p>No notifications found.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
jQuery(function($){
    // Use localized AM_NOTIF.ajax_url and nonce
    var ajaxUrl = (typeof AM_NOTIF !== 'undefined') ? AM_NOTIF.ajax_url : (typeof ajaxurl !== 'undefined' ? ajaxurl : '');
    var nonce = (typeof AM_NOTIF !== 'undefined') ? AM_NOTIF.nonce : '';

    // MARK ALL READ
    $('.mark-all-read').on('click', function(e){
        e.preventDefault();
        if (!ajaxUrl) { location.reload(); return; }
        $.post(ajaxUrl, { action: 'am_mark_all_read', nonce: nonce }, function(res){
            location.reload();
        });
    });

    // MARK SINGLE: prevent immediate navigation so the AJAX can run (then navigate)
    $('.mark-read-single').on('click', function(e){
        var $link = $(this);
        var threadID = $link.data('thread') || $link.attr('data-thread');

        if (!ajaxUrl || !threadID) {
            return; // fallback to default navigation
        }

        e.preventDefault();

        $.post(ajaxUrl, { action: 'am_mark_single_read', nonce: nonce, thread_id: threadID }, function(){
            // update UI immediately
            $link.closest('.notification-card').removeClass('unread');
            $link.find('.status-dot').remove();
            // then navigate to the thread
            window.location = $link.attr('href');
        });
    });
});
</script>

<style>
.no-notifications { text-align:center; padding:30px 0; color:#666; font-size:16px; }
.notif-count { background:#e74c3c; color:#fff; border-radius:50%; padding:2px 6px; font-size:12px; margin-left:6px; }
.notification-card.unread { background: #f5fbff; }
.status-dot { display:inline-block; width:8px; height:8px; background:#e74c3c; border-radius:50%; margin-left:8px; }
</style>
