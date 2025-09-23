<?php
// Get current logged-in user
$current_user = wp_get_current_user();
$current_user_id = $current_user->ID;

// Check if admin wants to view another user
$view_user_id = $current_user_id;

if (in_array('administrator', (array) $current_user->roles) && isset($_GET['user_id'])) {
    $view_user_id = intval($_GET['user_id']); // Securely get user ID from URL
}

// Get target user data
$target_user = get_userdata($view_user_id);

// Get user meta data
/* $broker_number   = get_user_meta($view_user_id, 'broker_number', true);
$company_name    = get_user_meta($view_user_id, 'company_name', true);
$profile_picture = get_user_meta($view_user_id, 'profile_picture', true); */

// Set default values if empty
if (empty($broker_number)) $broker_number = 'BRK-2023-5876';
if (empty($company_name)) $company_name = 'Prime Realty Associates';
if (empty($profile_picture)) {
    $upload_dir = wp_upload_dir(); 
    $profile_picture = esc_url($upload_dir['baseurl'] . '/2025/08/client-photo.jpg');
}
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
        <a href="?tab=am-settings-pi-edit" class="piv-edit-button-link">
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
                <span class="piv-detail-value"><?php echo esc_html($target_user->display_name); ?></span>
            </div>
            
            <!-- <div class="piv-detail-row">
                <span class="piv-detail-label">Broker Number:</span>
                <span class="piv-detail-value"><?php //echo esc_html($broker_number); ?></span>
            </div> -->
            
            <div class="piv-detail-row">
                <span class="piv-detail-label">Email:</span>
                <span class="piv-detail-value"><?php echo esc_html($target_user->user_email); ?></span>
            </div>
            
            <div class="piv-detail-row">
                <span class="piv-detail-label">Company Name:</span>
                <span class="piv-detail-value"><?php echo esc_html(get_bloginfo('name')); ?></span>
            </div>
        </div>
    </div>
</div>
