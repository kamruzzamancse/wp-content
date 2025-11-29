document.addEventListener('DOMContentLoaded', function () {
    let isProcessing = false; // Prevent multiple submissions

    // --- AJAX helper ---
    async function ajaxFetch(formData) {
        try {
            const res = await fetch(rtDashboardAjax.ajax_url, { method: 'POST', body: formData });
            return await res.json();
        } catch (e) {
            showNotification('Network error: ' + e.message, 'error');
            return { success: false };
        }
    }

    // --- Debounce ---
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // --- Notifications ---
    function showNotification(msg, type = 'success') {
        document.querySelectorAll('.dashboard-notification').forEach(n => n.remove());
        const n = document.createElement('div');
        n.className = 'dashboard-notification ' + type;
        n.style.cssText = `
            position: fixed; top: 20px; right: 20px; padding: 15px 20px;
            background: ${type === 'success' ? '#4CAF50' : '#f44336'};
            color:white; border-radius:4px; z-index:10000; font-weight:500;
            box-shadow:0 4px 12px rgba(0,0,0,0.15);
        `;
        n.textContent = msg;
        document.body.appendChild(n);
        setTimeout(() => n.remove(), 3000);
    }

    // --- Wait for element ---
    function waitForElement(selector, callback) {
        const el = document.querySelector(selector);
        if (el) return callback(el);
        const observer = new MutationObserver(() => {
            const el2 = document.querySelector(selector);
            if (el2) {
                observer.disconnect();
                callback(el2);
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

    // --- Fetch Leads Table ---
    async function fetchLeads({ page = 1, rows = 10, search = '', bodyId, paginationId }) {
        const fd = new FormData();
        fd.append('action', 'fetch_dashboard_leads_ajax');
        fd.append('nonce', rtDashboardAjax.edit_nonce);
        fd.append('page', page);
        fd.append('rows', rows);
        fd.append('search', search);

        const tbody = document.getElementById(bodyId);
        const pagination = document.getElementById(paginationId);
        if (!tbody || !pagination) return;

        Array.from(pagination.children).forEach(btn => btn.disabled = true);

        const result = await ajaxFetch(fd);
        tbody.innerHTML = '';
        pagination.innerHTML = '';

        if (result.success && result.data?.leads?.length > 0) {
            result.data.leads.forEach(lead => {
                const tr = document.createElement('tr');
                tr.dataset.clientId = lead.client_id;
                tr.innerHTML = `
                    <td>${lead.first_name || ''} ${lead.second_name || ''}</td>
                    <td>${lead.last_touch || '-'}</td>
                    <td>${lead.lead_status || ''}</td>
                    <td>${lead.note || ''}</td>
                    <td>
                        <span class="editLeadBtn" style="cursor:pointer;color:#0052cc;">✏️</span>
                        <span class="convertLeadBtn" style="cursor:pointer;color:green;margin-left:5px;">➡️</span>
                        <span class="deleteLeadBtn" style="cursor:pointer;color:red;margin-left:5px;">🗑️</span>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            for (let i = 1; i <= result.data.total_pages; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                if (i === page) btn.classList.add('active');
                btn.addEventListener('click', () => fetchLeads({ page: i, rows, search, bodyId, paginationId }));
                pagination.appendChild(btn);
            }

            setupRowButtons(bodyId);
        } else {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;">No leads found</td></tr>`;
        }
    }

    // --- Setup Row Buttons ---
    function setupRowButtons(bodyId) {
        const tbody = document.getElementById(bodyId);
        if (!tbody) return;

        tbody.querySelectorAll('.editLeadBtn, .deleteLeadBtn, .convertLeadBtn')
            .forEach(btn => btn.replaceWith(btn.cloneNode(true))); // Remove previous listeners

        // Edit Lead
        tbody.querySelectorAll('.editLeadBtn').forEach(btn => {
            btn.addEventListener('click', async function () {
                if (isProcessing) return;
                isProcessing = true;

                const row = this.closest('tr');
                const clientId = row.dataset.clientId;
                if (!clientId) { isProcessing = false; return; }

                const modal = document.getElementById('rtDbLeadEditModal');
                modal.style.display = 'flex';

                // Loading placeholders
                ['first_name','second_name','first_email','second_email','first_phone','second_phone','address','note','lead_status']
                    .forEach(id => document.getElementById(`edit_rt_db_lead_${id}`).value='Loading...');
                document.getElementById('editRtDbLeadPreviewAvatar').src = rtDashboardAjax.default_avatar;

                const fd = new FormData();
                fd.append('action','fetch_dashboard_lead_ajax');
                fd.append('nonce', rtDashboardAjax.edit_nonce);
                fd.append('client_id', clientId);

                try {
                    const res = await fetch(rtDashboardAjax.ajax_url,{method:'POST',body:fd});
                    const result = await res.json();

                    if(result.success && result.data){
                        const lead = result.data;
                        document.getElementById('edit_rt_db_lead_id').value = lead.client_id || '';
                        document.getElementById('edit_rt_db_lead_first_name').value = lead.first_name || '';
                        document.getElementById('edit_rt_db_lead_second_name').value = lead.second_name || '';
                        document.getElementById('edit_rt_db_lead_first_email').value = lead.first_email || '';
                        document.getElementById('edit_rt_db_lead_second_email').value = lead.second_email || '';
                        document.getElementById('edit_rt_db_lead_first_phone').value = lead.first_phone || '';
                        document.getElementById('edit_rt_db_lead_second_phone').value = lead.second_phone || '';
                        document.getElementById('edit_rt_db_lead_address').value = lead.address || '';
                        document.getElementById('edit_rt_db_lead_note').value = lead.note || '';
                        document.getElementById('edit_rt_db_lead_lead_status').value = lead.lead_status || 'cold';
                        document.getElementById('editRtDbLeadPreviewAvatar').src = lead.profile_picture || rtDashboardAjax.default_avatar;
                    } else {
                        showNotification('Error: ' + (result.data || 'Lead not found'), 'error');
                        modal.style.display='none';
                    }

                } catch(e){
                    showNotification('Network error', 'error');
                    modal.style.display='none';
                }
                isProcessing=false;
            });
        });

        // Delete Lead
        tbody.querySelectorAll('.deleteLeadBtn').forEach(btn=>{
            btn.addEventListener('click', async function(){
                const row = this.closest('tr');
                const clientId = row.dataset.clientId;
                if(!clientId || !confirm('Are you sure you want to delete this lead?')) return;

                const fd = new FormData();
                fd.append('action','delete_dashboard_lead_ajax');
                fd.append('nonce', rtDashboardAjax.delete_nonce);
                fd.append('client_id', clientId);

                const result = await ajaxFetch(fd);
                if(result.success){
                    showNotification('Lead deleted successfully!');
                    fetchLeads({
                        page:1,
                        rows:parseInt(document.getElementById('leadsRows').value,10),
                        search:document.getElementById('leadsSearch').value.trim(),
                        bodyId,
                        paginationId:'leadsPagination'
                    });
                } else showNotification('Error: '+(result.data||'Unknown error'),'error');
            });
        });

        // Convert Lead
        tbody.querySelectorAll('.convertLeadBtn').forEach(btn=>{
            btn.addEventListener('click', async function(){
                const row = this.closest('tr');
                const clientId = row.dataset.clientId;
                if(!clientId || !confirm('Convert this lead to active client?')) return;

                const fd = new FormData();
                fd.append('action','convert_lead_to_client_ajax');
                fd.append('nonce', rtDashboardAjax.convert_nonce);
                fd.append('client_id', clientId);

                const result = await ajaxFetch(fd);
                if(result.success){
                    showNotification('Lead converted to client!');
                    setTimeout(()=>location.reload(),500);
                } else showNotification('Error: '+(result.data||'Unknown error'),'error');
            });
        });
    }

    // --- Create Lead Form ---
    waitForElement('#createRtDbLeadForm', function(form){
        const modal = document.getElementById('rtDbLeadCreateModal');
        const profileInput = document.getElementById('create_rt_db_lead_profile_picture');
        const previewAvatar = document.getElementById('createRtDbLeadPreviewAvatar');

        // Profile preview
        if(profileInput){
            profileInput.addEventListener('change', function(){
                const file = this.files[0];
                if(file){
                    const reader = new FileReader();
                    reader.onload = e => previewAvatar.src = e.target.result;
                    reader.readAsDataURL(file);
                }
            });
        }

        form.addEventListener('submit', async function(e){
            e.preventDefault();
            if(isProcessing) return;
            isProcessing = true;

            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';

            const fd = new FormData(form);
            fd.append('action','create_dashboard_lead_ajax');
            fd.append('nonce', rtDashboardAjax.create_nonce);

            const result = await ajaxFetch(fd);
            if(result.success){
                showNotification('Lead created successfully!');
                form.reset();
                if(modal) modal.style.display='none';
                document.getElementById('createRtDbLeadPreviewAvatar').src = rtDashboardAjax.default_avatar;

                fetchLeads({
                    page:1,
                    rows:parseInt(document.getElementById('leadsRows').value,10),
                    search:document.getElementById('leadsSearch').value.trim(),
                    bodyId:'leadsBody',
                    paginationId:'leadsPagination'
                });
            } else showNotification('Error: '+(result.data||'Unknown error'),'error');

            submitBtn.disabled = false;
            submitBtn.textContent = 'Add Lead';
            isProcessing=false;
        });
    });

    // --- Edit Lead Form ---
    const editForm = document.getElementById('editRtDbLeadForm');
    if(editForm){
        editForm.addEventListener('submit', async function(e){
            e.preventDefault();
            if(isProcessing) return;
            isProcessing=true;

            const submitBtn = editForm.querySelector('button[type="submit"]');
            submitBtn.disabled=true;
            submitBtn.textContent='Updating...';

            const fd = new FormData(editForm);
            fd.append('action','update_dashboard_lead_ajax');
            fd.append('nonce', rtDashboardAjax.edit_nonce);

            const result = await ajaxFetch(fd);
            if(result.success){
                showNotification('Lead updated successfully!');
                const modal = document.getElementById('rtDbLeadEditModal');
                if(modal) modal.style.display='none';
                fetchLeads({
                    page:1,
                    rows:parseInt(document.getElementById('leadsRows').value,10),
                    search:document.getElementById('leadsSearch').value.trim(),
                    bodyId:'leadsBody',
                    paginationId:'leadsPagination'
                });
            } else showNotification('Error: '+(result.data||'Unknown error'),'error');

            submitBtn.disabled=false;
            submitBtn.textContent='Update Lead';
            isProcessing=false;
        });
    }

    // --- Search & Rows ---
    function setupSearchAndRows(searchInputId, rowsSelectId, bodyId, paginationId){
        const searchInput = document.getElementById(searchInputId);
        const rowsSelect = document.getElementById(rowsSelectId);
        if(!searchInput || !rowsSelect) return;

        const fetchTable = debounce(()=>{
            fetchLeads({
                page:1,
                rows:parseInt(rowsSelect.value,10),
                search:searchInput.value.trim(),
                bodyId,
                paginationId
            });
        },300);

        searchInput.addEventListener('input', fetchTable);
        rowsSelect.addEventListener('change', fetchTable);
    }

    setupSearchAndRows('leadsSearch','leadsRows','leadsBody','leadsPagination');

    // --- Create Modal Open/Close ---
    const addLeadBtn = document.getElementById('addLeadBtn');
    const createModal = document.getElementById('rtDbLeadCreateModal');
    const closeCreateBtn = document.getElementById('closeRtDbLeadCreateModal');
    if(addLeadBtn && createModal) addLeadBtn.addEventListener('click', ()=>createModal.style.display='flex');
    if(closeCreateBtn) closeCreateBtn.addEventListener('click', ()=>createModal.style.display='none');
    if(createModal) createModal.addEventListener('click', e=>{if(e.target===createModal) createModal.style.display='none';});

    // --- Initial Fetch ---
    fetchLeads({page:1,rows:10,search:'',bodyId:'leadsBody',paginationId:'leadsPagination'});
});
