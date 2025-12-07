<?php
global $wpdb;

// Get current user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Clients table
$table = $wpdb->prefix . 'clients';

// Fetch client data
$client = $wpdb->get_row(
    $wpdb->prepare("SELECT * FROM $table WHERE user_id = %d", $user_id),
    ARRAY_A
);

// If no record exists, create one
if (!$client) {
    $wpdb->insert($table, [
        'user_id'    => $user_id,
        'full_name'  => $current_user->display_name,
        'created_at' => current_time('mysql'),
    ]);
    $client = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE user_id = %d", $user_id),
        ARRAY_A
    );
}

// Assign values with fallback
$full_name          = !empty($client['full_name']) ? $client['full_name'] : $current_user->display_name;
$email              = $current_user->user_email;
$phone              = !empty($client['phone']) ? $client['phone'] : 'N/A';
$budget             = !empty($client['budget']) ? number_format($client['budget'], 2) : '0.00';
$preferred_location = !empty($client['preferred_location']) ? $client['preferred_location'] : 'Not set';

// Profile picture (from user_meta)
$profile_picture = get_user_meta($user_id, 'profile_picture', true);
if (empty($profile_picture)) {
    $upload_dir = wp_upload_dir();
    $profile_picture = esc_url($upload_dir['baseurl'] . '/2025/08/client-photo.jpg');
}
?>

<div class="cl-back-link">
    <a href="?tab=settings" class="cl-back-link">
        <span class="cl-header-arrow">←</span>
        <h1 class="header-title">⚙️ Settings</h1>
    </a>
</div>

<div class="piv-realtor-profile-container">
    <div class="piv-profile-header">
        <h2>👤 Personal Information</h2>
        <a href="?tab=cl-settings-pi-edit" class="piv-edit-button-link">
            <button class="piv-edit-button">Edit Profile</button>
        </a>
    </div>
    
    <div class="piv-profile-content">
        <div class="piv-profile-pic-container">
            <img class="realtor-avatar" src="<?php echo esc_url($profile_picture); ?>" alt="Client Profile Picture">
        </div>
        
        <div class="piv-profile-details">
            <div class="piv-detail-row">
                <span class="piv-detail-label">Full Name:</span>
                <span class="piv-detail-value"><?php echo esc_html($full_name); ?></span>
            </div>
            
            <div class="piv-detail-row">
                <span class="piv-detail-label">Email:</span>
                <span class="piv-detail-value"><?php echo esc_html($email); ?></span>
            </div>
            
            <div class="piv-detail-row">
                <span class="piv-detail-label">Phone:</span>
                <span class="piv-detail-value"><?php echo esc_html($phone); ?></span>
            </div>

            <div class="piv-detail-row">
                <span class="piv-detail-label">Budget:</span>
                <span class="piv-detail-value">$<?php echo esc_html($budget); ?></span>
            </div>

            <div class="piv-detail-row">
                <span class="piv-detail-label">Preferred Location:</span>
                <span class="piv-detail-value"><?php echo esc_html($preferred_location); ?></span>
            </div>
        </div>
    </div>
</div>
