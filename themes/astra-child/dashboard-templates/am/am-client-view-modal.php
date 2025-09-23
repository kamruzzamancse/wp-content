<?php
// Get current user data
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Get user meta data
$broker_number   = get_user_meta($user_id, 'broker_number', true);
$company_name    = get_user_meta($user_id, 'company_name', true);
$profile_picture = get_user_meta($user_id, 'profile_picture', true);
$phone_number    = get_user_meta($user_id, 'phone_number', true);
$address         = get_user_meta($user_id, 'address', true);

// Set default values if empty
if (empty($broker_number)) $broker_number = 'BRK-2023-5876';
if (empty($company_name)) $company_name = 'Prime Realty Associates';
if (empty($phone_number)) $phone_number = 'Not Provided';
if (empty($address)) $address = 'Not Provided';

if (empty($profile_picture)) {
    $upload_dir = wp_upload_dir(); 
    $profile_picture = esc_url($upload_dir['baseurl'] . '/2025/08/client-photo.jpg');
}
?>

<!-- Realtor View Modal -->
<div id="amRealtorViewModal" class="modal-overlay">
    <div class="modal-content">

        <div class="piv-realtor-profile-container">
            <div class="piv-profile-header">
                <h2>Md. Anamul Haque</h2>
                <span id="closeRealtorModal" class="close-button">&times;</span>
            </div>
            
            <div class="piv-profile-content">
                <div class="piv-profile-pic-container">
                    <img class="realtor-avatar" src="<?php echo $profile_picture; ?>" alt="Realtor Profile Pic">
                </div>
                
                <div class="piv-profile-details">
                    <div class="piv-detail-row">
                        <span class="piv-detail-label">Full Name:</span>
                        <span class="piv-detail-value">Md. Anamul Haque</span>
                    </div>
                    
                    <div class="piv-detail-row">
                        <span class="piv-detail-label">Email:</span>
                        <span class="piv-detail-value">anamul.haque@example.com</span>
                    </div>
                    
                    <div class="piv-detail-row">
                        <span class="piv-detail-label">Company Name:</span>
                        <span class="piv-detail-value">Prime Realty Associates</span>
                    </div>

                    <div class="piv-detail-row">
                        <span class="piv-detail-label">Phone Number:</span>
                        <span class="piv-detail-value">01971843463</span>
                    </div>

                    <div class="piv-detail-row">
                        <span class="piv-detail-label">Address:</span>
                        <span class="piv-detail-value">123 Midtown Ave, New York, NY 10001</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
