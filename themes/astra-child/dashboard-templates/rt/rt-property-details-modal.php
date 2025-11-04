<?php
// URL to the child theme's assets/images folder
$image_url = get_stylesheet_directory_uri();
?>


<div id="propertyDetailsModal" class="property-modal" style="display: none;">
    <div class="property-modal-content">
        <span class="close-property-modal">&times;</span>

        <div class="container">
            <div class="left-column">
                <!-- Image Gallery -->
                <div class="image-gallery-container">
                    <div class="thumbnail-gallery" id="propertyGalleryThumbnails">
                        <!-- Thumbnails will be dynamically added here -->
                    </div>
                    <div class="main-image-container">
                        <img src="<?php echo esc_url($image_url . '/assets/images/default-property.png'); ?>" 
                            id="mainPreview" 
                            class="main-image" 
                            alt="Main Image">
                    </div>
                </div>
                
                <!-- Property Header -->
                <div class="property-header">
                    <h1 class="property-title" id="propertyTitle">Property Title</h1>
                    <p class="property-description" id="propertyDescription">
                        Property description will appear here
                    </p>
                </div>
                
                <!-- Property Features Grid -->
                <div class="property-features-modal">
                    <!-- Address -->
                    <div class="feature-box">
                        <div class="feature-label"><span class="dashicons dashicons-location-alt"></span> Address</div>
                        <div class="feature-value" id="propertyAddress">—</div>
                    </div>

                    <!-- Price -->
                    <div class="feature-box">
                        <div class="feature-label"><span class="dashicons dashicons-admin-site-alt3"></span> Price</div>
                        <div class="feature-value" id="price-value">—</div>
                    </div>

                    <!-- Bedrooms -->
                    <div class="feature-box">
                        <div class="feature-label"><span class="dashicons dashicons-admin-home"></span> Bedrooms</div>
                        <div class="feature-value" id="propertyBedrooms">—</div>
                    </div>

                    <!-- Bathrooms -->
                    <div class="feature-box">
                        <div class="feature-label"><span class="dashicons dashicons-admin-users"></span> Bathrooms</div>
                        <div class="feature-value" id="propertyBathrooms">—</div>
                    </div>

                    <!-- Square Footage -->
                    <div class="feature-box">
                        <div class="feature-label"><span class="dashicons dashicons-layout"></span> Square Footage</div>
                        <div class="feature-value" id="propertySquareFootage">—</div>
                    </div>

                    <!-- Property ID -->
                    <div class="feature-box">
                        <div class="feature-label"><span class="dashicons dashicons-admin-multisite"></span> Property ID</div>
                        <div class="feature-value" id="propertyListingId">—</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    console.log('🏠 Property modal script initializing...');
    
    // Global function to open property modal
    window.openPropertyDetailsModal = function(propertyData) {
        console.log('🎯 openPropertyDetailsModal executed with:', propertyData);
        
        const modal = document.getElementById('propertyDetailsModal');
        if (!modal) {
            console.error('❌ Property modal element not found!');
            return;
        }

        // Show modal
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        document.documentElement.style.overflow = 'hidden';
        
        // Set property details
        document.getElementById('propertyTitle').textContent = propertyData.property_title || 'No Title Available';
        document.getElementById('propertyAddress').textContent = propertyData.property_location || '—';
        document.getElementById('propertyBedrooms').textContent = propertyData.bedrooms || '—';
        document.getElementById('propertyBathrooms').textContent = propertyData.bathrooms || '—';
        document.getElementById('propertySquareFootage').textContent = propertyData.sqft ? numberWithCommas(propertyData.sqft) + ' sqft' : '—';
        document.getElementById('price-value').textContent = propertyData.property_price ? '$' + numberWithCommas(propertyData.property_price) : '—';
        document.getElementById('propertyListingId').textContent = propertyData.property_listing_id || '—';
        
        // Set description
        const description = propertyData.property_description || 
                           `This property located at ${propertyData.property_location || 'unknown location'} features ${propertyData.bedrooms || 'unknown'} bedrooms and ${propertyData.bathrooms || 'unknown'} bathrooms.`;
        document.getElementById('propertyDescription').textContent = description;

        // Handle gallery images
        const galleryContainer = document.getElementById('propertyGalleryThumbnails');
        const mainImage = document.getElementById('mainPreview');
        
        if (galleryContainer) galleryContainer.innerHTML = '';
        
        // Use property image if available
        if (propertyData.property_image_url) {
            mainImage.src = propertyData.property_image_url;
            
            // Create thumbnail
            const thumb = document.createElement('img');
            thumb.src = propertyData.property_image_url;
            thumb.alt = 'Property image';
            
            // Add click event for thumbnail
            thumb.addEventListener('click', function() {
                mainImage.src = propertyData.property_image_url;
                document.querySelectorAll('.thumbnail-gallery img').forEach(img => {
                    img.classList.remove('active');
                });
                this.classList.add('active');
            });
            
            galleryContainer.appendChild(thumb);
        } else {
            // Use default image
            mainImage.src = '<?php echo esc_url($image_url . "/assets/images/default-property.png"); ?>';
        }

        // Activate first thumbnail
        const firstThumbnail = document.querySelector('.thumbnail-gallery img');
        if (firstThumbnail) {
            firstThumbnail.classList.add('active');
        }

        console.log('✅ Property modal populated successfully');
    };

    // Utility function to format numbers
    function numberWithCommas(x) {
        if (!x) return '0';
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // Close modal functionality
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('propertyDetailsModal');
        const closeBtn = document.querySelector('.close-property-modal');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                document.documentElement.style.overflow = '';
            });
        }

        // Close when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                document.documentElement.style.overflow = '';
            }
        });

        // Close with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && modal.style.display === 'block') {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                document.documentElement.style.overflow = '';
            }
        });
    });

    console.log('✅ Property modal system ready');
})();
</script>

<style>
/* Property Details Modal Styles */
.property-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.85);
    z-index: 10000;
    overflow-y: auto;
    padding: 20px;
    box-sizing: border-box;
}

.property-modal-content {
    background-color: #fff;
    border-radius: 10px;
    max-width: 900px;
    margin: 40px auto;
    position: relative;
    padding: 35px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    overflow-y: auto;
    max-height: calc(100vh - 80px);
}

.close-property-modal {
    position: absolute;
    top: 15px;
    right: 25px;
    font-size: 28px;
    font-weight: bold;
    color: #333;
    cursor: pointer;
    transition: color 0.3s;
    z-index: 10001;
}

.close-property-modal:hover {
    color: #2271b1;
}

.container {
    display: flex;
    gap: 30px;
    margin-top: 20px;
}

.left-column {
    flex: 1;
}

.image-gallery-container {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
}

.thumbnail-gallery {
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 80px;
}

.thumbnail-gallery img {
    width: 80px;
    height: 60px;
    object-fit: cover;
    border-radius: 5px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.thumbnail-gallery img:hover,
.thumbnail-gallery img.active {
    border-color: #2271b1;
}

.main-image-container {
    flex: 1;
    position: relative;
}

.main-image {
    width: 100%;
    max-height: 500px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.property-header {
    margin-bottom: 20px;
}

.property-title {
    font-size: 1.375rem!important;
    font-weight: 700;
    color: #222;
    margin: 20px 0 5px 0;
}

.property-description {
    font-size: 16px;
    line-height: 1.6;
    color: #555;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.property-features-modal {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin: 30px 0 30px 0;
}

.feature-box {
    display: flex;
    flex-direction: column;
    padding: 16px;
    background-color: #f5f5f5;
    border-radius: 8px;
    min-height: 70px;
    box-sizing: border-box;
}

.feature-label {
    font-size: 13px;
    color: #666;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.feature-value {
    font-size: 15px;
    font-weight: 500;
    color: #333;
}

/* Dashicons colors */
.dashicons-location-alt { color: #e74c3c; }
.dashicons-admin-site-alt3 { color: #3498db; }
.dashicons-admin-home { color: #2ecc71; }
.dashicons-admin-users { color: #9b59b6; }
.dashicons-layout { color: #1abc9c; }
.dashicons-admin-multisite { color: #f39c12; }

@media (max-width: 1024px) {
    .property-features-modal {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .container { flex-direction: column; }
    .image-gallery-container { flex-direction: column; }
    .thumbnail-gallery { flex-direction: row; width: 100%; overflow-x: auto; padding-bottom: 10px; }
    .property-features-modal { grid-template-columns: 1fr; }
    .property-modal-content { padding: 15px; margin: 20px auto; max-height: calc(100vh - 40px); }
}

@media (max-width: 480px) {
    .property-features-modal { grid-template-columns: 1fr; }
    .property-modal { padding: 0; }
    .property-modal-content { border-radius: 0; margin: 0; min-height: 100vh; max-height: 100vh; }
}
</style>