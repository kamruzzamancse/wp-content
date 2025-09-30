<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Default profile picture
$upload_dir = wp_upload_dir();
$default_avatar = esc_url($upload_dir['baseurl'] . '/2025/08/client-photo.jpg');

// Get profile picture from user meta
$profile_picture = get_user_meta($user_id, 'profile_picture', true) ?: $default_avatar;

// Query realtor data
$table_name = $wpdb->prefix . 'realtors';
$realtor_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $user_id), ARRAY_A);

// Default values if missing
$phone = $realtor_data['phone'] ?? '';
$agency_name = $realtor_data['agency_name'] ?? '';
$license_number = $realtor_data['license_number'] ?? '';
$rating_avg = $realtor_data['rating_avg'] ?? 0;
?>

<div class="back-link">
    <a href="?tab=settings" class="pd-back-link">
        <span class="pd-back-link__arrow">←</span>
        <h1 class="header-title">Settings</h1>
    </a>
</div>

<div class="piv-realtor-profile-container">
    <div class="piv-profile-header">
        <h2>Personal Information</h2>
        <a href="?tab=rt-settings-pi-edit" class="piv-edit-button-link">
            <button class="piv-edit-button">Edit Profile</button>
        </a>
    </div>

    <div class="piv-profile-content">
        <div class="piv-profile-pic-container">
            <img class="realtor-avatar" src="<?php echo esc_url($profile_picture); ?>" alt="Realtor Profile Pic">
        </div>

        <div class="piv-profile-details">
            <div class="piv-detail-row">
                <span class="piv-detail-label">Full Name:</span>
                <span class="piv-detail-value"><?php echo esc_html($current_user->display_name); ?></span>
            </div>
            <div class="piv-detail-row">
                <span class="piv-detail-label">Email:</span>
                <span class="piv-detail-value"><?php echo esc_html($current_user->user_email); ?></span>
            </div>
            <div class="piv-detail-row">
                <span class="piv-detail-label">Phone:</span>
                <span class="piv-detail-value"><?php echo esc_html($phone); ?></span>
            </div>
            <div class="piv-detail-row">
                <span class="piv-detail-label">Agency Name:</span>
                <span class="piv-detail-value"><?php echo esc_html($agency_name); ?></span>
            </div>
            <div class="piv-detail-row">
                <span class="piv-detail-label">License Number:</span>
                <span class="piv-detail-value"><?php echo esc_html($license_number); ?></span>
            </div>
            <div class="piv-detail-row">
                <span class="piv-detail-label">Rating (Avg):</span>
                <span class="piv-detail-value"><?php echo esc_html($rating_avg); ?></span>
            </div>
        </div>
    </div>
</div>
