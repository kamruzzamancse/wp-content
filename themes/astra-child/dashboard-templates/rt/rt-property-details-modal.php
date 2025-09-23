<!-- Property Details Modal -->
<?php 
    $upload_dir = wp_upload_dir(); 
    $image_url = $upload_dir['baseurl']; 
?>

<div id="propertyDetailsModal" class="property-modal">
    <div class="property-modal-content">
        <span class="close-property-modal">&times;</span>

        <div class="container">
            <div class="left-column">
                <!-- Image Gallery -->
                <div class="image-gallery-container">
                    <div class="thumbnail-gallery">
                        <img src="<?php echo esc_url( $image_url . '/2025/08/lakeview-standard-4.png' ); ?>" 
                            onclick="changeImage(this.src, this)" 
                            alt="Gallery Image 1">
                        <img src="<?php echo esc_url( $image_url . '/2025/08/lakeview-standard-5.png' ); ?>" 
                            onclick="changeImage(this.src, this)" 
                            alt="Gallery Image 2">
                        <img src="<?php echo esc_url( $image_url . '/2025/08/lakeview-standard-6.png' ); ?>" 
                            onclick="changeImage(this.src, this)" 
                            alt="Gallery Image 3">
                    </div>
                    <div class="main-image-container">
                        <img src="<?php echo esc_url( $image_url . '/2025/08/lakeview-standard.png' ); ?>" 
                            id="mainPreview" 
                            class="main-image" 
                            alt="Main Image">
                    </div>
                </div>
                <!-- Property Header -->
                <div class="property-header">
                    <h1 class="property-title">Lakeview Premium Apartment</h1>
                    <p class="property-description">
                        Discover luxury living at the heart of the city with this stunning Lakeview Premium Apartment, 
                        offering breathtaking views of the lake and a modern, high-end finish throughout.
                    </p>
                </div>
                
                <!-- Property Features Grid -->
                <div class="property-features-modal">

                    <!-- Address -->
                    <div class="feature-box">
                        <div class="feature-label"><span class="dashicons dashicons-location-alt"></span> Address</div>
                        <div class="feature-value">Le Marais, Paris, France</div>
                    </div>

                    <!-- Price -->
                    <div class="feature-box" id="price-feature">
                        <div class="feature-label">
                            <span class="dashicons dashicons-admin-site-alt3"></span> Price
                            <select id="price-type-select" style="margin-left:10px;">
                                <option value="sales">Sales</option>
                                <option value="rental">Rental</option>
                            </select>
                            <button class="edit-btn" title="Edit Price">&#9998;</button>
                        </div>
                        <div class="feature-value" id="price-value">450,000</div>
                    </div>

                    <!-- Bedrooms -->
                    <div class="feature-box">
                        <div class="feature-label"><span class="dashicons dashicons-admin-home"></span> Bedrooms</div>
                        <div class="feature-value">3</div>
                    </div>

                    <!-- Bathrooms -->
                    <div class="feature-box">
                        <div class="feature-label"><span class="dashicons dashicons-admin-users"></span> Bathrooms</div>
                        <div class="feature-value">2</div>
                    </div>

                    <!-- Year Built -->
                    <div class="feature-box">
                        <div class="feature-label"><span class="dashicons dashicons-calendar-alt"></span> Year Built</div>
                        <div class="feature-value">2023</div>
                    </div>

                    <!-- Square Footage -->
                    <div class="feature-box">
                        <div class="feature-label"><span class="dashicons dashicons-layout"></span> Square Footage</div>
                        <div class="feature-value">140 m²</div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<style>
/* Property Details Modal Styles */

.edit-btn {
    margin-left: auto;
    background: transparent;
    border: none;
    cursor: pointer;
    font-size: 14px;
    color: #FFF;
    transition: color 0.3s;
    padding: 5px;
}

.edit-btn:hover {
    color: #FFF;
}

#save-price-btn {
    padding: 5px 10px;
    color: #FFF!important;
}

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

.dashicons-location { color: #e74c3c; }
.dashicons-admin-home { color: #3498db; }
.dashicons-money { color: #2ecc71; }
.dashicons-admin-users { color: #9b59b6; }
.dashicons-admin-tools { color: #1abc9c; }
.dashicons-randomize { color: #f39c12; }
.dashicons-admin-site { color: #d35400; }
.dashicons-car { color: #27ae60; }

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('propertyDetailsModal');
    const viewButtons = document.querySelectorAll('.view-details-btn');
    const closeBtn = document.querySelector('.close-property-modal');

    function changeImage(src, clickedElement) {
        document.getElementById('mainPreview').src = src;
        document.querySelectorAll('.thumbnail-gallery img').forEach(thumb => thumb.classList.remove('active'));
        clickedElement.classList.add('active');
    }

    document.querySelectorAll('.thumbnail-gallery img').forEach(thumb => {
        thumb.addEventListener('click', function() { changeImage(this.src, this); });
    });

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            document.documentElement.style.overflow = 'hidden';

            const addressBookModal = document.getElementById('clientDetailsModal');
            if (addressBookModal) addressBookModal.style.display = 'none';

            const firstThumbnail = document.querySelector('.thumbnail-gallery img');
            if (firstThumbnail && !firstThumbnail.classList.contains('active')) firstThumbnail.classList.add('active');
        });
    });

    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        document.documentElement.style.overflow = '';
    });

    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
            document.documentElement.style.overflow = '';
        }
    });

    // Price Editing for Sales & Rental
    const priceFeature = document.getElementById('price-feature');
    const editBtn = priceFeature.querySelector('.edit-btn');
    const priceValueDiv = document.getElementById('price-value');
    const priceSelect = document.getElementById('price-type-select');

    let prices = { sales: 450000, rental: 1600 }; // default values

    editBtn.addEventListener('click', function() {
        const type = priceSelect.value;
        const currentPrice = prices[type];
        priceValueDiv.innerHTML = `
            <input type="number" id="price-input" value="${currentPrice}" style="width: 100px; margin-right:5px;" />
            <button id="save-price-btn">Save</button>
        `;
        const saveBtn = document.getElementById('save-price-btn');
        const priceInput = document.getElementById('price-input');

        saveBtn.addEventListener('click', function() {
            const newPrice = priceInput.value;
            prices[type] = newPrice;
            priceValueDiv.textContent = newPrice;
        });
    });

    // Update displayed price when switching type
    priceSelect.addEventListener('change', function() {
        const type = priceSelect.value;
        priceValueDiv.textContent = prices[type];
    });
});
</script>
