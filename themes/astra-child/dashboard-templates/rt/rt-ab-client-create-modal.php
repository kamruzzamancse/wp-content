<!-- Client Create Modal -->
<div id="rmRealtorClientCreateModal" class="modal-overlay-realtor-client">
    <div class="modal-content-realtor-client">
        <div class="realtor-client-create-container">

            <!-- Header -->
            <div class="create-header-realtor-client">
                <h2>Create New Client</h2>
                <span id="closeRealtorClientCreateModal" class="close-button-realtor-client">&times;</span>
            </div>

            <!-- Form -->
            <form id="createRealtorClientForm" method="POST" enctype="multipart/form-data">
                <div class="create-content-realtor-client">

                    <!-- Profile Picture -->
                    <div class="create-pic-container-realtor-client">
                        <label for="create_realtor_client_profile_picture" title="Click to upload profile picture">
                            <img class="create-realtor-client-avatar" id="createRealtorClientPreviewAvatar"
                                 src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/default-avatar.jpg'); ?>"
                                 alt="Profile Preview">
                            <input type="file" id="create_realtor_client_profile_picture"
                                   name="realtor_client_profile_picture" accept="image/*" style="display:none;">
                        </label>
                        <p>Click image to upload</p>
                    </div>

                    <!-- Client Details -->
                    <div class="create-details-realtor-client">

                        <div class="create-detail-row-realtor-client">
                            <label for="create_realtor_client_full_name">Full Name: *</label>
                            <input type="text" id="create_realtor_client_full_name" name="realtor_client_full_name" required placeholder="Enter full name">
                        </div>

                        <div class="create-detail-row-realtor-client">
                            <label for="create_realtor_client_email">Email: *</label>
                            <input type="email" id="create_realtor_client_email" name="realtor_client_email" required placeholder="Enter email address">
                        </div>

                        <div class="create-detail-row-realtor-client">
                            <label for="create_realtor_client_phone">Phone:</label>
                            <input type="text" id="create_realtor_client_phone" name="realtor_client_phone" placeholder="Enter phone number">
                        </div>

                        <div class="create-detail-row-realtor-client">
                            <label for="create_realtor_client_preferred_location">Preferred Location:</label>
                            <input type="text" id="create_realtor_client_preferred_location" name="preferred_location" placeholder="Enter preferred location">
                        </div>

                        <div class="create-detail-row-realtor-client">
                            <label for="create_realtor_client_note">Note:</label>
                            <textarea id="create_realtor_client_note" name="realtor_client_note" rows="4" placeholder="Enter note"></textarea>
                        </div>

                        <div class="create-detail-row-realtor-client">
                            <label for="create_realtor_client_status">Status: *</label>
                            <select id="create_realtor_client_status" name="realtor_client_status" required>
                                <option value="" disabled selected>Select Status</option>
                                <option value="lead">Lead</option>
                                <option value="active">Active</option>
                            </select>
                        </div>

                        <!-- Property Search -->
                        <div class="create-detail-row-realtor-client" style="position:relative;">
                            <label for="create_realtor_client_property">Select Property:</label>
                            <input type="text" id="create_realtor_client_property" name="realtor_client_property" placeholder="Search property address..." autocomplete="off">
                            <input type="hidden" id="create_realtor_client_property_id" name="realtor_client_property_id">
                            <div id="property_suggestions" class="suggestions-box"></div>
                        </div>

                    </div>
                </div>

                <!-- Submit Button -->
                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" class="create-submit-btn-realtor-client">Create Client</button>
                </div>
            </form>
            
        </div>
    </div>
</div>

<!-- JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const createModal = document.getElementById('rmRealtorClientCreateModal');
    const addContactBtn = document.querySelector('.ab-btn-create');
    const closeBtn = document.getElementById('closeRealtorClientCreateModal');
    const form = document.getElementById('createRealtorClientForm');
    const profileInput = document.getElementById('create_realtor_client_profile_picture');
    const previewAvatar = document.getElementById('createRealtorClientPreviewAvatar');
    const propertyInput = document.getElementById('create_realtor_client_property');
    const propertyIdInput = document.getElementById('create_realtor_client_property_id');
    const suggestionsBox = document.getElementById('property_suggestions');

    // Open modal
    if(addContactBtn && createModal){
        addContactBtn.addEventListener('click', () => createModal.style.display='flex');
    }

    // Close modal
    if(closeBtn && createModal){
        closeBtn.addEventListener('click', () => createModal.style.display='none');
        createModal.addEventListener('click', e => { if(e.target === createModal) createModal.style.display='none'; });
        document.addEventListener('keydown', e => { if(e.key==='Escape') createModal.style.display='none'; });
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

    // Property search AJAX
    if(propertyInput){
        propertyInput.addEventListener('keyup', function(){
            const keyword = this.value.trim();
            if(keyword.length > 1){
                const data = new FormData();
                data.append('action','search_properties');
                data.append('keyword', keyword);
                data.append('nonce', rtClientAjax.create_nonce);

                fetch(rtClientAjax.ajax_url, { method:'POST', body:data })
                    .then(res => res.json())
                    .then(result => {
                        if(result.success){
                            suggestionsBox.innerHTML = result.data.html;
                            suggestionsBox.style.display = 'block';
                        } else {
                            suggestionsBox.innerHTML = '<div class="property-suggestion">No results found</div>';
                            suggestionsBox.style.display = 'block';
                        }
                    });
            } else {
                suggestionsBox.style.display = 'none';
            }
        });
    }

    // Click suggestion
    document.addEventListener('click', function(e){
        if(e.target && e.target.classList.contains('property-suggestion')){
            propertyInput.value = e.target.textContent;
            propertyIdInput.value = e.target.dataset.id;
            suggestionsBox.style.display = 'none';
        } else if(!e.target.closest('#property_suggestions') && e.target !== propertyInput){
            suggestionsBox.style.display = 'none';
        }
    });

    // AJAX submit
    if(form){
        form.addEventListener('submit', async function(e){
            e.preventDefault();
            const formData = new FormData(form);
            formData.append('action', 'create_realtor_client_ajax');
            formData.append('nonce', rtClientAjax.create_nonce);

            try {
                const response = await fetch(rtClientAjax.ajax_url, { method:'POST', body: formData });
                const result = await response.json();

                if(result.success){
                    alert('Client created successfully!');
                    form.reset();
                    previewAvatar.src = rtClientAjax.default_avatar;
                    propertyIdInput.value = '';
                    createModal.style.display = 'none';

                    // Refresh clients table if function exists
                    if(typeof fetchClients === 'function'){
                        fetchClients({ page:1, rows:10, search:'', bodyId:'addressBookBody', paginationId:'addressBookPagination' });
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

<!-- CSS -->
<style>
.modal-overlay-realtor-client { display: none; align-items:center; justify-content:center; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; }
.modal-content-realtor-client { background:#fff; border-radius:8px; max-width:600px; width:90%; box-shadow:0 6px 18px rgba(0,0,0,0.12); padding:25px 30px; max-height:90vh; overflow-y:auto; }
.create-header-realtor-client { display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; }
.close-button-realtor-client { font-size:28px; cursor:pointer; color:#555; transition: color 0.25s ease; }
.close-button-realtor-client:hover { color:#0052cc; }
.create-content-realtor-client { display:flex; gap:30px; flex-wrap:wrap; }
.create-pic-container-realtor-client { flex:0 0 140px; text-align:center; }
.create-realtor-client-avatar { width:140px; height:140px; object-fit:cover; border-radius:50%; cursor:pointer; border:3px solid #ddd; transition:border-color 0.3s ease; }
.create-realtor-client-avatar:hover { border-color:#0052cc; }
.create-pic-container-realtor-client p { font-size:12px; color:#888; margin-top:8px; }
.create-details-realtor-client { flex:1; min-width:280px; }
.create-detail-row-realtor-client { margin-bottom:18px; display:flex; flex-direction:column; }
.create-submit-btn-realtor-client { background-color:#0052cc; border:none; color:white; padding:10px 25px; font-size:1.1rem; border-radius:8px; cursor:pointer; transition: background-color 0.25s ease; }
.create-submit-btn-realtor-client:hover { background-color:#003d99; }
.suggestions-box{ border:1px solid #ccc; max-height:200px; overflow-y:auto; position:absolute; background:#fff; width:100%; z-index:999; display:none;}
.property-suggestion{ padding:5px; cursor:pointer;}
.property-suggestion:hover{ background:#f0f0f0;}
@media (max-width:600px){ .create-content-realtor-client { flex-direction:column; } .create-pic-container-realtor-client { margin:0 auto 25px auto; } }
</style>
