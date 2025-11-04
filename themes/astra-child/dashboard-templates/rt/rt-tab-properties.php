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
</div>