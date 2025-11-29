<div id="rtDbClientEditModal" class="modal-overlay-rt-db-lead-edit">
    <div class="modal-content-rt-db-lead-edit">
        <div class="rt-db-lead-edit-container">

            <!-- Header -->
            <div class="edit-header-rt-db-lead-edit">
                <h2>Edit Client</h2>
                <span id="closeRtDbClientEditModal" class="close-button-rt-db-lead-edit">&times;</span>
            </div>

            <!-- Form -->
            <form id="editRtDbClientForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit_rt_db_client_id" name="client_id">

                <div class="edit-content-rt-db-lead-edit">

                    <!-- Profile Picture -->
                    <div class="edit-pic-container-rt-db-lead-edit">
                        <label for="edit_rt_db_client_profile_picture" title="Click to upload profile picture">
                            <img 
                                class="edit-rt-db-lead-avatar" 
                                id="editRtDbClientPreviewAvatar"
                                src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/default-avatar.jpg'); ?>" 
                                alt="Profile Preview"
                            >
                            <input 
                                type="file" 
                                id="edit_rt_db_client_profile_picture" 
                                name="profile_picture" 
                                accept="image/*" 
                                style="display:none;"
                            >
                        </label>
                        <p>Click image to upload</p>
                    </div>

                    <!-- Client Details -->
                    <div class="edit-details-rt-db-lead-edit">

                        <div class="edit-detail-row-rt-db-lead-edit">
                            <label for="edit_rt_db_client_full_name">Full Name: *</label>
                            <input type="text" id="edit_rt_db_client_full_name" name="full_name" required>
                        </div>

                        <div class="edit-detail-row-rt-db-lead-edit">
                            <label for="edit_rt_db_client_email">Email: *</label>
                            <input type="email" id="edit_rt_db_client_email" name="email" required>
                        </div>

                        <div class="edit-detail-row-rt-db-lead-edit">
                            <label for="edit_rt_db_client_phone">Phone:</label>
                            <input type="text" id="edit_rt_db_client_phone" name="phone">
                        </div>

                        <div class="edit-detail-row-rt-db-lead-edit">
                            <label for="edit_rt_db_client_address">Address:</label>
                            <textarea id="edit_rt_db_client_address" name="address" rows="4" placeholder="Enter address"></textarea>
                        </div>

                        <div class="edit-detail-row-rt-db-lead-edit">
                            <label for="edit_rt_db_client_note">Notes:</label>
                            <textarea id="edit_rt_db_client_note" name="note" rows="4"></textarea>
                        </div>

                    </div>
                </div>

                <!-- Submit -->
                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" class="edit-submit-btn-rt-db-lead-edit">Update Client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const editModal = document.getElementById('rtDbClientEditModal');
    const closeBtn = document.getElementById('closeRtDbClientEditModal');
    const profileInput = document.getElementById('edit_rt_db_client_profile_picture');
    const editForm = document.getElementById('editRtDbClientForm');

    // Close modal
    if (closeBtn && editModal) {
        closeBtn.addEventListener('click', () => editModal.style.display = 'none');
        editModal.addEventListener('click', e => { 
            if (e.target === editModal) editModal.style.display = 'none'; 
        });
    }

    // Profile preview
    if (profileInput) {
        profileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = e => document.getElementById('editRtDbClientPreviewAvatar').src = e.target.result;
            reader.readAsDataURL(file);
        });
    }

});
</script>
