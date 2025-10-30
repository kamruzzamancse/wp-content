<!-- Realtor Create Modal -->
<div id="amRealtorCreateModal" class="modal-overlay-create" style="display:none; align-items:center; justify-content:center; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999;">
    <div class="modal-content-create">

        <div class="realtor-create-container">
            <div class="create-header">
                <h2>Create New Realtor</h2>
                <span id="closeRealtorCreateModal" class="close-button-create">&times;</span>
            </div>

            <form id="createRealtorForm" method="POST" enctype="multipart/form-data" novalidate>
                <div class="create-content">
                    <!-- Profile Picture -->
                    <div class="create-pic-container">
                        <label for="create_realtor_profile_picture" title="Click to upload profile picture">
                            <img class="create-realtor-avatar" id="createPreviewAvatar" src="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>" alt="Profile Preview">
                            <input type="file" id="create_realtor_profile_picture" name="profile_picture" accept="image/*" style="display:none;">
                        </label>
                        <p>Click image to upload</p>
                    </div>

                    <div class="create-details">
                        <div class="create-detail-row">
                            <label class="create-detail-label" for="full_name">Full Name:</label>
                            <input class="create-detail-value" type="text" id="full_name" name="full_name" required placeholder="Enter full name">
                        </div>

                        <div class="create-detail-row">
                            <label class="create-detail-label" for="email">Email:</label>
                            <input class="create-detail-value" type="email" id="email" name="email" required placeholder="Enter email address">
                        </div>

                        <div class="create-detail-row">
                            <label class="create-detail-label" for="phone">Phone Number:</label>
                            <input class="create-detail-value" type="text" id="phone" name="phone" placeholder="Enter phone number">
                        </div>

                        <div class="create-detail-row">
                            <label class="create-detail-label" for="agency_name">Company / Agency Name:</label>
                            <input class="create-detail-value" type="text" id="agency_name" name="agency_name" placeholder="Enter company/agency name">
                        </div>

                        <div class="create-detail-row">
                            <label class="create-detail-label" for="license_number">License Number:</label>
                            <input class="create-detail-value" type="text" id="license_number" name="license_number" placeholder="Enter license number">
                        </div>

                        <div class="create-detail-row">
                            <label class="create-detail-label" for="rating_avg">Rating (optional):</label>
                            <input class="create-detail-value" type="number" id="rating_avg" name="rating_avg" step="0.1" placeholder="Enter rating">
                        </div>
                    </div>
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" class="create-submit-btn">Create Realtor</button>
                </div>
            </form>

        </div>

    </div>
</div>

<style>
/* Modal overlay and centering */
.modal-overlay-create {
    display: none;
    align-items: center;
    justify-content: center;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
}

/* Modal content box */
.modal-content-create {
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

/* Header */
.create-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}
.create-header h2 {
    font-weight: 700;
    font-size: 1.8rem;
    color: #222;
}
.close-button-create {
    font-size: 28px;
    cursor: pointer;
    color: #555;
    transition: color 0.25s ease;
}
.close-button-create:hover {
    color: #0052cc;
}

/* Flex container for avatar and form */
.create-content {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
}

/* Avatar container */
.create-pic-container {
    flex: 0 0 140px;
    text-align: center;
}
.create-realtor-avatar {
    width: 140px;
    height: 140px;
    object-fit: cover;
    border-radius: 50%;
    cursor: pointer;
    border: 3px solid #ddd;
    transition: border-color 0.3s ease;
}
.create-realtor-avatar:hover {
    border-color: #0052cc;
}
.create-pic-container p {
    font-size: 12px;
    color: #888;
    margin-top: 8px;
}

/* Details container */
.create-details {
    flex: 1;
    min-width: 280px;
}

/* Form row */
.create-detail-row {
    margin-bottom: 18px;
    display: flex;
    flex-direction: column;
}
.create-detail-label {
    font-weight: 600;
    margin-bottom: 6px;
    color: #333;
    font-size: 0.95rem;
}
.create-detail-value {
    padding: 10px 14px;
    border: 1.5px solid #ccc;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}
.create-detail-value:focus {
    border-color: #0052cc;
    outline: none;
}

/* Submit button */
button[type="submit"] {
    background-color: #0052cc;
    border: none;
    color: white;
    padding: 14px 32px;
    font-size: 1.1rem;
    border-radius: 8px;
    cursor: pointer;
}

button[type="submit"]:hover {
    background-color: #003d99;
}

/* Responsive adjustments */
@media (max-width: 600px) {
    .create-content {
        flex-direction: column;
    }
    .create-pic-container {
        margin: 0 auto 25px auto;
    }
}
</style>
