<?php 
    $upload_dir = wp_upload_dir(); 
    $image_url = $upload_dir['baseurl']; 
?>

<div class="back-link">
  <a href="?tab=rt-settings-pi" class="pd-back-link">
    <span class="pd-back-link__arrow">←</span>
    <h1 class="header-title">Personal Information</h1>
  </a>
</div>

<div id="profile-notice" class="profile-notice" style="display: none;"></div>

<div class="rpe-profile-container">
  <div class="rpe-profile-header">
    <div class="rpe-header-content">
      <div class="piv-profile-pic-container">
        <div class="piv-profile-pic-wrapper">
          <img class="realtor-avatar" id="profile-avatar" src=<?php echo esc_url( $image_url . '/2025/08/client-photo.jpg' ); ?> alt="Realtor Profile Pic">
          <label for="profile-pic-upload" class="piv-edit-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
          </label>
          <input type="file" id="profile-pic-upload" accept="image/*" style="display: none;">
        </div>
      </div>
      <div class="rpe-profile-info">
        <h1 class="rpe-profile-name" id="profile-display-name">Anisur Rahman</h1>
        <span class="rpe-profile-role">Realtor</span>
      </div>
    </div>
  </div>
  <form class="rpe-profile-form" id="profile-form">
    <div class="rpe-form-section">
      <label class="rpe-form-label">Full name</label>
      <input type="text" class="rpe-form-input" id="full-name" disabled>
    </div>
    <div class="rpe-form-section">
      <label class="rpe-form-label">Broker Number</label>
      <input type="text" class="rpe-form-input" id="broker-number" name="broker_number">
    </div>
    <div class="rpe-form-section">
      <label class="rpe-form-label">Email</label>
      <input type="email" class="rpe-form-input" id="email" disabled>
    </div>
    <div class="rpe-form-section">
      <label class="rpe-form-label">Company Name</label>
      <input type="text" class="rpe-form-input" id="company-name" name="company_name">
    </div>
    <div class="rpe-form-actions">
      <button type="button" class="rpe-cancel-button">Cancel</button>
      <button type="submit" class="rpe-save-button">Save Changes</button>
    </div>
  </form>
</div>

<style>
  /* Back Link Styles */
  .back-link {
    margin-bottom: 20px;
  }
  
  .pd-back-link {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #333;
  }
  
  /* Notice Styles */
  .profile-notice {
    padding: 12px 15px;
    margin-bottom: 20px;
    border-radius: 4px;
  }
  
  .profile-notice.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }
  
  .profile-notice.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }
  
  /* Main Profile Container */
  .rpe-profile-container {
    max-width: 700px;
    padding: 20px;
    color: #333;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  }
  
  /* Profile Header */
  .rpe-profile-header {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
  }
  
  .rpe-header-content {
    display: flex;
    align-items: center;
  }
  
  /* Profile Picture */
  .piv-profile-pic-container {
    flex: 0 0 auto;
  }
  
  .realtor-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #f1f1f1;
  }
  
  /* Profile Info (right-aligned) */
  .rpe-profile-info {
    flex: 1;
    text-align: right;
  }
  
  .rpe-profile-name {
    font-size: 28px;
    font-weight: bold;
    margin: 0;
    color: #000;
  }
  
  .rpe-profile-role {
    display: block;
    font-size: 16px;
    font-weight: normal;
    color: #666;
    margin-top: 5px;
  }
  
  /* Form Sections */
  .rpe-profile-form {
    display: flex;
    flex-direction: column;
  }
  
  .rpe-form-section {
    margin-bottom: 5px;
  }
  
  .rpe-form-label {
    display: block;
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 8px;
    color: #555;
  }
  
  .rpe-form-input {
    width: 100%;
    padding: 10px 12px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
    margin-bottom: 15px;
  }
  
  .rpe-form-input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
  }
  
  /* Divider */
  .rpe-divider {
    height: 1px;
    background-color: #eee;
    margin: 15px 0;
  }
  
  /* Form Actions */
  .rpe-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
  }
  
  .rpe-save-button {
    background-color: #3498db;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  
  .rpe-save-button:hover {
    background-color: #2980b9;
  }
  
  .rpe-cancel-button {
    background-color: #f8f9fa;
    color: #333;
    border: 1px solid #ddd;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  
  .rpe-cancel-button:hover {
    background-color: #e9ecef;
  }
  
  /* Profile Picture Edit Icon */
  .piv-profile-pic-wrapper {
    position: relative;
    display: inline-block;
  }
  
  .piv-edit-icon {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: #3498db;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  }
  
  .piv-edit-icon:hover {
    background: #2980b9;
    transform: scale(1.1);
  }
  
  .piv-edit-icon svg {
    width: 16px;
    height: 16px;
  }
  
  /* Responsive adjustments */
  @media (max-width: 600px) {
    .rpe-profile-container {
      padding: 15px;
    }
    
    .rpe-header-content {
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
    }
    
    .rpe-profile-info {
      text-align: left;
      width: 100%;
    }
    
    .realtor-avatar {
      width: 60px;
      height: 60px;
    }
    
    .rpe-profile-name {
      font-size: 24px;
    }
    
    .rpe-form-label {
      font-size: 13px;
    }
    
    .rpe-form-input {
      font-size: 15px;
    }
    
    .rpe-form-actions {
      flex-direction: column;
    }
    
    .rpe-save-button, .rpe-cancel-button {
      width: 100%;
    }
  }
</style>

<script>
// Wait for the document to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Show notification function
    function showNotice(message, type) {
        const notice = document.getElementById('profile-notice');
        notice.textContent = message;
        notice.className = `profile-notice ${type}`;
        notice.style.display = 'block';
        
        // Hide notice after 5 seconds
        setTimeout(() => {
            notice.style.display = 'none';
        }, 5000);
    }
    
    // Load profile data from server
    function loadProfileData() {
        const data = new FormData();
        data.append('action', 'load_profile_data');
        data.append('nonce', profile_ajax.nonce);
        
        fetch(profile_ajax.ajax_url, {
            method: 'POST',
            body: data
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Populate form fields with data
                document.getElementById('full-name').value = result.data.full_name;
                document.getElementById('email').value = result.data.email;
                document.getElementById('broker-number').value = result.data.broker_number;
                document.getElementById('company-name').value = result.data.company_name;
                document.getElementById('profile-display-name').textContent = result.data.full_name;
                
                // Set profile picture if available
                if (result.data.profile_picture) {
                    document.getElementById('profile-avatar').src = result.data.profile_picture;
                }
            } else {
                showNotice('Error loading profile data: ' + result.data, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotice('Error loading profile data', 'error');
        });
    }
    
    // Save profile data to server
    function saveProfileData(formData) {
        formData.append('action', 'save_profile_data');
        formData.append('nonce', profile_ajax.nonce);
        
        fetch(profile_ajax.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotice('Profile updated successfully', 'success');
            } else {
                showNotice('Error saving profile: ' + result.data, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotice('Error saving profile', 'error');
        });
    }
    
    // Upload profile picture to server
    function uploadProfilePicture(file) {
        const formData = new FormData();
        formData.append('action', 'upload_profile_picture');
        formData.append('nonce', profile_ajax.nonce);
        formData.append('profile_picture', file);
        
        fetch(profile_ajax.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                document.getElementById('profile-avatar').src = result.data.url;
                showNotice('Profile picture updated successfully', 'success');
            } else {
                showNotice('Error uploading picture: ' + result.data, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotice('Error uploading picture', 'error');
        });
    }
    
    // Profile picture upload functionality
    document.getElementById('profile-pic-upload').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('profile-avatar').src = event.target.result;
                // Upload the image to server
                uploadProfilePicture(e.target.files[0]);
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });
    
    // Form submit handler
    document.getElementById('profile-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        saveProfileData(formData);
    });
    
    // Cancel button functionality - go back to previous page
    document.querySelector('.rpe-cancel-button').addEventListener('click', function() {
        window.location.href = '?tab=rt-settings-pi';
    });
    
    // Load profile data when page loads
    loadProfileData();
});
</script>