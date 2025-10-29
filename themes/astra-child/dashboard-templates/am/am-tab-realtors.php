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
        <th class="ab-actions-column">Actions</th>
      </tr>
    </thead>
    <tbody id="realtorBody">
      <tr><td colspan="7" style="text-align:center;">Loading...</td></tr>
    </tbody>
  </table>

  <div id="realtorPagination" class="ab-pagination"></div>
</div>

<?php
// Include modals for Realtors
include locate_template('dashboard-templates/rt/rt-ab-realtor-create-modal.php');
include locate_template('dashboard-templates/rt/rt-ab-realtor-edit-modal.php');
include locate_template('dashboard-templates/rt/rt-ab-realtor-details-modal.php');
?>

<!-- ===== Export Modal ===== -->
<div id="realtorExportModal" class="ab-modal">
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
        <label><input type="checkbox" name="realtor_export_columns" value="status" checked> Status</label>
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
<div id="realtorImportModal" class="ab-modal">
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
