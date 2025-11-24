<?php
if (!defined('ABSPATH')) exit;

// ======================
// Feature-Based Includes
// ======================
$feature_groups = [
    // CLIENT FEATURES
    'client' => [
        'cl-client-profile.php',
        'cl-settings-password.php', 
        'cl-settings-support-ajax.php',
        'cl-reply-docs-ajax.php',
        'cl-db-tracking-property.php',
        'cl-rentcast-properties-ajax.php',
        'cl-link-property-ajax.php',
    ],
    
    // REALTOR FEATURES  
    'realtor' => [
        'rt-db-active-client-ajax.php',
        'rt-db-lead-client-ajax.php',
        'rt-ab-client-ajax.php',
        'rt-realtor-profile.php',
        'rt-document-type.php',
        'rt-documents.php',
        'rt-settings-password.php',
        'rt-ap-assign-property-ajax.php',
        'rt-ap-assign-task-ajax.php',
    ],
    
    // ADMIN & SYSTEM FEATURES
    'admin' => [
        'am-rt-realtor-ajax.php',
        'rentcast-properties.php',
        'rentcast-cron.php',
    ]
];

foreach ($feature_groups as $group => $files) {
    foreach ($files as $file) {
        $path = get_stylesheet_directory() . '/includes/' . $file;
        if (file_exists($path)) {
            require_once $path;
        }
    }
}
