document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.getElementById('profile-form');
    const profileNotice = document.getElementById('profile-notice');

    function showNotice(message, type = 'success') {
        profileNotice.textContent = message;
        profileNotice.className = 'profile-notice ' + type;
        profileNotice.style.display = 'block';
        setTimeout(() => profileNotice.style.display = 'none', 5000);
    }

    function saveProfileData(formData) {
        formData.append('action', 'save_client_profile_data');
        formData.append('nonce', profile_ajax.nonce);

        fetch(profile_ajax.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if(res.success) showNotice('Profile updated successfully', 'success');
            else showNotice('Error saving profile: ' + res.data, 'error');
        })
        .catch(() => showNotice('Error saving profile', 'error'));
    }

    profileForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        saveProfileData(formData);
    });

    document.querySelector('.rpe-cancel-button').addEventListener('click', function() {
        window.location.href = '?tab=cl-settings-pi';
    });
});
