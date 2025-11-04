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
            var $container = $('.pt-property-list'); // <-- সঠিক container
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