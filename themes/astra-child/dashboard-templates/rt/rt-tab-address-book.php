<div class="ab-container">
  <div class="ab-table-header">
    <div class="ab-header-left">
      <h2 class="header-title">📖 Address Book</h2>
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

  <div class="ab-controls" style="display:flex; gap:15px; align-items:center; flex-wrap:wrap;">
    <!-- ROWS DROPDOWN -->
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
            <th>Profile</th>
            <th class="sortable" data-sort="first_name">1st Name</th>
            <th class="sortable" data-sort="second_name">2nd Name</th>
            <th class="sortable" data-sort="first_email">1st Email</th>
            <th class="sortable" data-sort="second_email">2nd Email</th>
            <th class="sortable" data-sort="first_phone">1st Phone</th>
            <th class="sortable" data-sort="second_phone">2nd Phone</th>
            <th class="sortable" data-sort="address">Address</th>
            <th class="sortable" data-sort="note">Notes</th>
            <th class="sortable" data-sort="status">Status</th>
            <th style="width: 100px; text-align: center">Actions</th>
        </tr>
    </thead>
    <tbody id="addressBookBody">
        <tr><td colspan="11" style="text-align:center;">Loading...</td></tr>
    </tbody>
</table>

  <div id="addressBookPagination" class="ab-pagination"></div>
</div>

<?php
include locate_template('dashboard-templates/rt/rt-ab-client-create-modal.php');
include locate_template('dashboard-templates/rt/rt-ab-client-edit-modal.php');
include locate_template('dashboard-templates/rt/rt-ab-client-details-modal.php');
?>

<!-- Export Modal -->
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
        <label><input type="checkbox" name="ab_export_columns" value="profile_picture"> Profile</label>
        <label><input type="checkbox" name="ab_export_columns" value="first_name" checked> 1st Name</label>
        <label><input type="checkbox" name="ab_export_columns" value="second_name"> 2nd Name</label>
        <label><input type="checkbox" name="ab_export_columns" value="first_email" checked> 1st Email</label>
        <label><input type="checkbox" name="ab_export_columns" value="second_email"> 2nd Email</label>
        <label><input type="checkbox" name="ab_export_columns" value="first_phone" checked> 1st Phone</label>
        <label><input type="checkbox" name="ab_export_columns" value="second_phone"> 2nd Phone</label>
        <label><input type="checkbox" name="ab_export_columns" value="address" checked> Address</label>
        <label><input type="checkbox" name="ab_export_columns" value="note" checked> Notes</label>
        <label><input type="checkbox" name="ab_export_columns" value="status" checked> Status</label>
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

<!-- Import Modal -->
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

  const rowsDropdown = document.getElementById('addressBookRows');
  const searchInput = document.getElementById('addressBookSearch');

  const closeModal = modal => modal.style.display='none';
  const showNotification = (msg,type='success')=>{console.log(type.toUpperCase(),msg);};

  // Refresh Clients Table WITHOUT SORTING (sorting removed)
  const refreshClientsTable = async () => {
    if(typeof window.fetchClients==='function'){
      const clients = await window.fetchClients({
        page:1,
        rows:parseInt(rowsDropdown.value,10),
        search:searchInput.value.trim(),
        sort:null,   // removed sorting
        bodyId:'addressBookBody',
        paginationId:'addressBookPagination'
      });

      if(clients && Array.isArray(clients)){
        const tbody = document.getElementById('addressBookBody');
        tbody.innerHTML='';
        clients.forEach(client=>{
          const tr=document.createElement('tr');
          tr.innerHTML=`
            <td>${client.profile_picture||''}</td>
            <td>${client.first_name||''}</td>
            <td>${client.second_name||''}</td>
            <td>${client.first_email||''}</td>
            <td>${client.second_email||''}</td>
            <td>${client.first_phone||''}</td>
            <td>${client.second_phone||''}</td>
            <td>${client.address||''}</td>
            <td>${client.note||''}</td>
            <td>${client.status||''}</td>
            <td>Actions</td>
          `;
          tbody.appendChild(tr);
        });
      }
    }
  };

  // Modal open/close
  document.getElementById('openAbExportModal')?.addEventListener('click',()=>exportModal.style.display='flex');
  document.getElementById('abExportClose')?.addEventListener('click',()=>closeModal(exportModal));
  document.getElementById('abExportCancel')?.addEventListener('click',()=>closeModal(exportModal));
  exportModal?.addEventListener('click',e=>{if(e.target===exportModal) closeModal(exportModal);});

  document.getElementById('openAbImportModal')?.addEventListener('click',()=>importModal.style.display='flex');
  document.getElementById('abImportClose')?.addEventListener('click',()=>closeModal(importModal));
  document.getElementById('abImportCancel')?.addEventListener('click',()=>closeModal(importModal));
  importModal?.addEventListener('click',e=>{if(e.target===importModal) closeModal(importModal);});

  // Event listeners (SORT REMOVED)
  rowsDropdown?.addEventListener('change',refreshClientsTable);
  searchInput?.addEventListener('input',()=>setTimeout(refreshClientsTable,300));

  // Import file preview
  importFileInput?.addEventListener('change',async()=>{
    importPreview.innerHTML='Loading preview...';
    importStatus.textContent='';
    importStartBtn.disabled=true;
    if(!importFileInput.files?.length){importPreview.textContent='No file selected'; return;}
    let file=importFileInput.files[0];
    const ext=file.name.split('.').pop().toLowerCase();
    try{
      let rows=[];
      if(ext==='csv'){
        const text=await file.text();
        rows=text.split('\n').slice(0,5).map(r=>r.split(','));
      }else if(ext==='xlsx'){
        const data=await file.arrayBuffer();
        const workbook=XLSX.read(data,{type:'array'});
        const sheet=XLSX.utils.sheet_to_json(workbook.Sheets[workbook.SheetNames[0]],{header:1}).slice(0,5);
        rows=sheet;
      }else{importPreview.textContent='Unsupported file format.'; return;}
      importPreview.innerHTML=`<table class="ab-preview-table">${rows.map(r=>`<tr>${r.map(c=>`<td>${c??''}</td>`).join('')}</tr>`).join('')}</table>`;
      importStatus.textContent=`Selected file: ${file.name}`;
      importStartBtn.disabled=false;
    }catch(err){importPreview.textContent='Failed to preview file: '+err.message;}
  });

  // Export clients
  document.getElementById('abExportStart')?.addEventListener('click',async()=>{
    const format=document.querySelector('input[name="ab_export_format"]:checked')?.value||'csv';
    const scope=document.querySelector('input[name="ab_export_scope"]:checked')?.value||'current';
    const columns=Array.from(document.querySelectorAll('input[name="ab_export_columns"]:checked')).map(el=>el.value);
    exportStatus.textContent='Exporting...';
    try{
      const formData=new FormData();
      formData.append('action','export_clients_ajax');
      formData.append('nonce',rtClientAjax.export_nonce);
      formData.append('format',format);
      formData.append('scope',scope);
      formData.append('columns',JSON.stringify(columns));
      const response=await fetch(rtClientAjax.ajax_url,{method:'POST',body:formData});
      if(!response.ok) throw new Error('Network response not ok');
      const data=await response.json();
      if(!data.success) throw new Error(data.data?.message||data.data||'Export failed');
      const clients=data.data.clients||[];
      if(format==='csv'){
        const csvRows=[];
        csvRows.push(columns.join(','));
        clients.forEach(client=>{
          const row=columns.map(col=>{
            let val=client[col]??'';
            if(typeof val==='string'&&val.includes(',')) val=`"${val.replace(/"/g,'""')}"`;
            return val;
          });
          csvRows.push(row.join(','));
        });
        const blob=new Blob([csvRows.join('\n')],{type:'text/csv;charset=utf-8;'});
        const url=URL.createObjectURL(blob);
        const a=document.createElement('a'); a.href=url;
        a.download=`clients-export-${new Date().toISOString().slice(0,10)}.csv`;
        document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
      }else{
        const worksheetData=clients.map(client=>{const obj={};columns.forEach(col=>obj[col]=client[col]??'');return obj;});
        const ws=XLSX.utils.json_to_sheet(worksheetData);
        const wb=XLSX.utils.book_new(); XLSX.utils.book_append_sheet(wb,ws,'Clients'); XLSX.writeFile(wb,`clients-export-${new Date().toISOString().slice(0,10)}.xlsx`);
      }
      exportStatus.textContent='Export completed!';
      setTimeout(()=>closeModal(exportModal),1000);
      showNotification('Export completed!');
    }catch(err){exportStatus.textContent='Export failed: '+err.message; showNotification('Export failed: '+err.message,'error');}
  });

  // Import clients
  importStartBtn?.addEventListener('click',async()=>{
    if(!importFileInput.files?.length){importStatus.textContent='Please select a file.'; return;}
    let file=importFileInput.files[0];
    const duplicateHandling=document.querySelector('input[name="ab_import_duplicate"]:checked')?.value||'skip';
    importStartBtn.disabled=true; importStartBtn.textContent='Importing...'; importStatus.textContent='Importing...';
    try{
      if(file.name.split('.').pop().toLowerCase()==='xlsx'){
        importStatus.textContent='Converting Excel to CSV...';
        const data=await file.arrayBuffer();
        const workbook=XLSX.read(data,{type:'array'});
        const csv=XLSX.utils.sheet_to_csv(workbook.Sheets[workbook.SheetNames[0]]);
        file=new File([csv],file.name.replace('.xlsx','.csv'),{type:'text/csv'});
      }
      const formData=new FormData();
      formData.append('action','import_clients_ajax');
      formData.append('nonce',rtClientAjax.import_nonce);
      formData.append('clients_file',file);
      formData.append('duplicate_handling',duplicateHandling);
      const response=await fetch(rtClientAjax.ajax_url,{method:'POST',body:formData});
      if(!response.ok) throw new Error('Network response not ok: '+response.status);
      const data=await response.json();
      if(data.success){
        const msg=data.data?.message||'Import successful!';
        importStatus.textContent=msg; showNotification(msg);
        await refreshClientsTable();
        importFileInput.value=''; importPreview.innerHTML='No file selected';
        setTimeout(()=>closeModal(importModal),1500);
      }else{
        const errMsg=data.data?.message||data.data||'Unknown error occurred';
        importStatus.textContent='Import failed: '+errMsg; showNotification('Import failed: '+errMsg,'error');
      }
    }catch(err){importStatus.textContent='Import failed: '+err.message; showNotification('Import failed: '+err.message,'error');}
    finally{importStartBtn.disabled=false; importStartBtn.textContent='Import';}
  });

  // Initial load
  refreshClientsTable();
});
</script>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const rowsDropdown = document.getElementById('addressBookRows');
    const searchInput = document.getElementById('addressBookSearch');

    // Track current sort state
    let currentSortBy = 'first_name';
    let currentSortOrder = 'ASC';

    const refreshClientsTable = async (page = 1) => {
        if (typeof window.fetchClients === 'function') {
            await window.fetchClients({
                page,
                rows: parseInt(rowsDropdown.value, 10),
                search: searchInput.value.trim(),
                sort_by: currentSortBy,
                sort_order: currentSortOrder,
                bodyId: 'addressBookBody',
                paginationId: 'addressBookPagination'
            });
        }
    };

    // Header sorting
    document.querySelectorAll('th.sortable').forEach(th => {
        th.addEventListener('click', () => {
            const sortBy = th.dataset.sort;
            if (!sortBy) return;

            // Toggle order if same column, otherwise default to ASC
            currentSortOrder = (currentSortBy === sortBy && currentSortOrder === 'ASC') ? 'DESC' : 'ASC';
            currentSortBy = sortBy;

            refreshClientsTable(1); // Go back to page 1 when sorting
        });
    });

    // Search & rows change
    const debouncedRefresh = () => setTimeout(refreshClientsTable, 300);
    searchInput?.addEventListener('input', debouncedRefresh);
    rowsDropdown?.addEventListener('change', () => refreshClientsTable(1));

    // Initial load
    refreshClientsTable();
});

</script>


