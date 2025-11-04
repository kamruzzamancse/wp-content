jQuery(document).ready(function($){
    $(document).on('change', '.property-image-input', function(){
        var listingId = $(this).data('listing-id').toString().trim();
        var file = this.files[0];
        if(!file) return;

        var formData = new FormData();
        formData.append('action','upload_property_image');
        formData.append('nonce', property_image_vars.nonce);
        formData.append('listing_id', listingId);
        formData.append('property_image', file);

        $.ajax({
            url: property_image_vars.ajax_url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response){
                if(response.success){
                    // Image upload success -> reload page
                    location.reload();
                } else {
                    alert('Upload failed: ' + (response.data || 'Unknown error'));
                }
            },
            error: function(xhr, status, error){
                alert('AJAX error: ' + error);
            }
        });
    });
});
