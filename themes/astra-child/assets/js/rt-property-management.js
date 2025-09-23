document.addEventListener('DOMContentLoaded', function() {
    // Make gallery images clickable to view larger
    document.querySelectorAll('.pt-gallery img').forEach(img => {
        img.addEventListener('click', function() {
            // Get the main image container in the same property item
            const mainImg = this.closest('.pt-property-item').querySelector('.pt-main-image');
            
            // Swap the src between clicked image and main image
            const tempSrc = mainImg.src;
            mainImg.src = this.src;
            this.src = tempSrc;
        });
    });
});

/* js for recent document */

// You can add JavaScript functionality here if needed
document.querySelectorAll('.pt-action-btn').forEach(button => {
    button.addEventListener('click', function() {
        const action = this.textContent;
        const docName = this.closest('.pt-document-card').querySelector('.pt-document-name').textContent;
        // Add your actual functionality here
    });
});

/* js for top-bar of property listing */

 // JavaScript functionality
document.querySelectorAll('.pt-action-button').forEach(button => {
    button.addEventListener('click', function() {
        const action = this.textContent.trim();
        // Add your actual functionality here
    });
});

document.querySelector('.pt-search-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        // Add search functionality here
    }
});

document.querySelector('.pt-sort-select').addEventListener('change', function() {
    // Add sorting functionality here
});

// js for searching functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.pt-search-input');
    const propertyItems = document.querySelectorAll('.pt-property-item');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim().toLowerCase();
        let hasMatches = false;

        propertyItems.forEach(item => {
            const title = item.querySelector('.pt-property-title').textContent.toLowerCase();
            const isVisible = title.includes(searchTerm);
            
            item.classList.toggle('hidden', !isVisible); // Use CSS class instead of inline style

            if (isVisible) hasMatches = true;
        });

        const noResults = document.querySelector('.no-results') || createNoResultsMessage();
        noResults.style.display = hasMatches || searchTerm === '' ? 'none' : 'block';
    });

    function createNoResultsMessage() {
        const message = document.createElement('div');
        message.className = 'no-results';
        message.textContent = 'No properties found';
        message.style.display = 'none';
        document.querySelector('.pt-property-list').appendChild(message);
        return message;
    }
});

function debounce(func, delay) {
    let timeout;
    return function() {
        const context = this;
        const args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), delay);
    };
}

// js for sorting functionality
document.addEventListener('DOMContentLoaded', function() {
    // Selectors (Update class names here if different in your HTML)
    const searchInput = document.querySelector('.pt-search-input');
    const sortSelect = document.querySelector('.pt-sort-select'); // Make sure your <select> has class="sort-select"
    const propertyList = document.querySelector('.pt-property-list');
    const propertyItems = Array.from(document.querySelectorAll('.pt-property-item'));

    // Create and append the no-results message
    const noResults = document.createElement('div');
    noResults.className = 'no-results';
    noResults.textContent = 'No properties found matching your criteria.';
    noResults.style.display = 'none';
    propertyList.appendChild(noResults);

    // Initialize with default sort (empty)
    sortProperties('');

    // Debounce utility function
    function debounce(func, delay) {
        let timeout;
        return function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, arguments), delay);
        };
    }

    // Filter properties based on search term
    function filterProperties(searchTerm) {
        let visibleCount = 0;

        propertyItems.forEach(item => {
            const title = item.querySelector('.pt-property-title').textContent.toLowerCase();
            const isVisible = searchTerm === '' || title.includes(searchTerm);
            item.style.display = isVisible ? 'block' : 'none';
            if (isVisible) visibleCount++;
        });

        noResults.style.display = visibleCount > 0 ? 'none' : 'block';
        sortProperties(sortSelect.value); // Re-sort after filtering
    }

    // Sort properties based on selected option
    function sortProperties(sortValue) {
        const visibleItems = propertyItems.filter(item => item.style.display !== 'none');

        visibleItems.sort((a, b) => {
            switch (sortValue) {
                case 'price-asc':
                    return getPrice(a) - getPrice(b);
                case 'price-desc':
                    return getPrice(b) - getPrice(a);
                case 'name-asc':
                    return getText(a, '.pt-property-title').localeCompare(getText(b, '.pt-property-title'));
                case 'name-desc':
                    return getText(b, '.pt-property-title').localeCompare(getText(a, '.pt-property-title'));
                case 'date-asc':
                    return new Date(getText(a, '.pt-property-date')) - new Date(getText(b, '.pt-property-date'));
                case 'date-desc':
                    return new Date(getText(b, '.pt-property-date')) - new Date(getText(a, '.pt-property-date'));
                default:
                    return 0;
            }
        });

        visibleItems.forEach(item => propertyList.appendChild(item));
    }

    // Helper: Get price number from property-item element
    function getPrice(element) {
        const priceText = element.querySelector('.pt-property-price').textContent;
        return parseFloat(priceText.replace(/[^0-9.]/g, '')) || 0;
    }

    // Helper: Get text content from a selector inside an element
    function getText(element, selector) {
        return element.querySelector(selector).textContent;
    }

    // Event listeners with debounce
    searchInput.addEventListener('input', debounce(function() {
        filterProperties(this.value.trim().toLowerCase());
    }, 300));

    sortSelect.addEventListener('change', function() {
        //alert('Sorting changed to: ' + this.options[this.selectedIndex].text);
        sortProperties(this.value);
    });
});

// css for propety details
function loadPropertyDetails() {
    const propertyData = JSON.parse(sessionStorage.getItem('currentProperty'));
    
    if (propertyData) {
        document.getElementById('property-title').textContent = propertyData.title;
        document.getElementById('property-description').textContent = propertyData.description;
        document.getElementById('property-location').textContent = propertyData.location;
        document.getElementById('property-type').textContent = propertyData.type;
        document.getElementById('property-price').textContent = propertyData.price;
        document.getElementById('property-bedrooms').textContent = propertyData.bedrooms;
        document.getElementById('property-bathrooms').textContent = propertyData.bathrooms;
        document.getElementById('property-size').textContent = propertyData.size;
        document.getElementById('property-furnished').textContent = propertyData.furnished;
        document.getElementById('property-parking').textContent = propertyData.parking;
        
        // Set main image
        const mainImage = document.getElementById('property-main-image');
        if (propertyData.mainImage) {
            mainImage.src = propertyData.mainImage;
        }
        
        // Create thumbnails
        const thumbnailsContainer = document.querySelector('.property-thumbnails');
        thumbnailsContainer.innerHTML = '';
        
        if (propertyData.galleryImages && propertyData.galleryImages.length > 0) {
            propertyData.galleryImages.forEach(imgSrc => {
                const img = document.createElement('img');
                img.src = imgSrc;
                img.alt = 'Property Image';
                img.addEventListener('click', () => {
                    mainImage.src = imgSrc;
                });
                thumbnailsContainer.appendChild(img);
            });
        }
    }
}

// js for change image
function changeImage(newSrc) {
    const mainImage = document.getElementById('pd-mainPreview');
    mainImage.style.opacity = 0;
    setTimeout(() => {
        mainImage.src = newSrc;
        mainImage.style.opacity = 1;
    }, 200);
}

// Create Property Modal
function openCreateModal() {
    document.getElementById("propertyCreateModal").style.display = "flex";
}

function closeCreateModal() {
    document.getElementById("propertyCreateModal").style.display = "none";
}

// Edit Property Modal
function propertyEditModal() {
    document.getElementById("propertyEditModal").style.display = "flex";
}

function closeEditModal() {
    document.getElementById("propertyEditModal").style.display = "none";
}

// Close modal when clicking outside
document.addEventListener('click', function (e) {
    const modalCreate = document.getElementById('propertyCreateModal');
    const modalEdit = document.getElementById('propertyEditModal');

    if (e.target === modalCreate) closeCreateModal();
    if (e.target === modalEdit) closeEditModal();
});



