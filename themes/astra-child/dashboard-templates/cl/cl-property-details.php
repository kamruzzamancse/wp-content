<?php 
    $upload_dir = wp_upload_dir(); 
    $image_url = $upload_dir['baseurl']; 
    $site_url = site_url();
?>

<div class="cl-back-link">
    <a href="<?php echo esc_url( $site_url . '/client-dashboard/?tab=properties' ); ?>" class="cl-back-link">
        <span class="cl-header-arrow">←</span>
        <h1 class="header-title">Property Details</h1>
    </a>
</div>

<div class="pd-container">
    <div class="pd-left-column">
        <div class="pd-image-gallery-container">
            <div class="pd-thumbnail-gallery">
                <img src="<?php echo esc_url( $image_url . '/2025/08/lakeview-standard-4.png' ); ?>" onclick="changeImage(this.src)" alt="Gallery Image 1">
                <img src="<?php echo esc_url( $image_url . '/2025/08/lakeview-standard-5.png' ); ?>" onclick="changeImage(this.src)" alt="Gallery Image 2">
                <img src="<?php echo esc_url( $image_url . '/2025/08/lakeview-standard-6.png' ); ?>" onclick="changeImage(this.src)" alt="Gallery Image 3">
            </div>
            <div class="pd-main-image-container">
                <img src="<?php echo esc_url( $image_url . '/2025/08/lakeview-standard.png' ); ?>" id="pd-mainPreview" class="pd-main-image" alt="Main Image">
            </div>
        </div>

        <div class="pd-property-title">Lakeview Standard Apartment</div>
        <div class="pd-property-description">
            Discover luxury living at the heart of the city with this stunning Lakeview Premium Apartment, offering breathtaking views of the lake and a modern, high-end finish throughout.
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
                    <button class="edit-btn" title="Edit Price">&#9998;</button> <!-- small edit icon -->
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


    <div class="pd-right-column">
        <div class="pd-right-box pd-assigned-client">
            <strong style="font-size: 16px; margin-bottom: 10px; display: block;">Realtor</strong>
            <div class="pd-client-name">
                <img style="border-radius: 50%; width:50px; margin-right: 12px" src="<?php echo esc_url( $image_url . '/2025/08/client-photo.jpg' ); ?>" alt="Client Photo">
                Anisur Rahman
            </div>
            <div class="pd-info-row"><span>Phone Number:</span><span>999-888-666</span></div>
            <div class="pd-info-row"><span>Email:</span><span>support.info@gmail.com</span></div>
            <div class="pd-info-row"><span>Address:</span><span>Le Marais, Paris, France</span></div>
            <div class="pd-info-row"><span>Added Date:</span><span>10 June, 2025</span></div>
        </div>
    </div>
</div>

<?php include locate_template('dashboard-templates/rt/rt-property-edit-modal.php'); ?>

<style>

/* Property Features Grid */
.property-features-modal {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin: 30px 0;
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

/* Edit Button */
.edit-btn {
    margin-left: auto;
    background: transparent;
    border: none;
    cursor: pointer;
    font-size: 14px;
    color: #555;
    transition: color 0.3s;
    padding: 5px;
}

.edit-btn:hover {
    color: #2271b1;
}

/* Specific icon colors */
.dashicons-location-alt { color: #e74c3c; }
.dashicons-admin-home { color: #3498db; }
.dashicons-admin-site-alt3 { color: #d35400; }
.dashicons-admin-users { color: #9b59b6; }
.dashicons-calendar-alt { color: #1abc9c; }
.dashicons-layout { color: #f39c12; }

/* Responsive adjustments */
@media (max-width: 1024px) {
    .property-features-modal {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .property-features-modal {
        grid-template-columns: 1fr;
    }
}

/* Responsive adjustments for gallery and columns */
@media (max-width: 768px) {
    .container {
        flex-direction: column;
    }
    
    .image-gallery-container {
        flex-direction: column-reverse;
    }
    
    .thumbnail-gallery {
        flex-direction: row;
        width: 100%;
        overflow-x: auto;
        padding-bottom: 10px;
    }
    
    .property-modal-content {
        padding: 15px;
    }
}
</style>
