// Basic search functionality
document.querySelector('.search-box input').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Modal functionality

const clientDetailsModal = document.getElementById('clientDetailsModal');
const viewClientDetails = document.getElementById('viewClientDetails');
const closeClientDetailsModal = document.getElementById('closeClientDetailsModal');

viewClientDetails.addEventListener('click', () => {
    clientDetailsModal.style.display = 'flex';
});

closeClientDetailsModal.addEventListener('click', () => {
    clientDetailsModal.style.display = 'none';
});

// Close modal when clicking outside
clientDetailsModal.addEventListener('click', (e) => {
    if (e.target === clientDetailsModal) {
        clientDetailsModal.style.display = 'none';
    }
});