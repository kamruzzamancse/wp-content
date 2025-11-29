<!-- Active Lead Create Modal -->
<div id="rtDbLeadCreateModal" class="modal-overlay-rt-db-lead" style="display:none;">
    <div class="modal-content-rt-db-lead">
        <div class="rt-db-lead-create-container">

            <!-- Header -->
            <div class="create-header-rt-db-lead">
                <h2>Create New Lead</h2>
                <span id="closeRtDbLeadCreateModal" class="close-button-rt-db-lead">&times;</span>
            </div>

            <!-- Form -->
            <form id="createRtDbLeadForm" method="POST" enctype="multipart/form-data">
                <div class="create-content-rt-db-lead">

                    <!-- Profile Picture -->
                    <div class="create-pic-container-rt-db-lead">
                        <label for="create_rt_db_lead_profile_picture" title="Click to upload profile picture">
                            <img class="create-rt-db-lead-avatar" id="createRtDbLeadPreviewAvatar"
                                 src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/default-avatar.jpg'); ?>"
                                 alt="Profile Preview">
                            <input type="file" id="create_rt_db_lead_profile_picture"
                                   name="rt_db_lead_profile_picture" accept="image/*" style="display:none;">
                        </label>
                        <p>Click image to upload</p>
                    </div>

                    <!-- Lead Details -->
                    <div class="create-details-rt-db-lead">

                        <div class="create-detail-row-rt-db-lead">
                            <label for="create_rt_db_lead_first_name">First Name: *</label>
                            <input type="text" id="create_rt_db_lead_first_name" name="first_name"
                                   required placeholder="Enter first name">
                        </div>

                        <div class="create-detail-row-rt-db-lead">
                            <label for="create_rt_db_lead_second_name">Second Name:</label>
                            <input type="text" id="create_rt_db_lead_second_name" name="second_name"
                                   placeholder="Enter second name">
                        </div>

                        <div class="create-detail-row-rt-db-lead">
                            <label for="create_rt_db_lead_first_email">Primary Email: *</label>
                            <input type="email" id="create_rt_db_lead_first_email" name="first_email"
                                   required placeholder="Enter primary email">
                        </div>

                        <div class="create-detail-row-rt-db-lead">
                            <label for="create_rt_db_lead_second_email">Secondary Email:</label>
                            <input type="email" id="create_rt_db_lead_second_email" name="second_email"
                                   placeholder="Enter secondary email">
                        </div>

                        <div class="create-detail-row-rt-db-lead">
                            <label for="create_rt_db_lead_first_phone">Primary Phone:</label>
                            <input type="text" id="create_rt_db_lead_first_phone" name="first_phone"
                                   placeholder="Enter primary phone number">
                        </div>

                        <div class="create-detail-row-rt-db-lead">
                            <label for="create_rt_db_lead_second_phone">Secondary Phone:</label>
                            <input type="text" id="create_rt_db_lead_second_phone" name="second_phone"
                                   placeholder="Enter secondary phone number">
                        </div>

                        <div class="create-detail-row-rt-db-lead">
                            <label for="create_rt_db_lead_address">Address:</label>
                            <input type="text" id="create_rt_db_lead_address" name="address"
                                   placeholder="Enter full address">
                        </div>

                        <div class="create-detail-row-rt-db-lead">
                            <label for="create_rt_db_lead_note">Note:</label>
                            <textarea id="create_rt_db_lead_note" name="note" rows="4"
                                      placeholder="Enter note"></textarea>
                        </div>

                    </div>
                </div>

                <!-- Submit Button -->
                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" id="createLeadSubmitBtn" class="create-submit-btn-rt-db-lead">
                        Create Lead
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const modal = document.getElementById('rtDbLeadCreateModal');
    const openBtn = document.getElementById('addLeadBtn');
    const closeBtn = document.getElementById('closeRtDbLeadCreateModal');
    const form = document.getElementById('createRtDbLeadForm');
    const submitBtn = document.getElementById('createLeadSubmitBtn');

    const profileInput = document.getElementById('create_rt_db_lead_profile_picture');
    const previewAvatar = document.getElementById('createRtDbLeadPreviewAvatar');

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

    /* ----------------------
       AJAX SUBMIT
    ---------------------- */
    if(form){
        form.addEventListener('submit', async function(e){
            e.preventDefault();

            if(createSubmitting) return;
            createSubmitting = true;

            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';

            const formData = new FormData(form);
            formData.append('action', 'create_dashboard_lead_ajax');
            formData.append('nonce', rtDashboardAjax.create_nonce);

            try {
                const res = await fetch(rtDashboardAjax.ajax_url, { method: 'POST', body: formData });
                const json = await res.json();

                if(json.success){
                    alert('Lead created successfully!');
                    form.reset();
                    previewAvatar.src = rtDashboardAjax.default_avatar;
                    modal.style.display = 'none';

                    if(typeof fetchLeads === 'function'){
                        fetchLeads({ page:1, rows:10, search:'', bodyId:'leadsBody', paginationId:'leadsPagination' });
                    }
                } else {
                    alert('Error: ' + (json.data || 'Unknown error'));
                }

            } catch(err){
                alert('Network error: ' + err.message);
            } finally {
                createSubmitting = false;
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Lead';
            }
        });
    }

});
</script>

<style>
.modal-overlay-rt-db-lead { display: none; align-items:center; justify-content:center; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; }
.modal-content-rt-db-lead { background:#fff; border-radius:8px; max-width:600px; width:90%; box-shadow:0 6px 18px rgba(0,0,0,0.12); padding:25px 30px; max-height:90vh; overflow-y:auto; }
.create-header-rt-db-lead { display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; }
.close-button-rt-db-lead { font-size:28px; cursor:pointer; color:#555; transition: color 0.25s ease; }
.close-button-rt-db-lead:hover { color:#0052cc; }
.create-content-rt-db-lead { display:flex; gap:30px; flex-wrap:wrap; }
.create-pic-container-rt-db-lead { flex:0 0 140px; text-align:center; }
.create-rt-db-lead-avatar { width:140px; height:140px; object-fit:cover; border-radius:50%; cursor:pointer; border:3px solid #ddd; transition:border-color 0.3s ease; }
.create-rt-db-lead-avatar:hover { border-color:#0052cc; }
.create-pic-container-rt-db-lead p { font-size:12px; color:#888; margin-top:8px; }
.create-details-rt-db-lead { flex:1; min-width:280px; }
.create-detail-row-rt-db-lead { margin-bottom:18px; display:flex; flex-direction:column; }
.create-submit-btn-rt-db-lead { background-color:#0052cc; border:none; color:white; padding:10px 25px; font-size:1.1rem; border-radius:8px; cursor:pointer; transition: background-color 0.25s ease; }
.create-submit-btn-rt-db-lead:hover { background-color:#003d99; }
@media (max-width:600px){ .create-content-rt-db-lead { flex-direction:column; } .create-pic-container-rt-db-lead { margin:0 auto 25px auto; } }
</style>
