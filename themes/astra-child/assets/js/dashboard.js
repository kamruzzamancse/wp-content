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