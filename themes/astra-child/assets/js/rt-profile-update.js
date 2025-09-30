/**
 * RT Profile Update JavaScript
 * Handles AJAX operations for realtor profile management
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('RT Profile Update Script Loaded');
    
    // Check if profile_ajax object is available
    if (typeof profile_ajax === 'undefined') {
        console.error('profile_ajax object not defined. Script not properly enqueued.');
        showNotice('AJAX functionality not available. Please refresh the page.', 'error');
        initializeFallbackMode();
        return;
    }

    console.log('AJAX URL:', profile_ajax.ajax_url);
    console.log('Nonce:', profile_ajax.nonce);

    // Check if nonce is valid
    if (!profile_ajax.nonce || profile_ajax.nonce === '') {
        console.error('Invalid nonce detected');
        showNotice('Security error. Please refresh the page and try again.', 'error');
        return;
    }

    // Initialize the profile functionality
    initializeProfileFunctions();
});

/**
 * Initialize all profile related functions
 */
function initializeProfileFunctions() {
    // Load profile data via AJAX (as backup to PHP-loaded data)
    loadProfileData();
    
    // Initialize event listeners
    initializeEventListeners();
}

/**
 * Initialize all event listeners
 */
function initializeEventListeners() {
    // Profile picture upload
    const profilePicInput = document.getElementById('profile-pic-upload');
    if (profilePicInput) {
        profilePicInput.addEventListener('change', handleProfilePictureUpload);
    }

    // Profile form submission
    const profileForm = document.getElementById('profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', handleProfileFormSubmit);
    }

    // Cancel button
    const cancelButton = document.querySelector('.rpe-cancel-button');
    if (cancelButton) {
        cancelButton.addEventListener('click', handleCancelButton);
    }
}

/**
 * Fallback mode when AJAX is not available
 */
function initializeFallbackMode() {
    console.warn('Running in fallback mode - AJAX not available');
    
    const profileForm = document.getElementById('profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            showNotice('AJAX functionality not available. Please refresh the page and try again.', 'error');
        });
    }
}

/**
 * Show notice message
 */
function showNotice(message, type) {
    const notice = document.getElementById('profile-notice');
    if (notice) {
        notice.textContent = message;
        notice.className = `profile-notice ${type}`;
        notice.style.display = 'block';
        setTimeout(() => { 
            notice.style.display = 'none'; 
        }, 5000);
    }
}

/**
 * Load profile data via AJAX
 */
function loadProfileData() {
    console.log('Loading profile data via AJAX...');
    
    const data = new FormData();
    data.append('action', 'load_rt_profile_data');
    data.append('nonce', profile_ajax.nonce);

    fetch(profile_ajax.ajax_url, { 
        method: 'POST', 
        body: data 
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(result => {
        console.log('AJAX Result:', result);
        
        if (result.success) {
            updateFormWithData(result.data);
            console.log('Profile data loaded successfully via AJAX');
        } else {
            console.error('Error loading profile via AJAX:', result);
            if (result.data && result.data.includes('Security verification')) {
                showNotice('Session expired. Please refresh the page.', 'error');
            }
        }
    })
    .catch(error => {
        console.error('AJAX Fetch error:', error);
    });
}

/**
 * Update form fields with data from AJAX response
 */
function updateFormWithData(profileData) {
    // Update only the fields that are not already set via PHP
    document.getElementById('phone').value = profileData.phone || '';
    document.getElementById('agency-name').value = profileData.agency_name || '';
    document.getElementById('license-number').value = profileData.license_number || '';
    document.getElementById('rating-avg').value = profileData.rating_avg || 0;
    
    // Update display name
    if (profileData.full_name) {
        document.getElementById('profile-display-name').textContent = profileData.full_name;
    }
    
    // Update profile picture if different
    const currentAvatar = document.getElementById('profile-avatar').src;
    if (profileData.profile_picture && profileData.profile_picture !== currentAvatar) {
        document.getElementById('profile-avatar').src = profileData.profile_picture;
    }
}

/**
 * Handle profile form submission
 */
function handleProfileFormSubmit(e) {
    e.preventDefault();
    
    // Validate form data
    if (!validateForm()) {
        return;
    }
    
    // Create FormData manually to ensure all fields are included
    const formData = new FormData();
    
    // Add all form fields manually
    formData.append('full_name', document.getElementById('full-name').value || '');
    formData.append('phone', document.getElementById('phone').value || '');
    formData.append('agency_name', document.getElementById('agency-name').value || '');
    formData.append('license_number', document.getElementById('license-number').value || '');
    formData.append('rating_avg', document.getElementById('rating-avg').value || '0');
    
    saveProfileData(formData);
}

/**
 * Validate form data before submission
 */
function validateForm() {
    const phone = document.getElementById('phone').value;
    const rating = document.getElementById('rating-avg').value;
    
    // Validate phone number (basic validation)
    if (phone && !/^[\d\s\-\+\(\)]+$/.test(phone)) {
        showNotice('Please enter a valid phone number', 'error');
        return false;
    }
    
    // Validate rating
    if (rating && (rating < 0 || rating > 5)) {
        showNotice('Rating must be between 0 and 5', 'error');
        return false;
    }
    
    return true;
}

/**
 * Save profile data via AJAX
 */
function saveProfileData(formData) {
    // Show loading state
    const saveButton = document.querySelector('.rpe-save-button');
    const originalText = saveButton.textContent;
    saveButton.textContent = 'Saving...';
    saveButton.disabled = true;

    // Add action and nonce to FormData
    formData.append('action', 'save_rt_profile_data');
    formData.append('nonce', profile_ajax.nonce);

    console.log('Saving profile data with nonce:', profile_ajax.nonce);
    console.log('Form data entries:');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }

    fetch(profile_ajax.ajax_url, { 
        method: 'POST', 
        body: formData 
    })
    .then(res => {
        console.log('Save response status:', res.status);
        if (!res.ok) {
            throw new Error('Network response was not ok: ' + res.status);
        }
        return res.json();
    })
    .then(result => {
        console.log('Save result:', result);
        
        if (result.success) {
            showNotice(result.data || 'Profile updated successfully', 'success');
            
            // Update display name
            const fullName = document.getElementById('full-name').value;
            if (fullName) {
                document.getElementById('profile-display-name').textContent = fullName;
            }
        } else {
            console.error('Save failed:', result);
            if (result.data && result.data.includes('Security verification')) {
                showNotice('Session expired. Please refresh the page and try again.', 'error');
            } else {
                showNotice('Error saving profile: ' + (result.data || 'Unknown error'), 'error');
            }
        }
    })
    .catch(err => {
        console.error('Save error:', err);
        showNotice('Network error: ' + err.message, 'error');
    })
    .finally(() => {
        // Restore button state
        saveButton.textContent = originalText;
        saveButton.disabled = false;
    });
}

/**
 * Handle profile picture upload
 */
function handleProfilePictureUpload(e) {
    if (e.target.files && e.target.files[0]) {
        const file = e.target.files[0];
        
        // Validate file type
        if (!file.type.match('image.*')) {
            showNotice('Please select a valid image file', 'error');
            return;
        }
        
        // Validate file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            showNotice('Image size should be less than 2MB', 'error');
            return;
        }
        
        // Show preview immediately
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('profile-avatar').src = ev.target.result;
        };
        reader.readAsDataURL(file);
        
        // Upload to server
        uploadProfilePicture(file);
    }
}

/**
 * Upload profile picture to server
 */
function uploadProfilePicture(file) {
    const formData = new FormData();
    formData.append('action', 'upload_rt_profile_picture');
    formData.append('nonce', profile_ajax.nonce);
    formData.append('profile_picture', file);

    fetch(profile_ajax.ajax_url, { 
        method: 'POST', 
        body: formData 
    })
    .then(res => {
        if (!res.ok) {
            throw new Error('Network response was not ok');
        }
        return res.json();
    })
    .then(result => {
        if (result.success && result.data.url) {
            showNotice('Profile picture updated successfully', 'success');
        } else {
            showNotice('Error uploading picture: ' + (result.data || 'Unknown error'), 'error');
        }
    })
    .catch(err => {
        console.error('Upload error:', err);
        showNotice('Error uploading picture: ' + err.message, 'error');
    });
}

/**
 * Handle cancel button click
 */
function handleCancelButton() {
    window.location.href = '?tab=rt-settings-pi';
}