<div class="dashboard-top">

    <!-- LEFT SIDE -->
    <div class="dashboard-top-left">

        <!-- Active Clients Section -->
        <div class="dashboard-section active-clients-section">
            <div class="clients-header">
                <h1 class="header-title">Active Clients</h1>
                <div class="table-controls-row">
                    <input type="text" id="activeClientsSearch" placeholder="Search Active Clients">
                    <select id="activeClientsRows">
                        <option value="5">5 rows</option>
                        <option value="10" selected>10 rows</option>
                        <option value="25">25 rows</option>
                    </select>
                    <button id="addClientBtn" class="btn-primary">+ Add Client</button>
                </div>
            </div>

            <table class="active-clients-table">
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Notes</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="activeClientsBody">
                    <tr><td colspan="5" style="text-align:center;">Loading...</td></tr>
                </tbody>
            </table>
            <div id="activeClientsPagination" class="pagination"></div>
        </div>

        <!-- Leads Section -->
        <div class="dashboard-section leads-section">
            <div class="leads-header">
                <h1 class="header-title">Leads</h1>
                <div class="table-controls-row">
                    <input type="text" id="leadsSearch" placeholder="Search Leads">
                    <select id="leadsRows">
                        <option value="5">5 rows</option>
                        <option value="10" selected>10 rows</option>
                        <option value="25">25 rows</option>
                    </select>
                    <button id="addLeadBtn" class="btn-primary">+ Add Lead</button>
                </div>
            </div>

            <table class="leads-table">
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Last Touch</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th style="width:140px">Actions</th>
                    </tr>
                </thead>
                <tbody id="leadsBody">
                    <tr><td colspan="5" style="text-align:center;">Loading...</td></tr>
                </tbody>
            </table>
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

        <div class="notes-header">
            <h1>Notes</h1>
            <button class="add-note-btn">+</button>
        </div>

        <div class="sticky-notes-container"></div>
    </div>
</div>

<?php 
// Include modals
include locate_template('dashboard-templates/rt/rt-client-create-modal.php');
include locate_template('dashboard-templates/rt/rt-client-edit-modal.php');
?>

<!-- =================== IMPROVED JS =================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // -------------------
    // CREATE CLIENT / LEAD
    // -------------------
    const addClientBtn = document.getElementById('addClientBtn');
    const addLeadBtn   = document.getElementById('addLeadBtn');
    const createModal  = document.getElementById('rmRealtorClientCreateModal');
    const closeCreateBtn = document.getElementById('closeRealtorClientCreateModal');
    const createForm   = document.getElementById('createRealtorClientForm');
    const createProfileInput = document.getElementById('create_realtor_client_profile_picture');

    if(addClientBtn && createModal){
        addClientBtn.addEventListener('click', () => { 
            createForm?.reset();
            createModal.style.display = 'flex';
        });
    }

    if(addLeadBtn && createModal){
        addLeadBtn.addEventListener('click', () => { 
            createForm?.reset();
            document.getElementById('create_realtor_client_status').value = 'lead';
            document.getElementById('createRealtorClientPreviewAvatar').src = "<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>";
            createModal.style.display = 'flex';
        });
    }

    closeCreateBtn?.addEventListener('click', ()=> createModal.style.display='none');
    createModal?.addEventListener('click', e=> {if(e.target===createModal) createModal.style.display='none'});

    createProfileInput?.addEventListener('change', function(){
        const file = this.files[0];
        if(file){
            const reader = new FileReader();
            reader.onload = e => document.getElementById('createRealtorClientPreviewAvatar').src = e.target.result;
            reader.readAsDataURL(file);
        }
    });

    createForm?.addEventListener('submit', function(e){
        e.preventDefault();
        const fullName = document.getElementById('create_realtor_client_full_name').value.trim();
        const email = document.getElementById('create_realtor_client_email').value.trim();
        const status = document.getElementById('create_realtor_client_status').value;
        if(!fullName || !email || !status){ alert('Fill all required fields'); return; }

        const fd = new FormData(createForm);
        fd.append('action','create_realtor_client_ajax');
        fd.append('nonce','<?php echo wp_create_nonce("cl_client_create_nonce"); ?>');

        const submitBtn = createForm.querySelector('button[type="submit"]');
        const origText = submitBtn.textContent;
        submitBtn.textContent='Creating...'; submitBtn.disabled=true;

        fetch('<?php echo admin_url("admin-ajax.php"); ?>',{method:'POST',body:fd})
        .then(r=>r.json())
        .then(d=>{
            if(d.success){
                alert('Client created!');
                createForm.reset();
                document.getElementById('createRealtorClientPreviewAvatar').src="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>";
                createModal.style.display='none';
                setTimeout(()=>window.location.reload(),800);
            } else alert('Error: '+d.data);
        }).finally(()=>{submitBtn.textContent=origText; submitBtn.disabled=false;});
    });

    // -------------------
    // EVENT DELEGATION FOR DYNAMIC ROWS
    // -------------------
    const tableContainer = document.querySelector('.dashboard-top-left');
    tableContainer.addEventListener('click', function(e){
        const target = e.target;

        // ===== EDIT =====
        if(target.matches('.edit-client-btn, .edit-lead-btn')){
            const row = target.closest('tr');
            const clientId = row.dataset.clientId;
            if(!clientId) return;
            const editModal = document.getElementById('rmRealtorClientEditModal');
            const leadStatusRow = document.getElementById('leadStatusRow');
            const leadStatusSelect = document.getElementById('edit_realtor_lead_status');

            editModal.style.display='flex';
            fetch('<?php echo admin_url("admin-ajax.php"); ?>',{
                method:'POST',
                body: new URLSearchParams({
                    action:'fetch_realtor_client_ajax',
                    nonce:'<?php echo wp_create_nonce("cl_client_edit_nonce"); ?>',
                    client_id:clientId
                })
            }).then(r=>r.json()).then(d=>{
                if(d.success){
                    const c=d.data;
                    document.getElementById('edit_realtor_client_id').value=c.client_id;
                    document.getElementById('edit_realtor_client_full_name').value=c.full_name;
                    document.getElementById('edit_realtor_client_email').value=c.email;
                    document.getElementById('edit_realtor_client_phone').value=c.phone;
                    document.getElementById('edit_realtor_client_notes').value=c.note;
                    document.getElementById('edit_realtor_client_status').value=c.status;
                    document.getElementById('editRealtorClientPreviewAvatar').src=c.profile_picture || "<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>";

                    if(c.status==='lead'){
                        leadStatusRow.style.display='flex';
                        leadStatusSelect.value=c.lead_status||'cold';
                    } else leadStatusRow.style.display='none';
                } else { alert('Failed to fetch client'); editModal.style.display='none'; }
            }).catch(()=>{alert('Network error'); editModal.style.display='none';});
        }

        // ===== DELETE =====
        if(target.matches('.delete-client-btn, .delete-lead-btn')){
            const row = target.closest('tr');
            const clientId = row.dataset.clientId;
            if(!clientId || !confirm('Are you sure?')) return;

            const fd = new FormData();
            fd.append('action','delete_realtor_client_ajax');
            fd.append('nonce','<?php echo wp_create_nonce("cl_client_delete_nonce"); ?>');
            fd.append('client_id',clientId);

            const origText = target.textContent;
            target.textContent='Deleting...'; target.disabled=true;

            fetch('<?php echo admin_url("admin-ajax.php"); ?>',{method:'POST',body:fd})
            .then(r=>r.json())
            .then(d=>{
                if(d.success) row.remove();
                else{ alert('Error: '+d.data); target.textContent=origText; target.disabled=false; }
            }).catch(err=>{ alert('Network error'); target.textContent=origText; target.disabled=false;});
        }

        // ===== CONVERT LEAD =====
        if(target.matches('.convert-lead-btn')){
            const row = target.closest('tr');
            const clientId = row.dataset.clientId;
            if(!clientId || !confirm('Convert this lead?')) return;

            const fd = new FormData();
            fd.append('action','convert_lead_to_client');
            fd.append('nonce','<?php echo wp_create_nonce("convert_lead_nonce"); ?>');
            fd.append('client_id',clientId);

            const origText = target.textContent;
            target.textContent='Converting...'; target.disabled=true;

            fetch('<?php echo admin_url("admin-ajax.php"); ?>',{method:'POST',body:fd})
            .then(r=>r.json())
            .then(d=>{
                if(d.success){
                    alert('Lead converted!');
                    row.remove();
                    // Optionally, reload table via AJAX
                    // loadTable('activeClients');
                } else { alert('Error: '+d.data); target.textContent=origText; target.disabled=false; }
            }).catch(()=>{ alert('Network error'); target.textContent=origText; target.disabled=false;});
        }
    });

    // -------------------
    // EDIT FORM SUBMIT
    // -------------------
    const editForm = document.getElementById('editRealtorClientForm');
    const editProfileInput = document.getElementById('edit_realtor_client_profile_picture');
    const editModal = document.getElementById('rmRealtorClientEditModal');
    const closeEditBtn = document.getElementById('closeRealtorClientEditModal');

    closeEditBtn?.addEventListener('click', ()=> editModal.style.display='none');
    editModal?.addEventListener('click', e=>{if(e.target===editModal) editModal.style.display='none';});

    editProfileInput?.addEventListener('change', function(){
        const file=this.files[0];
        if(file){const reader=new FileReader(); reader.onload=e=>document.getElementById('editRealtorClientPreviewAvatar').src=e.target.result; reader.readAsDataURL(file);}
    });

    editForm?.addEventListener('submit', function(e){
        e.preventDefault();
        const fd = new FormData(editForm);
        fd.append('action','update_realtor_client_ajax');
        fd.append('nonce','<?php echo wp_create_nonce("cl_client_edit_nonce"); ?>');

        const submitBtn = editForm.querySelector('button[type="submit"]');
        const origText = submitBtn.textContent;
        submitBtn.textContent='Updating...'; submitBtn.disabled=true;

        fetch('<?php echo admin_url("admin-ajax.php"); ?>',{method:'POST',body:fd})
        .then(r=>r.json())
        .then(d=>{
            if(d.success){ alert('Client updated!'); editForm.reset(); editModal.style.display='none'; setTimeout(()=>window.location.reload(),800);}
            else alert('Error: '+d.data);
        }).finally(()=>{submitBtn.textContent=origText; submitBtn.disabled=false;});
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

.active-clients-section .header-title {
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
/* Header + controls row */
.clients-header,
.leads-header {
    display: flex;
    justify-content: space-between; /* title left, controls right */
    align-items: center;
    margin-bottom: 15px;
}

/* Controls container */
.table-controls-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Search box */
.table-controls-row input[type="text"] {
    width: 200px;
    padding: 6px 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

/* Dropdown */
.table-controls-row select {
    width: 100px;
    padding: 6px 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

/* Button already styled; small top adjustment if needed */
.table-controls-row .btn-primary {
    padding: 12px 12px;
}

/* Pagination buttons styling */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px; /* spacing between buttons */
    margin-top: 12px;
}

.pagination button {
    background: transparent;       /* remove background */
    border: 1px solid #2271b1;     /* only border */
    color: #2271b1;                /* text color same as border */
    padding: 4px 10px;             /* small size */
    border-radius: 4px;            /* rounded corners */
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.pagination button:hover {
    background: #2271b1;  /* blue hover */
    color: #fff;
}

.pagination button.active {
    background: #2271b1;  /* active state */
    color: #fff!important;
    font-weight: 600;
}


/* Responsive fix */
@media screen and (max-width: 600px) {
    .clients-header,
    .leads-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .table-controls-row {
        margin-top: 10px;
        flex-wrap: wrap;
        justify-content: flex-start;
    }
}
</style>