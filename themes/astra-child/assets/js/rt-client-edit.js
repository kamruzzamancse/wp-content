document.addEventListener('DOMContentLoaded', function() {

    // ======================
    // 1️⃣ OPEN EDIT MODAL
    // ======================
    document.querySelectorAll('.ab-editClientDetails').forEach(editBtn => {
        editBtn.addEventListener('click', function() {
            const row = this.closest('tr');
            const editModal = document.getElementById('rmRealtorClientEditModal');

            if (!editModal) return;

            // Fetch data from table row
            const clientID = row.getAttribute('data-client-id');
            const fullName = row.querySelector('.client-name-text')?.textContent.trim() || '';
            const email    = row.querySelector('td[data-label="Email"]')?.textContent.trim() || '';
            const phone    = row.querySelector('td[data-label="Phone Number"]')?.textContent.trim() || '';
            const notes    = row.querySelector('td[data-label="Notes"]')?.textContent.trim() || '';
            const status   = row.querySelector('td[data-label="Status"]')?.textContent.trim() || '';
            const profile  = row.querySelector('td.ab-sl-column img')?.src || '';

            // Populate modal fields
            editModal.querySelector('#edit_realtor_client_id').value = clientID;
            editModal.querySelector('#edit_realtor_client_full_name').value = fullName;
            editModal.querySelector('#edit_realtor_client_email').value = email;
            editModal.querySelector('#edit_realtor_client_phone').value = phone;
            editModal.querySelector('#edit_realtor_client_notes').value = notes;
            editModal.querySelector('#edit_realtor_client_status').value = status;
            
            const profileImg = editModal.querySelector('#editRealtorClientPreviewAvatar');
            if (profileImg) profileImg.src = profile;

            // Show modal
            editModal.style.display = 'flex';
        });
    });

    // ======================
    // 2️⃣ CLOSE MODAL
    // ======================
    const editModal = document.getElementById('rmRealtorClientEditModal');
    const closeEditModalBtn = document.getElementById('closeRealtorClientEditModal');
    
    if (closeEditModalBtn && editModal) {
        closeEditModalBtn.addEventListener('click', () => {
            editModal.style.display = 'none';
        });
    }

    if (editModal) {
        editModal.addEventListener('click', e => {
            if (e.target === editModal) {
                editModal.style.display = 'none';
            }
        });
    }

    // ======================
    // 3️⃣ IMAGE PREVIEW CHANGE
    // ======================
    const editProfileInput = document.getElementById('edit_realtor_client_profile_picture');
    if (editProfileInput) {
        editProfileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('editRealtorClientPreviewAvatar');
                    if (preview) preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ======================
    // 4️⃣ AJAX SUBMIT FORM
    // ======================
    const editForm = document.getElementById('editRealtorClientForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(editForm);
            formData.append('action', 'update_realtor_client_ajax');
            formData.append('nonce', cl_client_edit_ajax.nonce);

            // Loading state
            const submitBtn = editForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Updating...';
            submitBtn.disabled = true;

            fetch(cl_client_edit_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.data);
                    editModal.style.display = 'none';
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    alert('❌ ' + data.data);
                }
            })
            .catch(err => {
                console.error('AJAX Error:', err);
                alert('Network error occurred.');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});
