<?php
global $wpdb;
$table_name = $wpdb->prefix . 'rentcast_properties';

// Get listing ID or numeric ID from URL
$listing_id = isset($_GET['listing_id']) ? sanitize_key($_GET['listing_id']) : '';
$property_id = isset($_GET['id']) ? absint($_GET['id']) : 0;

// Fetch property from DB — either by listing_id or id
$property = null;
if ($property_id) {
    $property = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $property_id));
} elseif ($listing_id) {
    $property = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE listing_id = %s", $listing_id));
}
?>

<?php if ($property): ?>

<?php 
$upload_dir = wp_upload_dir(); 
$image_url  = !empty($property->image_url) ? esc_url($property->image_url) : esc_url($upload_dir['baseurl'] . '/placeholder.png');
$site_url   = site_url();
?>

<div class="cl-back-link">
    <a href="<?php echo esc_url($site_url . '/client-dashboard/?tab=properties'); ?>" class="cl-back-link">
        <span class="cl-header-arrow">←</span>
        <h1 class="header-title"><?php echo esc_html($property->address ?: 'Property Details'); ?></h1>
    </a>
</div>

<div class="pd-container">
    <!-- LEFT COLUMN -->
    <div class="pd-left-column">
        <div class="pd-image-gallery-container">
            <div class="pd-thumbnail-gallery">
                <img src="<?php echo esc_url($image_url); ?>" onclick="changeImage(this.src)" alt="Gallery Image">

                <?php 
                if (!empty($property->photos)) {
                    $photos = is_string($property->photos) ? json_decode($property->photos, true) : $property->photos;
                    if (is_array($photos)) {
                        foreach ($photos as $photo) {
                            if ($photo !== $property->image_url) {
                                echo '<img src="' . esc_url($photo) . '" onclick="changeImage(this.src)" alt="Gallery Image">';
                            }
                        }
                    }
                }
                ?>
            </div>

            <div class="pd-main-image-container">
                <img src="<?php echo esc_url($image_url); ?>" id="pd-mainPreview" class="pd-main-image" alt="Main Image">
            </div>
        </div>

        <div class="pd-property-title"><?php echo esc_html($property->address ?: 'Unknown Address'); ?></div>

        <div class="pd-property-description">
            <?php
                $desc_address   = $property->address ?? 'Unknown Address';
                $desc_city      = $property->city ?? '';
                $desc_state     = $property->state ?? '';
                $desc_zip       = $property->zip ?? '';
                $desc_bedrooms  = $property->bedrooms ?? 0;
                $desc_bathrooms = $property->bathrooms ?? 0;
                $desc_sqft      = $property->sqft ?? 0;
                $desc_price     = $property->price ?? '';

                $description_text = "This beautiful property located at {$desc_address}, {$desc_city}, {$desc_state} {$desc_zip}";

                if($desc_bedrooms > 0 || $desc_bathrooms > 0){
                    $description_text .= " features {$desc_bedrooms} " . ($desc_bedrooms > 1 ? "bedrooms" : "bedroom");
                    $description_text .= " and {$desc_bathrooms} " . ($desc_bathrooms > 1 ? "bathrooms" : "bathroom") . ".";
                } else {
                    $description_text .= ".";
                }

                if($desc_sqft){
                    $description_text .= " It offers approximately {$desc_sqft} square feet of living space.";
                }

                if($desc_price){
                    $description_text .= " Available for rent at $" . number_format((float)$desc_price) . " per month.";
                }

                echo esc_html($description_text);
            ?>
        </div>

        <!-- PROPERTY FEATURES -->
        <div class="property-features-modal">
            <div class="feature-box">
                <div class="feature-label"><span class="dashicons dashicons-location-alt"></span> Address</div>
                <div class="feature-value">
                    <?php echo esc_html("{$property->address}, {$property->city}, {$property->state}"); ?>
                </div>
            </div>

            <div class="feature-box" id="price-feature">
                <div class="feature-label"><span class="dashicons dashicons-money-alt"></span> Rent</div>
                <div class="feature-value" id="price-value">
                    <?php 
                        echo ($property->price ? '$' . number_format((float)$property->price) . '/Month' : 'N/A');
                        
                    ?>
                </div>
            </div>

            <div class="feature-box">
                <div class="feature-label"><span class="dashicons dashicons-admin-site-alt3"></span> Value</div>
                <div class="feature-value"><?php echo esc_html($property->property_value ?? 'N/A'); ?></div>
            </div>

            <div class="feature-box">
                <div class="feature-label"><span class="dashicons dashicons-admin-home"></span> Bedrooms</div>
                <div class="feature-value"><?php echo esc_html($property->bedrooms ?? 'N/A'); ?></div>
            </div>

            <div class="feature-box">
                <div class="feature-label"><span class="dashicons dashicons-admin-users"></span> Bathrooms</div>
                <div class="feature-value"><?php echo esc_html($property->bathrooms ?? 'N/A'); ?></div>
            </div>

            <div class="feature-box">
                <div class="feature-label"><span class="dashicons dashicons-layout"></span> Square Footage</div>
                <div class="feature-value"><?php echo esc_html($property->sqft ?? 'N/A'); ?> m²</div>
            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="pd-right-column">
        <div class="pd-right-box pd-assigned-client">
            <strong style="font-size: 16px; margin-bottom: 10px; display: block;">Realtor</strong>
            <div class="pd-client-name">
                <img style="border-radius: 50%; width:50px; margin-right: 12px" 
                    src="<?php echo esc_url($property->realtor_image ?? $upload_dir['baseurl'] . '/default-user.png'); ?>" 
                    alt="Client Photo">
                <?php echo esc_html($property->realtor_name ?? 'Not Assigned'); ?>
            </div>
            <div class="pd-info-row"><span>Phone Number:</span><span><?php echo esc_html($property->realtor_phone ?? 'N/A'); ?></span></div>
            <div class="pd-info-row"><span>Email:</span><span><?php echo esc_html($property->realtor_email ?? 'N/A'); ?></span></div>
            <div class="pd-info-row"><span>Address:</span><span><?php echo esc_html($property->realtor_address ?? 'N/A'); ?></span></div>
            <div class="pd-info-row"><span>Added Date:</span>
                <span><?php echo esc_html(date('d F, Y', strtotime($property->added_date ?? 'now'))); ?></span>
            </div>
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
    .pd-container {
        flex-direction: column;
    }
    
    .pd-image-gallery-container {
        flex-direction: column-reverse;
    }
    
    .pd-thumbnail-gallery {
        flex-direction: row;
        width: 100%;
        overflow-x: auto;
        padding-bottom: 10px;
    }
    
    .pd-property-title {
        font-size: 18px;
        text-align: center;
    }
}
</style>

<?php else: ?>
<p style="text-align:center; padding:40px; color:red;">Property not found or invalid ID. Please check the URL or contact support.</p>
<?php endif; ?>
