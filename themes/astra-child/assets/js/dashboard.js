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
    
    // Close modal when clicking outside the modal content
    $(window).on('click', function(event) {
        if ($(event.target).is(documentModal)) {
            documentModal.css('display', 'none');
        }
    });
    
    // Update file name display when file is selected
    fileInput.on('change', function() {
        const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
        fileNameDisplay.text(fileName);
    });
    
    // Handle form submission
    uploadForm.on('submit', function(e) {
        e.preventDefault();
        
        // Collect form data
        const formData = new FormData();
        formData.append('title', $('#documentTitle').val());
        formData.append('type', $('#documentType').val());
        formData.append('dueDate', $('#dueDate').val());
        
        // Add file if selected
        if (fileInput[0].files[0]) {
            formData.append('file', fileInput[0].files[0]);
        }
        
        // Basic validation
        if (!formData.get('title') || !formData.get('type') || !formData.get('file')) {
            alert('Please fill in all required fields and select a file');
            return;
        }
        
        // Here you would typically make an AJAX call to your backend
        console.log('Form submitted:', {
            title: formData.get('title'),
            type: formData.get('type'),
            dueDate: formData.get('dueDate'),
            fileName: formData.get('file') ? formData.get('file').name : 'No file'
        });
        
        // For demo purposes, we'll just show an alert and close the modal
        alert('Document uploaded successfully!');
        
        // Reset form and close modal
        uploadForm[0].reset();
        fileNameDisplay.text('No file chosen');
        documentModal.css('display', 'none');
    });
});
