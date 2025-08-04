document.addEventListener('DOMContentLoaded', function() {
    // Make gallery images clickable to view larger
    document.querySelectorAll('.gallery img').forEach(img => {
        img.addEventListener('click', function() {
            // Get the main image container in the same property item
            const mainImg = this.closest('.property-item').querySelector('.main-image');
            
            // Swap the src between clicked image and main image
            const tempSrc = mainImg.src;
            mainImg.src = this.src;
            this.src = tempSrc;
        });
    });
});

/* js for recent document */

// You can add JavaScript functionality here if needed
document.querySelectorAll('.action-btn').forEach(button => {
    button.addEventListener('click', function() {
        const action = this.textContent;
        const docName = this.closest('.document-card').querySelector('.document-name').textContent;
        console.log(`${action} clicked for ${docName}`);
        // Add your actual functionality here
    });
});

/* js for top-bar of property listing */

 // JavaScript functionality
document.querySelectorAll('.action-button').forEach(button => {
    button.addEventListener('click', function() {
        const action = this.textContent.trim();
        console.log(`${action} button clicked`);
        // Add your actual functionality here
    });
});

document.querySelector('.search-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        console.log('Searching for:', this.value);
        // Add search functionality here
    }
});

document.querySelector('.sort-select').addEventListener('change', function() {
    console.log('Sorting by:', this.value);
    // Add sorting functionality here
});