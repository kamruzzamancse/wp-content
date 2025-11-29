<!-- Active Client Create Modal -->
<div id="rtDbClientCreateModal" class="modal-overlay-rt-db-lead" style="display:none;">
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
                        <label for="create_rt_db_client_profile_picture">
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
                            <label for="create_rt_db_client_first_name">First Name: *</label>
                            <input type="text" id="create_rt_db_client_first_name" name="first_name"
                                   required placeholder="Enter first name">
                        </div>

                        <div class="create-detail-row-rt-db-lead">
                            <label for="create_rt_db_client_second_name">Second Name:</label>
                            <input type="text" id="create_rt_db_client_second_name" name="second_name"
                                   placeholder="Enter second name">
                        </div>

                        <div class="create-detail-row-rt-db-lead">
                            <label for="create_rt_db_client_first_email">Primary Email: *</label>
                            <input type="email" id="create_rt_db_client_first_email" name="first_email"
                                   required placeholder="Enter primary email">
                        </div>

                        <div class="create-detail-row-rt-db-lead">
                            <label for="create_rt_db_client_second_email">Secondary Email:</label>
                            <input type="email" id="create_rt_db_client_second_email" name="second_email"
                                   placeholder="Enter secondary email">
                        </div>

                        <div class="create-detail-row-rt-db-lead">
                            <label for="create_rt_db_client_first_phone">Primary Phone:</label>
                            <input type="text" id="create_rt_db_client_first_phone" name="first_phone"
                                   placeholder="Enter primary phone number">
                        </div>

                        <div class="create-detail-row-rt-db-lead">
                            <label for="create_rt_db_client_second_phone">Secondary Phone:</label>
                            <input type="text" id="create_rt_db_client_second_phone" name="second_phone"
                                   placeholder="Enter secondary phone number">
                        </div>

                        <div class="create-detail-row-rt-db-lead">
                            <label for="create_rt_db_client_address">Address:</label>
                            <input type="text" id="create_rt_db_client_address" name="address"
                                   placeholder="Enter full address">
                        </div>

                        <div class="create-detail-row-rt-db-lead">
                            <label for="create_rt_db_client_note">Note:</label>
                            <textarea id="create_rt_db_client_note" name="note" rows="4"
                                      placeholder="Enter note"></textarea>
                        </div>

                    </div>
                </div>

                <!-- Submit Button -->
                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" id="createClientSubmitBtn" class="create-submit-btn-rt-db-lead">
                        Create Client
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const modal = document.getElementById('rtDbClientCreateModal');
    const openBtn = document.getElementById('addActiveBtn');
    const closeBtn = document.getElementById('closeRtDbClientCreateModal');
    const form = document.getElementById('createRtDbClientForm');
    const submitBtn = document.getElementById('createClientSubmitBtn');

    const profileInput = document.getElementById('create_rt_db_client_profile_picture');
    const previewAvatar = document.getElementById('createRtDbClientPreviewAvatar');

    let createSubmitting = false; // Prevent multiple submissions

    /* ----------------------
       BLOCK ENTER KEY
    ---------------------- */
    form.addEventListener('keydown', function(e){
        if(e.key === "Enter"){
            e.preventDefault();
        }
    });

    /* ----------------------
        OPEN / CLOSE MODAL
    ---------------------- */
    if(openBtn) openBtn.addEventListener('click', () => modal.style.display = 'flex');

    if(closeBtn){
        closeBtn.addEventListener('click', () => modal.style.display = 'none');
        modal.addEventListener('click', e => { if(e.target === modal) modal.style.display = 'none'; });
        document.addEventListener('keydown', e => { if(e.key === 'Escape') modal.style.display = 'none'; });
    }

    /* ----------------------
       PROFILE PREVIEW
    ---------------------- */
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
