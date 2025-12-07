<div class="ab-container">
  <div class="ab-table-header">
    <div class="ab-header-left">
      <h1 class="header-title">Realtors</h1>
    </div>
    <div class="ab-header-right">
      <div class="ab-search-box">
        <span class="pt-search-icon">🔍</span>
        <input type="text" class="pt-search-input" placeholder="Search: Realtor Name" id="realtorSearch">
      </div>
      <div class="ab-action-buttons">
        <button class="ab-btn ab-btn-import" id="openRealtorImportModal">
          <span class="dashicons dashicons-upload"></span> Import
        </button>
        <div class="ab-export-dropdown">
          <button class="ab-btn ab-btn-export" id="openRealtorExportModal">
            <span class="dashicons dashicons-download"></span> Export
          </button>
        </div>
        <button class="ab-btn ab-btn-create">
          <span class="dashicons dashicons-plus-alt"></span> Add Realtor
        </button>
      </div>
    </div>
  </div>

  <div class="ab-controls">
    <label for="realtorRows">Show:</label>
    <select id="realtorRows">
      <option value="5">5 rows</option>
      <option value="10" selected>10 rows</option>
      <option value="25">25 rows</option>
    </select>
  </div>

  <table>
    <thead>
      <tr>
        <th class="ab-sl-column">Profile</th>
        <th class="realtor-name">Realtor Name</th>
        <th class="email">Email</th>
        <th class="phone-number">Phone Number</th>
        <th class="agency-name">Agency</th>
        <th class="license-number">License Number</th>
        <th class="rating">Rating</th>
        <th class="ab-actions-column" style="width:80px">Actions</th>
      </tr>
    </thead>
    <tbody id="realtorBody">
      <tr><td colspan="8" style="text-align:center;">Loading...</td></tr>
    </tbody>
  </table>

  <div id="realtorPagination" class="ab-pagination"></div>
</div>

<?php
// Include modals for Realtors
include locate_template('dashboard-templates/am/am-realtor-create-modal.php');
include locate_template('dashboard-templates/am/am-realtor-edit-modal.php');
include locate_template('dashboard-templates/am/am-realtor-view-modal.php');
?>

<!-- ===== Export Modal ===== -->
<div id="realtorExportModal" class="ab-modal" style="display:none;">
  <div class="ab-modal-inner" role="dialog" aria-modal="true" aria-labelledby="realtorExportTitle">
    <button class="ab-modal-close" id="realtorExportClose">✕</button>
    <h2 id="realtorExportTitle">Export Realtors</h2>
    <p>Select export format and data scope below.</p>

    <div class="ab-section">
      <label>File format</label>
      <div class="ab-radios">
        <label><input type="radio" name="realtor_export_format" value="csv" checked> CSV (.csv)</label>
        <label><input type="radio" name="realtor_export_format" value="xlsx"> Excel (.xlsx)</label>
      </div>
    </div>

    <div class="ab-section">
      <label>Export scope</label>
      <div class="ab-radios">
        <label><input type="radio" name="realtor_export_scope" value="current" checked> Current page only</label>
        <label><input type="radio" name="realtor_export_scope" value="all"> All records</label>
      </div>
    </div>

    <div class="ab-section">
      <label>Columns to include</label>
      <div class="ab-checkboxes">
        <label><input type="checkbox" name="realtor_export_columns" value="full_name" checked> Realtor Name</label>
        <label><input type="checkbox" name="realtor_export_columns" value="email" checked> Email</label>
        <label><input type="checkbox" name="realtor_export_columns" value="phone" checked> Phone</label>
        <label><input type="checkbox" name="realtor_export_columns" value="agency_name" checked> Agency</label>
        <label><input type="checkbox" name="realtor_export_columns" value="license_number" checked> License Number</label>
        <label><input type="checkbox" name="realtor_export_columns" value="rating_avg" checked> Rating</label>
        <label><input type="checkbox" name="realtor_export_columns" value="profile_picture"> Profile Picture URL</label>
        <label><input type="checkbox" name="realtor_export_columns" value="created_at"> Created At</label>
      </div>
    </div>

    <div class="ab-footer-buttons">
      <button class="ab-btn" id="realtorExportCancel">Cancel</button>
      <button class="ab-btn ab-btn-primary" id="realtorExportStart">Export</button>
    </div>

    <div id="realtorExportStatus"></div>
  </div>
</div>

<!-- ===== Import Modal ===== -->
<div id="realtorImportModal" class="ab-modal" style="display:none;">
  <div class="ab-modal-inner" role="dialog" aria-modal="true" aria-labelledby="realtorImportTitle">
    <button class="ab-modal-close" id="realtorImportClose">✕</button>
    <h2 id="realtorImportTitle">Import Realtors</h2>
    <p>You can upload a CSV (.csv) or Excel (.xlsx) file to import realtors. Download a sample template first to check the format.</p>

    <div class="ab-section">
      <a href="#" id="realtorDownloadCsvTemplate" class="ab-btn">Download CSV Template</a>
      <a href="#" id="realtorDownloadXlsxTemplate" class="ab-btn">Download Excel Template</a>
    </div>

    <div class="ab-section">
      <label>Select file to import</label>
      <input type="file" id="realtorImportFileInput" accept=".csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
    </div>

    <div class="ab-section">
      <label>If a record with the same email exists:</label>
      <div class="ab-radios">
        <label><input type="radio" name="realtor_import_duplicate" value="skip" checked> Skip (keep existing)</label>
        <label><input type="radio" name="realtor_import_duplicate" value="update"> Update existing</label>
        <label><input type="radio" name="realtor_import_duplicate" value="create"> Create duplicate</label>
      </div>
    </div>

    <div class="ab-section">
      <label>Preview first rows (optional)</label>
      <div id="realtorImportPreview">No file selected</div>
    </div>

    <div class="ab-footer-buttons">
      <button class="ab-btn" id="realtorImportCancel">Cancel</button>
      <button class="ab-btn ab-btn-primary" id="realtorImportStart" disabled>Import</button>
    </div>

    <div id="realtorImportStatus"></div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

  const exportModal = document.getElementById('realtorExportModal');
  const importModal = document.getElementById('realtorImportModal');
  const exportStatus = document.getElementById('realtorExportStatus');
  const importStatus = document.getElementById('realtorImportStatus');
  const importFileInput = document.getElementById('realtorImportFileInput');
  const importStartBtn = document.getElementById('realtorImportStart');

  const closeModal = (modal) => { modal.style.display = 'none'; };
  const showNotification = (msg, type='success') => { console.log(type.toUpperCase(), msg); };

  const refreshRealtorsTable = async () => {
    if (typeof window.fetchRealtors === 'function') {
      await window.fetchRealtors({
        page: 1,
        rows: parseInt(document.getElementById('realtorRows').value, 10),
        search: document.getElementById('realtorSearch').value.trim(),
        bodyId: 'realtorBody',
        paginationId: 'realtorPagination'
      });
    }
  };

  // Open/Close Modals
  document.getElementById('openRealtorExportModal')?.addEventListener('click', () => exportModal.style.display = 'flex');
  document.getElementById('realtorExportClose')?.addEventListener('click', () => closeModal(exportModal));
  document.getElementById('realtorExportCancel')?.addEventListener('click', () => closeModal(exportModal));
  exportModal?.addEventListener('click', e => { if (e.target === exportModal) closeModal(exportModal); });

  document.getElementById('openRealtorImportModal')?.addEventListener('click', () => importModal.style.display = 'flex');
  document.getElementById('realtorImportClose')?.addEventListener('click', () => closeModal(importModal));
  document.getElementById('realtorImportCancel')?.addEventListener('click', () => closeModal(importModal));
  importModal?.addEventListener('click', e => { if (e.target === importModal) closeModal(importModal); });

  // ESC key to close modals
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') [exportModal, importModal].forEach(m => m && (m.style.display = 'none'));
  });

  // Import File Input
  importFileInput?.addEventListener('change', () => {
    if (importFileInput.files && importFileInput.files.length > 0) {
      importStartBtn.disabled = false;
      importStatus.textContent = `Selected file: ${importFileInput.files[0].name}`;
    } else {
      importStartBtn.disabled = true;
      importStatus.textContent = 'No file selected';
    }
  });

  // Export Realtors
  document.getElementById('realtorExportStart')?.addEventListener('click', async () => {
    const format = document.querySelector('input[name="realtor_export_format"]:checked')?.value || 'csv';
    const scope = document.querySelector('input[name="realtor_export_scope"]:checked')?.value || 'current';
    const columns = Array.from(document.querySelectorAll('input[name="realtor_export_columns"]:checked')).map(el => el.value);

    exportStatus.textContent = 'Exporting...';

    try {
      const formData = new FormData();
      formData.append('action', 'export_realtors_ajax');
      formData.append('nonce', rtRealtorAjax.export_nonce);
      formData.append('format', format);
      formData.append('scope', scope);
      formData.append('columns', JSON.stringify(columns));

      const response = await fetch(rtRealtorAjax.ajax_url, { method: 'POST', body: formData });
      const data = await response.json();
      if (!data.success) throw new Error(data.data?.message || data.data || 'Export failed');

      const realtors = data.data.realtors || [];

      if (format === 'csv') {
        const csvRows = [];
        csvRows.push(columns.join(','));
        realtors.forEach(r => {
          const row = columns.map(col => {
            let val = r[col] ?? '';
            if (typeof val === 'string' && val.includes(',')) val = `"${val.replace(/"/g, '""')}"`;
            return val;
          });
          csvRows.push(row.join(','));
        });
        const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `realtors-export-${new Date().toISOString().slice(0,10)}.csv`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
      } else {
        const worksheetData = realtors.map(r => {
          const obj = {};
          columns.forEach(col => obj[col] = r[col] ?? '');
          return obj;
        });
        const ws = XLSX.utils.json_to_sheet(worksheetData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Realtors');
        XLSX.writeFile(wb, `realtors-export-${new Date().toISOString().slice(0,10)}.xlsx`);
      }

      exportStatus.textContent = 'Export completed!';
      setTimeout(() => closeModal(exportModal), 1000);
      showNotification('Export completed!');

    } catch (error) {
      exportStatus.textContent = 'Export failed: ' + error.message;
      showNotification('Export failed: ' + error.message, 'error');
    }
  });

  // Import Realtors
  importStartBtn?.addEventListener('click', async () => {
      if (!importFileInput.files || importFileInput.files.length === 0) {
          importStatus.textContent = 'Please select a file.';
          return;
      }

      const file = importFileInput.files[0];
      const duplicateHandling = document.querySelector('input[name="realtor_import_duplicate"]:checked')?.value || 'skip';

      importStartBtn.disabled = true;
      importStartBtn.textContent = 'Importing...';
      importStatus.textContent = 'Importing...';

      try {
          const formData = new FormData();
          formData.append('action', 'import_realtors_ajax');
          formData.append('nonce', rtRealtorAjax.import_nonce);
          formData.append('realtors_file', file);
          formData.append('duplicate_handling', duplicateHandling);

          const response = await fetch(rtRealtorAjax.ajax_url, { method: 'POST', body: formData });
          const data = await response.json();

          if (data.success) {
              const message = data.data?.message || 'Import successful!';
              importStatus.textContent = message;
              showNotification(message);

              await refreshRealtorsTable();

              importFileInput.value = '';
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


<script>
document.addEventListener('DOMContentLoaded', () => {

  /* ===========================
     CREATE REALTOR MODAL
  =========================== */
  const createModal = document.getElementById('amRealtorCreateModal');
  const openCreateBtn = document.querySelector('.ab-btn-create');
  const closeCreateBtn = document.getElementById('closeRealtorCreateModal');

  if (openCreateBtn && createModal) {
    openCreateBtn.addEventListener('click', () => {
      createModal.style.display = 'flex';
    });
  }

  if (closeCreateBtn && createModal) {
    closeCreateBtn.addEventListener('click', () => { createModal.style.display = 'none'; });
    createModal.addEventListener('click', e => { if (e.target === createModal) createModal.style.display = 'none'; });
  }

  /* ===========================
     EDIT REALTOR MODAL
  =========================== */
  const editModal = document.getElementById('amRealtorEditModal');
  const closeEditBtn = document.getElementById('closeRealtorEditModal');

  document.addEventListener('click', (e) => {
    const editBtn = e.target.closest('.edit-realtor-btn');
    if (editBtn && editModal) {
      const realtorId = editBtn.dataset.id;

      // Open modal
      editModal.style.display = 'flex';

      // Hook for live data population
      if (typeof window.loadRealtorForEdit === 'function') {
        window.loadRealtorForEdit(realtorId);
      }
    }
  });

  if (closeEditBtn && editModal) {
    closeEditBtn.addEventListener('click', () => { editModal.style.display = 'none'; });
    editModal.addEventListener('click', e => { if (e.target === editModal) editModal.style.display = 'none'; });
  }

  /* ===========================
     VIEW REALTOR MODAL
  =========================== */
  const viewModal = document.getElementById('amRealtorViewModal');
  const closeViewBtn = document.getElementById('closeRealtorViewModal');

  document.addEventListener('click', (e) => {
    const viewBtn = e.target.closest('.details-realtor-btn');
    if (viewBtn && viewModal) {
      const realtorId = viewBtn.dataset.id;

      // Open modal
      viewModal.style.display = 'flex';

      // Hook for live data population
      if (typeof window.loadRealtorDetails === 'function') {
        window.loadRealtorDetails(realtorId);
      }
    }
  });

  if (closeViewBtn && viewModal) {
    closeViewBtn.addEventListener('click', () => { viewModal.style.display = 'none'; });
    viewModal.addEventListener('click', e => { if (e.target === viewModal) viewModal.style.display = 'none'; });
  }

});
</script>

<style>
/* Cleaned and optimized CSS (duplicates removed, no required styles omitted) */

/* Container & Header */
.ab-container { width: 100%; margin: 0 auto; font-family: Arial, sans-serif; }
.ab-table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.ab-header-left { font-size: 24px; font-weight: 600; margin: 0; }
.ab-header-right { display: flex; align-items: center; gap: 10px; }
.ab-search-box { display: flex; align-items: center; border: 1px solid #ccc; border-radius: 6px; padding: 4px 6px; background: #fff; }
.pt-search-icon { margin-right: 6px; }
.pt-search-input { border: none; outline: none; font-size: 14px; width: 180px; }

/* Buttons */
.ab-btn { display: flex; align-items: center; gap: 6px; padding: 8px 14px; font-size: 14px; margin-bottom: 5px; border-radius: 6px; border: 1px solid #007bff; background-color: #007bff; color: #fff; cursor: pointer; transition: all 0.3s ease; }
.ab-btn:hover { background-color: #0056b3; border-color: #0056b3; }
.ab-btn:active { transform: scale(0.98); }
.ab-btn .dashicons { font-size: 16px; vertical-align: middle; }

/* Controls */
.ab-controls { display: flex; justify-content: flex-end; align-items: center; gap: 6px; margin-bottom: 10px; }
#realtorRows { width: 100px; padding: 6px 8px; border: 1px solid #ccc; border-radius: 6px; background-color: #fff; font-size: 14px; cursor: pointer; }

/* Table */
table { width: 100%; border-collapse: separate; border-spacing: 0; border: 1px solid #ddd; border-radius: 10px 10px 0 0; background: #fff; font-family: Arial, sans-serif; font-size: 14px; table-layout: auto; overflow: hidden; }
thead th { padding: 10px; border-bottom: 2px solid #ddd; font-weight: 600; text-align: left; }
table thead th:first-child { border-top-left-radius: 10px; }
table thead th:last-child { border-top-right-radius: 10px; }

tbody td { padding: 10px; border-bottom: 1px solid #eee; vertical-align: middle; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; position: relative; }
tbody td:hover::after { content: attr(title); position: absolute; left: 0; top: 100%; background: #333; color: #fff; padding: 6px 10px; border-radius: 4px; white-space: normal; min-width: 200px; max-width: 400px; z-index: 1000; font-size: 13px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
tbody tr:hover { background-color: #f9f9f9; }
tbody tr:last-child td { border-bottom: none; }

.ab-sl-column, .rating, .ab-actions-column { text-align: center; vertical-align: middle; }
.ab-sl-column img { display: block; margin: 0 auto; border-radius: 50%; width: 40px; height: 40px; object-fit: cover; }
.client-name { min-width: 150px; font-weight: 600; }
.client-name-text { cursor: pointer; color: #0073aa; text-decoration: underline; }
.client-name-text:hover { color: #0056b3; }
.email, .phone-number, .agency-name, .license-number, .rating { min-width: 120px; }
.ab-action-icons { display: flex; gap: 8px; justify-content: center; }
.ab-action-icon, .editClientBtn, .deleteClientBtn { cursor: pointer; font-size: 16px; transition: transform 0.2s; display: inline-flex; align-items: center; justify-content: center; margin: 0 6px; }
.ab-action-icon:hover, .editClientBtn:hover, .deleteClientBtn:hover { transform: scale(1.2); }

/* Pagination */
.ab-pagination { display: flex; justify-content: flex-end; align-items: center; gap: 6px; margin-top: 15px; padding-right: 10px; }
.ab-pagination button { padding: 4px 8px; font-size: 13px; border: 1px solid #ccc; border-radius: 4px; background-color: #f9f9f9; cursor: pointer; transition: all 0.2s ease; }
.ab-pagination button:hover { background-color: #e6e6e6; border-color: #bbb; }
.ab-pagination button.active { background-color: #0052cc; color: #fff; border-color: #0052cc; }

/* Modal */
.ab-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 9999; align-items: center; justify-content: center; animation: fadeIn 0.2s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.ab-modal-inner { background: #fff; border-radius: 12px; padding: 24px; width: 100%; max-width: 600px; box-shadow: 0 8px 30px rgba(0,0,0,0.15); position: relative; animation: slideUp 0.25s ease-out; }
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.ab-modal-close { position: absolute; top: 14px; right: 14px; border: none; background: transparent; font-size: 20px; cursor: pointer; color: #444; }
.ab-modal h2 { margin-top: 0; font-size: 20px; font-weight: 600; color: #222; }
.ab-modal p { color: #555; font-size: 14px; margin-bottom: 12px; }
.ab-section { margin: 14px 0; }
.ab-section label { font-weight: 600; display: block; margin-bottom: 6px; }
.ab-checkboxes, .ab-radios { display: flex; flex-wrap: wrap; gap: 10px; }
.ab-radios label, .ab-checkboxes label { font-weight: normal; cursor: pointer; }
#abImportPreview { max-height: 220px; overflow: auto; border: 1px solid #eee; padding: 10px; background: #fafafa; font-size: 13px; }
#abExportStatus, #realtorImportStatus { margin-top: 10px; color: #555; font-size: 14px; display: none; }

/* Footer Buttons */
.ab-footer-buttons { display: flex; justify-content: flex-end; align-items: center; gap: 10px; margin-top: 20px; padding-top: 15px; border-top: 1px solid #e0e0e0; }
.ab-footer-buttons .ab-btn { width: 80px; height: 36px; display: flex; justify-content: center; align-items: center; font-weight: 500; border-radius: 6px; border: 1px solid #ccc; background: #f5f5f5; color: #333; cursor: pointer; transition: background-color 0.25s ease; text-align: center; }
.ab-footer-buttons .ab-btn:hover { background-color: #e9e9e9; }
.ab-footer-buttons .ab-btn-primary { background-color: #0073aa; color: white; border: none; }
.ab-footer-buttons .ab-btn-primary:hover { background-color: #005f8d; }

/* Responsive */
@media screen and (max-width: 768px) {
  table, thead, tbody, th, tr, td { display: block; width: 100%; }
  thead { display: none; }
  tr { margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; padding: 12px; background: #f9f9ff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
  td { display: flex; flex-direction: column; width: 100%; padding: 8px 0; border: none; border-bottom: 1px solid #eee; white


</style>
