document.addEventListener('DOMContentLoaded', function() {

    function showNotice(message, type) {
        const notice = document.getElementById('profile-notice');
        notice.textContent = message;
        notice.className = `profile-notice ${type}`;
        notice.style.display = 'block';
        setTimeout(() => notice.style.display = 'none', 5000);
    }

    // ======================
    // Upload profile picture
    // ======================
    const profilePicInput = document.getElementById('profile-pic-upload');
    profilePicInput?.addEventListener('change', function(e) {
        if(e.target.files[0]) {
            const file = e.target.files[0];
            const formData = new FormData();
            formData.append('action', 'upload_cl_profile_picture');
            formData.append('nonce', cl_profile_ajax.nonce);
            formData.append('profile_picture', file);

            fetch(cl_profile_ajax.ajax_url, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(res => {
                    if(res.success && res.data?.url) {
                        document.getElementById('profile-avatar').src = res.data.url;
                        showNotice('Profile picture updated successfully', 'success');
                    } else {
                        showNotice('Error uploading picture: ' + (res.data || res.message), 'error');
                    }
                })
                .catch(() => showNotice('Error uploading picture', 'error'));
        }
    });

    // ======================
    // Save client profile (with email)
    // ======================
    const profileForm = document.getElementById('profile-form');
    profileForm?.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(profileForm);
        formData.append('action', 'save_cl_profile_data');
        formData.append('nonce', cl_profile_ajax.nonce);

        fetch(cl_profile_ajax.ajax_url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    showNotice('Profile updated successfully', 'success');
                } else {
                    showNotice('Error saving profile: ' + (res.data || res.message), 'error');
                }
            })
            .catch(() => showNotice('Error saving profile', 'error'));
    });

    // ======================
    // Create Realtor Client Form Submit
    // ======================
    const createForm = document.getElementById('createRealtorClientForm');
    createForm?.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(createForm);
        formData.append('action', 'save_rt_client_data');
        formData.append('nonce', cl_profile_ajax.nonce);

        fetch(cl_profile_ajax.ajax_url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    showNotice('Client created successfully!', 'success');
                    createForm.reset();
                    document.getElementById('rmRealtorClientCreateModal').style.display = 'none';
                } else {
                    showNotice('Error creating client: ' + (res.data || res.message), 'error');
                }
            })
            .catch(() => showNotice('Error creating client', 'error'));
    });

    // ======================
    // Cancel button
    // ======================
    document.querySelector('.rpe-cancel-button')?.addEventListener('click', function() {
        window.location.href = '?tab=cl-settings-pi';
    });

});
