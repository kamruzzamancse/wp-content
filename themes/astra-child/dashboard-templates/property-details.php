<?php
/**
 * Template Name: Property Details
 */

get_header(); ?>

<div class="property-details-page">
    <div class="property-details-header">
        <button id="back-to-properties" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Properties
        </button>
        <h1>Property Details</h1>
    </div>
    
    <div class="property-details-main">
        <div class="property-hero">
            <div class="main-image-container">
                <img id="property-main-image" src="" alt="Property Main Image">
            </div>
            <div class="property-thumbnails"></div>
        </div>
        
        <div class="property-info-section">
            <h2 id="property-title">Lakeview Premium Apartment</h2>
            <p id="property-description">Discover luxury living at the heart of the city with this stunning Lakeview Premium Apartment, offering breathtaking views of the lake and a modern, high-end finish throughout.</p>
            
            <div class="property-details-grid">
                <div class="detail-item">
                    <span class="detail-label">Location</span>
                    <span class="detail-value" id="property-location">Le Marais, Paris, France</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Property Type</span>
                    <span class="detail-value" id="property-type">Apartment</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Price</span>
                    <span class="detail-value" id="property-price">450.000</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Bedrooms</span>
                    <span class="detail-value" id="property-bedrooms">3</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Bathrooms</span>
                    <span class="detail-value" id="property-bathrooms">2</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Property Size</span>
                    <span class="detail-value" id="property-size">N0 m²</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Furnished</span>
                    <span class="detail-value" id="property-furnished">Fully Furnished</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Parking Available</span>
                    <span class="detail-value" id="property-parking">Underground</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>