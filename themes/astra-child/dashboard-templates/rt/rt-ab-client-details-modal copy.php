<?php
$upload_dir = wp_upload_dir();
$image_url = $upload_dir['baseurl'];
?>

<div class="modal-overlay-address-book" id="clientDetailsModal" style="display:none;">
    <div class="modal-container">
        <div class="modal-header-realtor">
            <h1 class="header-title" style="margin-bottom: 20px">Client Details</h1>
            <div class="client-profile-container">
                <img class="client-avatar" id="clientAvatar" src="<?php echo esc_url($image_url . '/2025/08/client-photo.jpg'); ?>" alt="Client Photo">
                <span class="client-info">
                    <span class="client-name" id="clientName">Client Name</span><br>
                    <span id="clientCompany">Company / Role</span>
                </span>
            </div>
        </div>

        <div class="modal-body">
            <table class="client-details-rt">
                <tr><td>Client Name</td><td id="clientNameCell">—</td></tr>
                <tr><td>Email</td><td id="clientEmailCell">—</td></tr>
                <tr><td>Phone Number</td><td id="clientPhoneCell">—</td></tr>
                <tr><td>Preferred Location</td><td id="clientPreferredLocationCell">—</td></tr>
                <tr><td>Notes</td><td id="clientNotesCell">—</td></tr>
                <tr><td>Status</td><td id="clientStatusCell">—</td></tr>
            </table>

            <h2 class="modal-title" style="margin-bottom: 10px">Associated Property</h2>
            <div class="property-item-modal">
                <img src="" id="clientPropertyImage" alt="Property Image" class="main-image client-details-property-details">
                <div class="property-details">
                    <h3 class="property-title" id="clientPropertyTitle">No Property Associated</h3>
                    <div class="property-price" id="clientPropertyPrice">—</div>
                    <div class="property-location" id="clientPropertyLocation">—</div>
                    <div class="property-features">
                        <span class="feature" id="clientPropertyBedrooms">— beds</span>
                        <span class="feature" id="clientPropertyBathrooms">— baths</span>
                        <span class="feature" id="clientPropertySqft">— sqft</span>
                    </div>
                    <button class="view-details-btn" id="clientPropertyViewBtn" style="display: none;">View Property Details</button>
                </div>
            </div>

            <div class="upload-documents">
                <button class="cld-upload-btn" data-modal="cl-upload-document-modal">
                    Upload Document
                </button>
            </div>
        </div>

        <div class="modal-footer">
            <button class="close-btn" id="closeClientDetailsModal">Close</button>
        </div>
    </div>
</div>

<?php 
include locate_template('dashboard-templates/rt/rt-property-details-modal.php'); 
include locate_template('dashboard-templates/rt/rt-upload-document-modal.php');
?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    console.log("Upload Document modal logic loaded");

    // ==========================
    // Open Upload Modal
    // ==========================
    document.body.addEventListener("click", function (e) {
        const btn = e.target.closest(".cld-upload-btn");
        if (!btn) return;

        e.preventDefault();
        console.log("Upload Document button clicked");

        const modalId = btn.dataset.modal || "cl-upload-document-modal";
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const clientModal = document.getElementById("clientDetailsModal");
        if (clientModal) clientModal.style.display = "none";

        modal.style.display = "flex";
        modal.classList.add("active");
    });

    // ==========================
    // Close Upload Modal
    // ==========================
    document.body.addEventListener("click", function (e) {
        // Close when click outside
        const activeModal = document.querySelector(".clup-modal-overlay.active");
        if (activeModal && e.target === activeModal) {
            activeModal.classList.remove("active");
            activeModal.style.display = "none";
        }

        // Close on cross button
        if (e.target.classList.contains("clup-close-btn")) {
            const modal = e.target.closest(".clup-modal-overlay");
            if (modal) {
                modal.classList.remove("active");
                modal.style.display = "none";
            }
        }
    });

    // Close on ESC
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") {
            const activeModal = document.querySelector(".clup-modal-overlay.active");
            if (activeModal) {
                activeModal.classList.remove("active");
                activeModal.style.display = "none";
            }
        }
    });

    // ==========================
    // Browse Button & File Select
    // ==========================
    document.body.addEventListener("click", function (e) {
        const browseBtn = e.target.closest(".clup-browse");
        if (!browseBtn) return;

        e.preventDefault();
        const form = browseBtn.closest("form");
        const fileInput = form.querySelector(".clup-file-input");
        const fileNameLabel = form.querySelector("#selected-file-name");

        if (fileInput) fileInput.click();

        // When file selected
        fileInput.addEventListener("change", function () {
            if (fileInput.files.length > 0) {
                fileNameLabel.textContent = "Selected: " + fileInput.files[0].name;
            } else {
                fileNameLabel.textContent = "";
            }
        });
    });

    // ==========================
    // Save Button (Form Submit)
    // ==========================
    const uploadForm = document.getElementById("upload-document-form");
    if (uploadForm) {
        uploadForm.addEventListener("submit", function (e) {
            e.preventDefault();

            console.log("Save clicked (form submitted)");
            // এখানে AJAX বা backend submit logic যাবে
            // Example:
            // const formData = new FormData(uploadForm);
            // fetch("your-ajax-url", { method: "POST", body: formData })

            // For now, just close modal after a short delay
            const modal = uploadForm.closest(".clup-modal-overlay");
            if (modal) {
                setTimeout(() => {
                    modal.classList.remove("active");
                    modal.style.display = "none";
                    uploadForm.reset();
                    const label = uploadForm.querySelector("#selected-file-name");
                    if (label) label.textContent = "";
                    console.log("Modal closed after save");
                }, 500);
            }
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🏠 Client details modal script loaded');
    
    const clientModal = document.getElementById('clientDetailsModal');
    const closeModalBtn = document.getElementById('closeClientDetailsModal');
    let currentClientData = null;

    // Close modal functions
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            clientModal.style.display = 'none';
        });
    }

    if (clientModal) {
        clientModal.addEventListener('click', function(e) { 
            if (e.target === clientModal) {
                clientModal.style.display = 'none'; 
            }
        });
    }

    // View Property Button Event Handler
    function handleViewPropertyClick() {
        console.log('🖱️ View Details button clicked');
        
        if (!currentClientData) {
            console.error('❌ No client data available');
            alert('No client data loaded yet.');
            return;
        }

        // Check if property data exists
        if (!currentClientData.property_listing_id) {
            alert('No property associated with this client.');
            return;
        }

        if (typeof window.openPropertyDetailsModal !== 'function') {
            console.error('❌ Property modal function not available');
            alert('Property details feature is not loaded.');
            return;
        }

        console.log('✅ Opening property modal with data:', currentClientData);
        window.openPropertyDetailsModal(currentClientData);
    }

    // Initialize view button
    function initializeViewButton() {
        const viewPropertyBtn = document.getElementById('clientPropertyViewBtn');
        if (viewPropertyBtn) {
            console.log('✅ View button found');
            
            // Clear existing events and add new one
            viewPropertyBtn.replaceWith(viewPropertyBtn.cloneNode(true));
            const newBtn = document.getElementById('clientPropertyViewBtn');
            newBtn.addEventListener('click', handleViewPropertyClick);
            
        } else {
            console.log('⏳ View button not found');
        }
    }

    // Main function to open client modal
    window.openClientDetailsModal = async function(clientId) {
        console.log('👤 Opening client details for ID:', clientId);
        
        if (!clientId) {
            console.error('❌ No client ID provided');
            return;
        }

        // Show modal and loading state
        clientModal.style.display = 'flex';
        showLoadingState();

        try {
            console.log('📡 Fetching client data...');
            
            const response = await fetch(rtClientAjax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'fetch_realtor_client_ajax',
                    nonce: rtClientAjax.edit_nonce,
                    client_id: clientId
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            console.log('🎯 API Response:', result);
            
            if (!result.success) {
                throw new Error(result.data || 'API returned failure');
            }
            
            const client = result.data;
            console.log('📊 Client data loaded:', client);

            // Store data globally
            currentClientData = client;
            
            // Update UI with client data
            updateClientUI(client);
            
            // Initialize view button
            setTimeout(initializeViewButton, 100);

        } catch(error) {
            console.error('❌ Error loading client data:', error);
            alert('Error loading client data: ' + error.message);
            showErrorState();
        }
    };

    // Show loading state
    function showLoadingState() {
        document.getElementById('clientName').textContent = 'Loading...';
        document.getElementById('clientNameCell').textContent = 'Loading...';
        document.getElementById('clientPropertyTitle').textContent = 'Loading property...';
        document.getElementById('clientPropertyPrice').textContent = '...';
        document.getElementById('clientPropertyViewBtn').style.display = 'none';
    }

    // Show error state
    function showErrorState() {
        document.getElementById('clientName').textContent = 'Error Loading';
        document.getElementById('clientPropertyTitle').textContent = 'Failed to load property';
    }

    // Update UI with client data
    function updateClientUI(client) {
        console.log('🎨 Updating UI with client data');
        
        // Profile info
        document.getElementById('clientAvatar').src = client.profile_picture || (rtClientAjax?.default_avatar || '<?php echo esc_url($image_url . '/2025/08/client-photo.jpg'); ?>');
        document.getElementById('clientName').textContent = client.full_name || '—';
        document.getElementById('clientCompany').textContent = client.company || '—';

        // Client details table - REMOVED BUDGET AND LEAD STATUS
        document.getElementById('clientNameCell').textContent = client.full_name || '—';
        document.getElementById('clientEmailCell').textContent = client.email || '—';
        document.getElementById('clientPhoneCell').textContent = client.phone || '—';
        document.getElementById('clientPreferredLocationCell').textContent = client.preferred_location || '—';
        document.getElementById('clientNotesCell').textContent = client.note || '—';
        document.getElementById('clientStatusCell').textContent = client.status || '—';

        // Property information
        updatePropertyUI(client);
    }

    // Update property section
    function updatePropertyUI(client) {
        const viewBtn = document.getElementById('clientPropertyViewBtn');
        
        // Check if client has associated property
        if (client.property_listing_id && client.property_title) {
            console.log('🏠 Client has associated property');
            
            // Property image
            document.getElementById('clientPropertyImage').src = client.property_image_url || (rtClientAjax?.default_property_image || '<?php echo esc_url($image_url . '/assets/images/default-property.png'); ?>');
            
            // Property details
            document.getElementById('clientPropertyTitle').textContent = client.property_title || 'Property';
            document.getElementById('clientPropertyPrice').textContent = client.property_price ? '$' + numberWithCommas(client.property_price) : 'Price not set';
            document.getElementById('clientPropertyLocation').textContent = client.property_location || 'Location not specified';
            
            // Property features
            document.getElementById('clientPropertyBedrooms').textContent = (client.bedrooms || '—') + ' beds';
            document.getElementById('clientPropertyBathrooms').textContent = (client.bathrooms || '—') + ' baths';
            document.getElementById('clientPropertySqft').textContent = (client.sqft ? numberWithCommas(client.sqft) : '—') + ' sqft';
            
            // Show view button
            viewBtn.style.display = 'block';
            
        } else {
            console.log('❌ No associated property found');
            document.getElementById('clientPropertyTitle').textContent = 'No Property Associated';
            document.getElementById('clientPropertyPrice').textContent = '—';
            document.getElementById('clientPropertyLocation').textContent = '—';
            document.getElementById('clientPropertyImage').src = '<?php echo esc_url($image_url . '/assets/images/default-property.png'); ?>';
            
            // Hide features and view button
            document.getElementById('clientPropertyBedrooms').textContent = '— beds';
            document.getElementById('clientPropertyBathrooms').textContent = '— baths';
            document.getElementById('clientPropertySqft').textContent = '— sqft';
            viewBtn.style.display = 'none';
        }
    }

    // Utility function to format numbers with commas
    function numberWithCommas(x) {
        if (!x) return '0';
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // Initial initialization
    initializeViewButton();
});
</script>

<style>
.modal-overlay-address-book { 
    display: none; 
    align-items: center; 
    justify-content: center; 
    position: fixed; 
    inset: 0; 
    background: rgba(0,0,0,0.5); 
    z-index: 9999; 
}
.modal-container { 
    background: #fff; 
    border-radius: 8px; 
    max-width: 700px; 
    width: 90%; 
    box-shadow: 0 6px 18px rgba(0,0,0,0.12); 
    padding: 25px 30px; 
    max-height: 90vh; 
    overflow-y: auto; 
}
.client-profile-container { 
    display: flex; 
    align-items: center; 
    gap: 15px; 
    margin-bottom: 20px; 
}
.client-avatar { 
    width: 70px; 
    height: 70px; 
    border-radius: 50%; 
    object-fit: cover; 
    border: 3px solid #ddd; 
}
.client-name { 
    font-weight: 600; 
    font-size: 1.2rem; 
}
.client-details-rt { 
    width: 100%; 
    border-collapse: collapse; 
    margin-bottom: 20px; 
}
.client-details-rt td { 
    padding: 8px 12px; 
    border-bottom: 1px solid #eee; 
}
.client-details-rt tr:last-child td { 
    border-bottom: none; 
}
.property-features {
    display: flex;
    gap: 15px;
    margin: 10px 0;
}
.feature {
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.9em;
    color: #666;
}
.view-details-btn {
    background: #28a745;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 10px;
    font-size: 14px;
}
.view-details-btn:hover {
    background: #218838;
}
.upload-documents {
    margin-top: 20px;
    text-align: right;
}
.cld-upload-btn {
    color: #FFF !important;
}
</style>