// Updated Search functionality
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.querySelector('.pt-search-input');
    const rows = document.querySelectorAll('tbody tr');

    searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase().trim();

        rows.forEach(row => {
            const clientNameCell = row.querySelector('td[data-label="Client Name"]');
            if (clientNameCell) {
                const clientName = clientNameCell.textContent.toLowerCase();
                row.style.display = clientName.includes(searchTerm) ? '' : 'none';
            }
        });
    });
});