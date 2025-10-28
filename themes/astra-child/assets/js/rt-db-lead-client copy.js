document.addEventListener('DOMContentLoaded', function () {
    let isProcessing = false;

    // ---------------------------
    // AJAX Fetch Wrapper
    // ---------------------------
    async function ajaxFetch(formData) {
        try {
            const res = await fetch(rtDashboardAjax.ajax_url, { method: 'POST', body: formData });
            return await res.json();
        } catch (e) {
            showNotification('Network error: ' + e.message, 'error');
            return { success: false };
        }
    }

    // ---------------------------
    // Debounce
    // ---------------------------
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // ---------------------------
    // Notification
    // ---------------------------
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

    // ---------------------------
    // Fetch Leads with Pagination
    // ---------------------------
    async function fetchLeads({ page = 1, rows = 10, search = '', bodyId, paginationId }) {
        const fd = new FormData();
        fd.append('action', 'fetch_dashboard_leads_ajax');
        fd.append('nonce', rtDashboardAjax.edit_nonce);
        fd.append('page', page);
        fd.append('rows', rows);
        fd.append('search', search);

        const result = await ajaxFetch(fd);
        const tbody = document.getElementById(bodyId);
        const pagination = document.getElementById(paginationId);
        if (!tbody || !pagination) return;

        tbody.innerHTML = '';
        pagination.innerHTML = '';

        if (result.success && result.data?.leads?.length > 0) {
            result.data.leads.forEach(lead => {
                const tr = document.createElement('tr');
                tr.dataset.clientId = lead.client_id;
                tr.innerHTML = `
                    <td>${lead.full_name || ''}</td>
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

            // Pagination buttons
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

    // ---------------------------
    // Setup Row Buttons (Edit/Delete/Convert)
    // ---------------------------
    function setupRowButtons(bodyId) {
        const tbody = document.getElementById(bodyId);
        if (!tbody) return;

        tbody.querySelectorAll('.editLeadBtn, .deleteLeadBtn, .convertLeadBtn')
            .forEach(btn => btn.replaceWith(btn.cloneNode(true)));

        // Edit Lead
        tbody.querySelectorAll('.editLeadBtn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const row = this.closest('tr');
                const clientId = row.dataset.clientId;
                if (!clientId) return;

                const modal = document.getElementById('rtDbLeadEditModal');
                modal.style.display = 'flex';

                const fd = new FormData();
                fd.append('action', 'fetch_dashboard_lead_ajax');
                fd.append('nonce', rtDashboardAjax.edit_nonce);
                fd.append('client_id', clientId);

                const result = await ajaxFetch(fd);
                if (result.success) {
                    const lead = result.data;
                    document.getElementById('edit_rt_db_lead_id').value = lead.client_id;
                    document.getElementById('edit_rt_db_lead_full_name').value = lead.full_name || '';
                    document.getElementById('edit_rt_db_lead_email').value = lead.email || '';
                    document.getElementById('edit_rt_db_lead_phone').value = lead.phone || '';
                    document.getElementById('edit_rt_db_lead_note').value = lead.note || '';
                    document.getElementById('edit_rt_db_lead_status').value = lead.status || 'lead';
                    document.getElementById('edit_rt_db_lead_lead_status').value = lead.lead_status || 'cold';
                    document.getElementById('editRtDbLeadPreviewAvatar').src = lead.profile_picture || rtDashboardAjax.default_avatar;

                    // Show/hide lead status dropdown
                    document.getElementById('rtLeadStatusRow').style.display = lead.status === 'lead' ? 'block' : 'none';
                } else {
                    showNotification('Error: ' + (result.data || 'Unknown error'), 'error');
                    modal.style.display = 'none';
                }
            });
        });

        // Delete Lead
        tbody.querySelectorAll('.deleteLeadBtn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const row = this.closest('tr');
                const clientId = row.dataset.clientId;
                if (!clientId || !confirm('Are you sure you want to delete this lead?')) return;

                const fd = new FormData();
                fd.append('action', 'delete_dashboard_lead_ajax');
                fd.append('nonce', rtDashboardAjax.delete_nonce);
                fd.append('client_id', clientId);

                const result = await ajaxFetch(fd);
                if (result.success) {
                    showNotification('Lead deleted successfully!');
                    fetchLeads({
                        page: 1,
                        rows: parseInt(document.getElementById('leadsRows').value, 10),
                        search: document.getElementById('leadsSearch').value.trim(),
                        bodyId,
                        paginationId: 'leadsPagination'
                    });
                } else showNotification('Error: ' + (result.data || 'Unknown error'), 'error');
            });
        });

        // Convert Lead
        tbody.querySelectorAll('.convertLeadBtn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const row = this.closest('tr');
                const clientId = row.dataset.clientId;
                if (!clientId || !confirm('Convert this lead to active client?')) return;

                const fd = new FormData();
                fd.append('action', 'convert_lead_to_client_ajax');
                fd.append('nonce', rtDashboardAjax.convert_nonce);
                fd.append('client_id', clientId);

                const result = await ajaxFetch(fd);
                if (result.success) {
                    showNotification('Lead converted to client!');
                    fetchLeads({
                        page: 1,
                        rows: parseInt(document.getElementById('leadsRows').value, 10),
                        search: document.getElementById('leadsSearch').value.trim(),
                        bodyId,
                        paginationId: 'leadsPagination'
                    });
                } else showNotification('Error: ' + (result.data || 'Unknown error'), 'error');
            });
        });
    }

    // ---------------------------
    // Search & Rows Filter
    // ---------------------------
    function setupSearchAndRows(searchInputId, rowsSelectId, bodyId, paginationId) {
        const searchInput = document.getElementById(searchInputId);
        const rowsSelect = document.getElementById(rowsSelectId);
        if (!searchInput || !rowsSelect) return;

        const fetchTable = debounce(() => {
            fetchLeads({
                page: 1,
                rows: parseInt(rowsSelect.value, 10),
                search: searchInput.value.trim(),
                bodyId,
                paginationId
            });
        }, 300);

        searchInput.addEventListener('input', fetchTable);
        rowsSelect.addEventListener('change', fetchTable);
    }

    // ---------------------------
    // Create Lead Form
    // ---------------------------
    const createLeadForm = document.getElementById('rtDbLeadCreateForm');
    if (createLeadForm) {
        createLeadForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (isProcessing) return;
            isProcessing = true;

            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';

            const fd = new FormData(this);
            fd.append('action', 'create_dashboard_lead_ajax');
            fd.append('nonce', rtDashboardAjax.create_nonce);

            const result = await ajaxFetch(fd);
            if (result.success) {
                showNotification('Lead created successfully!');
                this.reset();
                document.getElementById('rtDbLeadCreateModal').style.display = 'none';
                fetchLeads({
                    page: 1,
                    rows: parseInt(document.getElementById('leadsRows').value, 10),
                    search: document.getElementById('leadsSearch').value.trim(),
                    bodyId: 'leadsBody',
                    paginationId: 'leadsPagination'
                });
            } else showNotification('Error: ' + (result.data || 'Unknown error'), 'error');

            submitBtn.disabled = false;
            submitBtn.textContent = 'Add Lead';
            isProcessing = false;
        });
    }

    // ---------------------------
    // Edit Lead Form
    // ---------------------------
    const editLeadForm = document.getElementById('editRtDbLeadForm');
    if (editLeadForm) {
        editLeadForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (isProcessing) return;
            isProcessing = true;

            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';

            // Correct mapping: match PHP expected POST keys
            const fd = new FormData();
            fd.append('action', 'update_dashboard_lead_ajax');
            fd.append('nonce', rtDashboardAjax.edit_nonce);
            fd.append('client_id', document.getElementById('edit_rt_db_lead_id').value);
            fd.append('full_name', document.getElementById('edit_rt_db_lead_full_name').value);
            fd.append('email', document.getElementById('edit_rt_db_lead_email').value);
            fd.append('phone', document.getElementById('edit_rt_db_lead_phone').value);
            fd.append('note', document.getElementById('edit_rt_db_lead_note').value);
            fd.append('status', document.getElementById('edit_rt_db_lead_status').value);
            fd.append('lead_status', document.getElementById('edit_rt_db_lead_lead_status').value);

            const fileInput = document.getElementById('edit_rt_db_lead_profile_picture');
            if (fileInput && fileInput.files.length > 0) {
                fd.append('profile_picture', fileInput.files[0]);
            }

            const result = await ajaxFetch(fd);
            if (result.success) {
                showNotification('Lead updated successfully!');
                document.getElementById('rtDbLeadEditModal').style.display = 'none';
                fetchLeads({
                    page: 1,
                    rows: parseInt(document.getElementById('leadsRows').value, 10),
                    search: document.getElementById('leadsSearch').value.trim(),
                    bodyId: 'leadsBody',
                    paginationId: 'leadsPagination'
                });
            } else showNotification('Error: ' + (result.data || 'Unknown error'), 'error');

            submitBtn.disabled = false;
            submitBtn.textContent = 'Update Lead';
            isProcessing = false;
        });
    }

    // ---------------------------
    // Modal Handling
    // ---------------------------
    const addLeadBtn = document.getElementById('addLeadBtn');
    const leadCreateModal = document.getElementById('rtDbLeadCreateModal');
    const closeLeadCreateBtn = document.getElementById('closeRtDbLeadCreateModal');
    if (addLeadBtn && leadCreateModal) addLeadBtn.addEventListener('click', () => leadCreateModal.style.display = 'flex');
    if (closeLeadCreateBtn) closeLeadCreateBtn.addEventListener('click', () => leadCreateModal.style.display = 'none');
    if (leadCreateModal) leadCreateModal.addEventListener('click', e => { if (e.target === leadCreateModal) leadCreateModal.style.display = 'none'; });

    // ---------------------------
    // Initialize
    // ---------------------------
    fetchLeads({ page: 1, rows: 10, search: '', bodyId: 'leadsBody', paginationId: 'leadsPagination' });
    setupSearchAndRows('leadsSearch', 'leadsRows', 'leadsBody', 'leadsPagination');
});
