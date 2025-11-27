document.addEventListener('DOMContentLoaded', function() {
    const ajaxUrl = rtActiveClientAjax.ajax_url;
    const paginationNonce = rtActiveClientAjax.pagination_nonce;
    const createClientNonce = rtActiveClientAjax.create_client_nonce;
    const defaultAvatar = rtActiveClientAjax.default_avatar;

    // Fetch and render Active Clients
    async function fetchClients({ page = 1, rows = 10, search = '' }) {
        const fd = new FormData();
        fd.append('action', 'fetch_dashboard_active_clients_ajax');
        fd.append('nonce', paginationNonce);
        fd.append('page', page);
        fd.append('rows', rows);
        fd.append('search', search);

        try {
            const res = await fetch(ajaxUrl, { method: 'POST', body: fd });
            const data = await res.json();

            const tbody = document.getElementById('activeClientsBody');
            const pagination = document.getElementById('activeClientsPagination');
            if (!tbody || !pagination) return;

            tbody.innerHTML = '';
            pagination.innerHTML = '';

            if (data.success && data.data.clients.length > 0) {
                data.data.clients.forEach(client => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${client.full_name || ''}</td>
                        <td>${client.email || ''}</td>
                        <td>${client.phone || ''}</td>
                        <td>${client.note || ''}</td>
                    `;
                    tbody.appendChild(tr);
                });

                for (let i = 1; i <= data.data.total_pages; i++) {
                    const btn = document.createElement('button');
                    btn.textContent = i;
                    if (i === page) btn.classList.add('active');
                    btn.addEventListener('click', () => fetchClients({ page: i, rows, search }));
                    pagination.appendChild(btn);
                }
            } else {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">No active clients found</td></tr>`;
            }
        } catch (err) {
            console.error('Fetch clients error', err);
        }
    }

    // Search & rows setup
    function setupSearchAndRows() {
        const searchInput = document.getElementById('activeClientsSearch');
        const rowsSelect = document.getElementById('activeClientsRows');
        if (!searchInput || !rowsSelect) return;

        const debounce = (fn, delay) => {
            let timer;
            return function(...args) {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), delay);
            };
        };

        const fetchTable = debounce(() => fetchClients({
            page: 1,
            rows: parseInt(rowsSelect.value),
            search: searchInput.value
        }), 300);

        searchInput.addEventListener('input', fetchTable);
        rowsSelect.addEventListener('change', fetchTable);
    }

    // Modal & Create Client
    const createModal = document.getElementById('rtDbClientCreateModal');
    const addActiveBtn = document.getElementById('addActiveBtn');
    const closeBtn = document.getElementById('closeRtDbClientCreateModal');
    const form = document.getElementById('createRtDbClientForm');
    const profileInput = document.getElementById('create_rt_db_client_profile_picture');
    const previewAvatar = document.getElementById('createRtDbClientPreviewAvatar');

    if(addActiveBtn && createModal){
        addActiveBtn.addEventListener('click', () => createModal.style.display='flex');
    }

    if(closeBtn && createModal){
        closeBtn.addEventListener('click', () => createModal.style.display='none');
        createModal.addEventListener('click', e => { if(e.target === createModal) createModal.style.display='none'; });
    }

    if(profileInput){
        profileInput.addEventListener('change', function() {
            const file = this.files[0];
            if(file){
                const reader = new FileReader();
                reader.onload = e => previewAvatar.src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }

    // AJAX submit for creating client
    if(form){
        form.addEventListener('submit', async function(e){
            e.preventDefault();
            const formData = new FormData(form);
            
            formData.append('action', 'create_dashboard_client_ajax');  
            formData.append('nonce', createClientNonce);

            try {
                const response = await fetch(ajaxUrl, { method:'POST', body: formData });
                const result = await response.json();

                if(result.success){
                    alert('Client created successfully!');
                    form.reset();
                    previewAvatar.src = defaultAvatar;
                    createModal.style.display = 'none';

                    fetchClients({
                        page:1,
                        rows:parseInt(document.getElementById('activeClientsRows')?.value || 10),
                        search:document.getElementById('activeClientsSearch')?.value || ''
                    });
                } else {
                    alert('Error: ' + (result.data || 'Unknown error'));
                }
            } catch(err){
                alert('Network error: ' + err.message);
            }
        });
    }

    // Initial fetch
    fetchClients({
        page: 1,
        rows: parseInt(document.getElementById('activeClientsRows')?.value || 10),
        search: document.getElementById('activeClientsSearch')?.value || ''
    });

    setupSearchAndRows();
});
