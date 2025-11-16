<!-- Client Details Modal Structure -->
<?php
    $upload_dir = wp_upload_dir();
    $image_url = $upload_dir['baseurl'];
    $client_id = 123; // Example ID, replace dynamically

    // Use the WordPress site name for company name
    $company_name = get_bloginfo('name');
    if (empty($company_name)) {
        $role_names = [
            'realtor' => 'Realtor',
            'client' => 'Client',
            'administrator' => 'Admin'
        ];
        $user_roles = $current_user->roles;
        $company_name = $role_names[$user_roles[0]] ?? ucfirst($user_roles[0]);
    }
?>
<div class="modal-overlay-address-book" id="clientDetailsModal">
    <div class="modal-container">
        <div class="modal-header-realtor">
            <h1 class="header-title" style="margin-bottom: 20px">Client Details11111111</h1>
            <div class="client-profile-container">
                <img style="display:none" decoding="async" class="client-avatar" src="https://maryasfour.livewebsite.space/wp-content/uploads/2025/08/client-photo.jpg" alt="Client Photo">
                <img decoding="async" class="client-avatar" src="https://maryasfour.livewebsite.space/wp-content/uploads/2025/08/client-photo.jpg" alt="Client Photo">
                <span class="client-info">
                    <span class="client-name"><?php echo esc_html($current_user->display_name); ?></span><br>
                    <?php echo esc_html($company_name); ?>
                </span>
            </div>
        </div>
        <div class="modal-body">
            <table class="client-details-rt">
                <tr><td>Client Name</td><td>Afsana Hamid Mim</td></tr>
                <tr><td>Email</td><td>Support.info@gmail.com</td></tr>
                <tr><td>Phone Number</td><td>999-888-666</td></tr>
                <tr><td>Notes</td><td>Prefers evening meetings. Interested in commercial properties.</td></tr>
                <tr><td>Date of Birth</td><td>11 Oct 24, 1995</td></tr>
                <tr><td>House Closing Date</td><td>11 Aug 25, 2025</td></tr>
            </table>

            <h2 class="modal-title" style="margin-bottom: 10px">Client Information</h2>
            <div class="property-item-modal">
                <img src="<?php echo esc_url( $image_url . '/2025/08/lakeview-standard.png' ); ?>" alt="Lakeview Standard" class="main-image client-details-property-details">
                <div class="property-details">
                    <h3 class="property-title">Lakeview Standard Apartment</h3>
                    <div class="property-price">$1,600</div>
                    <div class="property-location">
                        <span class="dashicons dashicons-location"></span> Midtown, New York
                    </div>
                    <div class="gallery">
                        <img src="<?php echo esc_url( $image_url . '/2025/08/lakeview-standard-4.png' ); ?>" alt="Gallery Image 1">
                        <img src="<?php echo esc_url( $image_url . '/2025/08/lakeview-standard-5.png' ); ?>" alt="Gallery Image 2">
                        <img src="<?php echo esc_url( $image_url . '/2025/08/lakeview-standard-6.png' ); ?>" alt="Gallery Image 3">
                    </div>
                    <button class="view-details-btn">View Details</button>
                </div>
            </div>
            <div class="upload-documents">
                <button class="cld-upload-btn" data-modal="cl-upload-document-modal">
                    Upload Document <span class="dashicons dashicons-media-document"></span>
                </button>
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="close-btn" id="closeClientDetailsModal">Close</button>
        </div>
    </div>
</div>

<?php 
    include locate_template('dashboard-templates/rt/rt-property-details-modal.php');
    include locate_template('dashboard-templates/rt/rt-upload-document-modal.php');
?>

<style>
/* Upload Documents Section */
.upload-documents {
    margin-top: 20px;
    display: flex;
    align-items: center;
}

/* Upload Button Styling */
.cld-upload-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background-color: #007bff;
    color: #fff!important;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.cld-upload-btn:hover {
    background-color: #155ab6;
    transform: scale(1.02);
}

.cld-upload-btn .dashicons {
    font-size: 16px;
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const uploadModal = document.getElementById('cl-upload-document-modal');
    const closeBtn = uploadModal.querySelector('.clup-close-btn');
    const browseBtn = uploadModal.querySelector('.clup-browse');
    const fileInput = uploadModal.querySelector('#clup-file-input');

    // Close modal when clicking the cross
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            uploadModal.classList.remove('show');
        });
    }

    // Close modal when clicking outside modal box
    if (uploadModal) {
        uploadModal.addEventListener('click', function (e) {
            if (e.target === uploadModal) uploadModal.classList.remove('show');
        });
    }

    // Trigger file input when Browse button is clicked
    if (browseBtn && fileInput) {
        browseBtn.addEventListener('click', function () {
            fileInput.click();
        });
    }

    // Show upload modal and hide client details modal
    const uploadBtn = document.querySelector('.cld-upload-btn');
    const clientModal = document.getElementById('clientDetailsModal');

    if (uploadBtn) {
        uploadBtn.addEventListener('click', function () {
            if (clientModal) clientModal.style.display = 'none';
            uploadModal.classList.add('show');
        });
    }
});
</script>