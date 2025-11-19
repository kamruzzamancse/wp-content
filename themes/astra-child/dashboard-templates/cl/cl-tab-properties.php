<?php
    $upload_dir = wp_upload_dir();
    $image_url  = $upload_dir['baseurl'];
?>

<div class="pt-toolbar-container">
    
    <div class="pt-left-section">
            <h1 class="header-title">All Properties</h1>
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
    <div class="pt-property-list">
</div>

<div id="propertyEditModal" class="simple-modal">
    <div class="simple-modal-content">
        
        <h2>Edit Property</h2>

        <form id="propertyEditForm">

            <input type="hidden" name="listing_id" id="edit_listing_id">

            <label>Bedrooms</label>
            <input type="number" name="bedrooms" id="edit_bedrooms">

            <label>Bathrooms</label>
            <input type="number" name="bathrooms" id="edit_bathrooms">

            <label>Square Feet</label>
            <input type="number" name="sqft" id="edit_sqft">

            <label>Rent Price ($)</label>
            <input type="text" name="price" id="edit_price">

            <label>Year Built</label>
            <input type="number" name="year_built" id="edit_year_built">

            <label>Description</label>
            <textarea name="description" id="edit_description"></textarea>

            <!-- Button group wrapper -->
            <div class="button-group">
                <button type="button" id="closeModal" class="simple-modal-close">Cancel</button>
                <button type="submit" class="simple-modal-btn">Update</button>
            </div>

        </form>

    </div>
</div>

<script>
jQuery(document).ready(function($){

    // ==============================
    // OPEN EDIT MODAL
    // ==============================

    $(document).on("click", ".pt-upload-icon", function(){
        var listingId = $(this).data("listing");
        if(!listingId) return;

        $.ajax({
            url: property_edit_vars.ajax_url,
            type: "POST",
            data: {
                action: "get_property_data",
                nonce: property_edit_vars.nonce,
                listing_id: listingId
            },
            success: function(response){
                if(response.success){
                    var p = response.data;
                    $("#edit_listing_id").val(p.listing_id);
                    $("#edit_bedrooms").val(p.bedrooms);
                    $("#edit_bathrooms").val(p.bathrooms);
                    $("#edit_sqft").val(p.sqft);
                    $("#edit_price").val(p.price);
                    $("#edit_year_built").val(p.year_built);
                    $("#edit_description").val(p.description);

                    $("#propertyEditModal").fadeIn(200); // modal open
                } else {
                    alert("Property not found");
                }
            }
        });
    });

    // Close modal
    $("#closeModal").on("click", function(){
        $("#propertyEditModal").fadeOut(200);
    });


    // ==============================
    // SUBMIT EDIT FORM
    // ==============================
    $("#propertyEditForm").on("submit", function(e){
        e.preventDefault();
        $.ajax({
            url: property_edit_vars.ajax_url,
            type: "POST",
            data: $(this).serialize() + "&action=update_property_fields&nonce=" + property_edit_vars.nonce,
            success: function(response){
                if(response.success){
                    alert("Property Updated!");
                    location.reload();
                } else {
                    alert("Update failed");
                }
            },
            error: function(xhr, status, error){
                alert("AJAX submit failed");
            }
        });
    });

});

</script>

<script>
    jQuery(document).ready(function($){
        // Live Search
        $('.pt-search-input').on('keyup', function(){
            var searchTerm = $(this).val().toLowerCase();
            $('.pt-property-item').each(function(){
                var text = $(this).find('h3').text().toLowerCase();
                $(this).toggle(text.indexOf(searchTerm) > -1);
            });
        });

        // Sorting
        $('.pt-sort-select').on('change', function(){
            var sortVal = $(this).val();
            var $container = $('.pt-property-list');
            var $items = $container.find('.pt-property-item');

            var sorted = $items.get().sort(function(a,b){
                var aText = $(a).find('h3').text();
                var bText = $(b).find('h3').text();
                var aPrice = parseFloat($(a).find('.pt-property-details div').eq(0).text().replace(/[^0-9.]/g,'') || 0);
                var bPrice = parseFloat($(b).find('.pt-property-details div').eq(0).text().replace(/[^0-9.]/g,'') || 0);
                var aDate = $(a).data('date') || 0;
                var bDate = $(b).data('date') || 0;

                switch(sortVal){
                    case 'price-asc': return aPrice - bPrice;
                    case 'price-desc': return bPrice - aPrice;
                    case 'name-asc': return aText.localeCompare(bText);
                    case 'name-desc': return bText.localeCompare(aText);
                    case 'date-asc': return aDate - bDate;
                    case 'date-desc': return bDate - aDate;
                    default: return 0;
                }
            });

            // Preserve grid layout
            $.each(sorted, function(idx, itm){
                $container.append(itm);
            });
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

</style>