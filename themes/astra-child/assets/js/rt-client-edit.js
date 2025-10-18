document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('rmRealtorClientEditModal');
    const closeBtn = document.getElementById('closeRealtorClientEditModal');
    const form = document.getElementById('editRealtorClientForm');
    const defaultAvatar = '<?php echo esc_url(wp_upload_dir()["baseurl"] . "/2025/08/client-photo.jpg"); ?>';
    const leadStatusRow = document.getElementById('leadStatusRow');
    const leadStatusSelect = document.getElementById('edit_realtor_lead_status');

    // Open edit modal
    document.querySelectorAll('.ab-editClientDetails').forEach(btn => {
        btn.addEventListener('click', function() {
            const clientId = this.closest('tr').dataset.clientId;
            if (!clientId || !editModal) return;

            editModal.style.display = 'flex';

            // Fetch client data
            fetch(cl_client_edit_ajax.ajax_url, {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'fetch_realtor_client_ajax',
                    nonce: cl_client_edit_ajax.nonce,
                    client_id: clientId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const client = data.data;
                    document.getElementById('edit_realtor_client_id').value = client.client_id;
                    document.getElementById('edit_realtor_client_full_name').value = client.full_name;
                    document.getElementById('edit_realtor_client_email').value = client.email;
                    document.getElementById('edit_realtor_client_phone').value = client.phone;
                    document.getElementById('edit_realtor_client_notes').value = client.note;
                    document.getElementById('edit_realtor_client_status').value = client.status;
                    document.getElementById('editRealtorClientPreviewAvatar').src = client.profile_picture || defaultAvatar;

                    // ===== NEW LOGIC: Show lead_status dropdown only for leads =====
                    if (client.status === 'lead') {
                        if (leadStatusRow) leadStatusRow.style.display = 'flex';
                        if (leadStatusSelect) leadStatusSelect.value = client.lead_status || 'cold';
                    } else {
                        if (leadStatusRow) leadStatusRow.style.display = 'none';
                    }
                } else {
                    alert('Failed to fetch client data');
                    editModal.style.display = 'none';
                }
            })
            .catch(() => {
                alert('Network error. Please try again.');
                editModal.style.display = 'none';
            });
        });
    });

    // Close modal
    if (closeBtn && editModal) {
        closeBtn.addEventListener('click', () => editModal.style.display = 'none');
        editModal.addEventListener('click', e => {
            if (e.target === editModal) editModal.style.display = 'none';
        });
    }

    // Image preview
    const profileInput = document.getElementById('edit_realtor_client_profile_picture');
    if (profileInput) {
        profileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => document.getElementById('editRealtorClientPreviewAvatar').src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }

    // AJAX submit
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(form);
            formData.append('action', 'update_realtor_client_ajax');
            formData.append('nonce', cl_client_edit_ajax.nonce);

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Updating...';
            submitBtn.disabled = true;

            fetch(cl_client_edit_ajax.ajax_url, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Client updated successfully!');
                        form.reset();
                        editModal.style.display = 'none';
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        alert('Error: ' + data.data);
                    }
                })
                .catch(() => alert('Network error. Please try again.'))
                .finally(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
        });
    }
});
