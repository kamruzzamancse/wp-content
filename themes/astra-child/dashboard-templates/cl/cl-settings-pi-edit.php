<?php
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Default profile picture
$upload_dir = wp_upload_dir();
$default_avatar = esc_url($upload_dir['baseurl'] . '/2025/08/client-photo.jpg');
$profile_picture = get_user_meta($user_id, 'profile_picture', true) ?: $default_avatar;

// Get client data from wp_clients table
global $wpdb;
$table_name = $wpdb->prefix . 'clients';
$client_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $user_id), ARRAY_A);

// Always get full name and email from wp_users
$full_name = $current_user->display_name;
$email     = $current_user->user_email;

// Client-specific fields (always show, even if empty)
$phone              = $client_data['phone'] ?? '';
$budget             = $client_data['budget'] ?? '';
$preferred_location = $client_data['preferred_location'] ?? '';

$user_roles = $current_user->roles;
$user_role_name = !empty($user_roles) ? ucfirst($user_roles[0]) : 'Client';
?>

<div class="cl-back-link">
    <a href="?tab=cl-settings-pi" class="cl-back-link">
        <span class="cl-header-arrow">←</span>
        <h1 class="header-title">Personal Information</h1>
    </a>
</div>

<div id="profile-notice" class="profile-notice" style="display: none;"></div>

<div class="rpe-profile-container">
  <div class="rpe-profile-header">
    <div class="rpe-header-content">
      <div class="piv-profile-pic-container">
        <div class="piv-profile-pic-wrapper">
          <img class="client-avatar" id="profile-avatar" src="<?php echo esc_url($profile_picture); ?>" alt="Client Profile Pic">
          <label for="profile-pic-upload" class="piv-edit-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
          </label>
          <input type="file" id="profile-pic-upload" accept="image/*" style="display: none;">
        </div>
      </div>
      <div class="rpe-profile-info">
        <h1 class="rpe-profile-name" id="profile-display-name"><?php echo esc_html($full_name); ?></h1>
        <span class="rpe-profile-role"><?php echo esc_html($user_role_name); ?></span>
      </div>
    </div>
  </div>

  <form class="rpe-profile-form" id="profile-form">
    <!-- Full Name -->
    <div class="rpe-form-section">
      <label class="rpe-form-label">Full name</label>
      <input type="text" class="rpe-form-input" id="full-name" name="full_name"
            value="<?php echo esc_attr($full_name); ?>" readonly>
    </div>

    <!-- Email -->
    <div class="rpe-form-section">
      <label class="rpe-form-label">Email</label>
      <input type="email" class="rpe-form-input" id="email" name="email"
            value="<?php echo esc_attr($email); ?>" readonly>
    </div>

    <!-- Phone -->
    <div class="rpe-form-section">
      <label class="rpe-form-label">Phone</label>
      <input type="text" class="rpe-form-input" name="phone" value="<?php echo esc_attr($phone); ?>">
    </div>

    <!-- Budget -->
    <div class="rpe-form-section">
      <label class="rpe-form-label">Budget</label>
      <input type="number" step="0.01" class="rpe-form-input" name="budget" value="<?php echo esc_attr($budget); ?>">
    </div>

    <!-- Preferred Location -->
    <div class="rpe-form-section">
      <label class="rpe-form-label">Preferred Location</label>
      <input type="text" class="rpe-form-input" name="preferred_location" value="<?php echo esc_attr($preferred_location); ?>">
    </div>

    <div class="rpe-form-actions">
      <button type="button" class="rpe-cancel-button">Cancel</button>
      <button type="submit" class="rpe-save-button">Save Changes</button>
    </div>
  </form>
</div>


<style>
  /* Back Link Styles */
 
  .cl-back-link {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #333;
  }
  
  .cl-header-arrow {
    font-size: 20px;
    margin-right: 10px;
  }
  
  /* Notification Styles */
  .profile-notice {
    padding: 12px 15px;
    margin-bottom: 20px;
    border-radius: 4px;
    display: none;
  }
  
  .profile-notice.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }
  
  .profile-notice.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }
  
  /* Main Profile Container */
  .rpe-profile-container {
    max-width: 700px;
    padding: 20px;
    color: #333;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  }
  
  /* Profile Header */
  .rpe-profile-header {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
  }
  
  .rpe-header-content {
    display: flex;
    align-items: center;
  }
  
  /* Profile Picture */
  .piv-profile-pic-container {
    flex: 0 0 auto;
  }
  
  .realtor-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #f1f1f1;
  }
  
  /* Profile Info (right-aligned) */
  .rpe-profile-info {
    flex: 1;
    text-align: right;
  }
  
  .rpe-profile-name {
    font-size: 1.375rem!important;
    font-weight: bold;
    margin: 0;
    color: #000;
  }
  
  .rpe-profile-role {
    display: block;
    font-size: 16px;
    font-weight: normal;
    color: #666;
    margin-top: 5px;
  }
  
  /* Form Sections */
  .rpe-profile-form {
    display: flex;
    flex-direction: column;
  }
  
  .rpe-form-section {
    margin-bottom: 5px;
  }
  
  .rpe-form-label {
    display: block;
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 8px;
    color: #555;
  }
  
  .rpe-form-input {
    width: 100%;
    padding: 10px 12px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
    margin-bottom: 15px;
  }
  
  .rpe-form-input:disabled {
    background-color: #f5f5f5;
    color: #777;
  }
  
  .rpe-form-input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
  }
  
  /* Form Actions */
  .rpe-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
  }
  
  .rpe-save-button {
    background-color: #f8f9fa;
    color: #333;
    border: 1px solid #ddd;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  
  .rpe-cancel-button {
    background-color: #f8f9fa;
    color: #333;
    border: 1px solid #ddd;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  
  /* Profile Picture Edit Icon */
  .piv-profile-pic-wrapper {
    position: relative;
    display: inline-block;
  }
  
  .piv-edit-icon {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: #3498db;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
  }
  
  .piv-edit-icon:hover {
    background: #2980b9;
    transform: scale(1.1);
  }
  
  .piv-edit-icon svg {
    width: 16px;
    height: 16px;
  }
  
  /* Responsive adjustments */
  @media (max-width: 600px) {
    .rpe-profile-container {
      padding: 15px;
    }
    
    .rpe-header-content {
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
    }
    
    .rpe-profile-info {
      text-align: left;
      width: 100%;
    }
    
    .realtor-avatar {
      width: 60px;
      height: 60px;
    }
    
    .rpe-form-label {
      font-size: 13px;
    }
    
    .rpe-form-input {
      font-size: 15px;
    }
    
    .rpe-form-actions {
      flex-direction: column;
    }
    
    .rpe-save-button, .rpe-cancel-button {
      width: 100%;
    }
  }
</style>