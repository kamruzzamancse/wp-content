<!-- Realtor - Client Edit Modal -->
<div id="rmRealtorClientEditModal" class="modal-overlay-realtor-client-edit">
    <div class="modal-content-realtor-client-edit">

        <div class="realtor-client-edit-container">
            <div class="edit-header-realtor-client">
                <h2>Edit Client</h2>
                <span id="closeRealtorClientEditModal" class="close-button-realtor-client-edit">&times;</span>
            </div>

            <form id="editRealtorClientForm" method="POST" enctype="multipart/form-data" novalidate>
                <div class="edit-content-realtor-client">
                    <div class="edit-pic-container-realtor-client">
                        <label for="edit_realtor_client_profile_picture" title="Click to upload profile picture">
                            <img class="edit-realtor-client-avatar" id="editRealtorClientPreviewAvatar" 
                                src="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>" 
                                alt="Profile Preview">
                            <input type="file" id="edit_realtor_client_profile_picture" name="realtor_client_profile_picture" accept="image/*" style="display:none;">
                        </label>
                        <p>Click image to upload</p>
                    </div>

                    <div class="edit-details-realtor-client">
                        <input type="hidden" name="realtor_client_id" id="edit_realtor_client_id">

                        <div class="edit-detail-row-realtor-client">
                            <label class="edit-detail-label-realtor-client" for="edit_realtor_client_full_name">Full Name:</label>
                            <input class="edit-detail-value-realtor-client" type="text" id="edit_realtor_client_full_name" name="realtor_client_full_name" required placeholder="Enter full name">
                        </div>

                        <div class="edit-detail-row-realtor-client">
                            <label class="edit-detail-label-realtor-client" for="edit_realtor_client_email">Email:</label>
                            <input class="edit-detail-value-realtor-client" type="email" id="edit_realtor_client_email" name="realtor_client_email" required placeholder="Enter email address">
                        </div>

                        <div class="edit-detail-row-realtor-client">
                            <label class="edit-detail-label-realtor-client" for="edit_realtor_client_phone">Phone Number:</label>
                            <input class="edit-detail-value-realtor-client" type="text" id="edit_realtor_client_phone" name="realtor_client_phone" placeholder="Enter phone number">
                        </div>

                        <div class="edit-detail-row-realtor-client">
                            <label class="edit-detail-label-realtor-client" for="edit_realtor_client_preferred_location">Preferred Location:</label>
                            <input class="edit-detail-value-realtor-client" type="text" id="edit_realtor_client_preferred_location" name="preferred_location" placeholder="Enter preferred location">
                        </div>

                        <div class="edit-detail-row-realtor-client">
                            <label class="edit-detail-label-realtor-client" for="edit_realtor_client_note">Note:</label>
                            <textarea class="edit-detail-value-realtor-client" id="edit_realtor_client_note" name="realtor_client_note" rows="4" placeholder="Enter note"></textarea>
                        </div>

                        <!-- New dropdown for Lead / Active -->
                        <div class="edit-detail-row-realtor-client">
                            <label class="edit-detail-label-realtor-client" for="edit_realtor_client_status">Status:</label>
                            <select class="edit-detail-value-realtor-client" id="edit_realtor_client_status" name="realtor_client_status" required>
                                <option value="" disabled selected>Select Status</option>
                                <option value="lead">Lead</option>
                                <option value="active">Active</option>
                            </select>
                        </div>

                    </div>
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" class="edit-submit-btn-realtor-client">Update Client</button>
                </div>
            </form>
        </div>

    </div>
</div>

<style>
.modal-overlay-realtor-client-edit {
    display: none;
    align-items: center;
    justify-content: center;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
}

.modal-content-realtor-client-edit {
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

.edit-header-realtor-client {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}
.edit-header-realtor-client h2 {
    font-weight: 700;
    font-size: 1.8rem;
    color: #222;
}
.close-button-realtor-client-edit {
    font-size: 28px;
    cursor: pointer;
    color: #555;
    transition: color 0.25s ease;
}
.close-button-realtor-client-edit:hover {
    color: #0052cc;
}

.edit-content-realtor-client {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
}

.edit-pic-container-realtor-client {
    flex: 0 0 140px;
    text-align: center;
}
.edit-realtor-client-avatar {
    width: 140px;
    height: 140px;
    object-fit: cover;
    border-radius: 50%;
    cursor: pointer;
    border: 3px solid #ddd;
    transition: border-color 0.3s ease;
}
.edit-realtor-client-avatar:hover {
    border-color: #0052cc;
}
.edit-pic-container-realtor-client p {
    font-size: 12px;
    color: #888;
    margin-top: 8px;
}

.edit-details-realtor-client {
    flex: 1;
    min-width: 280px;
}

.edit-detail-row-realtor-client {
    margin-bottom: 18px;
    display: flex;
    flex-direction: column;
}
.edit-detail-label-realtor-client {
    font-weight: 600;
    margin-bottom: 6px;
    color: #333;
    font-size: 0.95rem;
}
.edit-detail-value-realtor-client {
    padding: 10px 14px;
    border: 1.5px solid #ccc;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}
.edit-detail-value-realtor-client:focus {
    border-color: #0052cc;
    outline: none;
}

.edit-submit-btn-realtor-client {
    background-color: #0052cc;
    border: none;
    color: white;
    padding: 10px 25px;
    font-size: 1.1rem;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.25s ease;
}
.edit-submit-btn-realtor-client:hover {
    background-color: #003d99;
}

@media (max-width: 600px) {
    .edit-content-realtor-client {
        flex-direction: column;
    }
    .edit-pic-container-realtor-client {
        margin: 0 auto 25px auto;
    }
}
</style>
