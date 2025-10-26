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
    include locate_template('dashboard-templates/rt/rt-client-create-modal.php');
    include locate_template('dashboard-templates/rt/rt-client-edit-modal.php');
    include locate_template('dashboard-templates/rt/rt-client-details-modal.php');
?>

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

.action-cell {
  text-align: center;
}

.action-cell .editClientBtn,
.action-cell .deleteClientBtn {
  display: inline-block;
  margin: 0 6px; /* <-- gap between icons */
  cursor: pointer;
  transition: transform 0.2s ease;
}

.action-cell .editClientBtn:hover,
.action-cell .deleteClientBtn:hover {
  transform: scale(1.2);
}

.ab-controls {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 6px;
}

#abControls #addressBookRows,
#addressBookRows {
    width: 100px;
    padding: 0 8px;
    margin-bottom: 5px;
    border: 1px solid #ccc;
    border-radius: 6px;
    background-color: #fff;
    font-size: 14px;
    cursor: pointer;
}

.ab-pagination {
  display: flex;
  justify-content: flex-end; /* ✅ Right aligned */
  align-items: center;
  gap: 5px;                 /* Space between buttons */
  margin-top: 15px;
  padding-right: 10px;      /* Optional – adds a little breathing room on the right */
}

.ab-pagination button {
  padding: 4px 8px;         /* Small button size */
  font-size: 13px;          /* Compact font */
  border: 1px solid #ccc;
  border-radius: 4px;
  background-color: #f9f9f9;
  cursor: pointer;
  transition: all 0.2s ease;
}

.ab-pagination button:hover {
  background-color: #e6e6e6;
  border-color: #bbb;
}

.ab-pagination button.active {
  background-color: #0052cc;
  color: #fff;
  border-color: #0052cc;
}


</style>