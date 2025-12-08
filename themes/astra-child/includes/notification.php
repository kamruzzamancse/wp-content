<?php
// --- Put this in your theme's functions.php or a small plugin file ---

/**
 * Helper: return unread count badge HTML
 */
function bm_get_unread_count_badge() {
    global $wpdb;
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        return '';
    }

    $count = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COALESCE(SUM(unread_count), 0) FROM {$wpdb->prefix}bm_message_recipients WHERE user_id = %d",
            $user_id
        )
    );

    if ( $count <= 0 ) {
        return '';
    }

    return '<span class="notif-badge" aria-live="polite" data-count="'. esc_attr($count) .'">' . esc_html($count) . '</span>';
}

/**
 * Enqueue notification assets (JS + CSS) and localize ajax data
 */
function am_enqueue_notification_assets() {
    // Register stylesheet (optional) - you can skip if placing CSS elsewhere
    wp_register_style('am-notif-style', false);
    wp_enqueue_style('am-notif-style');

    // Register script
    wp_register_script('am-notif-script', false, ['jquery'], null, true);

    // Localize required data
    $local = [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('am_notif_nonce'),
        'poll_interval' => 5000 // ms
    ];
    wp_localize_script('am-notif-script', 'AM_NOTIF', $local);

    // Inline JS (we'll output the full script below)
    $inline_js = file_get_contents(__DIR__ . '/am-notif-inline.js'); // optional external file
    // If you prefer inline directly here, use wp_add_inline_script:
    wp_add_inline_script('am-notif-script', am_notif_get_inline_js());

    wp_enqueue_script('am-notif-script');

    // Inline CSS for badge/animations
    wp_add_inline_style('am-notif-style', am_notif_get_inline_css());
}
add_action('wp_enqueue_scripts', 'am_enqueue_notification_assets');

/**
 * AJAX: return unread count (polled by frontend)
 */
add_action('wp_ajax_am_get_unread_count', 'am_get_unread_count_ajax');
add_action('wp_ajax_nopriv_am_get_unread_count', 'am_get_unread_count_ajax'); // optional if guests allowed
function am_get_unread_count_ajax() {
    check_ajax_referer('am_notif_nonce', 'nonce');

    global $wpdb;
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        wp_send_json_success(['count' => 0]);
    }

    $count = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COALESCE(SUM(unread_count), 0) FROM {$wpdb->prefix}bm_message_recipients WHERE user_id = %d",
            $user_id
        )
    );

    wp_send_json_success(['count' => $count]);
}

/**
 * Helper: returns the inline JS string (so we can keep code in PHP easily).
 * If you prefer put the JS in a separate file and register/enqueue it instead.
 */
function am_notif_get_inline_js() {
    return <<<JS
(function($){
    // config
    var ajaxUrl = (typeof AM_NOTIF !== 'undefined') ? AM_NOTIF.ajax_url : (typeof ajaxurl !== 'undefined' ? ajaxurl : '');
    var nonce = (typeof AM_NOTIF !== 'undefined') ? AM_NOTIF.nonce : '';
    var pollInterval = (typeof AM_NOTIF !== 'undefined' && AM_NOTIF.poll_interval) ? parseInt(AM_NOTIF.poll_interval,10) : 5000;

    if (!ajaxUrl) return;

    // Selectors
    var \$badge = null;
    var lastCount = 0;
    var isFirstLoad = true;

    function findBadge() {
        // The badge is expected inside .notification-icon
        var \$container = $('.notification-icon');
        \$badge = \$container.find('.notif-badge');

        if (!\$badge.length) {
            // create placeholder badge element (hidden initially)
            \$badge = \$('<span class="notif-badge" aria-live="polite" data-count="0" style="display:none;"></span>');
            \$container.append(\$badge);
        }
    }

    // sound using WebAudio (no external file)
    var audioCtx = null;
    function playBeep() {
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            var duration = 0.08;
            var oscillator = audioCtx.createOscillator();
            var gain = audioCtx.createGain();
            oscillator.type = 'sine';
            oscillator.frequency.value = 880; // Hz
            oscillator.connect(gain);
            gain.connect(audioCtx.destination);
            gain.gain.setValueAtTime(0.0001, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.2, audioCtx.currentTime + 0.01);
            oscillator.start(audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + duration);
            oscillator.stop(audioCtx.currentTime + duration + 0.02);
        } catch(e) {
            // ignore if browser blocks audio
            console.log('beep error', e);
        }
    }

    // Visual flash
    function flashBadge() {
        if (!\$badge || !\$badge.length) return;
        \$badge.addClass('notif-flash');
        setTimeout(function(){ \$badge.removeClass('notif-flash'); }, 900);
    }

    // Pulse on unread
    function updatePulse(count) {
        if (!\$badge || !\$badge.length) return;
        if (count > 0) {
            \$badge.addClass('notif-pulse');
        } else {
            \$badge.removeClass('notif-pulse');
        }
    }

    // Update badge DOM
    function setBadge(count) {
        if (!\$badge || !\$badge.length) return;
        count = parseInt(count, 10) || 0;
        if (count > 0) {
            \$badge.attr('data-count', count).text(count).show();
        } else {
            \$badge.attr('data-count', 0).hide().text('');
        }
    }

    // Poll for new count
    function poll() {
        $.post(ajaxUrl, { action: 'am_get_unread_count', nonce: nonce }, function(res){
            if (!res || !res.success) return;
            var count = parseInt(res.data.count,10) || 0;

            // first-time init
            if (isFirstLoad) {
                lastCount = count;
                setBadge(count);
                updatePulse(count);
                isFirstLoad = false;
                return;
            }

            // if increased -> flash + sound
            if (count > lastCount) {
                setBadge(count);
                updatePulse(count);
                flashBadge();
                playBeep();
            } else if (count < lastCount) {
                // decreased -> update quietly
                setBadge(count);
                updatePulse(count);
            } else {
                // no change -> leave as is (still ensure pulse matches state)
                updatePulse(count);
            }

            lastCount = count;
        }, 'json').fail(function(){ /* ignore network errors */ });
    }

    // Start
    \$(function(){
        findBadge();
        // initial poll immediately
        poll();
        // then interval
        setInterval(poll, pollInterval);
    });

})(jQuery);
JS;
}

/**
 * Helper: returns inline CSS for badge + animations
 */
function am_notif_get_inline_css() {
    return <<<CSS
/* Notification badge base */
.notification-icon {
    position: relative;
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.notification-icon .dashicons {
    font-size: 20px;
    line-height: 1;
}

/* badge */
.notif-badge {
    position: absolute;
    top: -6px;
    right: -8px;
    background: #ff3b30;
    color: #fff;
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 999px;
    font-weight: 700;
    min-width: 18px;
    text-align: center;
    line-height: 1;
    z-index: 50;
    display: inline-block;
    pointer-events: none;
    transform-origin: center;
}

/* pulse when unread exists - subtle breathing effect */
.notif-pulse {
    animation: notif-pulse 1.8s infinite;
}

@keyframes notif-pulse {
    0% { box-shadow: 0 0 0 0 rgba(255,59,48,0.12); transform: scale(1); }
    50% { box-shadow: 0 0 0 6px rgba(255,59,48,0.04); transform: scale(1.03); }
    100% { box-shadow: 0 0 0 0 rgba(255,59,48,0.00); transform: scale(1); }
}

/* flash animation when new message arrives */
.notif-flash {
    animation: notif-flash 0.9s ease;
}

@keyframes notif-flash {
    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255,255,255,0.0); filter: brightness(1); }
    20% { transform: scale(1.15); filter: brightness(1.25); }
    60% { transform: scale(0.98); filter: brightness(1); }
    100% { transform: scale(1); filter: brightness(1); }
}

/* when badge hidden, keep element layout-safe */
.notification-icon .notif-badge[style*="display:none"] {
    display: none !important;
}
CSS;
}
