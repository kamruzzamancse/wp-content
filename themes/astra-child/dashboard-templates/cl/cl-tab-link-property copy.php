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
                       placeholder="e.g., Houston, New York, 77001, 14150 Tomball Pkwy">
                <button id="search-property-btn" class="search-btn">
                    <span class="dashicons dashicons-search"></span>
                    Search
                </button>
            </div>
            <p class="search-hint">You can search by city name, ZIP code, or address</p>
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
  box-shadow: 0 0 0 3px rgba(0, 124, 186, 0.1);
}

.search-btn {
  padding: 12px 25px;
  background: #2271b1;
  color: white!important;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 5px;
  transition: all 0.3s ease;
}

.search-btn:hover {
  background: #135e96;
  transform: translateY(-1px);
}

.search-btn:active {
  transform: scale(0.98);
}

.search-hint {
  margin: 8px 0 0 0;
  font-size: 12px;
  color: #666;
  font-style: italic;
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
  transform: translateY(-2px);
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
  color: white!important;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.3s ease;
  font-weight: 500;
}

.link-property-btn:hover:not(:disabled) {
  background: #218838;
  transform: translateY(-1px);
}

.link-property-btn:active {
  transform: scale(0.98);
}

.link-property-btn:disabled {
  background: #6c757d;
  cursor: not-allowed;
  transform: none;
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
  animation: slideIn 0.3s ease-out;
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
  animation: pulse 1.5s ease-in-out infinite;
}

.error {
  text-align: center;
  padding: 20px;
  color: #dc3545;
  background: #f8d7da;
  border-radius: 4px;
  border: 1px solid #f5c6cb;
}

/* My Properties List Styles */
.my-properties-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 15px;
}

.my-property-item {
  border: 1px solid #e9ecef;
  border-radius: 8px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
}

.my-property-item:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  transform: translateY(-2px);
}

.my-property-item img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  border-bottom: 1px solid #e9ecef;
}

.property-details {
  padding: 15px;
}

.property-details h5 {
  margin: 0 0 8px 0;
  color: #333;
  font-size: 16px;
  line-height: 1.3;
}

.property-details .location {
  color: #666;
  margin: 0 0 10px 0;
  font-size: 14px;
}

.property-stats {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
  font-size: 14px;
  flex-wrap: wrap;
  gap: 5px;
}

.property-stats span {
  background: #f8f9fa;
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: 500;
}

.property-stats span:first-child {
  color: #28a745;
  background: #d4edda;
}

.property-stats span:last-child {
  color: #007cba;
  background: #cce7ff;
}

.property-meta {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: #888;
  border-top: 1px solid #f0f0f1;
  padding-top: 10px;
  margin-top: 10px;
}

.property-meta small {
  display: flex;
  align-items: center;
  gap: 3px;
}

.no-properties-message {
  text-align: center;
  padding: 40px 20px;
  background: #f8f9fa;
  border-radius: 8px;
  border: 2px dashed #dee2e6;
  color: #6c757d;
  font-size: 16px;
  grid-column: 1 / -1;
}

.no-properties-message p {
  margin: 0 0 15px 0;
}

/* Animations */
@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
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
  
  .my-properties-list {
    grid-template-columns: 1fr;
    gap: 15px;
  }
  
  .property-stats {
    flex-direction: column;
    gap: 8px;
  }
  
  .property-meta {
    flex-direction: column;
    gap: 5px;
  }
}

@media screen and (max-width: 480px) {
  .property-linker-container {
    padding: 10px;
  }
  
  .search-section {
    padding: 15px;
  }
  
  .search-results {
    padding: 15px;
  }
  
  .property-result-item {
    padding: 12px;
  }
  
  .my-property-item img {
    height: 150px;
  }
  
  .property-details {
    padding: 12px;
  }
}

/* Focus styles for accessibility */
.link-property-btn:focus,
.search-btn:focus {
  outline: 2px solid #2271b1;
  outline-offset: 2px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Search properties
    $('#search-property-btn').click(function() {
        var searchTerm = $('#property-search-input').val().trim();
        if (!searchTerm) {
            alert('Please enter a city name, ZIP code, or address');
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