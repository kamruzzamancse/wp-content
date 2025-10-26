<div id="rmDashboardClientEditModal" class="modal-overlay-dashboard-client">
    <div class="modal-content-dashboard-client">
        <div class="dashboard-client-edit-container">
            <div class="edit-header-dashboard-client">
                <h2>Edit Client</h2>
                <span id="closeDashboardClientEditModal" class="close-button-dashboard-client">&times;</span>
            </div>

            <form id="editDashboardClientForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit_dashboard_client_id" name="client_id">
                
                <div class="edit-content-dashboard-client">
                    <div class="edit-pic-container-dashboard-client">
                        <label for="edit_dashboard_client_profile_picture" title="Click to upload profile picture">
                            <img class="edit-dashboard-client-avatar" id="editDashboardClientPreviewAvatar" 
                                src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/default-avatar.jpg'); ?>" 
                                alt="Profile Preview">
                            <input type="file" id="edit_dashboard_client_profile_picture" name="profile_picture" accept="image/*" style="display:none;">
                        </label>
                        <p>Click image to upload</p>
                    </div>

                    <div class="edit-details-dashboard-client">
                        <div class="edit-detail-row-dashboard-client">
                            <label for="edit_dashboard_client_full_name">Full Name: *</label>
                            <input type="text" id="edit_dashboard_client_full_name" name="full_name" required>
                        </div>

                        <div class="edit-detail-row-dashboard-client">
                            <label for="edit_dashboard_client_email">Email: *</label>
                            <input type="email" id="edit_dashboard_client_email" name="email" required>
                        </div>

                        <div class="edit-detail-row-dashboard-client">
                            <label for="edit_dashboard_client_phone">Phone:</label>
                            <input type="text" id="edit_dashboard_client_phone" name="phone">
                        </div>

                        <div class="edit-detail-row-dashboard-client">
                            <label for="edit_dashboard_client_notes">Notes:</label>
                            <textarea id="edit_dashboard_client_notes" name="note" rows="4"></textarea>
                        </div>

                        <div class="edit-detail-row-dashboard-client">
                            <label for="edit_dashboard_client_status">Status: *</label>
                            <select id="edit_dashboard_client_status" name="status" required>
                                <option value="" disabled>Select Status</option>
                                <option value="lead">Lead</option>
                                <option value="active">Active</option>
                            </select>
                        </div>

                        <div class="edit-detail-row-dashboard-client" id="leadStatusRow" style="display:none;">
                            <label for="edit_dashboard_lead_status">Lead Status:</label>
                            <select id="edit_dashboard_lead_status" name="lead_status">
                                <option value="hot">Hot</option>
                                <option value="warm">Warm</option>
                                <option value="cold" selected>Cold</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" class="edit-submit-btn-dashboard-client">Update Client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('rmDashboardClientEditModal');
    const closeBtn = document.getElementById('closeDashboardClientEditModal');
    const profileInput = document.getElementById('edit_dashboard_client_profile_picture');
    const form = document.getElementById('editDashboardClientForm');
    const statusSelect = document.getElementById('edit_dashboard_client_status');
    const leadRow = document.getElementById('leadStatusRow');

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
                reader.onload = e => document.getElementById('editDashboardClientPreviewAvatar').src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }

    // Submit update via AJAX
    if(form){
        form.addEventListener('submit', async function(e){
            e.preventDefault();
            const clientId = document.getElementById('edit_dashboard_client_id').value;
            if(!clientId) return alert('Missing client ID');

            const formData = new FormData(form);
            formData.append('action', 'update_dashboard_client_ajax');
            formData.append('nonce', rtDashboardAjax.edit_nonce);

            try {
                const res = await fetch(rtDashboardAjax.ajax_url, { method: 'POST', body: formData });
                const result = await res.json();

                if(result.success){
                    alert('Client updated successfully!');
                    editModal.style.display='none';

                    // Refresh tables
                    if(typeof fetchTable === 'function'){
                        fetchTable({tableType:'active',page:1,rows:10,search:'',tbodyId:'activeClientsBody',paginationId:'activeClientsPagination'});
                        fetchTable({tableType:'leads',page:1,rows:10,search:'',tbodyId:'leadsBody',paginationId:'leadsPagination'});
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
.modal-overlay-dashboard-client {
    display: none;
    align-items: center;
    justify-content: center;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
}
.modal-content-dashboard-client {
    background: #fff;
    border-radius: 8px;
    max-width: 600px;
    width: 90%;
    box-shadow: 0 6px 18px rgba(0,0,0,0.12);
    padding: 25px 30px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-height: 90vh;
    overflow-y: auto;
}
.edit-header-dashboard-client {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}
.edit-header-dashboard-client h2 { font-weight: 700; font-size: 1.8rem; color: #222; }
.close-button-dashboard-client { font-size:28px; cursor:pointer; color:#555; transition: color 0.25s ease; }
.close-button-dashboard-client:hover { color:#0052cc; }
.edit-content-dashboard-client { display:flex; gap:30px; flex-wrap:wrap; }
.edit-pic-container-dashboard-client { flex:0 0 140px; text-align:center; }
.edit-dashboard-client-avatar { width:140px; height:140px; object-fit:cover; border-radius:50%; cursor:pointer; border:3px solid #ddd; transition:border-color 0.3s ease; }
.edit-dashboard-client-avatar:hover { border-color:#0052cc; }
.edit-pic-container-dashboard-client p { font-size:12px; color:#888; margin-top:8px; }
.edit-details-dashboard-client { flex:1; min-width:280px; }
.edit-detail-row-dashboard-client { margin-bottom:18px; display:flex; flex-direction:column; }
.edit-submit-btn-dashboard-client { background-color:#0052cc; border:none; color:white; padding:10px 25px; font-size:1.1rem; border-radius:8px; cursor:pointer; transition:background-color 0.25s ease; }
.edit-submit-btn-dashboard-client:hover { background-color:#003d99; }
@media (max-width:600px){ .edit-content-dashboard-client { flex-direction:column; } .edit-pic-container-dashboard-client { margin:0 auto 25px auto; } }
</style>
