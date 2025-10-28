<div id="rtDbClientCreateModal" class="modal-overlay-rt-db-active-create">
    <div class="modal-content-rt-db-active-create">
        <div class="rt-db-active-create-container">

            <!-- Header -->
            <div class="create-header-rt-db-active-create">
                <h2>Create New Active Client</h2>
                <span id="closeRtDbClientCreateModal" class="close-button-rt-db-active-create">&times;</span>
            </div>

            <!-- Form -->
            <form id="rtDbClientCreateForm" method="POST" enctype="multipart/form-data">
                <div class="create-content-rt-db-active-create">

                    <!-- Profile Picture -->
                    <div class="create-pic-container-rt-db-active-create">
                        <label for="create_rt_db_active_profile_picture" title="Click to upload profile picture">
                            <img class="create-rt-db-active-avatar" id="createRtDbActivePreviewAvatar"
                                 src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/default-avatar.jpg'); ?>"
                                 alt="Profile Preview">
                            <input type="file" id="create_rt_db_active_profile_picture"
                                   name="profile_picture" accept="image/*" style="display:none;">
                        </label>
                        <p>Click image to upload</p>
                    </div>

                    <!-- Client Details -->
                    <div class="create-details-rt-db-active-create">

                        <div class="create-detail-row-rt-db-active-create">
                            <label for="create_rt_db_active_full_name">Full Name: *</label>
                            <input type="text" id="create_rt_db_active_full_name" name="full_name" required placeholder="Enter full name">
                        </div>

                        <div class="create-detail-row-rt-db-active-create">
                            <label for="create_rt_db_active_email">Email: *</label>
                            <input type="email" id="create_rt_db_active_email" name="email" required placeholder="Enter email address">
                        </div>

                        <div class="create-detail-row-rt-db-active-create">
                            <label for="create_rt_db_active_phone">Phone:</label>
                            <input type="text" id="create_rt_db_active_phone" name="phone" placeholder="Enter phone number">
                        </div>

                        <div class="create-detail-row-rt-db-active-create">
                            <label for="create_rt_db_active_note">Note:</label>
                            <textarea id="create_rt_db_active_note" name="note" rows="4" placeholder="Enter note"></textarea>
                        </div>

                    </div>
                </div>

                <!-- Submit Button -->
                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" class="create-submit-btn-rt-db-active-create">Add Client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const createModal = document.getElementById('rtDbActiveCreateModal');
    const addClientBtn = document.querySelector('#addClientBtn');
    const closeBtn = document.getElementById('closeRtDbActiveCreateModal');
    const form = document.getElementById('createRtDbActiveForm');
    const profileInput = document.getElementById('create_rt_db_active_profile_picture');
    const previewAvatar = document.getElementById('createRtDbActivePreviewAvatar');

    // Open modal
    if(addClientBtn && createModal){
        addClientBtn.addEventListener('click', () => createModal.style.display='flex');
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

    // AJAX submit
    if(form){
        form.addEventListener('submit', async function(e){
            e.preventDefault();
            const formData = new FormData(form);
            formData.append('action', 'create_active_client_ajax');
            formData.append('nonce', rtClientAjax.create_nonce);

            try {
                const response = await fetch(rtClientAjax.ajax_url, { method:'POST', body: formData });
                const result = await response.json();

                if(result.success){
                    alert('Active client created successfully!');
                    form.reset();
                    previewAvatar.src = rtClientAjax.default_avatar;
                    createModal.style.display = 'none';
                    if(typeof fetchClients === 'function'){
                        fetchClients({ page:1, rows:10, search:'', bodyId:'activeClientsBody', paginationId:'activeClientsPagination' });
                    }
                } else {
                    alert('Error: ' + result.data);
                }
            } catch(err){
                alert('Network error: ' + err.message);
            }
        });
    }
});
</script>

<style>
.modal-overlay-rt-db-active-create { display: none; align-items:center; justify-content:center; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; }
.modal-content-rt-db-active-create { background:#fff; border-radius:8px; max-width:600px; width:90%; box-shadow:0 6px 18px rgba(0,0,0,0.12); padding:25px 30px; max-height:90vh; overflow-y:auto; }
.create-header-rt-db-active-create { display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; }
.close-button-rt-db-active-create { font-size:28px; cursor:pointer; color:#555; transition: color 0.25s ease; }
.close-button-rt-db-active-create:hover { color:#0052cc; }
.create-content-rt-db-active-create { display:flex; gap:30px; flex-wrap:wrap; }
.create-pic-container-rt-db-active-create { flex:0 0 140px; text-align:center; }
.create-rt-db-active-avatar { width:140px; height:140px; object-fit:cover; border-radius:50%; cursor:pointer; border:3px solid #ddd; transition:border-color 0.3s ease; }
.create-rt-db-active-avatar:hover { border-color:#0052cc; }
.create-pic-container-rt-db-active-create p { font-size:12px; color:#888; margin-top:8px; }
.create-details-rt-db-active-create { flex:1; min-width:280px; }
.create-detail-row-rt-db-active-create { margin-bottom:18px; display:flex; flex-direction:column; }
.create-submit-btn-rt-db-active-create { background-color:#0052cc; border:none; color:white; padding:10px 25px; font-size:1.1rem; border-radius:8px; cursor:pointer; transition: background-color 0.25s ease; }
.create-submit-btn-rt-db-active-create:hover { background-color:#003d99; }
@media (max-width:600px){ .create-content-rt-db-active-create { flex-direction:column; } .create-pic-container-rt-db-active-create { margin:0 auto 25px auto; } }
</style>
