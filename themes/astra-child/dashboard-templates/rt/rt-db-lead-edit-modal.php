<div id="rtDbLeadEditModal" class="modal-overlay-rt-db-lead-edit">
    <div class="modal-content-rt-db-lead-edit">
        <div class="rt-db-lead-edit-container">
            <div class="edit-header-rt-db-lead-edit">
                <h2>Edit Lead</h2>
                <span id="closeRtDbLeadEditModal" class="close-button-rt-db-lead-edit">&times;</span>
            </div>

            <form id="editRtDbLeadForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit_rt_db_lead_id" name="client_id">

                <div class="edit-content-rt-db-lead-edit">
                    <div class="edit-pic-container-rt-db-lead-edit">
                        <label for="edit_rt_db_lead_profile_picture" title="Click to upload profile picture">
                            <img class="edit-rt-db-lead-avatar" id="editRtDbLeadPreviewAvatar"
                                src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/default-avatar.jpg'); ?>"
                                alt="Profile Preview">
                            <input type="file" id="edit_rt_db_lead_profile_picture" name="profile_picture" accept="image/*" style="display:none;">
                        </label>
                        <p>Click image to upload</p>
                    </div>

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
                            <label for="edit_rt_db_lead_note">Notes:</label>
                            <textarea id="edit_rt_db_lead_note" name="note" rows="4"></textarea>
                        </div>

                        <div class="edit-detail-row-rt-db-lead-edit">
                            <label for="edit_rt_db_lead_status">Status: *</label>
                            <select id="edit_rt_db_lead_status" name="status" required>
                                <option value="" disabled>Select Status</option>
                                <option value="lead">Lead</option>
                                <option value="active">Active</option>
                            </select>
                        </div>

                        <div class="edit-detail-row-rt-db-lead-edit" id="rtLeadStatusRow" style="display:none;">
                            <label for="edit_rt_db_lead_lead_status">Lead Status:</label>
                            <select id="edit_rt_db_lead_lead_status" name="lead_status">
                                <option value="hot">Hot</option>
                                <option value="warm">Warm</option>
                                <option value="cold" selected>Cold</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" class="edit-submit-btn-rt-db-lead-edit">Update Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('rtDbLeadEditModal');
    const closeBtn = document.getElementById('closeRtDbLeadEditModal');
    const profileInput = document.getElementById('edit_rt_db_lead_profile_picture');
    const form = document.getElementById('editRtDbLeadForm');
    const statusSelect = document.getElementById('edit_rt_db_lead_status');
    const leadRow = document.getElementById('rtLeadStatusRow');

    // Close modal
    if(closeBtn && editModal){
        closeBtn.addEventListener('click', () => editModal.style.display='none');
        editModal.addEventListener('click', e => { if(e.target === editModal) editModal.style.display='none'; });
    }

    // Show/hide lead status
    if(statusSelect && leadRow){
        statusSelect.addEventListener('change', function(){
            leadRow.style.display = this.value === 'lead' ? 'block' : 'none';
        });
    }

    // Profile image preview
    if(profileInput){
        profileInput.addEventListener('change', function() {
            const file = this.files[0];
            if(file){
                const reader = new FileReader();
                reader.onload = e => document.getElementById('editRtDbLeadPreviewAvatar').src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }

    // Submit update via AJAX
    if(form){
        form.addEventListener('submit', async function(e){
            e.preventDefault();
            const clientId = document.getElementById('edit_rt_db_lead_id').value;
            if(!clientId) return alert('Missing lead ID');

            const formData = new FormData(form);
            formData.append('action', 'update_dashboard_lead_ajax'); // <-- keep your original PHP AJAX action name
            formData.append('nonce', rtDashboardAjax.edit_nonce);

            try {
                const res = await fetch(rtDashboardAjax.ajax_url, { method: 'POST', body: formData });
                const result = await res.json();

                if(result.success){
                    alert('Lead updated successfully!');
                    editModal.style.display='none';

                    // Refresh Leads table
                    if(typeof fetchLeads === 'function'){
                        fetchLeads({ page:1, rows:parseInt(document.getElementById('leadsRows').value,10), search:document.getElementById('leadsSearch').value.trim(), bodyId:'leadsBody', paginationId:'leadsPagination' });
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
