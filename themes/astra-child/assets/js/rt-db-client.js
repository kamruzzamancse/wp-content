document.addEventListener('DOMContentLoaded', function () {

    // ---------------------------
    // AJAX fetch wrapper
    // ---------------------------
    async function ajaxFetch(formData) {
        try {
            const response = await fetch(rtDashboardAjax.ajax_url, {
                method: 'POST',
                body: formData
            });
            return await response.json();
        } catch (error) {
            alert('Network error: ' + error.message);
            return { success: false, data: error.message };
        }
    }

    // ---------------------------
    // Debounce helper
    // ---------------------------
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // ---------------------------
    // Fetch clients / leads
    // ---------------------------
    async function fetchDashboardClients({ page = 1, rows = 10, search = '', bodyId, paginationId, type = 'active' }) {
        const formData = new FormData();
        formData.append('action', 'fetch_dashboard_clients_ajax');
        formData.append('nonce', rtDashboardAjax.edit_nonce);
        formData.append('page', page);
        formData.append('rows', rows);
        formData.append('search', search);
        formData.append('type', type);

        const result = await ajaxFetch(formData);
        const tbody = document.getElementById(bodyId);
        const paginationContainer = document.getElementById(paginationId);
        if (!tbody || !paginationContainer) return;

        tbody.innerHTML = '';
        paginationContainer.innerHTML = '';

        if (result.success && result.data.clients.length > 0) {
            result.data.clients.forEach(client => {
                const tr = document.createElement('tr');
                tr.dataset.clientId = client.client_id;

                if (type === 'active') {
                    tr.innerHTML = `
                        <td>${client.full_name}</td>
                        <td>${client.email}</td>
                        <td>${client.phone || ''}</td>
                        <td>${client.note || ''}</td>
                        <td>
                            <span class="deleteClientBtn" style="cursor:pointer;color:red;">🗑️</span>
                        </td>
                    `;
                } else if (type === 'leads') {
                    tr.innerHTML = `
                        <td>${client.full_name}</td>
                        <td>${client.last_touch || '-'}</td>
                        <td>${client.lead_status || ''}</td>
                        <td>${client.note || ''}</td>
                        <td>
                            <span class="editLeadBtn" style="cursor:pointer;color:#0052cc;">✏️</span>
                            <span class="convertLeadBtn" style="cursor:pointer;color:green;margin-left:5px;">➡️</span>
                            <span class="deleteLeadBtn" style="cursor:pointer;color:red;margin-left:5px;">🗑️</span>
                        </td>
                    `;
                }

                tbody.appendChild(tr);
            });

            // Pagination
            for (let i = 1; i <= result.data.total_pages; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                if (i === page) btn.classList.add('active');
                btn.addEventListener('click', () => fetchDashboardClients({ page: i, rows, search, bodyId, paginationId, type }));
                paginationContainer.appendChild(btn);
            }

            setupRowButtons(bodyId, type);
        } else {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;">No ${type==='active'?'clients':'leads'} found</td></tr>`;
        }
    }

    // ---------------------------
    // Setup Edit / Delete / Convert
    // ---------------------------
    function setupRowButtons(bodyId, type) {
        const tbody = document.getElementById(bodyId);
        if (!tbody) return;

        // Remove previous listeners
        tbody.querySelectorAll('.editLeadBtn, .deleteClientBtn, .deleteLeadBtn, .convertLeadBtn').forEach(btn => btn.replaceWith(btn.cloneNode(true)));

        if (type === 'active') {
            tbody.querySelectorAll('.deleteClientBtn').forEach(btn => {
                btn.addEventListener('click', async function () {
                    const row = this.closest('tr');
                    const clientId = row.dataset.clientId;
                    if (!clientId) return alert('Missing client ID');
                    if (!confirm('Are you sure you want to delete this client?')) return;

                    const formData = new FormData();
                    formData.append('action', 'delete_dashboard_client_ajax');
                    formData.append('nonce', rtDashboardAjax.delete_nonce);
                    formData.append('client_id', clientId);

                    const result = await ajaxFetch(formData);
                    if (result.success) {
                        fetchDashboardClients({ page:1, rows:parseInt(document.getElementById('activeClientsRows').value,10), search:document.getElementById('activeClientsSearch').value.trim(), bodyId:'activeClientsBody', paginationId:'activeClientsPagination', type:'active' });
                    } else {
                        alert('Error: ' + result.data);
                    }
                });
            });
        } else if (type === 'leads') {
            // Edit
            tbody.querySelectorAll('.editLeadBtn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const row = this.closest('tr');
                    const clientId = row.dataset.clientId;
                    if (!clientId) return alert('Missing client ID');

                    // Open modal & populate fields
                    const modal = document.getElementById('rmRealtorClientEditModal');
                    if (!modal) return;

                    modal.style.display = 'flex';

                    const formData = new FormData();
                    formData.append('action', 'fetch_dashboard_client_ajax');
                    formData.append('nonce', rtDashboardAjax.edit_nonce);
                    formData.append('client_id', clientId);

                    ajaxFetch(formData).then(result => {
                        if(result.success){
                            const client = result.data;
                            document.getElementById('edit_realtor_client_id').value = client.client_id;
                            document.getElementById('edit_realtor_client_full_name').value = client.full_name;
                            document.getElementById('edit_realtor_client_email').value = client.email;
                            document.getElementById('edit_realtor_client_phone').value = client.phone;
                            document.getElementById('edit_realtor_client_notes').value = client.note;
                            document.getElementById('edit_realtor_client_status').value = client.status;
                            document.getElementById('editRealtorClientPreviewAvatar').src = client.profile_picture || rtDashboardAjax.default_avatar;

                            // Lead status row display
                            document.getElementById('leadStatusRow').style.display = client.status==='lead'?'block':'none';
                            if(client.status==='lead'){
                                document.getElementById('edit_realtor_lead_status').value = client.lead_status;
                            }
                        } else {
                            alert('Error: ' + result.data);
                            modal.style.display='none';
                        }
                    });
                });
            });

            // Delete
            tbody.querySelectorAll('.deleteLeadBtn').forEach(btn => {
                btn.addEventListener('click', async function () {
                    const row = this.closest('tr');
                    const clientId = row.dataset.clientId;
                    if (!clientId) return alert('Missing client ID');
                    if (!confirm('Are you sure you want to delete this lead?')) return;

                    const formData = new FormData();
                    formData.append('action', 'delete_dashboard_client_ajax');
                    formData.append('nonce', rtDashboardAjax.delete_nonce);
                    formData.append('client_id', clientId);

                    const result = await ajaxFetch(formData);
                    if (result.success) {
                        fetchDashboardClients({ page:1, rows:parseInt(document.getElementById('leadsRows').value,10), search:document.getElementById('leadsSearch').value.trim(), bodyId:'leadsBody', paginationId:'leadsPagination', type:'leads' });
                    } else {
                        alert('Error: ' + result.data);
                    }
                });
            });

            // Convert to client
            tbody.querySelectorAll('.convertLeadBtn').forEach(btn => {
                btn.addEventListener('click', async function () {
                    const row = this.closest('tr');
                    const clientId = row.dataset.clientId;
                    if (!clientId) return alert('Missing client ID');
                    if (!confirm('Convert this lead to active client?')) return;

                    const formData = new FormData();
                    formData.append('action', 'convert_lead_to_client_ajax');
                    formData.append('nonce', rtDashboardAjax.convert_nonce);
                    formData.append('client_id', clientId);

                    const result = await ajaxFetch(formData);
                    if (result.success) {
                        // Refresh both tables
                        fetchDashboardClients({ page:1, rows:parseInt(document.getElementById('leadsRows').value,10), search:document.getElementById('leadsSearch').value.trim(), bodyId:'leadsBody', paginationId:'leadsPagination', type:'leads' });
                        fetchDashboardClients({ page:1, rows:parseInt(document.getElementById('activeClientsRows').value,10), search:document.getElementById('activeClientsSearch').value.trim(), bodyId:'activeClientsBody', paginationId:'activeClientsPagination', type:'active' });
                    } else {
                        alert('Error: ' + result.data);
                    }
                });
            });
        }
    }

    // ---------------------------
    // Setup Search & Rows
    // ---------------------------
    function setupSearchAndRows(searchInputId, rowsSelectId, bodyId, paginationId, type) {
        const searchInput = document.getElementById(searchInputId);
        const rowsSelect = document.getElementById(rowsSelectId);
        if (!searchInput || !rowsSelect) return;

        const fetchTable = debounce(() => {
            fetchDashboardClients({
                page: 1,
                rows: parseInt(rowsSelect.value, 10),
                search: searchInput.value.trim(),
                bodyId,
                paginationId,
                type
            });
        }, 300);

        searchInput.addEventListener('input', fetchTable);
        rowsSelect.addEventListener('change', fetchTable);
    }

    // ---------------------------
    // Initialize tables
    // ---------------------------
    fetchDashboardClients({ page:1, rows:10, search:'', bodyId:'activeClientsBody', paginationId:'activeClientsPagination', type:'active' });
    fetchDashboardClients({ page:1, rows:10, search:'', bodyId:'leadsBody', paginationId:'leadsPagination', type:'leads' });

    setupSearchAndRows('activeClientsSearch', 'activeClientsRows', 'activeClientsBody', 'activeClientsPagination', 'active');
    setupSearchAndRows('leadsSearch', 'leadsRows', 'leadsBody', 'leadsPagination', 'leads');

});
