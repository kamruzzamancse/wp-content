document.addEventListener('DOMContentLoaded', function () {

    const ajaxUrl = rtActiveClientAjax.ajax_url;
    const paginationNonce = rtActiveClientAjax.pagination_nonce;
    const createClientNonce = rtActiveClientAjax.create_client_nonce;
    const editClientNonce = rtActiveClientAjax.edit_client_nonce;
    const deleteClientNonce = rtActiveClientAjax.delete_client_nonce;
    const defaultAvatar = rtActiveClientAjax.default_avatar;

    // --------------------------
    // AJAX Helper
    // --------------------------
    async function ajaxFetch(formData) {
        try {
            const res = await fetch(ajaxUrl, { method: 'POST', body: formData });
            return await res.json();
        } catch (err) {
            alert("Network error: " + err.message);
            return { success: false };
        }
    }

    // --------------------------
    // FETCH CLIENTS
    // --------------------------
    async function fetchClients({ page = 1, rows = 10, search = '' }) {
        const fd = new FormData();
        fd.append('action', 'fetch_dashboard_active_clients_ajax');
        fd.append('nonce', paginationNonce);
        fd.append('page', page);
        fd.append('rows', rows);
        fd.append('search', search);

        const data = await ajaxFetch(fd);
        const tbody = document.getElementById('activeClientsBody');
        const pagination = document.getElementById('activeClientsPagination');
        if (!tbody || !pagination) return;

        tbody.innerHTML = '';
        pagination.innerHTML = '';

        if (data.success && data.data.clients.length > 0) {
            data.data.clients.forEach(client => {
                const tr = document.createElement('tr');
                tr.dataset.clientId = client.client_id;

                tr.innerHTML = `
                    <td>${client.first_name || ''} ${client.second_name || ''}</td>
                    <td>${client.first_email || ''}</td>
                    <td>${client.first_phone || ''}</td>
                    <td>${client.note || ''}</td>
                    <td>
                        <span class="editClientBtn" style="cursor:pointer;color:#0052cc;">✏️</span>
                        <span class="deleteClientBtn" style="cursor:pointer;color:red;margin-left:5px;">🗑️</span>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Pagination buttons
            for (let i = 1; i <= data.data.total_pages; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                if (i === page) btn.classList.add('active');
                btn.addEventListener('click', () => fetchClients({ page: i, rows, search }));
                pagination.appendChild(btn);
            }

            setupRowButtons();
        } else {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;">No active clients found</td></tr>`;
        }
    }

    // --------------------------
    // EDIT / DELETE BUTTONS
    // --------------------------
    function setupRowButtons() {
        const tbody = document.getElementById('activeClientsBody');
        if (!tbody) return;

        // EDIT
        tbody.querySelectorAll('.editClientBtn').forEach(btn => {
            btn.onclick = async function () {
                const row = this.closest('tr');
                const clientId = row.dataset.clientId;
                const modal = document.getElementById('rtDbClientEditModal');
                if (!modal) return alert('Edit modal not found!');

                const fd = new FormData();
                fd.append('action', 'fetch_dashboard_client_ajax');
                fd.append('nonce', editClientNonce);
                fd.append('client_id', clientId);

                const result = await ajaxFetch(fd);
                if (result.success) {
                    const client = result.data;
                    document.getElementById('edit_rt_db_client_id').value = client.client_id || '';
                    document.getElementById('edit_rt_db_client_first_name').value = client.first_name || '';
                    document.getElementById('edit_rt_db_client_second_name').value = client.second_name || '';
                    document.getElementById('edit_rt_db_client_first_email').value = client.first_email || '';
                    document.getElementById('edit_rt_db_client_second_email').value = client.second_email || '';
                    document.getElementById('edit_rt_db_client_first_phone').value = client.first_phone || '';
                    document.getElementById('edit_rt_db_client_second_phone').value = client.second_phone || '';
                    document.getElementById('edit_rt_db_client_address').value = client.address || '';
                    document.getElementById('edit_rt_db_client_note').value = client.note || '';
                    document.getElementById('editRtDbClientPreviewAvatar').src = client.profile_picture && client.profile_picture.trim() !== '' ? client.profile_picture : defaultAvatar;
                    modal.style.display = 'flex';
                } else {
                    alert('Error fetching client data');
                }
            };
        });

        // DELETE
        tbody.querySelectorAll('.deleteClientBtn').forEach(btn => {
            btn.onclick = async function () {
                const row = this.closest('tr');
                const clientId = row.dataset.clientId;
                if (!confirm('Are you sure you want to delete this client?')) return;

                const fd = new FormData();
                fd.append('action', 'delete_dashboard_client_ajax');
                fd.append('nonce', deleteClientNonce);
                fd.append('client_id', clientId);

                const result = await ajaxFetch(fd);
                if (result.success) {
                    alert('Client deleted successfully!');
                    fetchClients({
                        page: 1,
                        rows: parseInt(document.getElementById('activeClientsRows').value),
                        search: document.getElementById('activeClientsSearch').value
                    });
                } else {
                    alert('Error deleting client: ' + result.data);
                }
            };
        });
    }

    // --------------------------
    // SEARCH & ROWS
    // --------------------------
    function setupSearchAndRows() {
        const searchInput = document.getElementById('activeClientsSearch');
        const rowsSelect = document.getElementById('activeClientsRows');
        if (!searchInput || !rowsSelect) return;

        function debounce(fn, delay) {
            let timer;
            return function (...args) {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), delay);
            };
        }

        const fetchTable = debounce(() => {
            fetchClients({
                page: 1,
                rows: parseInt(rowsSelect.value),
                search: searchInput.value
            });
        }, 300);

        searchInput.addEventListener('input', fetchTable);
        rowsSelect.addEventListener('change', fetchTable);
    }

    // --------------------------
    // CREATE CLIENT MODAL
    // --------------------------
    const createModal = document.getElementById('rtDbClientCreateModal');
    const addActiveBtn = document.getElementById('addActiveBtn');
    const closeCreateBtn = document.getElementById('closeRtDbClientCreateModal');
    const createForm = document.getElementById('createRtDbClientForm');
    const createProfileInput = document.getElementById('create_rt_db_client_profile_picture');
    const createPreviewAvatar = document.getElementById('createRtDbClientPreviewAvatar');
    let createSubmitting = false;

    // OPEN / CLOSE
    if(addActiveBtn) addActiveBtn.addEventListener('click', () => createModal.style.display = 'flex');
    if(closeCreateBtn){
        closeCreateBtn.addEventListener('click', () => createModal.style.display = 'none');
        createModal.addEventListener('click', e => { if(e.target === createModal) createModal.style.display = 'none'; });
        document.addEventListener('keydown', e => { if(e.key === 'Escape') createModal.style.display = 'none'; });
    }

    // Avatar preview
    if(createProfileInput){
        createProfileInput.addEventListener('change', function() {
            const file = this.files[0];
            if(file){
                const reader = new FileReader();
                reader.onload = e => createPreviewAvatar.src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }

    // Submit create form
    if(createForm){
        createForm.addEventListener('submit', async function(e){
            e.preventDefault();
            if(createSubmitting) return; // prevent double submit
            createSubmitting = true;

            const btn = createForm.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Creating...';

            const fd = new FormData(createForm);
            fd.append('action', 'create_dashboard_client_ajax');
            fd.append('nonce', createClientNonce);

            try{
                const result = await ajaxFetch(fd);
                if(result.success){
                    alert('Client created successfully!');
                    createForm.reset();
                    createPreviewAvatar.src = defaultAvatar;
                    createModal.style.display = 'none';

                    fetchClients({
                        page: 1,
                        rows: parseInt(document.getElementById('activeClientsRows').value),
                        search: document.getElementById('activeClientsSearch').value
                    });
                } else {
                    alert('Error creating client: ' + result.data);
                }
            } catch(err){
                alert('Network error: ' + err.message);
            } finally {
                createSubmitting = false;
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });
    }

    // --------------------------
    // EDIT CLIENT MODAL
    // --------------------------
    const editForm = document.getElementById('editRtDbClientForm');
    const editProfileInput = document.getElementById('edit_rt_db_client_profile_picture');
    let editSubmitting = false;

    if(editProfileInput){
        editProfileInput.addEventListener('change', function(){
            const file = this.files[0];
            if(!file) return;
            const reader = new FileReader();
            reader.onload = e => document.getElementById('editRtDbClientPreviewAvatar').src = e.target.result;
            reader.readAsDataURL(file);
        });
    }

    if(editForm){
        editForm.addEventListener('submit', async function(e){
            e.preventDefault();
            if(editSubmitting) return;
            editSubmitting = true;

            const btn = editForm.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Updating...';

            const fd = new FormData(editForm);
            fd.append('action', 'update_dashboard_client_ajax');
            fd.append('nonce', editClientNonce);

            try{
                const result = await ajaxFetch(fd);
                if(result.success){
                    alert('Client updated successfully!');
                    document.getElementById('rtDbClientEditModal').style.display = 'none';
                    fetchClients({
                        page: 1,
                        rows: parseInt(document.getElementById('activeClientsRows').value),
                        search: document.getElementById('activeClientsSearch').value
                    });
                } else {
                    alert('Error updating client: ' + result.data);
                }
            } catch(err){
                alert('Network error: ' + err.message);
            } finally {
                editSubmitting = false;
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });
    }

    // --------------------------
    // INITIAL LOAD
    // --------------------------
    fetchClients({
        page: 1,
        rows: parseInt(document.getElementById('activeClientsRows').value || 10),
        search: document.getElementById('activeClientsSearch').value || ''
    });

    setupSearchAndRows();
});
