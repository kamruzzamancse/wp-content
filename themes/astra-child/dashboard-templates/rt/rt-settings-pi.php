<?php
// Get current user data
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Get user meta data
$broker_number = get_user_meta($user_id, 'broker_number', true);
$company_name = get_user_meta($user_id, 'company_name', true);
$profile_picture = get_user_meta($user_id, 'profile_picture', true);

// Set default values if empty
if (empty($broker_number)) $broker_number = 'BRK-2023-5876';
if (empty($company_name)) $company_name = 'Prime Realty Associates';
if (empty($profile_picture)) {
    $upload_dir = wp_upload_dir(); 
    $profile_picture = esc_url($upload_dir['baseurl'] . '/2025/08/client-photo.jpg');
}

// Get user role for display
$user_roles = $current_user->roles;
//$user_role_name = !empty($user_roles) ? ucfirst($user_roles[0]) : 'Realtor';
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
            <img class="realtor-avatar" src="<?php echo $profile_picture; ?>" alt="Realtor Profile Pic">
        </div>
        
        <div class="piv-profile-details">
            <div class="piv-detail-row">
                <span class="piv-detail-label">Full Name:</span>
                <span class="piv-detail-value"><?php echo esc_html($current_user->display_name); ?></span>
            </div>
            
            <div class="piv-detail-row">
                <span class="piv-detail-label">Broker Number:</span>
                <span class="piv-detail-value"><?php echo esc_html($broker_number); ?></span>
            </div>
            
            <div class="piv-detail-row">
                <span class="piv-detail-label">Email:</span>
                <span class="piv-detail-value"><?php echo esc_html($current_user->user_email); ?></span>
            </div>
            
            <div class="piv-detail-row">
                <span class="piv-detail-label">Company Name:</span>
                <span class="piv-detail-value"><?php echo esc_html($company_name); ?></span>
            </div>
        </div>
    </div>
</div>