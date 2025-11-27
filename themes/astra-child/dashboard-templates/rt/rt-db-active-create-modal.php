<div id="rtDbClientCreateModal" class="modal-overlay-rt-db-lead">
    <div class="modal-content-rt-db-lead">
        <div class="rt-db-lead-create-container">

            <!-- Header -->
            <div class="create-header-rt-db-lead">
                <h2>Create Active Client</h2>
                <span id="closeRtDbClientCreateModal" class="close-button-rt-db-lead">&times;</span>
            </div>

            <!-- Form -->
            <form id="createRtDbClientForm" method="POST" enctype="multipart/form-data">
                <div class="create-content-rt-db-lead">

                    <!-- Profile Picture -->
                    <div class="create-pic-container-rt-db-lead">
                        <label for="create_rt_db_client_profile_picture" title="Click to upload profile picture">
                            <img class="create-rt-db-lead-avatar" id="createRtDbClientPreviewAvatar"
                                 src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/default-avatar.jpg'); ?>"
                                 alt="Profile Preview">
                            <input type="file" id="create_rt_db_client_profile_picture"
                                   name="rt_db_client_profile_picture" accept="image/*" style="display:none;">
                        </label>
                        <p>Click image to upload</p>
                    </div>

                    <!-- Client Details -->
                    <div class="create-details-rt-db-lead">

                        <div class="create-detail-row-rt-db-lead">
                            <label for="create_rt_db_client_full_name">Full Name: *</label>
                            <input type="text" id="create_rt_db_client_full_name" name="rt_db_client_full_name" required placeholder="Enter full name">
                        </div>

                        <div class="create-detail-row-rt-db-lead">
                            <label for="create_rt_db_client_email">Email: *</label>
                            <input type="email" id="create_rt_db_client_email" name="rt_db_client_email" required placeholder="Enter email address">
                        </div>

                        <div class="create-detail-row-rt-db-lead">
                            <label for="create_rt_db_client_phone">Phone:</label>
                            <input type="text" id="create_rt_db_client_phone" name="rt_db_client_phone" placeholder="Enter phone number">
                        </div>

                        <div class="create-detail-row-rt-db-lead">
                            <label for="create_rt_db_client_address">Address:</label>
                            <input type="text" id="create_rt_db_client_address" name="rt_db_client_address" placeholder="Enter address">
                        </div>

                        <div class="create-detail-row-rt-db-lead">
                            <label for="create_rt_db_client_note">Note:</label>
                            <textarea id="create_rt_db_client_note" name="rt_db_client_note" rows="4" placeholder="Enter note"></textarea>
                        </div>

                    </div>
                </div>

                <!-- Submit Button -->
                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" class="create-submit-btn-rt-db-lead">Create Client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const createModal = document.getElementById('rtDbClientCreateModal');
    const addActiveBtn = document.getElementById('addActiveBtn');
    const closeBtn = document.getElementById('closeRtDbClientCreateModal');
    const form = document.getElementById('createRtDbClientForm');
    const profileInput = document.getElementById('create_rt_db_client_profile_picture');
    const previewAvatar = document.getElementById('createRtDbClientPreviewAvatar');

    // Open modal
    if(addActiveBtn && createModal){
        addActiveBtn.addEventListener('click', () => createModal.style.display='flex');
    }

    // Close modal
    if(closeBtn && createModal){
        closeBtn.addEventListener('click', () => createModal.style.display='none');
        createModal.addEventListener('click', e => { if(e.target === createModal) createModal.style.display='none'; });
    }

    // Image preview
    if(profileInput){
        profileInput.addEventListener('change', function() {
            const file = this.files[0];
            if(file){
                const reader = new FileReader();
                reader.onload = e => previewAvatar.src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }
});

</script>