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
        <button class="ab-btn ab-btn-import" id="openAbImportModal">
          <span class="dashicons dashicons-upload"></span> Import
        </button>
        <div class="ab-export-dropdown">
          <button class="ab-btn ab-btn-export" id="openAbExportModal">
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
    <label for="addressBookRows">Show:</label>
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
        <th class="address">Address</th>
        <th class="notes">Notes</th>
        <th class="Status">Status</th>
        <th class="ab-actions-column">Actions</th>
      </tr>
    </thead>
    <tbody id="addressBookBody">
      <tr><td colspan="7" style="text-align:center;">Loading...</td></tr>
    </tbody>
  </table>

  <div id="addressBookPagination" class="ab-pagination"></div>
</div>

<?php
// Include modals
include locate_template('dashboard-templates/rt/rt-ab-client-create-modal.php');
include locate_template('dashboard-templates/rt/rt-ab-client-edit-modal.php');
include locate_template('dashboard-templates/rt/rt-ab-client-details-modal.php');
?>

<!-- ===== Export Modal ===== -->
<div id="abExportModal" class="ab-modal">
  <div class="ab-modal-inner" role="dialog" aria-modal="true" aria-labelledby="abExportTitle">
    <button class="ab-modal-close" id="abExportClose">✕</button>
    <h2 id="abExportTitle">Export Address Book</h2>
    <p>Select export format and data scope below.</p>

    <div class="ab-section">
      <label>File format</label>
      <div class="ab-radios">
        <label><input type="radio" name="ab_export_format" value="csv" checked> CSV (.csv)</label>
        <label><input type="radio" name="ab_export_format" value="xlsx"> Excel (.xlsx)</label>
      </div>
    </div>

    <div class="ab-section">
      <label>Export scope</label>
      <div class="ab-radios">
        <label><input type="radio" name="ab_export_scope" value="current" checked> Current page only</label>
        <label><input type="radio" name="ab_export_scope" value="all"> All records</label>
      </div>
    </div>

    <div class="ab-section">
      <label>Columns to include</label>
      <div class="ab-checkboxes">
        <label><input type="checkbox" name="ab_export_columns" value="full_name" checked> Client Name</label>
        <label><input type="checkbox" name="ab_export_columns" value="email" checked> Email</label>
        <label><input type="checkbox" name="ab_export_columns" value="phone" checked> Phone</label>
        <label><input type="checkbox" name="ab_export_columns" value="address" checked> Address</label> <!-- New -->
        <label><input type="checkbox" name="ab_export_columns" value="note" checked> Notes</label>
        <label><input type="checkbox" name="ab_export_columns" value="status" checked> Status</label>
        <label><input type="checkbox" name="ab_export_columns" value="profile_picture"> Profile Picture URL</label>
        <label><input type="checkbox" name="ab_export_columns" value="created_at"> Created At</label>
      </div>
    </div>

    <div class="ab-footer-buttons">
      <button class="ab-btn" id="abExportCancel">Cancel</button>
      <button class="ab-btn ab-btn-primary" id="abExportStart">Export</button>
    </div>

    <div id="abExportStatus"></div>
  </div>
</div>

<!-- ===== Import Modal ===== -->
<div id="abImportModal" class="ab-modal">
  <div class="ab-modal-inner" role="dialog" aria-modal="true" aria-labelledby="abImportTitle">
    <button class="ab-modal-close" id="abImportClose">✕</button>
    <h2 id="abImportTitle">Import Address Book</h2>
    <p>You can upload a CSV (.csv) or Excel (.xlsx) file to import contacts. Download a sample template first to check the format.</p>

    <div class="ab-section">
      <a href="#" id="abDownloadCsvTemplate" class="ab-btn">Download CSV Template</a>
      <a href="#" id="abDownloadXlsxTemplate" class="ab-btn">Download Excel Template</a>
    </div>

    <div class="ab-section">
      <label>Select file to import</label>
      <input type="file" id="abImportFileInput" accept=".csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
    </div>

    <div class="ab-section">
      <label>If a record with the same email exists:</label>
      <div class="ab-radios">
        <label><input type="radio" name="ab_import_duplicate" value="skip" checked> Skip (keep existing)</label>
        <label><input type="radio" name="ab_import_duplicate" value="update"> Update existing</label>
        <label><input type="radio" name="ab_import_duplicate" value="create"> Create duplicate</label>
      </div>
    </div>

    <div class="ab-section">
      <label>Preview first rows (optional)</label>
      <div id="abImportPreview">No file selected</div>
    </div>

    <div class="ab-footer-buttons">
      <button class="ab-btn" id="abImportCancel">Cancel</button>
      <button class="ab-btn ab-btn-primary" id="abImportStart" disabled>Import</button>
    </div>

    <div id="abImportStatus"></div>
  </div>
</div>

<!-- XLSX library (required for Excel parsing) -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const exportModal = document.getElementById('abExportModal');
  const importModal = document.getElementById('abImportModal');
  const exportStatus = document.getElementById('abExportStatus');
  const importStatus = document.getElementById('abImportStatus');
  const importFileInput = document.getElementById('abImportFileInput');
  const importStartBtn = document.getElementById('abImportStart');
  const importPreview = document.getElementById('abImportPreview');

  const closeModal = (modal) => { modal.style.display = 'none'; };
  const showNotification = (msg, type='success') => { console.log(type.toUpperCase(), msg); };

  const refreshClientsTable = async () => {
    if (typeof window.fetchClients === 'function') {
      await window.fetchClients({
        page: 1,
        rows: parseInt(document.getElementById('addressBookRows').value, 10),
        search: document.getElementById('addressBookSearch').value.trim(),
        bodyId: 'addressBookBody',
        paginationId: 'addressBookPagination'
      });
    }
  };

  // ===== MODAL OPEN/CLOSE =====
  document.getElementById('openAbExportModal')?.addEventListener('click', () => exportModal.style.display = 'flex');
  document.getElementById('abExportClose')?.addEventListener('click', () => closeModal(exportModal));
  document.getElementById('abExportCancel')?.addEventListener('click', () => closeModal(exportModal));
  exportModal?.addEventListener('click', e => { if (e.target === exportModal) closeModal(exportModal); });

  document.getElementById('openAbImportModal')?.addEventListener('click', () => importModal.style.display = 'flex');
  document.getElementById('abImportClose')?.addEventListener('click', () => closeModal(importModal));
  document.getElementById('abImportCancel')?.addEventListener('click', () => closeModal(importModal));
  importModal?.addEventListener('click', e => { if (e.target === importModal) closeModal(importModal); });

  // ===== ENABLE IMPORT BUTTON =====
  importFileInput?.addEventListener('change', async () => {
    importPreview.innerHTML = 'Loading preview...';
    importStatus.textContent = '';
    importStartBtn.disabled = true;

    if (!importFileInput.files || importFileInput.files.length === 0) {
      importPreview.textContent = 'No file selected';
      return;
    }

    const file = importFileInput.files[0];
    const ext = file.name.split('.').pop().toLowerCase();

    try {
      if (ext === 'csv') {
        const text = await file.text();
        const rows = text.split('\n').slice(0, 5).map(r => r.split(','));
        importPreview.innerHTML = `
          <table class="ab-preview-table">
            ${rows.map(r => `<tr>${r.map(c => `<td>${c}</td>`).join('')}</tr>`).join('')}
          </table>
        `;
      } else if (ext === 'xlsx') {
        const data = await file.arrayBuffer();
        const workbook = XLSX.read(data, { type: 'array' });
        const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
        const rows = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });
        const previewRows = rows.slice(0, 5);
        importPreview.innerHTML = `
          <table class="ab-preview-table">
            ${previewRows.map(r => `<tr>${r.map(c => `<td>${c}</td>`).join('')}</tr>`).join('')}
          </table>
        `;
      } else {
        importPreview.textContent = 'Unsupported file format.';
        return;
      }

      importStatus.textContent = `Selected file: ${file.name}`;
      importStartBtn.disabled = false;
    } catch (err) {
      importPreview.textContent = 'Failed to preview file: ' + err.message;
    }
  });

  // ===== EXPORT CLIENTS =====
  document.getElementById('abExportStart')?.addEventListener('click', async () => {
    const format = document.querySelector('input[name="ab_export_format"]:checked')?.value || 'csv';
    const scope = document.querySelector('input[name="ab_export_scope"]:checked')?.value || 'current';
    const columns = Array.from(document.querySelectorAll('input[name="ab_export_columns"]:checked')).map(el => el.value);
    exportStatus.textContent = 'Exporting...';

    try {
      const formData = new FormData();
      formData.append('action', 'export_clients_ajax');
      formData.append('nonce', rtClientAjax.export_nonce);
      formData.append('format', format);
      formData.append('scope', scope);
      formData.append('columns', JSON.stringify(columns));

      const response = await fetch(rtClientAjax.ajax_url, { method: 'POST', body: formData });
      if (!response.ok) throw new Error('Network response not ok');
      const data = await response.json();
      if (!data.success) throw new Error(data.data?.message || data.data || 'Export failed');
      const clients = data.data.clients || [];

      if (format === 'csv') {
        const csvRows = [];
        csvRows.push(columns.join(','));
        clients.forEach(client => {
          const row = columns.map(col => {
            let val = client[col] ?? '';
            if (typeof val === 'string' && val.includes(',')) val = `"${val.replace(/"/g, '""')}"`;
            return val;
          });
          csvRows.push(row.join(','));
        });
        const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `clients-export-${new Date().toISOString().slice(0,10)}.csv`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
      } else {
        const worksheetData = clients.map(client => {
          const obj = {};
          columns.forEach(col => obj[col] = client[col] ?? '');
          return obj;
        });
        const ws = XLSX.utils.json_to_sheet(worksheetData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Clients');
        XLSX.writeFile(wb, `clients-export-${new Date().toISOString().slice(0,10)}.xlsx`);
      }

      exportStatus.textContent = 'Export completed!';
      setTimeout(() => closeModal(exportModal), 1000);
      showNotification('Export completed!');

    } catch (error) {
      exportStatus.textContent = 'Export failed: ' + error.message;
      showNotification('Export failed: ' + error.message, 'error');
    }
  });

  // ===== IMPORT CLIENTS =====
  importStartBtn?.addEventListener('click', async () => {
    if (!importFileInput.files || importFileInput.files.length === 0) {
      importStatus.textContent = 'Please select a file.';
      return;
    }

    let file = importFileInput.files[0];
    const duplicateHandling = document.querySelector('input[name="ab_import_duplicate"]:checked')?.value || 'skip';
    const ext = file.name.split('.').pop().toLowerCase();

    importStartBtn.disabled = true;
    importStartBtn.textContent = 'Importing...';
    importStatus.textContent = 'Importing...';

    try {
      // Convert XLSX → CSV if PHP backend only supports CSV
      if (ext === 'xlsx') {
        importStatus.textContent = 'Converting Excel to CSV...';
        const data = await file.arrayBuffer();
        const workbook = XLSX.read(data, { type: 'array' });
        const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
        const csv = XLSX.utils.sheet_to_csv(firstSheet);
        const csvBlob = new Blob([csv], { type: 'text/csv' });
        file = new File([csvBlob], file.name.replace('.xlsx', '.csv'), { type: 'text/csv' });
      }

      const formData = new FormData();
      formData.append('action', 'import_clients_ajax');
      formData.append('nonce', rtClientAjax.import_nonce);
      formData.append('clients_file', file);
      formData.append('duplicate_handling', duplicateHandling);

      const response = await fetch(rtClientAjax.ajax_url, { method: 'POST', body: formData });
      if (!response.ok) throw new Error('Network response not ok: ' + response.status);

      const data = await response.json();
      if (data.success) {
        const message = data.data?.message || 'Import successful!';
        importStatus.textContent = message;
        showNotification(message);
        await refreshClientsTable();
        importFileInput.value = '';
        importPreview.innerHTML = 'No file selected';
        setTimeout(() => closeModal(importModal), 1500);
      } else {
        const errMsg = data.data?.message || data.data || 'Unknown error occurred';
        importStatus.textContent = 'Import failed: ' + errMsg;
        showNotification('Import failed: ' + errMsg, 'error');
      }
    } catch (error) {
      console.error('Import error:', error);
      importStatus.textContent = 'Import failed: ' + error.message;
      showNotification('Import failed: ' + error.message, 'error');
    } finally {
      importStartBtn.disabled = false;
      importStartBtn.textContent = 'Import';
    }
  });
});
</script>

<style>
/* ---------- Layout ---------- */
.ab-controls {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 6px;
  margin-bottom: 10px;
}
#abControls #addressBookRows, #addressBookRows {
  width: 100px;
  padding: 6px 8px;
  border: 1px solid #ccc;
  border-radius: 6px;
  background-color: #fff;
  font-size: 14px;
  cursor: pointer;
}

/* ---------- Table ---------- */
table {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
  font-size: 14px;
  border: 1px solid #ddd;
  border-radius: 10px 10px 0 0;
  overflow: hidden;
}
thead th {
  text-align: left;
  padding: 10px;
  border-bottom: 2px solid #ddd;
  font-weight: 600;
}
tbody td {
  padding: 10px;
  border-bottom: 1px solid #eee;
  vertical-align: middle;
}
tbody tr:hover { background-color: #f9f9f9; }
.ab-sl-column img {
  border-radius: 50%;
  width: 40px;
  height: 40px;
  object-fit: cover;
}

/* ---------- Pagination ---------- */
.ab-pagination {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 6px;
  margin-top: 15px;
}
.ab-pagination button {
  padding: 4px 8px;
  font-size: 13px;
  border: 1px solid #ccc;
  border-radius: 4px;
  background-color: #f9f9f9;
  cursor: pointer;
}
.ab-pagination button.active {
  background-color: #0052cc;
  color: #fff;
  border-color: #0052cc;
}

/* ---------- Modal ---------- */
.ab-modal {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.45);
  z-index: 9999;
  align-items: center;
  justify-content: center;
}
.ab-modal-inner {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  width: 100%;
  max-width: 600px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.15);
  position: relative;
}
.ab-modal-close {
  position: absolute;
  top: 14px;
  right: 14px;
  border: none;
  background: transparent;
  font-size: 20px;
  cursor: pointer;
  color: #444;
}

/* ---------- Buttons (Updated) ---------- */
.ab-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  font-size: 14px;
  margin-bottom: 10px;
  border-radius: 6px;
  border: 1px solid #007bff;
  background-color: #007bff;
  color: #fff !important;
  cursor: pointer;
  transition: all 0.3s ease;
}

.ab-btn:hover {
  background-color: #0056b3;
  border-color: #0056b3;
}

.ab-btn:active {
  background-color: #004a99;
  border-color: #004a99;
  transform: scale(0.98);
}

/* Specific buttons if needed */
.ab-btn-import { background-color: #007bff; border-color: #007bff; }
.ab-btn-export { background-color: #007bff; border-color: #007bff; }
.ab-btn-create { background-color: #007bff; border-color: #007bff; }

/* Optional icon spacing */
.ab-btn .dashicons {
  font-size: 16px;
  vertical-align: middle;
}

/* Cancel button (gray variant) */
.cancel-btn {
  background-color: #6c757d;
  color: #fff !important;
  border: 1px solid #6c757d;
}
.cancel-btn:hover {
  background-color: #5a6268;
  border-color: #545b62;
}
.cancel-btn:active {
  background-color: #4e555b;
  transform: scale(0.98);
}

/* ---------- Footer Buttons ---------- */
.ab-footer-buttons {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 10px;
  margin-top: 20px;
}
.ab-footer-buttons .ab-btn {
  min-width: 60px;
  text-align: center;
}

/* ==== Table & Modal CSS ==== */
table { 
    width: 100%; 
    border-collapse: collapse; 
    font-family: Arial, sans-serif; 
    font-size: 14px; 
    background: #fff; 
    table-layout: auto; 
}

.ab-sl-column, 
.ab-actions-column, 
.Status { 
    width: 100px; 
    min-width: 100px; 
    max-width: 100px; 
    text-align: center;
}

.client-name { 
    min-width: 150px; 
    font-size: 14px; 
    font-weight: 600; 
}

.client-name-text { 
    cursor: pointer; 
    color: #0073aa; 
    text-decoration: underline; 
}
.client-name-text:hover { color: #0056b3; }

.email, .phone-number, .notes { 
    min-width: 120px; 
}

thead th { 
    text-align: left; 
    padding: 10px; 
    border-bottom: 2px solid #ddd; 
    font-weight: 600; 
}

tbody td { 
    padding: 10px; 
    border-bottom: 1px solid #eee; 
    vertical-align: middle; 
    max-width: 200px; 
    white-space: nowrap; 
    overflow: hidden; 
    text-overflow: ellipsis; 
}

tbody td:hover::after { 
    content: attr(title); 
    position: absolute; 
    left: 0; 
    top: 100%; 
    background: #333; 
    color: #fff; 
    padding: 6px 10px; 
    border-radius: 4px; 
    white-space: normal; 
    min-width: 200px; 
    max-width: 400px; 
    z-index: 1000; 
    font-size: 13px; 
    box-shadow: 0 1px 3px rgba(0,0,0,0.05); 
}

/* ✅ Action icons */
.ab-action-icons { 
    display: flex; 
    gap: 8px; 
    justify-content: center; 
}
.ab-action-icon, 
.editClientBtn, 
.deleteClientBtn { 
    cursor: pointer; 
    font-size: 16px; 
    transition: transform 0.2s; 
    display: inline-flex; 
    align-items: center; 
    justify-content: center; 
    margin: 0 6px;
}
.ab-action-icon:hover, 
.editClientBtn:hover, 
.deleteClientBtn:hover { 
    transform: scale(1.2); 
}

tbody tr.client-row:hover { background-color: #f5f5f5; }

.ab-sl-column,
.ab-actions-column,
table td:first-child,
table td:last-child {
    text-align: center;
    vertical-align: middle;
}

.ab-sl-column img {
    display: block;
    margin: 0 auto;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    object-fit: cover;
}

/* ==== Modal & Form ==== */
.modal { 
    display: none; 
    position: fixed; 
    z-index: 999; 
    left: 0; 
    top: 0; 
    width: 100%; 
    height: 100%; 
    background: rgba(0,0,0,0.5); 
    justify-content: center; 
    align-items: center; 
}
.modal-content { 
    background: #fff; 
    padding: 25px; 
    border-radius: 8px; 
    width: 400px; 
    max-width: 90%; 
    position: relative; 
}
.close { 
    position: absolute; 
    top: 10px; 
    right: 15px; 
    font-size: 24px; 
    cursor: pointer; 
}
.modal-title { 
    text-align: left; 
    margin-bottom: 15px; 
    font-size: 20px; 
    font-weight: bold; 
}
.form-group { 
    margin-bottom: 15px; 
    display: flex; 
    flex-direction: column; 
}
label { 
    margin-bottom: 5px; 
    font-weight: 600; 
    text-align: left; 
}
input { 
    padding: 10px; 
    border: 1px solid #ccc; 
    border-radius: 4px; 
}
.save-btn { 
    background: #007bff!important; 
    color: #FFF!important; 
    border: none; 
    padding: 10px 15px; 
    font-size: 16px; 
    border-radius: 4px; 
    cursor: pointer; 
    width: 100%; 
    transition: 0.3s; 
}
.save-btn:hover { background: #0056b3; }

/* ==== Responsive Table ==== */
@media screen and (max-width: 768px) {
  table:not(.client-details), 
  table:not(.client-details) thead, 
  table:not(.client-details) tbody, 
  table:not(.client-details) th, 
  table:not(.client-details) tr { 
    display: block; 
    width: 100%; 
  }

  table:not(.client-details) thead { display: none; }

  table:not(.client-details) tr { 
    margin-bottom: 15px; 
    border: 1px solid #ddd; 
    border-radius: 8px; 
    padding: 12px; 
    background: #f9f9ff; 
    box-shadow: 0 1px 3px rgba(0,0,0,0.05); 
  }

  table:not(.client-details) td { 
    display: flex; 
    flex-direction: column; 
    width: 100%; 
    padding: 8px 0; 
    border: none; 
    border-bottom: 1px solid #eee; 
    max-width: none !important; 
    white-space: normal; 
    overflow: visible; 
    text-overflow: unset; 
  }

  table:not(.client-details) td:last-child { border-bottom: none; }

  table:not(.client-details) td::before { 
    content: attr(data-label); 
    font-weight: 600; 
    color: #333; 
    margin-bottom: 4px; 
  }

  table:not(.client-details) .ab-actions-column { 
    flex-direction: row; 
    justify-content: center; 
    align-items: center; 
    padding: 8px 0; 
  }

  table:not(.client-details) .ab-action-icons { gap: 10px; }
  table:not(.client-details) td:hover::after { display: none; }
}

/* ==== Table Border & Header Styling ==== */
table {
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #ddd;
    border-radius: 10px 10px 0 0;
    overflow: hidden;
}

table thead th:first-child { border-top-left-radius: 10px; }
table thead th:last-child { border-top-right-radius: 10px; }

.modal button:last-child { color: #fff!important; }

/* ==== Pagination ==== */
.ab-pagination {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 6px;
    margin-top: 15px;
    padding-right: 10px;
}

.ab-pagination button {
    padding: 4px 8px;
    font-size: 13px;
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
    color: #fff!important;
    border-color: #0052cc;
}

/* Dropdown container alignment */
.ab-controls {
    display: flex;
    justify-content: flex-end; /* ✅ Aligns to right side */
    align-items: center;
    gap: 6px;
    margin-bottom: 10px;
}

/* Dropdown styling */
#abControls #addressBookRows,
#addressBookRows {
    width: 100px;           /* ✅ Fixed width */
    padding: 6px 8px;
    border: 1px solid #ccc;
    border-radius: 6px;
    background-color: #fff;
    font-size: 14px;
    cursor: pointer;
}

/* ===== Address Book Modals ===== */
.ab-modal {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  z-index: 9999;
  align-items: center;
  justify-content: center;
  animation: fadeIn 0.2s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; }
  to   { opacity: 1; }
}
.ab-modal-inner {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  width: 100%;
  max-width: 600px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.15);
  position: relative;
  animation: slideUp 0.25s ease-out;
}
@keyframes slideUp {
  from { transform: translateY(20px); opacity: 0; }
  to   { transform: translateY(0);   opacity: 1; }
}
.ab-modal-close {
  position: absolute;
  top: 14px;
  right: 14px;
  border: none;
  background: transparent;
  font-size: 20px;
  cursor: pointer;
  color: #444;
}
.ab-modal h2 {
  margin-top: 0;
  font-size: 20px;
  font-weight: 600;
  color: #222;
}
.ab-modal p {
  color: #555;
  font-size: 14px;
  margin-bottom: 12px;
}
.ab-section { margin: 14px 0; }
.ab-section label {
  font-weight: 600;
  display: block;
  margin-bottom: 6px;
}
.ab-checkboxes, .ab-radios {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}
.ab-radios label, .ab-checkboxes label {
  font-weight: normal;
  cursor: pointer;
}
#abImportPreview {
  max-height: 220px;
  overflow: auto;
  border: 1px solid #eee;
  padding: 10px;
  background: #fafafa;
  font-size: 13px;
}
#abExportStatus, #abImportStatus {
  margin-top: 10px;
  color: #555;
  font-size: 14px;
  display: none;
}
</style>
