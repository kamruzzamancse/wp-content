<?php
if (!defined('ABSPATH')) exit;
?>

<div class="property-linker-container">

    <h3>🔗 Link New Property</h3>
    
    <div class="search-section">
        <div class="search-box">
            <label for="property-search-input">Search Properties:</label>
            <div class="search-input-group">
                <input type="text" id="property-search-input" 
                       placeholder="e.g., Houston, New York, 77001, 10001">
                <button id="search-property-btn" class="search-btn">
                    <span class="dashicons dashicons-search"></span>
                    Search
                </button>
            </div>
        </div>
    </div>

    <!-- Search Results -->
    <div id="property-search-results" class="search-results" style="display:none;">
        <h4>Search Results</h4>
        <div id="search-results-list" class="results-list"></div>
    </div>

    <!-- Success Message -->
    <div id="link-success" class="success-message" style="display:none;">
        <span class="dashicons dashicons-yes-alt"></span>
        <div>
            <strong>Property linked successfully!</strong>
            <p>It will be automatically updated every month with current market data.</p>
        </div>
    </div>

    <!-- User's Linked Properties -->
    <div class="linked-properties-section">
        <h4>Your Linked Properties</h4>
        <?php echo do_shortcode('[my_properties]'); ?>
    </div>
</div>

<style>
/* ===== Property Linker Page Styling ===== */
.property-linker-container {
  padding: 20px;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  font-family: Arial, sans-serif;
  max-width: 100%;
}

.property-linker-container h3 {
  color: #2271b1;
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 2px solid #f0f0f1;
}

.property-linker-container h4 {
  color: #2271b1;
  margin-bottom: 15px;
}

.search-section {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  border: 1px solid #e9ecef;
}

.search-box label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #333;
}

.search-input-group {
  display: flex;
  gap: 10px;
  align-items: center;
}

#property-search-input {
  flex: 1;
  padding: 12px 15px;
  border: 2px solid #e9ecef;
  border-radius: 6px;
  font-size: 14px;
}

#property-search-input:focus {
  border-color: #007cba;
  outline: none;
}

.search-btn {
  padding: 12px 25px;
  background: #2271b1;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 5px;
  transition: background 0.3s ease;
}

.search-btn:hover {
  background: #135e96;
}

.search-results {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  margin: 20px 0;
  border: 1px solid #ddd;
}

.results-list {
  margin-top: 15px;
}

.property-result-item {
  border: 1px solid #e9ecef;
  padding: 15px;
  margin: 10px 0;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 15px;
  transition: all 0.3s ease;
  background: #fff;
}

.property-result-item:hover {
  border-color: #2271b1;
  box-shadow: 0 2px 8px rgba(34,113,177,0.1);
}

.property-result-item img {
  width: 120px;
  height: 90px;
  object-fit: cover;
  border-radius: 4px;
  border: 1px solid #ddd;
}

.property-info {
  flex: 1;
}

.property-info h4 {
  margin: 0 0 5px 0;
  color: #333;
  font-size: 16px;
}

.property-info p {
  margin: 2px 0;
  color: #666;
  font-size: 14px;
}

.link-property-btn {
  background: #28a745;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.3s ease;
}

.link-property-btn:hover:not(:disabled) {
  background: #218838;
}

.link-property-btn:disabled {
  background: #6c757d;
  cursor: not-allowed;
}

.success-message {
  background: #d4edda;
  border: 1px solid #c3e6cb;
  color: #155724;
  padding: 15px 20px;
  border-radius: 6px;
  margin: 20px 0;
  display: flex;
  align-items: center;
  gap: 10px;
  border-left: 4px solid #28a745;
}

.linked-properties-section {
  margin-top: 30px;
  padding-top: 20px;
  border-top: 2px solid #e9ecef;
}

.loading {
  text-align: center;
  padding: 20px;
  color: #666;
  background: #f8f9fa;
  border-radius: 4px;
}

.error {
  text-align: center;
  padding: 20px;
  color: #dc3545;
  background: #f8d7da;
  border-radius: 4px;
  border: 1px solid #f5c6cb;
}

/* Mobile responsiveness */
@media screen and (max-width: 768px) {
  .property-linker-container {
    padding: 15px;
  }
  
  .search-input-group {
    flex-direction: column;
    align-items: stretch;
  }
  
  #property-search-input {
    width: 100%;
    margin-bottom: 10px;
  }
  
  .search-btn {
    width: 100%;
    justify-content: center;
  }
  
  .property-result-item {
    flex-direction: column;
    text-align: center;
    gap: 10px;
  }
  
  .property-result-item img {
    width: 100%;
    height: 150px;
  }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Search properties
    $('#search-property-btn').click(function() {
        var searchTerm = $('#property-search-input').val().trim();
        if (!searchTerm) {
            alert('Please enter a city name or ZIP code');
            return;
        }
        
        $('#search-results-list').html('<div class="loading">Searching properties...</div>');
        $('#property-search-results').show();
        
        $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
            action: 'real_time_property_search',
            search: searchTerm,
            nonce: '<?php echo wp_create_nonce('property_search_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                $('#search-results-list').html(response.data);
            } else {
                $('#search-results-list').html('<div class="error">' + response.data + '</div>');
            }
        }).fail(function() {
            $('#search-results-list').html('<div class="error">Search failed. Please try again.</div>');
        });
    });

    // Link property
    $(document).on('click', '.link-property-btn', function() {
        var $btn = $(this);
        var propertyId = $btn.data('property-id');
        
        $btn.text('Linking...').prop('disabled', true);
        
        $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
            action: 'simple_link_property',
            property_id: propertyId,
            nonce: '<?php echo wp_create_nonce('property_link_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                $('#link-success').show();
                $('#property-search-results').hide();
                $('#property-search-input').val('');
                
                // Refresh linked properties
                $('.linked-properties-section').load(' .linked-properties-section > *');
                
                // Scroll to success message
                $('html, body').animate({
                    scrollTop: $('#link-success').offset().top - 100
                }, 1000);
            } else {
                alert('Error: ' + response.data);
                $btn.text('Link Property').prop('disabled', false);
            }
        });
    });

    // Enter key support
    $('#property-search-input').on('keypress', function(e) {
        if (e.which === 13) {
            $('#search-property-btn').click();
        }
    });
});
</script>