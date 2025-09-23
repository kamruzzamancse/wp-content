jQuery(document).on('change', '.property-image-input', function(){
    var listingId = jQuery(this).data('listing-id');
    var file = this.files[0];
    if(!file) return;

    var formData = new FormData();
    formData.append('action','upload_property_image');
    formData.append('nonce', property_image_vars.nonce);
    formData.append('listing_id', listingId);
    formData.append('property_image', file);

    var imgElement = jQuery('#property-img-' + listingId);

    // Optional: show temporary loader
    imgElement.css('opacity', '0.5');

    jQuery.ajax({
        url: property_image_vars.ajax_url,
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response){
            if(response.success){
                // Add timestamp to force reload
                imgElement.attr('src', response.data.url + '?t=' + new Date().getTime());
            } else {
                alert('Upload failed: ' + (response.data || 'Unknown error'));
            }
        },
        complete: function(){
            // Restore opacity
            imgElement.css('opacity', '1');
        }
    });
});
