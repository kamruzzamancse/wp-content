<!-- Realtor View Modal -->
<div id="amRealtorViewModal" class="modal-overlay-view" style="display:none; align-items:center; justify-content:center; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999;">
    <div class="modal-content-view">

        <div class="realtor-view-container">
            <div class="view-header">
                <h2>Realtor Details</h2>
                <span id="closeRealtorViewModal" class="close-button-view">&times;</span>
            </div>

            <div class="view-content">
                <!-- Profile Picture -->
                <div class="view-pic-container">
                    <img class="view-realtor-avatar" id="viewPreviewAvatar" src="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>" alt="Profile Preview">
                </div>

                <div class="view-details">
                    <div class="view-detail-row">
                        <span class="view-detail-label">Full Name:</span>
                        <span class="view-detail-value" id="view_full_name"></span>
                    </div>

                    <div class="view-detail-row">
                        <span class="view-detail-label">Email:</span>
                        <span class="view-detail-value" id="view_email"></span>
                    </div>

                    <div class="view-detail-row">
                        <span class="view-detail-label">Phone Number:</span>
                        <span class="view-detail-value" id="view_phone"></span>
                    </div>

                    <div class="view-detail-row">
                        <span class="view-detail-label">Company / Agency Name:</span>
                        <span class="view-detail-value" id="view_agency_name"></span>
                    </div>

                    <div class="view-detail-row">
                        <span class="view-detail-label">License Number:</span>
                        <span class="view-detail-value" id="view_license_number"></span>
                    </div>

                    <div class="view-detail-row">
                        <span class="view-detail-label">Rating:</span>
                        <span class="view-detail-value" id="view_rating_avg"></span>
                    </div>
                    
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.modal-overlay-view {
    display: none;
    align-items: center;
    justify-content: center;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
}

.modal-content-view {
    background: #fff;
    border-radius: 8px;
    max-width: 600px;
    width: 90%;
    padding: 25px 30px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-height: 90vh;
    overflow-y: auto;
}

.view-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}
.view-header h2 {
    font-weight: 700;
    font-size: 1.8rem;
    color: #222;
}
.close-button-view {
    font-size: 28px;
    cursor: pointer;
    color: #555;
    transition: color 0.25s ease;
}
.close-button-view:hover {
    color: #0052cc;
}

.view-content {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
}

.view-pic-container {
    flex: 0 0 140px;
    text-align: center;
}
.view-realtor-avatar {
    width: 140px;
    height: 140px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #ddd;
}

.view-details {
    flex: 1;
    min-width: 280px;
}

.view-detail-row {
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
}
.view-detail-label {
    font-weight: 600;
    color: #333;
}
.view-detail-value {
    color: #555;
    text-align: right;
}

@media (max-width: 600px) {
    .view-content {
        flex-direction: column;
    }
    .view-pic-container {
        margin: 0 auto 25px auto;
    }
}
</style>
