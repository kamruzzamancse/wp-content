document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createRealtorClientForm');
    if(!form) {
        console.log('Form not found');
        return;
    }

    console.log('Form found, attaching event listener');

    form.addEventListener('submit', function(e){
        e.preventDefault();
        console.log('Form submitted via AJAX');

        // Basic validation before submission
        const fullName = document.getElementById('create_realtor_client_full_name').value;
        const email = document.getElementById('create_realtor_client_email').value;
        const status = document.getElementById('create_realtor_client_status').value;

        if (!fullName.trim() || !email.trim() || !status) {
            alert('Please fill in all required fields (Name, Email, Status)');
            return;
        }

        const formData = new FormData(form);
        formData.append('action', 'create_realtor_client_ajax');
        
        // Use the localized nonce if available, otherwise get from PHP
        if (typeof cl_client_create_ajax !== 'undefined') {
            formData.append('nonce', cl_client_create_ajax.nonce);
        } else {
            console.error('AJAX object not defined');
            alert('Configuration error. Please refresh the page.');
            return;
        }

        console.log('Sending AJAX request...');

        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Creating Client...';
        submitBtn.disabled = true;

        // AJAX request with better error handling
        fetch(cl_client_create_ajax.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Full response data:', data);
            
            if(data && data.success){
                // Success case
                const successMessage = data.data && data.data.message ? data.data.message : 'Client created successfully!';
                alert('✅ ' + successMessage);
                
                // Reset form
                form.reset();
                
                // Reset profile picture preview to default
                const previewAvatar = document.getElementById('createRealtorClientPreviewAvatar');
                if(previewAvatar) {
                    previewAvatar.src = "<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>";
                }
                
                // Close modal
                const modal = document.getElementById('rmRealtorClientCreateModal');
                if(modal) {
                    modal.style.display = 'none';
                }
                
                // Refresh the page to show new client in list
                console.log('Refreshing page to show new client...');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
                
            } else if (data && !data.success) {
                // Server returned error
                const errorMessage = data.data || 'Unknown server error occurred';
                console.error('Server error:', errorMessage);
                alert('❌ Error: ' + errorMessage);
            } else {
                // Invalid response format
                console.error('Invalid response format:', data);
                alert('❌ Invalid response from server. Please check console.');
            }
        })
        .catch(error => {
            console.error('AJAX Fetch Error:', error);
            alert('❌ Network error: ' + error.message);
        })
        .finally(() => {
            // Always reset button state
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });

    // Enhanced debug info
    console.log('Client create script loaded successfully');
    console.log('AJAX URL:', cl_client_create_ajax.ajax_url);
    console.log('Nonce:', cl_client_create_ajax.nonce);
});