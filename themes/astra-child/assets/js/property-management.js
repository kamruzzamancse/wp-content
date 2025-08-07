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
        // Add your actual functionality here
    });
});

/* js for top-bar of property listing */

 // JavaScript functionality
document.querySelectorAll('.action-button').forEach(button => {
    button.addEventListener('click', function() {
        const action = this.textContent.trim();
        // Add your actual functionality here
    });
});

document.querySelector('.search-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        // Add search functionality here
    }
});

document.querySelector('.sort-select').addEventListener('change', function() {
    // Add sorting functionality here
});

// js searching functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-input');
    const propertyItems = document.querySelectorAll('.property-item');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim().toLowerCase();
        let hasMatches = false;

        propertyItems.forEach(item => {
            const title = item.querySelector('.property-title').textContent.toLowerCase();
            const isVisible = title.includes(searchTerm);
            
            item.style.display = isVisible ? 'block' : 'none';
            if (isVisible) hasMatches = true;
        });

        // Optional: Show "no results" message
        const noResults = document.querySelector('.no-results') || createNoResultsMessage();
        noResults.style.display = hasMatches || searchTerm === '' ? 'none' : 'block';
    });

    function createNoResultsMessage() {
        const message = document.createElement('div');
        message.className = 'no-results';
        message.textContent = 'No properties found';
        message.style.display = 'none';
        document.querySelector('.property-list').appendChild(message);
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

// js for sorting

document.addEventListener('DOMContentLoaded', function() {
    // Selectors (Update class names here if different in your HTML)
    const searchInput = document.querySelector('.search-input');
    const sortSelect = document.querySelector('.sort-select'); // Make sure your <select> has class="sort-select"
    const propertyList = document.querySelector('.property-list');
    const propertyItems = Array.from(document.querySelectorAll('.property-item'));

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
            const title = item.querySelector('.property-title').textContent.toLowerCase();
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
                    return getText(a, '.property-title').localeCompare(getText(b, '.property-title'));
                case 'name-desc':
                    return getText(b, '.property-title').localeCompare(getText(a, '.property-title'));
                case 'date-asc':
                    return new Date(getText(a, '.property-date')) - new Date(getText(b, '.property-date'));
                case 'date-desc':
                    return new Date(getText(b, '.property-date')) - new Date(getText(a, '.property-date'));
                default:
                    return 0;
            }
        });

        visibleItems.forEach(item => propertyList.appendChild(item));
    }

    // Helper: Get price number from property-item element
    function getPrice(element) {
        const priceText = element.querySelector('.property-price').textContent;
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

// Add to your existing click handler for property items
document.querySelectorAll('.property-item').forEach(item => {
    item.addEventListener('click', function(e) {
        if (e.target.closest('.gallery') || e.target.tagName === 'BUTTON') {
            return;
        }
        
        // Example property data - replace with your actual data structure
        const propertyData = {
            title: "Lakeview Premium Apartment",
            description: "Discover luxury living at the heart of the city with this stunning Lakeview Premium Apartment, offering breathtaking views of the lake and a modern, high-end finish throughout.",
            location: "Le Marais, Paris, France",
            type: "Apartment",
            price: "450.000",
            bedrooms: "3",
            bathrooms: "2",
            size: "N0 m²",
            furnished: "Fully Furnished",
            parking: "Underground",
            mainImage: "http://example.com/path-to-image.jpg",
            galleryImages: [
                "http://example.com/gallery1.jpg",
                "http://example.com/gallery2.jpg",
                "http://example.com/gallery3.jpg"
            ]
        };
        
        sessionStorage.setItem('currentProperty', JSON.stringify(propertyData));
        window.location.href = "/mary/property-details";
    });
});

// Add this to load property details
document.addEventListener('DOMContentLoaded', function() {
    // Back button functionality
    const backButton = document.getElementById('back-to-properties');
    if (backButton) {
        backButton.addEventListener('click', function() {
            window.history.back();
        });
    }
    
    // Load property details if on details page
    if (document.querySelector('.property-details-page')) {
        loadPropertyDetails();
    }
});

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

function changeImage(src) {
    document.getElementById('mainPreview').src = src;
}

// js for create property modal

function openModal() {
    document.getElementById("propertyModal").style.display = "flex";
  }

function closeModal() {
    document.getElementById("propertyModal").style.display = "none";
}

// Optional: Close modal on outside click
document.addEventListener('click', function (e) {
    const modal = document.getElementById('propertyModal');
    if (e.target === modal) closeModal();
});

