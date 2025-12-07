<div class="dashboard-top">

  <!-- LEFT SIDE -->
  <div class="dashboard-top-left">

    <!-- ACTIVE CLIENTS SECTION -->
    <div class="dashboard-section active-clients-section">
      <div class="clients-header">
        <h1 class="header-title">Active Clients</h1>
        <button id="addClientBtn" class="btn-primary">+ Add Client</button>
      </div>

      <!-- Search + Rows Selector -->
      <div class="table-controls">
        <input type="text" id="activeClientsSearch" class="table-search" placeholder="Search clients...">
        <select id="activeClientsRows" class="rows-selector">
          <option value="6" selected>6 rows</option>
          <option value="10">10 rows</option>
          <option value="20">20 rows</option>
        </select>
      </div>

      <!-- Active Clients Table -->
      <table class="active-clients-table">
        <thead>
          <tr>
            <th>Client Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Notes</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="activeClientsBody">
          <tr><td colspan="5" style="text-align:center;">Loading...</td></tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div id="activeClientsPagination" class="pagination"></div>
    </div>

    <!-- LEADS SECTION -->
    <div class="dashboard-section leads-section">
      <div class="leads-header">
        <h1 class="header-title">Leads</h1>
        <button id="addLeadBtn" class="btn-primary">+ Add Lead</button>
      </div>

      <!-- Search + Rows Selector -->
      <div class="table-controls">
        <input type="text" id="leadsSearch" class="table-search" placeholder="Search leads...">
        <select id="leadsRows" class="rows-selector">
          <option value="6" selected>6 rows</option>
          <option value="10">10 rows</option>
          <option value="20">20 rows</option>
        </select>
      </div>

      <!-- Leads Table -->
      <table class="leads-table">
        <thead>
          <tr>
            <th>Client Name</th>
            <th>Last Touch</th>
            <th>Status</th>
            <th>Notes</th>
            <th style="width:140px;">Actions</th>
          </tr>
        </thead>
        <tbody id="leadsBody">
          <tr><td colspan="5" style="text-align:center;">Loading...</td></tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div id="leadsPagination" class="pagination"></div>
    </div>

  </div>

  <!-- RIGHT SIDE -->
  <div class="dashboard-top-right">
    <?php
      $current_user = wp_get_current_user();
      $user_email   = $current_user->user_email;

      if ($user_email) {
          global $wpdb;
          $calendar_id = $wpdb->get_var($wpdb->prepare("
              SELECT ID 
              FROM $wpdb->posts 
              WHERE post_type = 'calendar' 
                AND post_status = 'publish'
                AND post_title = %s
              LIMIT 1
          ", $user_email));

          if ($calendar_id) {
              echo do_shortcode('[calendar id="' . intval($calendar_id) . '"]');
          } else {
              echo '<p>No calendar found for your account.</p>';
          }
      } else {
          echo '<p>Please login to see your calendar.</p>';
      }
    ?>

    <!-- Sticky Notes -->
    <div class="notes-header">
      <h1>Notes</h1>
      <button class="add-note-btn">+</button>
    </div>
    <div class="sticky-notes-container"></div>
  </div>

</div>

<?php 
    include locate_template('dashboard-templates/rt/rt-client-create-modal.php');
    include locate_template('dashboard-templates/rt/rt-client-edit-modal.php');
?>

<!-- CREATE CLIENT / LEAD -->
<script>
// CREATE CLIENT / LEAD MODAL + AJAX
document.addEventListener('DOMContentLoaded', function() {

    const addClientBtn = document.getElementById('addClientBtn');
    const addLeadBtn = document.getElementById('addLeadBtn');
    const createModal = document.getElementById('rmRealtorClientCreateModal');
    const closeCreateBtn = document.getElementById('closeRealtorClientCreateModal');
    const createForm = document.getElementById('createRealtorClientForm');
    const createProfileInput = document.getElementById('create_realtor_client_profile_picture');

    // Open Add Client modal
    if (addClientBtn && createModal) {
        addClientBtn.addEventListener('click', () => {
            if (createForm) createForm.reset();
            createModal.style.display = 'flex';
        });
    }

    // Open Add Lead modal and set status to "lead"
    if (addLeadBtn && createModal) {
        addLeadBtn.addEventListener('click', () => {
            if (createForm) createForm.reset();
            const statusInput = document.getElementById('create_realtor_client_status');
            if (statusInput) statusInput.value = 'lead';
            const previewAvatar = document.getElementById('createRealtorClientPreviewAvatar');
            if (previewAvatar) previewAvatar.src = "<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>";
            createModal.style.display = 'flex';
        });
    }

    // Close modal
    if (closeCreateBtn && createModal) {
        closeCreateBtn.addEventListener('click', () => createModal.style.display = 'none');
        createModal.addEventListener('click', e => {
            if (e.target === createModal) createModal.style.display = 'none';
        });
    }

    // Profile picture preview
    if (createProfileInput) {
        createProfileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    const previewAvatar = document.getElementById('createRealtorClientPreviewAvatar');
                    if (previewAvatar) previewAvatar.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // AJAX submit
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const fullName = document.getElementById('create_realtor_client_full_name').value.trim();
            const email = document.getElementById('create_realtor_client_email').value.trim();
            const status = document.getElementById('create_realtor_client_status').value;

            if (!fullName || !email || !status) {
                alert('Please fill in all required fields (Name, Email, Status)');
                return;
            }

            const formData = new FormData(createForm);
            formData.append('action', 'create_realtor_client_ajax');
            formData.append('nonce', '<?php echo wp_create_nonce("cl_client_create_nonce"); ?>');

            const submitBtn = createForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Creating...';
            submitBtn.disabled = true;

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Client created successfully!');
                        createForm.reset();
                        const previewAvatar = document.getElementById('createRealtorClientPreviewAvatar');
                        if (previewAvatar) previewAvatar.src = "<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>";
                        createModal.style.display = 'none';
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        alert('Error: ' + data.data);
                    }
                })
                .catch(() => alert('Network error. Please try again.'))
                .finally(() => { submitBtn.textContent = originalText; submitBtn.disabled = false; });
        });
    }

});
</script>

<script>
// EDIT CLIENT / LEAD MODAL + AJAX
document.addEventListener('DOMContentLoaded', function() {

    const editModal = document.getElementById('rmRealtorClientEditModal');
    const closeEditBtn = document.getElementById('closeRealtorClientEditModal');
    const editForm = document.getElementById('editRealtorClientForm');
    const editProfileInput = document.getElementById('edit_realtor_client_profile_picture');

    document.querySelectorAll('.edit-client-btn, .edit-lead-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const clientId = this.closest('tr').dataset.clientId;
            if (!clientId || !editModal) return;

            editModal.style.display = 'flex';

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'fetch_realtor_client_ajax',
                    nonce: '<?php echo wp_create_nonce("cl_client_edit_nonce"); ?>',
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

                    // === Show Lead Status dropdown only for leads ===
                    const leadStatusRow = document.getElementById('leadStatusRow');
                    const leadStatusSelect = document.getElementById('edit_realtor_lead_status');

                    if (client.status === 'lead') {
                        leadStatusRow.style.display = 'flex';
                        // Set current lead_status value if exists, else default to 'cold'
                        if (client.lead_status) {
                            leadStatusSelect.value = client.lead_status;
                        } else {
                            leadStatusSelect.value = 'cold';
                        }
                    } else {
                        leadStatusRow.style.display = 'none';
                    }
                } else {
                    alert('Failed to fetch client data');
                    editModal.style.display = 'none';
                }
            })
            .catch(() => { alert('Network error. Please try again.'); editModal.style.display = 'none'; });
        });
    });

    // Close modal
    if (closeEditBtn) {
        closeEditBtn.addEventListener('click', () => editModal.style.display = 'none');
        editModal.addEventListener('click', e => { if (e.target === editModal) editModal.style.display = 'none'; });
    }

    // Profile picture preview
    if (editProfileInput) {
        editProfileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => { document.getElementById('editRealtorClientPreviewAvatar').src = e.target.result; };
                reader.readAsDataURL(file);
            }
        });
    }

    // AJAX submit
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(editForm);
            formData.append('action', 'update_realtor_client_ajax');
            formData.append('nonce', '<?php echo wp_create_nonce("cl_client_edit_nonce"); ?>');

            const submitBtn = editForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Updating...';
            submitBtn.disabled = true;

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Client updated successfully!');
                        editForm.reset();
                        editModal.style.display = 'none';
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        alert('Error: ' + data.data);
                    }
                })
                .catch(() => alert('Network error. Please try again.'))
                .finally(() => { submitBtn.textContent = originalText; submitBtn.disabled = false; });
        });
    }

});
</script>

<!-- DELETE CLIENT / LEAD -->
<script>
// DELETE CLIENT / LEAD AJAX
document.addEventListener('DOMContentLoaded', function() {

    document.querySelectorAll('.delete-client-btn, .delete-lead-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const clientId = row.dataset.clientId;
            if (!clientId) return;

            if (!confirm('Are you sure you want to delete this client/lead?')) return;

            const formData = new FormData();
            formData.append('action', 'delete_realtor_client_ajax');
            formData.append('nonce', '<?php echo wp_create_nonce("cl_client_delete_nonce"); ?>');
            formData.append('client_id', clientId);

            const btnText = this.textContent;
            this.textContent = 'Deleting...';
            this.disabled = true;

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Client deleted successfully!');
                        row.remove();
                    } else {
                        alert('Error: ' + data.data);
                        this.textContent = btnText;
                        this.disabled = false;
                    }
                })
                .catch(err => {
                    alert('Network error: ' + err.message);
                    this.textContent = btnText;
                    this.disabled = false;
                });
        });
    });

});
</script>

<!-- CONVERT LEAD TO CLIENT -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    const convertBtns = document.querySelectorAll('.convert-lead-btn');

    convertBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const clientId = row.dataset.clientId;
            if (!clientId) return;

            if (!confirm('Do you want to convert this lead to a client?')) return;

            const formData = new FormData();
            formData.append('action', 'convert_lead_to_client');
            formData.append('nonce', '<?php echo wp_create_nonce("convert_lead_nonce"); ?>');
            formData.append('client_id', clientId);

            btn.textContent = 'Converting...';
            btn.disabled = true;

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Lead successfully converted to client!');

                        // Remove the row from Leads table
                        row.remove();

                        // Add the row to Active Clients table
                        const activeClientsTable = document.querySelector('.active-clients-table tbody');
                        if (activeClientsTable) {
                            const newRow = document.createElement('tr');
                            newRow.dataset.clientId = clientId;
                            newRow.innerHTML = `
                                <td data-label="Client Name">${row.querySelector('[data-label="Client Name"]').textContent}</td>
                                <td data-label="Email">${row.querySelector('[data-label="Email"]')?.textContent || '—'}</td>
                                <td data-label="Phone">${row.querySelector('[data-label="Phone"]')?.textContent || '—'}</td>
                                <td data-label="Notes">${row.querySelector('[data-label="Notes"]')?.textContent || '—'}</td>
                                <td data-label="Actions" class="action-cell">
                                    <span class="delete-client-btn" title="Delete">🗑️</span>
                                </td>
                            `;
                            activeClientsTable.prepend(newRow);

                            // Rebind delete event for new row
                            newRow.querySelector('.delete-client-btn').addEventListener('click', function() {
                                const clientRow = this.closest('tr');
                                const clientId = clientRow.dataset.clientId;
                                if (!clientId) return;
                                if (!confirm('Are you sure you want to delete this client?')) return;

                                const fd = new FormData();
                                fd.append('action', 'delete_realtor_client_ajax');
                                fd.append('nonce', '<?php echo wp_create_nonce("cl_client_delete_nonce"); ?>');
                                fd.append('client_id', clientId);

                                const btnText = this.textContent;
                                this.textContent = 'Deleting...';
                                this.disabled = true;

                                fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method: 'POST', body: fd })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success) clientRow.remove();
                                        else { alert('Error: ' + data.data); this.textContent = btnText; this.disabled = false; }
                                    })
                                    .catch(err => { alert('Network error: ' + err.message); this.textContent = btnText; this.disabled = false; });
                            });
                        }
                    } else {
                        alert('Error: ' + data.data);
                        btn.textContent = '🔄';
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    alert('Network error: ' + err.message);
                    btn.textContent = '🔄';
                    btn.disabled = false;
                });
        });
    });

});
</script>

<style>
/* Primary button (Add & Save Lead) */
.btn-primary {
  background: #2271b1;
  color: #fff;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.2s ease;
}
.btn-primary:hover {
  background: #3c57c7;
}

/* Align Add Lead button to right */
.leads-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.leads-header .btn-primary {
  margin-left: auto;
}

/* Align Add Client button to right */
.clients-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.clients-header .btn-primary {
  margin-left: auto; /* Push button to right */
}

/* Modal Styling */
.lead-add-modal {
  display: none;
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
  z-index: 1000;
}
.lead-add-content {
  background: #fff;
  padding: 20px;
  border-radius: 10px;
  width: 400px;
  max-width: 90%;
}
.lead-add-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}
.close-lead-modal {
  cursor: pointer;
  font-size: 22px;
}
.action-cell span {
  cursor: pointer;
  margin: 0 4px;
  font-size: 18px;
  transition: transform 0.2s;
}
.action-cell span:hover {
  transform: scale(1.2);
}

/* Calendar styling */
.simcal-calendar {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    padding: 20px;
    margin-bottom: 20px;
}

#addLeadBtn, #saveLeadBtn {
    color: #fff!important;
}

/* Leads Table Styling */
.leads-table thead th:first-child {
    border-top-left-radius: 10px;
}
.leads-table thead th:last-child {
    border-top-right-radius: 10px;
}
.leads-table {
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #ddd;
    border-radius: 10px;
    overflow: hidden;
}

/* Active Clients Table Styling */
.simcal-calendar-grid {
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #ddd;
    border-radius: 10px;
    overflow: hidden;
}
.simcal-calendar-grid thead tr:first-child th:first-child {
    border-top-left-radius: 10px;
}
.simcal-calendar-grid thead tr:first-child th:last-child {
    border-top-right-radius: 10px;
}

/* ===== MOBILE VIEW ===== */
@media (max-width: 768px) {

  /* Hide table headers */
  .active-clients-table thead,
  .leads-table thead {
      display: none;
  }

  /* Make each row a block */
  .active-clients-table tr,
  .leads-table tr {
      display: block;
      margin-bottom: 12px;
      border: 1px solid #ddd;
      border-radius: 8px;
      overflow: hidden;
      padding: 8px 0;
  }

  /* Each cell becomes flex row */
  .active-clients-table td,
  .leads-table td {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 6px 12px;
      border-bottom: 1px solid #eee;
  }

  /* Remove border from last cell */
  .active-clients-table td:last-child,
  .leads-table td:last-child {
      border-bottom: 0;
  }

  /* Show data-label before value */
  .active-clients-table td::before,
  .leads-table td::before {
      content: attr(data-label);
      font-weight: 600;
      text-transform: uppercase;
      color: #555;
      margin-right: 10px;
      flex-shrink: 0;
  }

  /* + Add Lead button */
  #addLeadBtn {
      white-space: nowrap;
  }

  /* Action buttons spacing */
  .action-cell {
      display: flex;
      justify-content: flex-start;
      gap: 20px;
  }

  /* Status dots & text alignment fix */
  .leads-table td[data-label="Status"] {
      justify-content: flex-start;
  }

  .simcal-calendar {
    padding: 10px;
  }
}

/* Align Active Clients title and button in same line */
.active-clients-section {
    display: flex;
    flex-direction: column;
}

.active-clients-section {
    margin: 0;
    padding: 0;
    align-self: flex-start;
}

.active-clients-section .btn-primary {
    align-self: flex-end;
    margin-top: -28px; /* Adjust based on your title's line-height */
}

/* Ensure consistent styling with Add Lead button */
#addClientBtn {
    color: #fff !important;
}

/* Active Clients Table Actions column width */
.active-clients-table th:last-child,
.active-clients-table td:last-child {
    width: 100px;
    text-align: center;
}
</style>

<style>
/* Lead Status Display */
.lead-status {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-weight: 600;
  text-transform: capitalize;
}

.lead-status-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
}

/* Color Coding */
.lead-hot .lead-status-dot {
  background-color: #ff4d4d; /* red */
}

.lead-warm .lead-status-dot {
  background-color: #ffc107; /* yellow */
}

.lead-cold .lead-status-dot {
  background-color: #4caf50; /* green */
}
</style>

<style>
/* ======= TABLE CONTROLS ======= */
.table-controls {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  margin: 12px 0 16px;
  gap: 10px;
  flex-wrap: wrap;
}

/* Search Input */
.table-search {
  max-width: 200px !important;
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
  transition: all 0.2s ease;
}

.table-search:focus {
  outline: none;
  border-color: #0073aa;
  box-shadow: 0 0 0 2px rgba(0,115,170,0.2);
}

/* Dropdown */
.rows-selector {
  max-width: 100px !important;
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  background-color: #fff;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.rows-selector:hover {
  border-color: #0073aa;
}

/* Pagination Container */
.pagination {
    margin-top: 10px;
    text-align: center;
}

/* Pagination Buttons */
.pagination .page-btn {
    display: inline-block;
    margin: 0 3px; /* ছোট gap between buttons */
    padding: 4px 8px; /* ছোট padding */
    font-size: 13px; /* ছোট font */
    border: 1px solid #0073aa; /* শুধুমাত্র border */
    background-color: #fff; /* white background */
    color: #0073aa;
    cursor: pointer;
    border-radius: 3px;
    transition: background-color 0.2s, color 0.2s;
}

/* Hover Effect */
.pagination .page-btn:hover {
    background-color: #0073aa;
    color: #fff;
}

/* Active Button */
.pagination .page-btn.active {
    background-color: #0073aa;
    color: #fff;
}

/* Optional: Remove width change on small screens */
@media (max-width: 768px) {
  .table-controls {
    flex-direction: row;
    align-items: flex-start;
  }
}

</style>