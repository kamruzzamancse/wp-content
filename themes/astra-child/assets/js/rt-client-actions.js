jQuery(document).ready(function($){

    // Container for delegation
    const tableContainer = document.querySelector('.dashboard-top-left');

    tableContainer.addEventListener('click', function(e){
        const target = e.target;
        const row = target.closest('tr');
        if(!row) return;
        const clientId = row.dataset.clientId;
        if(!clientId) return;

        // ======================
        // EDIT CLIENT / LEAD
        // ======================
        if(target.classList.contains('edit-client-btn') || target.classList.contains('edit-lead-btn')){
            const editModal = document.getElementById('rmRealtorClientEditModal');
            if(!editModal) return;
            editModal.style.display = 'flex';

            fetch(clientActionData.ajax_url, {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'fetch_realtor_client_ajax',
                    nonce: clientActionData.edit_nonce,
                    client_id: clientId
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    const client = data.data;
                    document.getElementById('edit_realtor_client_id').value = client.client_id;
                    document.getElementById('edit_realtor_client_full_name').value = client.full_name;
                    document.getElementById('edit_realtor_client_email').value = client.email;
                    document.getElementById('edit_realtor_client_phone').value = client.phone;
                    document.getElementById('edit_realtor_client_notes').value = client.note;
                    document.getElementById('edit_realtor_client_status').value = client.status;
                    document.getElementById('editRealtorClientPreviewAvatar').src = client.profile_picture || clientActionData.default_avatar;

                    // Show lead status if lead
                    const leadStatusRow = document.getElementById('leadStatusRow');
                    const leadStatusSelect = document.getElementById('edit_realtor_lead_status');
                    if(client.status === 'lead'){
                        leadStatusRow.style.display = 'flex';
                        leadStatusSelect.value = client.lead_status || 'cold';
                    } else {
                        leadStatusRow.style.display = 'none';
                    }
                } else {
                    alert('Failed to fetch client data');
                    editModal.style.display = 'none';
                }
            })
            .catch(() => { alert('Network error.'); editModal.style.display = 'none'; });
        }

        // ======================
        // DELETE CLIENT / LEAD
        // ======================
        if(target.classList.contains('delete-client-btn') || target.classList.contains('delete-lead-btn')){
            if(!confirm('Are you sure you want to delete this client/lead?')) return;

            const formData = new FormData();
            formData.append('action', 'delete_realtor_client_ajax');
            formData.append('nonce', clientActionData.delete_nonce);
            formData.append('client_id', clientId);

            const btnText = target.textContent;
            target.textContent = 'Deleting...';
            target.disabled = true;

            fetch(clientActionData.ajax_url, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if(data.success){
                        alert('Client/Lead deleted successfully!');
                        row.remove();
                    } else {
                        alert('Error: ' + data.data);
                        target.textContent = btnText;
                        target.disabled = false;
                    }
                })
                .catch(err => {
                    alert('Network error: ' + err.message);
                    target.textContent = btnText;
                    target.disabled = false;
                });
        }

        // ======================
        // CONVERT LEAD TO CLIENT
        // ======================
        if(target.classList.contains('convert-lead-btn')){
            if(!confirm('Do you want to convert this lead to a client?')) return;

            const formData = new FormData();
            formData.append('action', 'convert_lead_to_client');
            formData.append('nonce', clientActionData.convert_nonce);
            formData.append('client_id', clientId);

            const btnText = target.textContent;
            target.textContent = 'Converting...';
            target.disabled = true;

            fetch(clientActionData.ajax_url, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if(data.success){
                        alert('Lead successfully converted to client!');
                        row.remove();

                        // Add row to Active Clients table
                        const activeClientsTable = document.querySelector('.active-clients-table tbody');
                        if(activeClientsTable){
                            const newRow = document.createElement('tr');
                            newRow.dataset.clientId = clientId;
                            newRow.innerHTML = `
                                <td data-label="Client Name">${row.querySelector('[data-label="Client Name"]').textContent}</td>
                                <td data-label="Email">${row.querySelector('[data-label="Email"]')?.textContent || '—'}</td>
                                <td data-label="Phone">${row.querySelector('[data-label="Phone"]')?.textContent || '—'}</td>
                                <td data-label="Notes">${row.querySelector('[data-label="Notes"]')?.textContent || '—'}</td>
                                <td data-label="Actions" class="action-cell">
                                    <span class="delete-client-btn" title="Delete">🗑️</span>
                                </td>
                            `;
                            activeClientsTable.prepend(newRow);
                        }
                    } else {
                        alert('Error: ' + data.data);
                        target.textContent = btnText;
                        target.disabled = false;
                    }
                })
                .catch(err => {
                    alert('Network error: ' + err.message);
                    target.textContent = btnText;
                    target.disabled = false;
                });
        }

    }); // End container click listener

});
