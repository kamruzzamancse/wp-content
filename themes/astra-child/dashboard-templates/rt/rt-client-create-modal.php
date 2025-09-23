<!-- Realtor - Client Create Modal -->
<div id="rmRealtorClientCreateModal" class="modal-overlay-realtor-client" style="display:none; align-items:center; justify-content:center; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999;">
    <div class="modal-content-realtor-client">

        <div class="realtor-client-create-container">
            <div class="create-header-realtor-client">
                <h2>Create New Client</h2>
                <span id="closeRealtorClientCreateModal" class="close-button-realtor-client">&times;</span>
            </div>

            <form id="createRealtorClientForm" method="POST" enctype="multipart/form-data" novalidate>
                <div class="create-content-realtor-client">
                    <div class="create-pic-container-realtor-client">
                        <label for="create_realtor_client_profile_picture" title="Click to upload profile picture">
                            <img class="create-realtor-client-avatar" id="createRealtorClientPreviewAvatar" 
                                src="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>" 
                                alt="Profile Preview">
                            <input type="file" id="create_realtor_client_profile_picture" name="realtor_client_profile_picture" accept="image/*" style="display:none;">
                        </label>
                        <p>Click image to upload</p>
                    </div>

                    <div class="create-details-realtor-client">

                        <div class="create-detail-row-realtor-client">
                            <label class="create-detail-label-realtor-client" for="create_realtor_client_full_name">Full Name:</label>
                            <input class="create-detail-value-realtor-client" type="text" id="create_realtor_client_full_name" name="realtor_client_full_name" required placeholder="Enter full name">
                        </div>

                        <div class="create-detail-row-realtor-client">
                            <label class="create-detail-label-realtor-client" for="create_realtor_client_email">Email:</label>
                            <input class="create-detail-value-realtor-client" type="email" id="create_realtor_client_email" name="realtor_client_email" required placeholder="Enter email address">
                        </div>

                        <div class="create-detail-row-realtor-client">
                            <label class="create-detail-label-realtor-client" for="create_realtor_client_phone">Phone Number:</label>
                            <input class="create-detail-value-realtor-client" type="text" id="create_realtor_client_phone" name="realtor_client_phone" placeholder="Enter phone number">
                        </div>

                        <div class="create-detail-row-realtor-client">
                            <label class="create-detail-label-realtor-client" for="create_realtor_client_address">Address:</label>
                            <input class="create-detail-value-realtor-client" type="text" id="create_realtor_client_address" name="realtor_client_address" placeholder="Enter address">
                        </div>

                        <div class="create-detail-row-realtor-client">
                            <label class="create-detail-label-realtor-client" for="create_realtor_client_note">Note:</label>
                            <textarea class="create-detail-value-realtor-client" id="create_realtor_client_note" name="realtor_client_note" rows="4" placeholder="Enter note"></textarea>
                        </div>

                        <!-- New dropdown for Lead / Active -->
                        <div class="create-detail-row-realtor-client">
                            <label class="create-detail-label-realtor-client" for="create_realtor_client_status">Status:</label>
                            <select class="create-detail-value-realtor-client" id="create_realtor_client_status" name="realtor_client_status" required>
                                <option value="" disabled selected>Select Status</option>
                                <option value="lead">Lead</option>
                                <option value="active">Active</option>
                            </select>
                        </div>

                    </div>
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" class="create-submit-btn-realtor-client">Create Client</button>
                </div>
            </form>
        </div>

    </div>
</div>

<style>
/* Modal overlay and centering */
.modal-overlay-realtor-client {
    display: none;
    align-items: center;
    justify-content: center;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
}

/* Modal content box */
.modal-content-realtor-client {
    background: #fff;
    border-radius: 8px;
    max-width: 600px;
    width: 90%;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
    padding: 25px 30px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-height: 90vh;
    overflow-y: auto;
}

/* Header */
.create-header-realtor-client {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}
.create-header-realtor-client h2 {
    font-weight: 700;
    font-size: 1.8rem;
    color: #222;
}
.close-button-realtor-client {
    font-size: 28px;
    cursor: pointer;
    color: #555;
    transition: color 0.25s ease;
}
.close-button-realtor-client:hover {
    color: #0052cc;
}

/* Flex container for avatar and form */
.create-content-realtor-client {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
}

/* Avatar container */
.create-pic-container-realtor-client {
    flex: 0 0 140px;
    text-align: center;
}
.create-realtor-client-avatar {
    width: 140px;
    height: 140px;
    object-fit: cover;
    border-radius: 50%;
    cursor: pointer;
    border: 3px solid #ddd;
    transition: border-color 0.3s ease;
}
.create-realtor-client-avatar:hover {
    border-color: #0052cc;
}
.create-pic-container-realtor-client p {
    font-size: 12px;
    color: #888;
    margin-top: 8px;
}

/* Details container */
.create-details-realtor-client {
    flex: 1;
    min-width: 280px;
}

/* Form row */
.create-detail-row-realtor-client {
    margin-bottom: 18px;
    display: flex;
    flex-direction: column;
}
.create-detail-label-realtor-client {
    font-weight: 600;
    margin-bottom: 6px;
    color: #333;
    font-size: 0.95rem;
}
.create-detail-value-realtor-client {
    padding: 10px 14px;
    border: 1.5px solid #ccc;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}
.create-detail-value-realtor-client:focus {
    border-color: #0052cc;
    outline: none;
}

/* Submit button */
.create-submit-btn-realtor-client {
    background-color: #0052cc;
    border: none;
    color: white;
    padding: 10px 25px;
    font-size: 1.1rem;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.25s ease;
}
.create-submit-btn-realtor-client:hover {
    background-color: #003d99;
}

/* Responsive adjustments */
@media (max-width: 600px) {
    .create-content-realtor-client {
        flex-direction: column;
    }
    .create-pic-container-realtor-client {
        margin: 0 auto 25px auto;
    }
}
</style>
