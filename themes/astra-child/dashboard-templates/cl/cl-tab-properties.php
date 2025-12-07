<?php
if (!defined('ABSPATH')) exit;

$upload_dir = wp_upload_dir();
$image_url  = $upload_dir['baseurl'];
?>

<div class="pt-toolbar-container">
    <h2 class="header-title">🏠 All Properties</h2>

    <div class="pt-right-section">
        <div class="pt-search-box">
            <span class="pt-search-icon">🔍</span>
            <input type="text" class="pt-search-input" placeholder="Search: Property Name" />
        </div>

        <div class="pt-sort-container">
            <select class="pt-sort-select">
                <option value="">Sort by</option>
                <option value="price-asc">Price: Low to High</option>
                <option value="price-desc">Price: High to Low</option>
                <option value="name-asc">Name: A to Z</option>
                <option value="name-desc">Name: Z to A</option>
                <option value="date-asc">Date: Oldest First</option>
                <option value="date-desc">Date: Newest First</option>
            </select>
        </div>
    </div>
</div>

<div class="pt-property-container">
    <?php echo do_shortcode('[rentcast_properties]'); ?>
    <div class="pt-property-item"></div>
</div>

<!-- =================== EDIT PROPERTY MODAL =================== -->
<div id="propertyEditModal" class="simple-modal">
    <div class="simple-modal-content">
        <h2>Edit Property</h2>
        <form id="propertyEditForm">
            <input type="hidden" name="listing_id" id="edit_listing_id">

            <!-- Image -->
            <label>Property Image</label>
            <div class="image-preview-container">
                <img id="edit_image_preview" src="" alt="Property Image" style="max-width:100%;height:auto;margin-bottom:10px;">
            </div>
            <input type="file" name="property_image" id="edit_property_image" data-listing-id="">

            <!-- Price -->
            <label>Rent Price ($)</label>
            <input type="text" name="price" id="edit_price">

            <!-- Property Value -->
            <label>Property Value ($)</label>
            <input type="text" name="property_value" id="edit_property_value">

            <div class="button-group">
                <button type="button" id="closeModal" class="simple-modal-close">Cancel</button>
                <button type="submit" class="simple-modal-btn">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($){
    // Define ajax URL and nonces directly to avoid localization issues
    const ajaxUrl = '<?php echo admin_url("admin-ajax.php"); ?>';
    const editNonce = '<?php echo wp_create_nonce("property_edit_nonce"); ?>';
    const imageNonce = '<?php echo wp_create_nonce("property_image_nonce"); ?>';

    // ===== OPEN MODAL =====
    $(document).on("click", ".pt-upload-icon", function(){
        var listingId = $(this).data("listing");
        if(!listingId) return;

        $.ajax({
            url: ajaxUrl,
            type: "POST",
            data: {
                action: "get_property_data",
                nonce: editNonce,
                listing_id: listingId
            },
            success: function(response){
                if(response.success){
                    var p = response.data;
                    $("#edit_listing_id").val(p.listing_id);
                    $("#edit_price").val(p.price);
                    $("#edit_property_value").val(p.property_value);
                    $("#edit_image_preview").attr('src', p.image_url || "https://placehold.co/500x300?text=No+Image");
                    $("#edit_property_image").data('listing-id', p.listing_id);
                    $("#propertyEditModal").fadeIn(200);
                } else {
                    alert("Property not found");
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
                alert("Error loading property data");
            }
        });
    });

    // ===== CLOSE MODAL =====
    $("#closeModal").on("click", function(){
        $("#propertyEditModal").fadeOut(200);
    });

    // Close modal when clicking outside
    $(document).on("click", function(e){
        if ($(e.target).hasClass("simple-modal")) {
            $("#propertyEditModal").fadeOut(200);
        }
    });

    // ===== UPDATE PRICE & PROPERTY VALUE =====
    $("#propertyEditForm").on("submit", function(e){
        e.preventDefault();
        var listingId = $("#edit_listing_id").val();
        var price = $("#edit_price").val();
        var propertyValue = $("#edit_property_value").val();

        // Basic validation
        if(!price || !propertyValue) {
            alert("Please fill in all fields");
            return;
        }

        $.ajax({
            url: ajaxUrl,
            type: "POST",
            data: {
                action: "update_property_fields",
                nonce: editNonce,
                listing_id: listingId,
                price: price,
                property_value: propertyValue
            },
            success: function(response){
                if(response.success){
                    // Update property list dynamically - FIXED SELECTOR
                    var $item = $("#property-item-" + listingId);
                    if($item.length){
                        // Updated selectors for new HTML structure
                        var rentText = "Rent: $" + parseFloat(price).toLocaleString() + "/month";
                        var valueText = "Value: $" + parseFloat(propertyValue).toLocaleString();
                        
                        $item.find(".property-stats span:first-child").text(rentText);
                        $item.find(".property-stats span:last-child").text(valueText);
                    }
                    alert("Property Updated!");
                    $("#propertyEditModal").fadeOut(200);
                } else {
                    alert("Update failed: " + (response.data || "Unknown error"));
                }
            },
            error: function(xhr, status, error){
                console.error("Update Error:", error);
                alert("Update failed - please try again");
            }
        });
    });

    // ===== IMAGE UPLOAD - MAXIMUM FORMAT SUPPORT =====
    $("#edit_property_image").on('change', function(){
        var listingId = $(this).data('listing-id');
        var file = this.files[0];
        
        if(!file) {
            return;
        }

        // Maximum supported image formats
        var validTypes = [
            'image/jpeg', 
            'image/jpg', 
            'image/png', 
            'image/gif',
            'image/webp',
            'image/bmp',
            'image/svg+xml',
            'image/tiff',
            'image/x-icon',
            'image/vnd.microsoft.icon',
            'image/avif',
            'image/heic',
            'image/heif'
        ];

        // Also check by file extension for broader support
        var fileName = file.name.toLowerCase();
        var validExtensions = [
            '.jpg', '.jpeg', '.png', '.gif', '.webp', 
            '.bmp', '.svg', '.tiff', '.tif', '.ico',
            '.avif', '.heic', '.heif'
        ];

        var hasValidExtension = validExtensions.some(ext => fileName.endsWith(ext));
        var hasValidType = validTypes.indexOf(file.type) > -1;

        if(!hasValidType && !hasValidExtension) {
            alert("Please select a valid image file (JPEG, JPG, PNG, GIF, WEBP, BMP, SVG, TIFF, ICO, AVIF, HEIC, HEIF)");
            $(this).val(''); // Clear the file input
            return;
        }

        // Validate file size (max 10MB for larger formats)
        if(file.size > 10 * 1024 * 1024) {
            alert("Image size should be less than 10MB");
            $(this).val('');
            return;
        }

        // Show loading state on preview image
        var $previewImg = $("#edit_image_preview");
        var originalSrc = $previewImg.attr('src');
        $previewImg.css('opacity', '0.6');

        var formData = new FormData();
        formData.append('action', 'upload_property_image');
        formData.append('nonce', imageNonce);
        formData.append('listing_id', listingId);
        formData.append('property_image', file);

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response){
                // Restore image opacity
                $previewImg.css('opacity', '1');
                
                if(response.success){
                    var newImageUrl = response.data + "?t=" + new Date().getTime();
                    
                    // Update preview image in modal
                    $previewImg.attr('src', newImageUrl);
                    
                    // Update property list image
                    var $itemImg = $("#property-img-" + listingId);
                    if($itemImg.length){
                        $itemImg.attr('src', newImageUrl);
                    }
                    
                    // Clear the file input for potential re-upload
                    $("#edit_property_image").val('');
                    
                } else {
                    // Silent failure - just clear the input
                    $("#edit_property_image").val('');
                }
            },
            error: function(xhr, status, error){
                // Restore image opacity
                $previewImg.css('opacity', '1');
                // Silent failure - just clear the input
                $("#edit_property_image").val('');
            }
        });
    });

    // ===== SEARCH FUNCTIONALITY - FIXED =====
    $('.pt-search-input').on('keyup', function(){
        var searchTerm = $(this).val().toLowerCase().trim();
        var $items = $('.pt-property-item');
        
        if(searchTerm === '') {
            $items.show();
            return;
        }

        $items.each(function(){
            var $item = $(this);
            // Search in property address (h3 text)
            var propertyAddress = $item.find('h3').text().toLowerCase();
            // Search in location (p.location text)
            var propertyLocation = $item.find('.location').text().toLowerCase();
            
            var matchesAddress = propertyAddress.indexOf(searchTerm) > -1;
            var matchesLocation = propertyLocation.indexOf(searchTerm) > -1;
            
            $item.toggle(matchesAddress || matchesLocation);
        });
    });

    // ===== SORT FUNCTIONALITY - FIXED =====
    $('.pt-sort-select').on('change', function(){
        var sortVal = $(this).val();
        if(!sortVal) return;

        var $container = $('.pt-property-container');
        var $items = $container.find('.pt-property-item').get();

        var sorted = $items.sort(function(a, b){
            var $a = $(a);
            var $b = $(b);
            
            // Get property address for name sorting
            var aText = $a.find('h3').text();
            var bText = $b.find('h3').text();
            
            // Get rent price for price sorting
            var aRentText = $a.find('.property-stats span:first-child').text();
            var bRentText = $b.find('.property-stats span:first-child').text();
            
            var aPrice = parseFloat(aRentText.replace(/[^0-9.]/g,'') || 0);
            var bPrice = parseFloat(bRentText.replace(/[^0-9.]/g,'') || 0);
            
            // Get linked date for date sorting
            var aDateText = $a.find('.property-meta small:first-child').text();
            var bDateText = $b.find('.property-meta small:first-child').text();
            
            // Extract date from text like "Linked: Jan 15, 2024"
            var aDateMatch = aDateText.match(/Linked:\s*(.+)/);
            var bDateMatch = bDateText.match(/Linked:\s*(.+)/);
            
            var aDate = aDateMatch ? new Date(aDateMatch[1]) : new Date(0);
            var bDate = bDateMatch ? new Date(bDateMatch[1]) : new Date(0);

            switch(sortVal){
                case 'price-asc': 
                    return aPrice - bPrice;
                case 'price-desc': 
                    return bPrice - aPrice;
                case 'name-asc': 
                    return aText.localeCompare(bText);
                case 'name-desc': 
                    return bText.localeCompare(aText);
                case 'date-asc': 
                    return aDate - bDate;
                case 'date-desc': 
                    return bDate - aDate;
                default: 
                    return 0;
            }
        });

        // Re-append sorted items
        $container.empty().append(sorted);
    });

    // Clear search when sort changes
    $('.pt-sort-select').on('change', function(){
        $('.pt-search-input').val('').trigger('keyup');
    });

});
</script>

<style>
/* ==========================
   Modal Overlay
========================== */
.simple-modal {
    display: none; /* hidden by default */
    position: fixed;
    z-index: 99999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.4);

    /* Flexbox for centering */
    justify-content: center;
    align-items: center;
}

#propertyEditModal {
    display: none; /* hidden by default */
    position: fixed;
    z-index: 99999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.4);
    justify-content: center;     /* horizontal center */
    align-items: center;         /* vertical center */
}

.simple-modal-content {
    background: #fff;
    padding: 30px 40px;
    width: 600px;       /* modal width */
    max-width: 90%;     /* responsive for small screens */
    border-radius: 6px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    
    /* Prevent modal content from stretching full width */
    box-sizing: border-box;
    margin: 0 auto;     
}

/* ==========================
   Form Layout
========================== */
.simple-modal-content form {
    display: flex;
    flex-direction: column; /* labels + inputs vertical */
    gap: 5px; /* spacing between fields */
}

/* Labels left aligned */
.simple-modal-content label {
    text-align: left;
    font-weight: 500;
    display: block;
}

/* Inputs and textarea full width */
.simple-modal-content input,
.simple-modal-content textarea {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

/* Textarea height */
.simple-modal-content textarea {
    min-height: 80px;
    resize: vertical;
}

/* ==========================
   Button Group
========================== */
.simple-modal-content .button-group {
    display: flex;
    justify-content: flex-end; /* right align buttons */
    gap: 10px; /* spacing between buttons */
    margin-top: 10px;
}

/* Buttons styling */
.simple-modal-btn,
.simple-modal-close {
    padding: 8px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.simple-modal-btn {
    background-color: #0073aa;
    color: #fff;
}

.simple-modal-close {
    background-color: #777;
    color: #fff;
}

/* ==========================
   Responsive adjustments
========================== */
@media (max-width: 650px) {
    .simple-modal-content {
        width: 90%; /* smaller screens */
        padding: 20px;
    }
}

/**************************************/

/* ==========================
   Property Details Design
========================== */
.pt-property-details {
    padding: 24px;
}

.pt-property-details h3 {
    margin: 0 0 12px 0;
    font-size: 20px;
    font-weight: 700;
    color: #2c3e50;
    line-height: 1.4;
}

.pt-property-details h3 a {
    color: #2c3e50;
    text-decoration: none;
    transition: color 0.3s ease;
}

.pt-property-details h3 a:hover {
    color: #3498db;
}

.pt-property-details .location {
    margin: 0 0 16px 0;
    color: #7f8c8d;
    font-size: 15px;
    line-height: 1.4;
    font-weight: 500;
}

.property-stats {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 16px;
}

.property-stats span {
    font-size: 15px;
    font-weight: 600;
    line-height: 1.4;
    padding: 8px 0;
}

.property-stats span:first-child {
    color: #27ae60;
    border-bottom: 1px solid #f0f0f0;
}

.property-stats span:last-child {
    color: #3498db;
}

.property-meta {
    display: flex;
    gap: 20px;
    font-size: 13px;
    color: #95a5a6;
    border-top: 1px solid #f0f0f0;
    padding-top: 16px;
}

.property-meta small {
    font-style: italic;
    display: flex;
    align-items: center;
}

.property-meta small:before {
    content: "•";
    margin-right: 5px;
    color: #bdc3c7;
}

.property-meta small:first-child:before {
    content: "";
    margin-right: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .pt-property-details {
        padding: 20px;
    }
    
    .pt-property-details h3 {
        font-size: 18px;
        margin-bottom: 10px;
    }
    
    .pt-property-details .location {
        font-size: 14px;
        margin-bottom: 14px;
    }
    
    .property-stats {
        gap: 6px;
        margin-bottom: 14px;
    }
    
    .property-stats span {
        font-size: 14px;
        padding: 6px 0;
    }
    
    .property-meta {
        flex-direction: column;
        gap: 8px;
        padding-top: 14px;
    }
    
    .property-meta small:before {
        content: "";
        margin-right: 0;
    }
}

@media (max-width: 480px) {
    .pt-property-details {
        padding: 18px;
    }
    
    .pt-property-details h3 {
        font-size: 17px;
    }
    
    .property-stats {
        flex-direction: column;
    }
    
    .property-meta {
        font-size: 12px;
    }
}

/* Optional: Add some icons for better visual */
.property-stats span:first-child {
    position: relative;
    padding-left: 24px;
}

.property-stats span:first-child:before {
    content: "💰";
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
}

.property-stats span:last-child {
    position: relative;
    padding-left: 24px;
}

.property-stats span:last-child:before {
    content: "🏠";
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
}

.property-meta small:first-child {
    position: relative;
    padding-left: 20px;
}

.property-meta small:first-child:before {
    content: "📅";
    position: absolute;
    left: 0;
    font-style: normal;
}

.property-meta small:last-child {
    position: relative;
    padding-left: 20px;
}

.property-meta small:last-child:before {
    content: "🔄";
    position: absolute;
    left: 0;
    font-style: normal;
}

.pt-property-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.pt-property-item {
    flex: 1 1 300px; /* grow/shrink, base width 300px */
    max-width: 350px;
}
</style>