<?php
if (!defined('ABSPATH')) exit;

// Add custom 24-hour interval
add_filter('cron_schedules', function($schedules){
    $schedules['every_24_hours'] = [
        'interval' => 86400, // 24 hours = 86400 seconds
        'display'  => __('Every 24 Hours')
    ];
    return $schedules;
});

// Schedule event if not scheduled
if (!wp_next_scheduled('rentcast_update_cron')) {
    wp_schedule_event(time(), 'every_24_hours', 'rentcast_update_cron');
}

// Hook the function
add_action('rentcast_update_cron', function() {
    fetch_rentcast_properties_to_db('Orlando', 1); // limit 1
});

// Clear cron on theme switch
add_action('switch_theme', function(){
    $timestamp = wp_next_scheduled('rentcast_update_cron');
    if ($timestamp) wp_unschedule_event($timestamp, 'rentcast_update_cron');
});

// Optional: manual trigger for system cron (CLI)
if (defined('WP_CLI') && WP_CLI) {
    fetch_rentcast_properties_to_db('Orlando', 1);
}