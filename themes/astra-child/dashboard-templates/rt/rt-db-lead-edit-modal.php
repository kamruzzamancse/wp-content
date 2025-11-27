<div id="rtDbLeadEditModal" class="modal-overlay-rt-db-lead-edit">
    <div class="modal-content-rt-db-lead-edit">
        <div class="rt-db-lead-edit-container">

            <!-- Header -->
            <div class="edit-header-rt-db-lead-edit">
                <h2>Edit Lead</h2>
                <span id="closeRtDbLeadEditModal" class="close-button-rt-db-lead-edit">&times;</span>
            </div>

            <!-- Form -->
            <form id="editRtDbLeadForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit_rt_db_lead_id" name="client_id">

                <div class="edit-content-rt-db-lead-edit">

                    <!-- Profile Picture -->
                    <div class="edit-pic-container-rt-db-lead-edit">
                        <label for="edit_rt_db_lead_profile_picture" title="Click to upload profile picture">
                            <img 
                                class="edit-rt-db-lead-avatar" 
                                id="editRtDbLeadPreviewAvatar"
                                src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/default-avatar.jpg'); ?>" 
                                alt="Profile Preview"
                            >
                            <input 
                                type="file" 
                                id="edit_rt_db_lead_profile_picture" 
                                name="profile_picture" 
                                accept="image/*" 
                                style="display:none;"
                            >
                        </label>
                        <p>Click image to upload</p>
                    </div>

                    <!-- Lead Details -->
                    <div class="edit-details-rt-db-lead-edit">

                        <div class="edit-detail-row-rt-db-lead-edit">
                            <label for="edit_rt_db_lead_full_name">Full Name: *</label>
                            <input type="text" id="edit_rt_db_lead_full_name" name="full_name" required>
                        </div>

                        <div class="edit-detail-row-rt-db-lead-edit">
                            <label for="edit_rt_db_lead_email">Email: *</label>
                            <input type="email" id="edit_rt_db_lead_email" name="email" required>
                        </div>

                        <div class="edit-detail-row-rt-db-lead-edit">
                            <label for="edit_rt_db_lead_phone">Phone:</label>
                            <input type="text" id="edit_rt_db_lead_phone" name="phone">
                        </div>

                        <div class="edit-detail-row-rt-db-lead-edit">
                            <label for="edit_rt_db_lead_address">Address:</label>
                            <textarea id="edit_rt_db_lead_address" name="address" rows="4" placeholder="Enter address"></textarea>
                        </div>

                        <div class="edit-detail-row-rt-db-lead-edit">
                            <label for="edit_rt_db_lead_note">Notes:</label>
                            <textarea id="edit_rt_db_lead_note" name="note" rows="4"></textarea>
                        </div>

                        <div class="edit-detail-row-rt-db-lead-edit" id="rtLeadStatusRow">
                            <label for="edit_rt_db_lead_lead_status">Lead Status:</label>
                            <select id="edit_rt_db_lead_lead_status" name="lead_status">
                                <option value="hot">Hot</option>
                                <option value="warm">Warm</option>
                                <option value="cold">Cold</option>
                            </select>
                        </div>

                    </div>
                </div>

                <!-- Submit -->
                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" class="edit-submit-btn-rt-db-lead-edit">Update Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const editModal = document.getElementById('rtDbLeadEditModal');
    const profileInput = document.getElementById('edit_rt_db_lead_profile_picture');
    const leadRow = document.getElementById('rtLeadStatusRow');
    const editLeadForm = document.getElementById('editRtDbLeadForm');

    // Close modal
    const closeBtn = document.getElementById('closeRtDbLeadEditModal');
    if (closeBtn && editModal) {
        closeBtn.addEventListener('click', () => editModal.style.display = 'none');
        editModal.addEventListener('click', e => { if (e.target === editModal) editModal.style.display = 'none'; });
    }

    // Profile image preview on file select
    if (profileInput) {
        profileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => document.getElementById('editRtDbLeadPreviewAvatar').src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }

    // Edit Lead button click handler
    document.querySelectorAll('.editLeadBtn').forEach(btn => {
        btn.addEventListener('click', async function () {
            const row = this.closest('tr');
            const clientId = row.dataset.clientId;
            if (!clientId) return;

            editModal.style.display = 'flex';

            const fd = new FormData();
            fd.append('action', 'fetch_dashboard_lead_ajax');
            fd.append('nonce', rtDashboardAjax.edit_nonce);
            fd.append('client_id', clientId);

            try {
                const res = await fetch(rtDashboardAjax.ajax_url, { method: 'POST', body: fd });
                const result = await res.json();

                if (result.success) {
                    const lead = result.data;

                    // Populate form fields
                    document.getElementById('edit_rt_db_lead_id').value = lead.client_id || '';
                    document.getElementById('edit_rt_db_lead_full_name').value = lead.full_name || '';
                    document.getElementById('edit_rt_db_lead_email').value = lead.email || '';
                    document.getElementById('edit_rt_db_lead_phone').value = lead.phone || '';
                    document.getElementById('edit_rt_db_lead_address').value = lead.address || '';
                    document.getElementById('edit_rt_db_lead_note').value = lead.note || '';
                    document.getElementById('edit_rt_db_lead_status').value = lead.status || 'lead';
                    document.getElementById('edit_rt_db_lead_lead_status').value = lead.lead_status || 'cold';

                    // Robust profile picture handling
                    const avatarImg = document.getElementById('editRtDbLeadPreviewAvatar');
                    if (lead.profile_picture && lead.profile_picture.trim() !== '') {
                        avatarImg.src = lead.profile_picture.startsWith('http')
                            ? lead.profile_picture
                            : `${window.location.origin}${lead.profile_picture}`;
                    } else {
                        avatarImg.src = rtDashboardAjax.default_avatar;
                    }

                    // Show/hide lead status row
                    leadRow.style.display = lead.status === 'lead' ? 'block' : 'none';
                } else {
                    alert('Error fetching lead: ' + (result.data || 'Unknown'));
                    editModal.style.display = 'none';
                }
            } catch (err) {
                alert('Network error: ' + err.message);
                editModal.style.display = 'none';
            }
        });
    });

    // Submit update via AJAX
    if (editLeadForm) {
        editLeadForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';

            const fd = new FormData(this);
            fd.append('action', 'update_dashboard_lead_ajax');
            fd.append('nonce', rtDashboardAjax.edit_nonce);

            try {
                const res = await fetch(rtDashboardAjax.ajax_url, { method: 'POST', body: fd });
                const result = await res.json();

                if (result.success) {
                    alert('Lead updated successfully!');
                    editModal.style.display = 'none';
                    // Refresh leads table if fetchLeads exists
                    if (typeof fetchLeads === 'function') {
                        fetchLeads({
                            page: 1,
                            rows: parseInt(document.getElementById('leadsRows').value, 10),
                            search: document.getElementById('leadsSearch').value.trim(),
                            bodyId: 'leadsBody',
                            paginationId: 'leadsPagination'
                        });
                    }
                } else {
                    alert('Error: ' + (result.data || 'Unknown error'));
                }
            } catch (err) {
                alert('Network error: ' + err.message);
            }

            submitBtn.disabled = false;
            submitBtn.textContent = 'Update Lead';
        });
    }
});

</script>

<style>
.modal-overlay-rt-db-lead-edit {
    display: none;
    align-items: center;
    justify-content: center;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
}
.modal-content-rt-db-lead-edit {
    background: #fff;
    border-radius: 8px;
    max-width: 600px;
    width: 90%;
    padding: 25px 30px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 6px 18px rgba(0,0,0,0.12);
}
.edit-header-rt-db-lead-edit {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}
.close-button-rt-db-lead-edit {
    font-size:28px; cursor:pointer; color:#555; transition: color 0.25s ease;
}
.close-button-rt-db-lead-edit:hover { color:#0052cc; }
.edit-content-rt-db-lead-edit { display:flex; gap:30px; flex-wrap:wrap; }
.edit-pic-container-rt-db-lead-edit { flex:0 0 140px; text-align:center; }
.edit-rt-db-lead-avatar { width:140px; height:140px; object-fit:cover; border-radius:50%; cursor:pointer; border:3px solid #ddd; transition:border-color 0.3s ease; }
.edit-rt-db-lead-avatar:hover { border-color:#0052cc; }
.edit-pic-container-rt-db-lead-edit p { font-size:12px; color:#888; margin-top:8px; }
.edit-details-rt-db-lead-edit { flex:1; min-width:280px; }
.edit-detail-row-rt-db-lead-edit { margin-bottom:18px; display:flex; flex-direction:column; }
.edit-submit-btn-rt-db-lead-edit { background-color:#0052cc; border:none; color:white; padding:10px 25px; font-size:1.1rem; border-radius:8px; cursor:pointer; transition: background-color 0.25s ease; }
.edit-submit-btn-rt-db-lead-edit:hover { background-color:#003d99; }
@media (max-width:600px){ .edit-content-rt-db-lead-edit { flex-direction:column; } .edit-pic-container-rt-db-lead-edit { margin:0 auto 25px auto; } }
</style>
