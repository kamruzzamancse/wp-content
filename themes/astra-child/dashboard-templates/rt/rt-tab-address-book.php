<div class="ab-container">
    <div class="ab-table-header">
        <div class="ab-header-left">
            <h1 class="header-title">Address Book</h1>
        </div>
        <div class="ab-header-right">
            <div class="ab-search-box">
                <span class="pt-search-icon">🔍</span>
                <input type="text" class="pt-search-input" placeholder="Search: Client Name">
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
    <table>
        <thead>
            <tr>
                <th class="ab-sl-column">Profile</th>
                <th class="client-name">Client Name</th>
                <th class="email">Email</th>
                <th class="phone-number">Phone Number</th>
                <th class="notes">Notes</th>
                <th class="ab-actions-column">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            global $wpdb;

            $clients = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}clients ORDER BY created_at DESC", ARRAY_A);

            if ($clients && !empty($clients)) :
                foreach($clients as $client):
                    // Use client's profile picture from clients table, fallback to user meta or default
                    if (!empty($client['profile_picture'])) {
                        $profile_pic = $client['profile_picture'];
                    } else {
                        // Fallback to user meta
                        $profile_pic = $wpdb->get_var($wpdb->prepare(
                            "SELECT meta_value FROM {$wpdb->prefix}usermeta WHERE user_id = %d AND meta_key = %s LIMIT 1",
                            $client['user_id'],
                            'profile_picture'
                        ));
                        
                        // Final fallback
                        if (empty($profile_pic)) {
                            $profile_pic = "https://i.pravatar.cc/40?img=" . rand(1,70);
                        }
                    }
            ?>
            <tr class="client-row" data-client-id="<?php echo intval($client['client_id']); ?>">
                <td class="ab-sl-column" data-label=" ">
                    <img src="<?php echo esc_url($profile_pic); ?>" 
                        alt="Profile Pic" class="profile-pic" 
                        style="border-radius:50%; width:40px; height:40px; object-fit:cover;">
                </td>
                <td class="client-name" data-label="Client Name">
                    <span class="client-name-text"><?php echo esc_html($client['full_name']); ?></span>
                </td>
                <td data-label="Email"><?php echo esc_html($client['email']); ?></td>
                <td data-label="Phone Number"><?php echo esc_html($client['phone']); ?></td>
                <td data-label="Notes"><?php echo esc_html($client['note']); ?></td>
                <td class="ab-actions-column" data-label="Actions">
                    <div class="ab-action-icons">
                        <span class="ab-action-icon ab-editClientDetails" title="Edit">✏️</span>
                        <span class="ab-action-icon" title="Delete">🗑️</span>
                    </div>
                </td>
            </tr>
            <?php
                endforeach;
            else:
            ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:15px;">No Clients Found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php 
    include locate_template('dashboard-templates/rt/rt-client-details-modal.php');
    include locate_template('dashboard-templates/rt/rt-client-create-modal.php');
    include locate_template('dashboard-templates/rt/rt-client-edit-modal.php');
?>

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

    // Open Edit Modal on Edit icon click
    document.querySelectorAll('.ab-editClientDetails').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = btn.closest('tr');

            // Get values from table row
            const fullName = row.querySelector('.client-name-text')?.textContent.trim() || '';
            const email    = row.querySelector('td[data-label="Email"]')?.textContent.trim() || '';
            const phone    = row.querySelector('td[data-label="Phone Number"]')?.textContent.trim() || '';
            const notes    = row.querySelector('td[data-label="Notes"]')?.textContent.trim() || '';
            const profile  = row.querySelector('td.ab-sl-column img')?.src || '';

            // Show the Realtor Edit Modal
            const editModal = document.getElementById('rmRealtorClientEditModal');
            if(editModal){
                editModal.style.display = 'flex';

                // Populate modal fields
                editModal.querySelector('#edit_realtor_client_full_name').value = fullName;
                editModal.querySelector('#edit_realtor_client_email').value = email;
                editModal.querySelector('#edit_realtor_client_phone').value = phone;
                editModal.querySelector('#edit_realtor_client_notes').value = notes;

                // Optional: profile picture preview
                const profileImg = editModal.querySelector('#editRealtorClientPreviewAvatar');
                if(profileImg) profileImg.src = profile;
            }
        });
    });

    // Close modal on close button click
    const closeEditBtn = document.getElementById('closeRealtorClientEditModal');
    if(closeEditBtn){
        closeEditBtn.addEventListener('click', function() {
            document.getElementById('rmRealtorClientEditModal').style.display = 'none';
        });
    }

    // Close modal on clicking outside modal content
    const editModal = document.getElementById('rmRealtorClientEditModal');
    if(editModal){
        editModal.addEventListener('click', function(e) {
            if(e.target === editModal) editModal.style.display = 'none';
        });
    }

    // Handle form submission (AJAX or demo alert)
    const editForm = document.getElementById('editRealtorClientForm');
    if(editForm){
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // You can replace this alert with AJAX call to update wp_clients
            alert('Client details updated successfully! (Demo)');
            editModal.style.display = 'none';
        });
    }

});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const clientDetailsModal = document.getElementById('clientDetailsModal');
    const closeClientDetailsModalBtn = document.getElementById('closeClientDetailsModal');

    // Open modal when clicking the "View" icon
//     document.querySelectorAll('.ab-viewClientDetails').forEach(btn => {
//         btn.addEventListener('click', function(e){
//             e.stopPropagation();
//             if(clientDetailsModal) clientDetailsModal.style.display = 'flex';
//         });
//     });
	document.querySelectorAll('.client-name-text').forEach(span => {
		span.addEventListener('click', function(e){
			e.stopPropagation();
			const clientDetailsModal = document.getElementById('clientDetailsModal');
			if (clientDetailsModal) clientDetailsModal.style.display = 'flex';
		});
	});


    // Also open modal when clicking the client name text
    document.querySelectorAll('.client-name-text').forEach(span => {
        span.addEventListener('click', function(e){
            e.stopPropagation();
            const row = this.closest('tr');
            const viewButton = row.querySelector('.ab-viewClientDetails');
            if(viewButton) viewButton.click();
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete functionality for Address Book
    document.querySelectorAll('.ab-actions-column .ab-action-icon[title="Delete"]').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = btn.closest('tr');
            if (confirm("Are you sure you want to delete this client?")) {
                row.remove();
            }
        });
    });
});
</script>


<style>
/* ==== Table & Modal CSS ==== */
table { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px; background: #fff; table-layout: fixed; }
.ab-sl-column { width: 50px; text-align: center; }
.ab-actions-column { width: 50px; }
.client-name { width: 15%; min-width: 150px; font-size: 14px; font-weight: 600; }
.client-name-text { cursor: pointer; color: #0073aa; text-decoration: underline; }
.client-name-text:hover { color: #0056b3; }
.email { width: 15%; min-width: 150px; }
.phone-number { width: 15%; min-width: 150px; }
.notes { width: 40%; min-width: 330px; }
thead th { text-align: left; padding: 10px; border-bottom: 2px solid #ddd; font-weight: 600; }
tbody td { padding: 10px; border-bottom: 1px solid #eee; vertical-align: middle; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
tbody td:hover::after { content: attr(title); position: absolute; left: 0; top: 100%; background: #333; color: #fff; padding: 6px 10px; border-radius: 4px; white-space: normal; min-width: 200px; max-width: 400px; z-index: 1000; font-size: 13px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.ab-action-icons { display: flex; gap: 8px; }
.ab-action-icon { cursor: pointer; font-size: 16px; transition: transform 0.2s; }
.ab-action-icon:hover { transform: scale(1.2); }
tbody tr.client-row:hover { background-color: #f5f5f5; }
tbody tr.client-row .ab-actions-column { cursor: default; }
tbody tr.client-row .ab-actions-column:hover { background-color: transparent; }

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
  table:not(.client-details) .ab-actions-column { flex-direction: row; justify-content: space-between; align-items: center; padding: 8px 0; }
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
table thead th:first-child {
    border-top-left-radius: 10px;
}
table thead th:last-child {
    border-top-right-radius: 10px;
}
.modal button:last-child {
    color: #fff!important;
}
/* Submit button */
.ab-btn {
  background-color: #007bff;
  color: #fff!important;
}

.ab-btn:hover {
  background-color: #0056b3;
}
</style>