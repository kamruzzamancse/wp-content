<!-- Edit Client Modal -->
<div id="rmRealtorClientEditModal" class="modal-overlay-realtor-client">
    <div class="modal-content-realtor-client">
        <div class="realtor-client-edit-container">
            <div class="edit-header-realtor-client">
                <h2>Edit Client</h2>
                <span id="closeRealtorClientEditModal" class="close-button-realtor-client">&times;</span>
            </div>

            <form id="editRealtorClientForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit_realtor_client_id" name="realtor_client_id">

                <div class="edit-content-realtor-client">

                    <!-- Profile Picture -->
                    <div class="edit-pic-container-realtor-client">
                        <label for="edit_realtor_client_profile_picture" title="Click to upload profile picture">
                            <img class="edit-realtor-client-avatar" id="editRealtorClientPreviewAvatar" 
                                 src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/default-avatar.jpg'); ?>" 
                                 alt="Profile Preview">
                            <input type="file" id="edit_realtor_client_profile_picture" name="realtor_client_profile_picture" accept="image/*" style="display:none;">
                        </label>
                        <p>Click image to upload</p>
                    </div>

                    <!-- Client Details -->
                    <div class="edit-details-realtor-client">

                        <div class="edit-detail-row-realtor-client">
                            <label for="edit_realtor_client_first_name">First Name: *</label>
                            <input type="text" id="edit_realtor_client_first_name" name="first_name" required>
                        </div>

                        <div class="edit-detail-row-realtor-client">
                            <label for="edit_realtor_client_second_name">Second Name:</label>
                            <input type="text" id="edit_realtor_client_second_name" name="second_name">
                        </div>

                        <div class="edit-detail-row-realtor-client">
                            <label for="edit_realtor_client_first_email">Primary Email: *</label>
                            <input type="email" id="edit_realtor_client_first_email" name="first_email" required>
                        </div>

                        <div class="edit-detail-row-realtor-client">
                            <label for="edit_realtor_client_second_email">Secondary Email:</label>
                            <input type="email" id="edit_realtor_client_second_email" name="second_email">
                        </div>

                        <div class="edit-detail-row-realtor-client">
                            <label for="edit_realtor_client_first_phone">Primary Phone: *</label>
                            <input type="text" id="edit_realtor_client_first_phone" name="first_phone" required>
                        </div>

                        <div class="edit-detail-row-realtor-client">
                            <label for="edit_realtor_client_second_phone">Secondary Phone:</label>
                            <input type="text" id="edit_realtor_client_second_phone" name="second_phone">
                        </div>

                        <div class="edit-detail-row-realtor-client">
                            <label for="edit_realtor_client_address">Address:</label>
                            <input type="text" id="edit_realtor_client_address" name="address">
                        </div>

                        <div class="edit-detail-row-realtor-client">
                            <label for="edit_realtor_client_notes">Notes:</label>
                            <textarea id="edit_realtor_client_notes" name="note" rows="4"></textarea>
                        </div>

                        <div class="edit-detail-row-realtor-client">
                            <label for="edit_realtor_client_status">Status:</label>
                            <select id="edit_realtor_client_status" name="status">
                                <option value="" disabled selected>Select Status</option>
                                <option value="lead">Lead</option>
                                <option value="active">Active</option>
                            </select>
                        </div>

                    </div>
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" class="edit-submit-btn-realtor-client">Update Client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('rmRealtorClientEditModal');
    const closeBtn = document.getElementById('closeRealtorClientEditModal');
    const profileInput = document.getElementById('edit_realtor_client_profile_picture');
    const form = document.getElementById('editRealtorClientForm');
    // const statusSelect = document.getElementById('edit_realtor_client_status');
    // const leadStatusRow = document.getElementById('leadStatusRow');

    // Close modal
    if(closeBtn && editModal){
        closeBtn.addEventListener('click', () => editModal.style.display='none');
        editModal.addEventListener('click', e => { if(e.target === editModal) editModal.style.display='none'; });
        document.addEventListener('keydown', e => { if(e.key === 'Escape') editModal.style.display='none'; });
    }

    // Profile image preview
    if(profileInput){
        profileInput.addEventListener('change', function() {
            const file = this.files[0];
            if(file){
                const reader = new FileReader();
                reader.onload = e => document.getElementById('editRealtorClientPreviewAvatar').src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }

    // Lead status toggle - REMOVED
    // if(statusSelect){
    //     statusSelect.addEventListener('change', function(){
    //         leadStatusRow.style.display = (this.value === 'lead') ? 'flex' : 'none';
    //     });
    // }

    // Submit update via AJAX
    if(form){
        form.addEventListener('submit', async function(e){
            e.preventDefault();
            const clientId = document.getElementById('edit_realtor_client_id').value;
            if(!clientId) return alert('Missing client ID');

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;

            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';

            try {
                const formData = new FormData(form);
                formData.append('action', 'realtor_update_client');
                formData.append('nonce', rtClientAjax.edit_nonce);

                const res = await fetch(rtClientAjax.ajax_url, { method: 'POST', body: formData });
                const result = await res.json();

                if(result.success){
                    alert('Client updated successfully!');
                    editModal.style.display='none';
                    if(typeof fetchClients === 'function'){
                        fetchClients({ 
                            page:1, 
                            rows:parseInt(document.getElementById('addressBookRows').value,10) || 10, 
                            search:document.getElementById('addressBookSearch').value.trim(), 
                            bodyId:'addressBookBody', 
                            paginationId:'addressBookPagination' 
                        });
                    }
                } else {
                    alert('Error: ' + result.data);
                }
            } catch(err){
                alert('Network error: ' + err.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }

    // Global function to open edit modal and fill data
    window.openEditClientModal = async function(clientId) {
        const modal = document.getElementById('rmRealtorClientEditModal');
        if (!modal) return;
        modal.style.display = 'flex';

        try {
            const formData = new FormData();
            formData.append('action', 'fetch_realtor_client_ajax');
            formData.append('nonce', rtClientAjax.edit_nonce);
            formData.append('client_id', clientId);

            const response = await fetch(rtClientAjax.ajax_url, { method: 'POST', body: formData });
            const result = await response.json();

            if(result.success){
                const client = result.data;
                
                document.getElementById('edit_realtor_client_id').value = client.client_id;
                document.getElementById('edit_realtor_client_first_name').value = client.first_name || '';
                document.getElementById('edit_realtor_client_second_name').value = client.second_name || '';
                document.getElementById('edit_realtor_client_first_email').value = client.first_email || '';
                document.getElementById('edit_realtor_client_second_email').value = client.second_email || '';
                document.getElementById('edit_realtor_client_first_phone').value = client.first_phone || '';
                document.getElementById('edit_realtor_client_second_phone').value = client.second_phone || '';
                document.getElementById('edit_realtor_client_address').value = client.address || '';
                document.getElementById('edit_realtor_client_notes').value = client.note || '';
                document.getElementById('edit_realtor_client_status').value = client.status || '';

                const previewAvatar = document.getElementById('editRealtorClientPreviewAvatar');
                previewAvatar.src = client.profile_picture || '<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/default-avatar.jpg'); ?>';

                // Lead status row removed
                // if (leadStatusRow) leadStatusRow.style.display = (client.status === 'lead') ? 'flex' : 'none';
                // if(client.lead_status) document.getElementById('edit_realtor_lead_status').value = client.lead_status;
            }
        } catch (error) {
            console.error('Error loading client data:', error);
        }
    };
});
</script>

<!-- CSS -->
<style>
.modal-overlay-realtor-client { display: none; align-items:center; justify-content:center; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; }
.modal-content-realtor-client { background:#fff; border-radius:8px; max-width:600px; width:90%; box-shadow:0 6px 18px rgba(0,0,0,0.12); padding:25px 30px; max-height:90vh; overflow-y:auto; }
.edit-header-realtor-client { display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; }
.close-button-realtor-client { font-size:28px; cursor:pointer; color:#555; transition: color 0.25s ease; }
.close-button-realtor-client:hover { color:#0052cc; }
.edit-content-realtor-client { display:flex; gap:30px; flex-wrap:wrap; }
.edit-pic-container-realtor-client { flex:0 0 140px; text-align:center; }
.edit-realtor-client-avatar { width:140px; height:140px; object-fit:cover; border-radius:50%; cursor:pointer; border:3px solid #ddd; transition:border-color 0.3s ease; }
.edit-realtor-client-avatar:hover { border-color:#0052cc; }
.edit-pic-container-realtor-client p { font-size:12px; color:#888; margin-top:8px; }
.edit-details-realtor-client { flex:1; min-width:280px; }
.edit-detail-row-realtor-client { margin-bottom:18px; display:flex; flex-direction:column; }
.edit-submit-btn-realtor-client { background-color:#0052cc; border:none; color:white; padding:10px 25px; font-size:1.1rem; border-radius:8px; cursor:pointer; transition:background-color 0.25s ease; }
.edit-submit-btn-realtor-client:hover { background-color:#003d99; }
@media (max-width:600px){ .edit-content-realtor-client { flex-direction:column; } .edit-pic-container-realtor-client { margin:0 auto 25px auto; } }
</style>
