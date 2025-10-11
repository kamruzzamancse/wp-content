document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.ab-deleteClient');

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const clientId = row.dataset.clientId;

            if (!clientId) return;

            if (!confirm('Are you sure you want to delete this client?')) return;

            const formData = new FormData();
            formData.append('action', 'delete_realtor_client_ajax');
            formData.append('nonce', cl_client_delete_ajax.nonce);
            formData.append('client_id', clientId);

            btn.textContent = 'Deleting...';
            btn.disabled = true;

            fetch(cl_client_delete_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    alert('Client deleted successfully!');
                    row.remove();
                } else {
                    alert('Error: ' + data.data);
                    btn.textContent = '🗑️';
                    btn.disabled = false;
                }
            })
            .catch(err => {
                alert('Network error: ' + err.message);
                btn.textContent = '🗑️';
                btn.disabled = false;
            });
        });
    });
});
