<div class="dashboard-top">

    <!-- LEFT SIDE -->
    <div class="dashboard-top-left">

        <!-- Active Clients Section -->
        <div class="dashboard-section active-clients-section">
            <div class="clients-header">
                <h2 class="header-title">👥 Active Clients</h2>
                <div class="table-controls-row">
                    <input type="text" id="activeClientsSearch" placeholder="Search Active Clients">
                    <select id="activeClientsRows">
                        <option value="5">5 rows</option>
                        <option value="10" selected>10 rows</option>
                        <option value="25">25 rows</option>
                    </select>
                    <button id="addActiveBtn" class="btn-primary">+ Add Active Client</button>
                </div>
            </div>

            <table class="active-clients-table">
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Notes</th>
                        <th style="width:100px; text-align:center">Actions</th>
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
                <h2 class="header-title">📋 Leads</h2>
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
                        <th style="width:100px; text-align:center">Actions</th>
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

        <!-- <div class="notes-header">
            <h1>📝 Notes</h1>
            <button class="add-note-btn">+</button>
        </div>

        <div class="sticky-notes-container"></div> -->

        <!-- ===== NOTE HEADER BOX ===== -->
        <div class="cld-box cld-note-header-box">
            <div class="cld-box-header">
                <span>📝 Note Header</span>
                <button type="button" class="cld-send-btn" id="add-note-header-btn">
                    Add Note Header
                </button>
            </div>
            <div class="cld-box-body">
                <ul id="note-header-list">
                    <li class="empty">No note headers found</li>
                </ul>
            </div>
        </div>

        <!-- ===== NOTE HEADER MODAL ===== -->
        <div id="noteHeaderModal" class="note-modal">
            <div class="note-modal-content">
                <span class="note-modal-close" data-modal="noteHeaderModal">&times;</span>
                <h3 id="note-header-modal-title">Add Note Header</h3>
                <input type="hidden" id="note_id">
                <label for="note_header_input">Note Header</label>
                <input type="text" id="note_header_input" placeholder="Note Header">
                <button type="button" id="save_note_header" class="cld-send-btn">Save</button>
            </div>
        </div>

        <!-- ===== NOTES BOX ===== -->
        <div class="cld-box cld-notes-box">
            <div class="cld-box-header">
                <span>📝 Notes</span>
                <button type="button" class="cld-send-btn" id="add-note-btn">Add Note</button>
            </div>
            <div class="cld-box-body">
                <ul id="notes-list" class="notes-preview-list">
                    <li class="empty">No notes found</li>
                </ul>
                <button id="view-all-notes-btn" class="cld-send-btn" style="display:none; margin-top:10px;">View All Notes</button>
            </div>
        </div>

        <!-- ===== NOTE MODAL (Add/Edit Note) ===== -->
        <div id="noteModal" class="note-modal">
            <div class="note-modal-content">
                <span class="note-modal-close" data-modal="noteModal">&times;</span>
                <h3 id="note-modal-title">Add Note</h3>
                <input type="hidden" id="note_row_id">
                <label for="note_header_select">Note Header</label>
                <select id="note_header_select">
                    <option value="">Select Note Header</option>
                </select>
                <label for="note_text">Note</label>
                <textarea id="note_text" placeholder="Write your note..." rows="5"></textarea>
                <button type="button" id="save_note" class="cld-send-btn">Save</button>
            </div>
        </div>

        <!-- ===== VIEW ALL NOTES MODAL ===== -->
        <div id="allNotesModal" class="note-modal">
            <div class="note-modal-content" style="width:500px; max-height:70vh; overflow-y:auto;">
                <span class="note-modal-close" data-modal="allNotesModal">&times;</span>
                <h3>All Notes</h3>
                <ul id="all-notes-list">
                    <!-- All notes will be appended here -->
                </ul>
            </div>
        </div>

    </div>
</div>

<?php 
// Include modals
include locate_template('dashboard-templates/rt/rt-db-active-create-modal.php');
include locate_template('dashboard-templates/rt/rt-db-active-edit-modal.php');

include locate_template('dashboard-templates/rt/rt-db-lead-create-modal.php');
include locate_template('dashboard-templates/rt/rt-db-lead-edit-modal.php');
?>

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

#addLeadBtn, #saveLeadBtn, #addActiveBtn {
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
    justify-content: right;
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



/* css for note header and notes section ************************************/

/* ===============================
   CLD BOX (Note Header & Notes)
   =============================== */

.cld-box {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.cld-box-header {
    background: #3578c6;
    color: #fff;
    padding: 12px 16px;
    font-size: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.cld-box-body {
    padding: 20px;
}

/* ===============================
   BUTTONS
   =============================== */

.cld-send-btn {
    padding: 8px 14px;
    background: #4e6ef2;
    color: #fff !important;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    font-size: 14px;
    transition: 0.3s;
}

.cld-send-btn:hover {
    background: #3b54c1;
}

#save_note {
    width: 100%;
}

#view-all-notes-btn {
    margin-top: 10px;
    display: none;
}

/* ===============================
   NOTE HEADER LIST
   =============================== */

#note-header-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

#note-header-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 12px;
    border-bottom: 1px solid #eee;
    font-size: 15px;
}

.note-header-text {
    flex: 1;
    font-weight: 500;
}

.edit-header,
.delete-header {
    margin-left: 10px;
    cursor: pointer;
    font-size: 14px;
}

.edit-header:hover { color: #3578c6; }
.delete-header:hover { color: #e74c3c; }

/* ===============================
   NOTES LIST
   =============================== */

#notes-list,
#all-notes-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.notes-preview-list {
    max-height: 600px;
    overflow-y: auto;
}

#notes-list li,
#all-notes-list li {
    padding: 10px 12px;
    border-bottom: 1px solid #eee;
    border-radius: 5px;
    margin-bottom: 5px;
}

.note-header-title {
    font-weight: 600;
    font-size: 15px;
    margin-bottom: 5px;
}

.note-text {
    font-size: 14px;
    color: #555;
}

.note-actions {
    margin-top: 6px;
}

.note-actions span {
    cursor: pointer;
    margin-right: 10px;
    font-size: 13px;
    color: #3578c6;
}

.note-actions .delete-note {
    color: #e74c3c;
}

/* ===============================
   MODALS
   =============================== */

.note-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.note-modal.show {
    display: flex;
}

.note-modal-content {
    background: #fff;
    padding: 20px;
    width: 400px;
    border-radius: 10px;
    position: relative;
}

#allNotesModal .note-modal-content {
    width: 500px;
    max-height: 70vh;
    overflow-y: auto;
}

.note-modal-content h3 {
    margin-top: 0;
    margin-bottom: 15px;
}

.note-modal-content label {
    display: block;
    margin-bottom: 5px;
    font-size: 14px;
}

.note-modal-content input,
.note-modal-content select,
.note-modal-content textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.note-modal-close {
    position: absolute;
    top: 10px;
    right: 12px;
    font-size: 22px;
    cursor: pointer;
}

</style>