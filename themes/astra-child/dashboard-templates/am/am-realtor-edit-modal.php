<!-- Realtor Edit Modal -->
<div id="amRealtorEditModal" class="modal-overlay-edit" style="display:none; align-items:center; justify-content:center; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999;">
    <div class="modal-content-edit">

        <div class="realtor-edit-container">
            <div class="edit-header">
                <h2>Edit Realtor</h2>
                <span id="closeRealtorEditModal" class="close-button-edit">&times;</span>
            </div>

            <form id="editRealtorForm" method="POST" enctype="multipart/form-data" novalidate>
                <div class="edit-content">
                    <!-- Profile Picture -->
                    <div class="edit-pic-container">
                        <label for="edit_realtor_profile_picture" title="Click to upload profile picture">
                            <img class="edit-realtor-avatar" id="editPreviewAvatar" src="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>" alt="Profile Preview">
                            <input type="file" id="edit_realtor_profile_picture" name="profile_picture" accept="image/*" style="display:none;">
                        </label>
                        <p>Click image to upload</p>
                    </div>

                    <div class="edit-details">
                        <input type="hidden" name="realtor_id" id="edit_realtor_id">

                        <div class="edit-detail-row">
                            <label class="edit-detail-label" for="edit_full_name">Full Name:</label>
                            <input class="edit-detail-value" type="text" id="edit_full_name" name="full_name" required placeholder="Enter full name">
                        </div>

                        <div class="edit-detail-row">
                            <label class="edit-detail-label" for="edit_email">Email:</label>
                            <input class="edit-detail-value" type="email" id="edit_email" name="email" required placeholder="Enter email address">
                        </div>

                        <div class="edit-detail-row">
                            <label class="edit-detail-label" for="edit_phone">Phone Number:</label>
                            <input class="edit-detail-value" type="text" id="edit_phone" name="phone" placeholder="Enter phone number">
                        </div>

                        <div class="edit-detail-row">
                            <label class="edit-detail-label" for="edit_agency_name">Company / Agency Name:</label>
                            <input class="edit-detail-value" type="text" id="edit_agency_name" name="agency_name" placeholder="Enter company/agency name">
                        </div>

                        <div class="edit-detail-row">
                            <label class="edit-detail-label" for="edit_license_number">License Number:</label>
                            <input class="edit-detail-value" type="text" id="edit_license_number" name="license_number" placeholder="Enter license number">
                        </div>

                        <div class="edit-detail-row">
                            <label class="edit-detail-label" for="edit_rating_avg">Rating (optional):</label>
                            <input class="edit-detail-value" type="number" id="edit_rating_avg" name="rating_avg" step="0.1" placeholder="Enter rating">
                        </div>
                    </div>
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" class="edit-submit-btn">Update Realtor</button>
                </div>
            </form>

        </div>

    </div>
</div>

<style>
.modal-overlay-edit {
    display: none;
    align-items: center;
    justify-content: center;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
}

.modal-content-edit {
    background: #fff;
    border-radius: 8px;
    max-width: 600px;
    width: 90%;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    padding: 25px 30px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-height: 90vh;
    overflow-y: auto;
}

.edit-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}
.edit-header h2 {
    font-weight: 700;
    font-size: 1.8rem;
    color: #222;
}
.close-button-edit {
    font-size: 28px;
    cursor: pointer;
    color: #555;
    transition: color 0.25s ease;
}
.close-button-edit:hover {
    color: #0052cc;
}

.edit-content {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
}

.edit-pic-container {
    flex: 0 0 140px;
    text-align: center;
}
.edit-realtor-avatar {
    width: 140px;
    height: 140px;
    object-fit: cover;
    border-radius: 50%;
    cursor: pointer;
    border: 3px solid #ddd;
    transition: border-color 0.3s ease;
}
.edit-realtor-avatar:hover {
    border-color: #0052cc;
}
.edit-pic-container p {
    font-size: 12px;
    color: #888;
    margin-top: 8px;
}

.edit-details {
    flex: 1;
    min-width: 280px;
}

.edit-detail-row {
    margin-bottom: 18px;
    display: flex;
    flex-direction: column;
}
.edit-detail-label {
    font-weight: 600;
    margin-bottom: 6px;
    color: #333;
    font-size: 0.95rem;
}
.edit-detail-value {
    padding: 10px 14px;
    border: 1.5px solid #ccc;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}
.edit-detail-value:focus {
    border-color: #0052cc;
    outline: none;
}

@media (max-width: 600px) {
    .edit-content {
        flex-direction: column;
    }
    .edit-pic-container {
        margin: 0 auto 25px auto;
    }
}
</style>

