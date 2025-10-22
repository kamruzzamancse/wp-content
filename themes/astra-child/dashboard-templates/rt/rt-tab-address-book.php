<div class="ab-container">
    <div class="ab-table-header">
        <div class="ab-header-left">
            <h1 class="header-title">Address Book</h1>
        </div>
        <div class="ab-header-right">
            <div class="ab-search-box">
                <span class="pt-search-icon">🔍</span>
                <input type="text" class="pt-search-input" placeholder="Search: Client Name" id="addressBookSearch">
            </div>
            <div class="ab-action-buttons">
                <button class="ab-btn ab-btn-import">
                    <span style="color: #000" class="dashicons dashicons-upload"></span> Import
                </button>
                <div class="ab-export-dropdown">
                    <button class="ab-btn ab-btn-export">
                        <span class="dashicons dashicons-download"></span> Export
                    </button>
                </div>
                <button class="ab-btn ab-btn-create">
                    <span class="dashicons dashicons-plus-alt"></span> Add Contact
                </button>
            </div>
        </div>
    </div>

    <div class="ab-controls">
        <label for="addressBookRows" style="margin-right:5px;">Show:</label>
        <select id="addressBookRows">
            <option value="5">5 rows</option>
            <option value="10" selected>10 rows</option>
            <option value="25">25 rows</option>
        </select>
    </div>

    <table>
        <thead>
            <tr>
                <th class="ab-sl-column">Profile</th>
                <th class="client-name">Client Name</th>
                <th class="email">Email</th>
                <th class="phone-number">Phone Number</th>
                <th class="notes">Notes</th>
                <th class="Status">Status</th>
                <th class="ab-actions-column">Actions</th>
            </tr>
        </thead>
        <tbody id="addressBookBody">
            <tr><td colspan="7" style="text-align:center;">Loading...</td></tr>
        </tbody>
    </table>

    <!-- Placeholder for AJAX pagination -->
    <div id="addressBookPagination" class="ab-pagination"></div>
</div>

<?php 
    // Include modals for create/edit/details
    include locate_template('dashboard-templates/rt/rt-client-details-modal.php');
    include locate_template('dashboard-templates/rt/rt-client-create-modal.php');
    include locate_template('dashboard-templates/rt/rt-client-edit-modal.php');
?>

<!-- Add JS object for delete AJAX -->
<script>
const cl_client_delete_ajax = {
    ajax_url: '<?php echo admin_url("admin-ajax.php"); ?>',
    nonce: '<?php echo wp_create_nonce("cl_client_delete_nonce"); ?>'
};
</script>

<!-- Delete client -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.ab-deleteClient').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const clientId = row.dataset.clientId;
            if (!clientId) return;

            if (!confirm('Are you sure you want to delete this client?')) return;

            // Disable button while processing
            btn.textContent = 'Deleting...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'delete_realtor_client_ajax');
            formData.append('nonce', cl_client_delete_ajax.nonce);
            formData.append('client_id', clientId);

            fetch(cl_client_delete_ajax.ajax_url, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Client deleted successfully!');
                        row.remove();
                    } else {
                        alert('Error: ' + data.data);
                        btn.textContent = '🗑️';
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    alert('Network error: ' + err.message);
                    btn.textContent = '🗑️';
                    btn.disabled = false;
                });
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal open/close functionality
    const addContactBtn = document.querySelector('.ab-btn-create');
    const createModal = document.getElementById('rmRealtorClientCreateModal');
    const closeCreateBtn = document.getElementById('closeRealtorClientCreateModal');
    
    // Open modal
    if (addContactBtn && createModal) {
        addContactBtn.addEventListener('click', function() {
            createModal.style.display = 'flex';
        });
    }
    
    // Close modal
    if (closeCreateBtn && createModal) {
        closeCreateBtn.addEventListener('click', function() {
            createModal.style.display = 'none';
        });
        
        createModal.addEventListener('click', function(e) {
            if (e.target === createModal) {
                createModal.style.display = 'none';
            }
        });
    }
    
    // Image preview functionality
    const profileInput = document.getElementById('create_realtor_client_profile_picture');
    if (profileInput) {
        profileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('createRealtorClientPreviewAvatar').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Form submission handling
    const form = document.getElementById('createRealtorClientForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic validation
            const fullName = document.getElementById('create_realtor_client_full_name').value;
            const email = document.getElementById('create_realtor_client_email').value;
            const status = document.getElementById('create_realtor_client_status').value;
            
            if (!fullName.trim() || !email.trim() || !status) {
                alert('Please fill in all required fields (Name, Email, Status)');
                return;
            }
            
            const formData = new FormData(form);
            formData.append('action', 'create_realtor_client_ajax');
            formData.append('nonce', '<?php echo wp_create_nonce('cl_client_create_nonce'); ?>');
            
            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Creating Client...';
            submitBtn.disabled = true;
            
            // AJAX request
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Client created successfully!');
                    form.reset();
                    
                    // Reset profile picture
                    const previewAvatar = document.getElementById('createRealtorClientPreviewAvatar');
                    if (previewAvatar) {
                        previewAvatar.src = "<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>";
                    }
                    
                    // Close modal and refresh page
                    createModal.style.display = 'none';
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Show specific error message from server
                    alert('Error: ' + data.data);
                }
            })
            .catch(error => {
                alert('Network error occurred. Please try again.');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('rmRealtorClientEditModal');
    const closeEditBtn = document.getElementById('closeRealtorClientEditModal');
    const form = document.getElementById('editRealtorClientForm');

    // Open edit modal on edit button click
    document.querySelectorAll('.ab-editClientDetails').forEach(btn => {
        btn.addEventListener('click', function() {
            const clientId = this.closest('tr').dataset.clientId;
            if (!clientId) return;

            // Show modal
            editModal.style.display = 'flex';

            // Fetch client data via AJAX
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'fetch_realtor_client_ajax',
                    nonce: '<?php echo wp_create_nonce('cl_client_edit_nonce'); ?>',
                    client_id: clientId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const client = data.data;
                    document.getElementById('edit_realtor_client_id').value = client.client_id;
                    document.getElementById('edit_realtor_client_full_name').value = client.full_name;
                    document.getElementById('edit_realtor_client_email').value = client.email;
                    document.getElementById('edit_realtor_client_phone').value = client.phone;
                    document.getElementById('edit_realtor_client_notes').value = client.note;
                    document.getElementById('edit_realtor_client_status').value = client.status;
                    document.getElementById('editRealtorClientPreviewAvatar').src = client.profile_picture || "<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>";
                } else {
                    alert('Failed to fetch client data');
                    editModal.style.display = 'none';
                }
            })
            .catch(err => {
                alert('Network error. Please try again.');
                editModal.style.display = 'none';
            });
        });
    });

    // Close modal
    if (closeEditBtn) {
        closeEditBtn.addEventListener('click', () => editModal.style.display = 'none');
        editModal.addEventListener('click', e => {
            if (e.target === editModal) editModal.style.display = 'none';
        });
    }

    // Image preview functionality
    const profileInput = document.getElementById('edit_realtor_client_profile_picture');
    if (profileInput) {
        profileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('editRealtorClientPreviewAvatar').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Form submission (Update client)
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            formData.append('action', 'update_realtor_client_ajax');
            formData.append('nonce', '<?php echo wp_create_nonce('cl_client_edit_nonce'); ?>');

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Updating...';
            submitBtn.disabled = true;

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Client updated successfully!');
                    form.reset();
                    editModal.style.display = 'none';
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    alert('Error: ' + data.data);
                }
            })
            .catch(err => alert('Network error. Please try again.'))
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const clientDetailsModal = document.getElementById('clientDetailsModal');
    const closeClientDetailsModalBtn = document.getElementById('closeClientDetailsModal');

	document.querySelectorAll('.client-name-text').forEach(span => {
		span.addEventListener('click', function(e){
			e.stopPropagation();
			const clientDetailsModal = document.getElementById('clientDetailsModal');
			if (clientDetailsModal) clientDetailsModal.style.display = 'flex';
		});
	});

    // Close modal
    if(closeClientDetailsModalBtn){
        closeClientDetailsModalBtn.addEventListener('click', () => { 
            if(clientDetailsModal) clientDetailsModal.style.display = 'none'; 
        });
    }

    // Close modal on outside click
    if(clientDetailsModal){
        clientDetailsModal.addEventListener('click', e => { 
            if(e.target === clientDetailsModal) clientDetailsModal.style.display = 'none'; 
        });
    }

});
</script>

<style>
/* ==== Table & Modal CSS ==== */
table { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px; background: #fff; table-layout: auto; }
.ab-sl-column, .ab-actions-column, .Status { 
    width: 100px; 
    min-width: 100px; 
    max-width: 100px; 
    text-align: center; /* ✅ Center aligned content */
}
.client-name { min-width: 150px; font-size: 14px; font-weight: 600; }
.client-name-text { cursor: pointer; color: #0073aa; text-decoration: underline; }
.client-name-text:hover { color: #0056b3; }
.email, .phone-number, .notes { /* flexible width based on content */ min-width: 120px; }
thead th { text-align: left; padding: 10px; border-bottom: 2px solid #ddd; font-weight: 600; }
tbody td { padding: 10px; border-bottom: 1px solid #eee; vertical-align: middle; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
tbody td:hover::after { content: attr(title); position: absolute; left: 0; top: 100%; background: #333; color: #fff; padding: 6px 10px; border-radius: 4px; white-space: normal; min-width: 200px; max-width: 400px; z-index: 1000; font-size: 13px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }

/* Action icons */
.ab-action-icons { display: flex; gap: 8px; justify-content: center; /* ✅ Center the icons */ }
.ab-action-icon { cursor: pointer; font-size: 16px; transition: transform 0.2s; }
.ab-action-icon:hover { transform: scale(1.2); }
tbody tr.client-row:hover { background-color: #f5f5f5; }

/* Modal & Form */
.modal { display: none; position: fixed; z-index: 999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
.modal-content { background: #fff; padding: 25px; border-radius: 8px; width: 400px; max-width: 90%; position: relative; }
.close { position: absolute; top: 10px; right: 15px; font-size: 24px; cursor: pointer; }
.modal-title { text-align: left; margin-bottom: 15px; font-size: 20px; font-weight: bold; }
.form-group { margin-bottom: 15px; display: flex; flex-direction: column; }
label { margin-bottom: 5px; font-weight: 600; text-align: left; }
input { padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
.save-btn { background: #007bff!important; color: #FFF!important; border: none; padding: 10px 15px; font-size: 16px; border-radius: 4px; cursor: pointer; width: 100%; transition: 0.3s; }
.save-btn:hover { background: #0056b3; }

/* Responsive Table */
@media screen and (max-width: 768px) {
  table:not(.client-details), table:not(.client-details) thead, table:not(.client-details) tbody, table:not(.client-details) th, table:not(.client-details) tr { display: block; width: 100%; }
  table:not(.client-details) thead { display: none; }
  table:not(.client-details) tr { margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; padding: 12px; background: #f9f9ff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
  table:not(.client-details) td { display: flex; flex-direction: column; width: 100%; padding: 8px 0; border: none; border-bottom: 1px solid #eee; max-width: none !important; white-space: normal; overflow: visible; text-overflow: unset; }
  table:not(.client-details) td:last-child { border-bottom: none; }
  table:not(.client-details) td::before { content: attr(data-label); font-weight: 600; color: #333; margin-bottom: 4px; }
  table:not(.client-details) .ab-actions-column { flex-direction: row; justify-content: center; align-items: center; padding: 8px 0; }
  table:not(.client-details) .ab-actions-column::before { content: attr(data-label); font-weight: 600; color: #333; margin-bottom: 0; margin-right: 0; }
  table:not(.client-details) .ab-action-icons { gap: 10px; }
  table:not(.client-details) td:hover::after { display: none; }
}

table {
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #ddd;
    border-radius: 10px 10px 0 0;
    overflow: hidden;
}

/* Optional: Match the top header row */
table thead th:first-child { border-top-left-radius: 10px; }
table thead th:last-child { border-top-right-radius: 10px; }
.modal button:last-child { color: #fff!important; }
/* Submit button */
.ab-btn { background-color: #007bff; color: #fff!important; }
.ab-btn:hover { background-color: #0056b3; }

</style>

<style>
/* === Pagination Styling (keeps your existing look) === */
.ab-pagination {
    display: flex;
    justify-content: flex-end; /* Right aligned */
    align-items: center;
    margin-top: 15px;
    gap: 6px;
}

.ab-page-btn {
    display: inline-block;
    padding: 6px 12px;
    background-color: #f5f5f5;
    color: #0073aa;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 14px;
}

.ab-page-btn:hover {
    background-color: #0073aa;
    color: #fff;
}

.ab-page-btn.active {
    background-color: #0073aa;
    color: #fff;
    font-weight: bold;
}
</style>