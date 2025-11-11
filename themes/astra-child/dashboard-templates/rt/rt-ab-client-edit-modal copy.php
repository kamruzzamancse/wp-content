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
                <!-- Property ID hidden field - UPDATED -->
                <input type="hidden" name="property_id" id="edit_realtor_client_property_id" value="">

                <div class="edit-content-realtor-client">
                    <div class="edit-pic-container-realtor-client">
                        <label for="edit_realtor_client_profile_picture" title="Click to upload profile picture">
                            <img class="edit-realtor-client-avatar" id="editRealtorClientPreviewAvatar" 
                                src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/default-avatar.jpg'); ?>" 
                                alt="Profile Preview">
                            <input type="file" id="edit_realtor_client_profile_picture" name="realtor_client_profile_picture" accept="image/*" style="display:none;">
                        </label>
                        <p>Click image to upload</p>
                    </div>

                    <div class="edit-details-realtor-client">
                        <div class="edit-detail-row-realtor-client">
                            <label for="edit_realtor_client_full_name">Full Name: *</label>
                            <input type="text" id="edit_realtor_client_full_name" name="realtor_client_full_name" required>
                        </div>

                        <div class="edit-detail-row-realtor-client">
                            <label for="edit_realtor_client_email">Email: *</label>
                            <input type="email" id="edit_realtor_client_email" name="realtor_client_email" required>
                        </div>

                        <div class="edit-detail-row-realtor-client">
                            <label for="edit_realtor_client_phone">Phone:</label>
                            <input type="text" id="edit_realtor_client_phone" name="realtor_client_phone">
                        </div>

                        <div class="edit-detail-row-realtor-client">
                            <label for="edit_realtor_client_notes">Notes:</label>
                            <textarea id="edit_realtor_client_notes" name="realtor_client_note" rows="4"></textarea>
                        </div>

                        <div class="edit-detail-row-realtor-client">
                            <label for="edit_realtor_client_status">Status: *</label>
                            <select id="edit_realtor_client_status" name="realtor_client_status" required>
                                <option value="" disabled>Select Status</option>
                                <option value="lead">Lead</option>
                                <option value="active">Active</option>
                            </select>
                        </div>

                        <div class="edit-detail-row-realtor-client" id="leadStatusRow" style="display:none;">
                            <label for="edit_realtor_lead_status">Lead Status:</label>
                            <select id="edit_realtor_lead_status" name="realtor_lead_status">
                                <option value="hot">Hot</option>
                                <option value="warm">Warm</option>
                                <option value="cold" selected>Cold</option>
                            </select>
                        </div>

                        <!-- Property selection with previous value - VERIFIED -->
                        <div class="edit-detail-row-realtor-client" style="position:relative;">
                            <label for="edit_realtor_client_property">Property:</label>

                            <!-- Show current property name -->
                            <input type="text" id="edit_realtor_client_property" 
                                placeholder="Search or update property address..."
                                autocomplete="off" style="width:100%;"
                            >

                            <!-- Hidden property ID field - MUST HAVE NAME="property_id" -->
                            <input type="hidden" id="edit_realtor_client_property_id" name="property_id" value="">

                            <!-- Suggestions dropdown -->
                            <div id="edit_property_suggestions" class="suggestions-box"></div>
                            
                            <!-- Display current property info -->
                            <div id="currentPropertyInfo" style="font-size: 12px; color: #666; margin-top: 5px;">
                                Current: <span id="currentPropertyText">No property selected</span>
                            </div>
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
    const statusSelect = document.getElementById('edit_realtor_client_status');
    const leadStatusRow = document.getElementById('leadStatusRow');
    
    // Property search elements
    const propertyInput = document.getElementById('edit_realtor_client_property');
    const propertyIdInput = document.getElementById('edit_realtor_client_property_id');
    const suggestionsBox = document.getElementById('edit_property_suggestions');
    const currentPropertyText = document.getElementById('currentPropertyText');

    // Close modal
    if(closeBtn && editModal){
        closeBtn.addEventListener('click', () => editModal.style.display='none');
        editModal.addEventListener('click', e => { if(e.target === editModal) editModal.style.display='none'; });
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

    // Lead status toggle
    if(statusSelect){
        statusSelect.addEventListener('change', function(){
            leadStatusRow.style.display = (this.value === 'lead') ? 'flex' : 'none';
        });
    }

    // ==========================
    // Property Search Functionality - UPDATED
    // ==========================
    function setupPropertySearch() {
        if (!propertyInput || !propertyIdInput || !suggestionsBox) return;

        // Input event: search as user types
        propertyInput.addEventListener('input', debounce(function () {
            const keyword = this.value.trim();
            if (keyword.length < 2) {
                suggestionsBox.style.display = 'none';
                suggestionsBox.innerHTML = '';
                return;
            }

            const formData = new FormData();
            formData.append('action', 'search_properties');
            formData.append('keyword', keyword);
            formData.append('nonce', rtClientAjax.edit_nonce);

            fetch(rtClientAjax.ajax_url, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(result => {
                    if (result.success && result.data.html) {
                        suggestionsBox.innerHTML = result.data.html;
                        suggestionsBox.style.display = 'block';
                    } else {
                        suggestionsBox.innerHTML = '<div class="property-suggestion">No results found</div>';
                        suggestionsBox.style.display = 'block';
                    }
                })
                .catch(err => {
                    suggestionsBox.style.display = 'none';
                });
        }, 300));

        // Click on suggestion - UPDATED
        suggestionsBox.addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('property-suggestion')) {
                const propertyId = e.target.dataset.id;
                const propertyAddress = e.target.textContent;
                
                if (propertyId && !isNaN(propertyId)) {
                    // Set the hidden property ID field
                    propertyIdInput.value = parseInt(propertyId, 10);
                    // Update the display text
                    propertyInput.value = propertyAddress;
                    // Update current property info
                    currentPropertyText.textContent = propertyAddress;
                    // Hide suggestions
                    suggestionsBox.style.display = 'none';
                }
            }
        });

        // Close suggestions if clicked outside
        document.addEventListener('click', function (e) {
            if (!suggestionsBox.contains(e.target) && e.target !== propertyInput) {
                suggestionsBox.style.display = 'none';
            }
        });
    }

    // Initialize property search
    setupPropertySearch();

    // Submit update via AJAX - FIXED VERSION
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
                formData.append('action', 'update_realtor_client_ajax');
                formData.append('nonce', rtClientAjax.edit_nonce);

                // DEBUG: Check property ID before sending
                const propertyIdInput = document.getElementById('edit_realtor_client_property_id');

                // Manually ensure property_id is included
                if (propertyIdInput && propertyIdInput.value) {
                    formData.append('property_id', propertyIdInput.value);
                }

                const res = await fetch(rtClientAjax.ajax_url, { 
                    method: 'POST', 
                    body: formData 
                });
                
                const result = await res.json();

                if(result.success){
                    alert('Client updated successfully!');
                    editModal.style.display='none';

                    // Refresh client list if function exists
                    if(typeof fetchClients === 'function'){
                        fetchClients({ page:1, rows:10, search:'', bodyId:'addressBookBody', paginationId:'addressBookPagination' });
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

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
});

// Global function to open edit modal with client data
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
            
            // Fill form fields
            document.getElementById('edit_realtor_client_id').value = client.client_id;
            document.getElementById('edit_realtor_client_full_name').value = client.full_name;
            document.getElementById('edit_realtor_client_email').value = client.email;
            document.getElementById('edit_realtor_client_phone').value = client.phone;
            document.getElementById('edit_realtor_client_notes').value = client.note;
            document.getElementById('edit_realtor_client_status').value = client.status;
            
            // Set profile picture
            const previewAvatar = document.getElementById('editRealtorClientPreviewAvatar');
            previewAvatar.src = client.profile_picture || '<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/default-avatar.jpg'); ?>';
            
            // Set property fields - UPDATED
            const propertyInput = document.getElementById('edit_realtor_client_property');
            const propertyIdInput = document.getElementById('edit_realtor_client_property_id');
            const currentPropertyText = document.getElementById('currentPropertyText');
            
            if (client.property_title) {
                propertyInput.value = client.property_title;
                propertyIdInput.value = client.property_id || '';
                currentPropertyText.textContent = client.property_title;
            } else {
                propertyInput.value = '';
                propertyIdInput.value = '';
                currentPropertyText.textContent = 'No property selected';
            }
            
            // Show lead status if applicable
            const leadStatusRow = document.getElementById('leadStatusRow');
            if (leadStatusRow) {
                leadStatusRow.style.display = (client.status === 'lead') ? 'flex' : 'none';
            }
            if (client.lead_status) {
                document.getElementById('edit_realtor_lead_status').value = client.lead_status;
            }

        } else {
            //console.error('❌ Failed to load client data for editing');
        }
    } catch (error) {
        //console.error('❌ Error loading client data for editing:', error);
    }
};
</script>

<style>
.modal-overlay-realtor-client {
    display: none;
    align-items: center;
    justify-content: center;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
}
.modal-content-realtor-client {
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
.edit-header-realtor-client {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}
.edit-header-realtor-client h2 { font-weight: 700; font-size: 1.8rem; color: #222; }
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

#edit_property_suggestions {
    position: absolute;
    background: white;
    border: 1px solid #ccc;
    z-index: 9999;
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
    display: none;
}
.property-suggestion {
    padding: 5px 10px;
    cursor: pointer;
}
.property-suggestion:hover { background: #f0f0f0; }

</style>
