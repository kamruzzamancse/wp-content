document.addEventListener('DOMContentLoaded', function() {
    if (typeof rt_profile_ajax === 'undefined') {
        alert('AJAX object not found. Refresh page.');
        return;
    }

    const form = document.getElementById('profile-form');
    const avatar = document.getElementById('profile-avatar');
    const notice = document.getElementById('profile-notice');

    function showNotice(msg, type='success') {
        if (!notice) return;
        notice.textContent = msg;
        notice.className = 'profile-notice ' + type;
        notice.style.display = 'block';
        setTimeout(()=>notice.style.display='none', 5000);
    }

    // Load profile via AJAX
    const loadProfile = () => {
        const data = new FormData();
        data.append('action','load_rt_profile_data');
        data.append('nonce', rt_profile_ajax.nonce);

        fetch(rt_profile_ajax.ajax_url, { method:'POST', body:data })
        .then(res => res.json())
        .then(res => {
            if (!res.success) {
                showNotice(res.data || 'Failed to load profile', 'error');
                return;
            }
            const d = res.data;
            document.getElementById('phone').value = d.phone || '';
            document.getElementById('agency-name').value = d.agency_name || '';
            document.getElementById('license-number').value = d.license_number || '';
            document.getElementById('rating-avg').value = d.rating_avg || 0;
            if (d.profile_picture) avatar.src = d.profile_picture;
        })
        .catch(err => showNotice('AJAX error: '+err,'error'));
    };

    // Save profile via AJAX
    const saveProfile = (e) => {
        e.preventDefault();
        const fd = new FormData();
        fd.append('action','save_rt_profile_data');
        fd.append('nonce', rt_profile_ajax.nonce);
        fd.append('full_name', document.getElementById('full-name').value);
        fd.append('phone', document.getElementById('phone').value);
        fd.append('agency_name', document.getElementById('agency-name').value);
        fd.append('license_number', document.getElementById('license-number').value);
        fd.append('rating_avg', document.getElementById('rating-avg').value);

        const btn = e.target.querySelector('.rpe-save-button');
        const oldText = btn.textContent;
        btn.textContent='Saving...'; btn.disabled=true;

        fetch(rt_profile_ajax.ajax_url, { method:'POST', body:fd })
        .then(res=>res.json())
        .then(res=>{
            if(res.success) showNotice(res.data || 'Profile updated');
            else showNotice(res.data || 'Save failed','error');
        }).catch(err=>showNotice('Network error: '+err,'error'))
        .finally(()=>{btn.textContent=oldText; btn.disabled=false;});
    };

    // Profile picture upload
    const uploadPic = (e) => {
        const file = e.target.files[0];
        if (!file || !file.type.match('image.*')) { showNotice('Invalid image','error'); return; }
        if(file.size>2*1024*1024){ showNotice('Max 2MB','error'); return; }
        const reader = new FileReader();
        reader.onload = ev => avatar.src = ev.target.result;
        reader.readAsDataURL(file);

        const fd = new FormData();
        fd.append('action','upload_rt_profile_picture');
        fd.append('nonce', rt_profile_ajax.nonce);
        fd.append('profile_picture', file);

        fetch(rt_profile_ajax.ajax_url, { method:'POST', body:fd })
        .then(res=>res.json())
        .then(res=>{ if(res.success) showNotice('Picture updated'); else showNotice(res.data || 'Upload failed','error'); })
        .catch(err=>showNotice('Upload error: '+err,'error'));
    };

    // Cancel button
    const cancelBtn = document.querySelector('.rpe-cancel-button');
    if(cancelBtn) cancelBtn.addEventListener('click', ()=>window.location.href='?tab=rt-settings-pi');

    // Event listeners
    form.addEventListener('submit', saveProfile);
    const fileInput = document.getElementById('profile-pic-upload');
    if(fileInput) fileInput.addEventListener('change', uploadPic);

    // Initial load
    loadProfile();
});
