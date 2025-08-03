document.addEventListener('DOMContentLoaded', function() {
    const leadRows = document.querySelectorAll('.leads-table tbody tr');
    const popupOverlay = document.getElementById('leadPopupOverlay');
    const closePopup = document.querySelector('.close-popup');
    
    leadRows.forEach(row => {
        row.addEventListener('click', function() {
            const cells = this.cells;
            
            // Update popup content
            document.querySelector('.popup-client-name').textContent = cells[0].textContent;
            document.querySelector('.popup-value').textContent = cells[1].textContent;
            
            // Get status from table cell
            const statusDot = cells[2].querySelector('.status-dot');
            const statusContainer = document.querySelector('.status-container');
            
            // Clear existing status classes
            statusContainer.querySelector('.status-dot').className = 'status-dot';
            
            // Apply correct status
            if (statusDot.classList.contains('status-hot')) {
                statusContainer.querySelector('.status-dot').classList.add('status-hot');
                statusContainer.querySelector('.status-text').textContent = 'Hot';
            } 
            else if (statusDot.classList.contains('status-warm')) {
                statusContainer.querySelector('.status-dot').classList.add('status-warm');
                statusContainer.querySelector('.status-text').textContent = 'Warm';
            } 
            else {
                statusContainer.querySelector('.status-dot').classList.add('status-cold');
                statusContainer.querySelector('.status-text').textContent = 'Cold';
            }
            
            // Update notes
            document.querySelectorAll('.popup-value')[1].textContent = cells[3].textContent;
            
            // Show popup
            popupOverlay.style.display = 'flex';
        });
    });
    
    closePopup.addEventListener('click', function() {
        popupOverlay.style.display = 'none';
    });
    
    popupOverlay.addEventListener('click', function(e) {
        if (e.target === popupOverlay) {
            popupOverlay.style.display = 'none';
        }
    });
});


// js for upload document
jQuery(document).ready(function($) {
    // Get elements
    const uploadDocumentCard = $('#upload-document');
    const documentModal = $('#documentUploadModal');
    const cancelBtn = $('.cancel-btn');
    const uploadForm = $('#documentUploadForm');
    const fileInput = $('#documentFile');
    const fileNameDisplay = $('.file-name');
    
    // Open modal when upload card is clicked
    uploadDocumentCard.on('click', function() {
        documentModal.css('display', 'block');
    });
    
    // Close modal when cancel button is clicked
    cancelBtn.on('click', function() {
        documentModal.css('display', 'none');
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(event) {
        if ($(event.target).is(documentModal)) {
            documentModal.css('display', 'none');
        }
    });
    
    // Update file name display
    fileInput.on('change', function() {
        const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
        fileNameDisplay.text(fileName);
    });
    
    // Handle form submission (front-end only)
    uploadForm.on('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const uploadBtn = $('.upload-btn');
        uploadBtn.prop('disabled', true).html('<span class="spinner"></span> Uploading...');
        
        // Simulate upload delay
        setTimeout(function() {
            // Show success message
            alert('Document uploaded successfully! (This is a front-end demo)');
            
            // Reset form
            uploadForm[0].reset();
            fileNameDisplay.text('No file chosen');
            documentModal.css('display', 'none');
            
            // Reset button
            uploadBtn.prop('disabled', false).text('Upload');
        }, 1500);
    });
    
    // Simple spinner styling (optional)
    $('<style>.spinner{display:inline-block;width:18px;height:18px;border:2px solid rgba(255,255,255,.3);border-radius:50%;border-top-color:#fff;animation:spin 1s ease-in-out infinite;margin-right:8px;}@keyframes spin{to{transform:rotate(360deg);}}</style>').appendTo('head');
});

// ja for notification

document.addEventListener('DOMContentLoaded', function() {
    // Mark all as read functionality
    const markAllReadBtn = document.querySelector('.mark-all-read');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function() {
            document.querySelectorAll('.notification-card.unread').forEach(card => {
                card.classList.remove('unread');
                const dot = card.querySelector('.status-dot');
                if (dot) dot.remove();
            });
            
            // Here you would typically make an AJAX call to update server status
            // Example:
            /* fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=mark_notifications_read'
            }); */
        });
    }
    
    // Notification click handler (optional)
    document.querySelectorAll('.notification-card').forEach(card => {
        card.addEventListener('click', function() {
            if (this.classList.contains('unread')) {
                this.classList.remove('unread');
                const dot = this.querySelector('.status-dot');
                if (dot) dot.remove();
                
                // AJAX call to mark single notification as read
                /* fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=mark_notification_read&id=' + this.dataset.id
                }); */
            }
            
            // Add your custom click behavior here
            // window.location.href = this.dataset.url;
        });
    });
});

// js for profile pic modal
jQuery(document).ready(function($) {
    // Open modal when clicking profile picture
    $('.profile-pic').on('click', function(e) {
        e.stopPropagation(); // Prevent event from reaching document
        $('#profileModal').fadeIn(200);
    });

    // Close modal when clicking outside
    $(document).on('click', function() {
        $('#profileModal').fadeOut(200);
    });

    // Prevent modal close when clicking inside modal
    $('#profileModal').on('click', function(e) {
        e.stopPropagation();
    });
});