<?php
if (!defined('ABSPATH')) exit;

// ======================
// Include Required Files
// ======================
$includes = [
    'core/init.php',
    'core/assets.php',
    'core/assets-dashboard.php',
    'core/dashboard-shortcodes.php',
    'core/helpers.php',
];

foreach ($includes as $file) {
    $path = get_stylesheet_directory() . '/' . $file;
    if (file_exists($path)) require_once $path;
}
