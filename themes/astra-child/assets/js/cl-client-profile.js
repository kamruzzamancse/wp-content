document.addEventListener('DOMContentLoaded', function() {

    function showNotice(message, type) {
        const notice = document.getElementById('profile-notice');
        notice.textContent = message;
        notice.className = `profile-notice ${type}`;
        notice.style.display = 'block';
        setTimeout(() => notice.style.display = 'none', 5000);
    }

    // ======================
    // Upload profile picture (Updates both user meta and clients table)
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
    // Save client profile (Updates both wp_users and wp_clients tables)
    // ======================
    const profileForm = document.getElementById('profile-form');
    profileForm?.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(profileForm);
        formData.append('action', 'save_cl_profile_data');
        formData.append('nonce', cl_profile_ajax.nonce);

        // Show loading state
        const submitBtn = profileForm.querySelector('.rpe-save-button');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Saving...';
        submitBtn.disabled = true;

        fetch(cl_profile_ajax.ajax_url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    showNotice('Profile updated successfully', 'success');
                    // Update displayed name immediately
                    document.getElementById('profile-display-name').textContent = document.getElementById('full-name').value;
                } else {
                    showNotice('Error saving profile: ' + (res.data || res.message), 'error');
                }
            })
            .catch(() => showNotice('Error saving profile', 'error'))
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
    });

    // ======================
    // Cancel button
    // ======================
    document.querySelector('.rpe-cancel-button')?.addEventListener('click', function() {
        window.location.href = '?tab=cl-settings-pi';
    });

});