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
                <tr><td>Notes</td><td id="clientNotesCell">—</td></tr>
                <tr><td>Date of Birth</td><td id="clientDobCell">—</td></tr>
                <tr><td>House Closing Date</td><td id="clientHouseClosingCell">—</td></tr>
            </table>

            <h2 class="modal-title" style="margin-bottom: 10px">Client Information</h2>
            <div class="property-item-modal">
                <img src="<?php echo esc_url($image_url . '/2025/08/lakeview-standard.png'); ?>" id="clientPropertyImage" alt="Property Image" class="main-image client-details-property-details">
                <div class="property-details">
                    <h3 class="property-title" id="clientPropertyTitle">Property Title</h3>
                    <div class="property-price" id="clientPropertyPrice">$0</div>
                    <div class="property-location" id="clientPropertyLocation">
                        <span class="dashicons dashicons-location"></span> Location
                    </div>
                    <div class="gallery" id="clientPropertyGallery">
                        <!-- JS can dynamically insert gallery images here -->
                    </div>
                    <button class="view-details-btn" id="clientPropertyViewBtn">View Details</button>
                </div>
            </div>

            <div class="upload-documents">
                <button class="cld-upload-btn" data-modal="cl-upload-document-modal">
                    Upload Document <span class="dashicons dashicons-media-document"></span>
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

<style>
.modal-overlay-address-book { display:none; align-items:center; justify-content:center; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; }
.modal-container { background:#fff; border-radius:8px; max-width:700px; width:90%; box-shadow:0 6px 18px rgba(0,0,0,0.12); padding:25px 30px; max-height:90vh; overflow-y:auto; }
.client-profile-container { display:flex; align-items:center; gap:15px; margin-bottom:20px; }
.client-avatar { width:70px; height:70px; border-radius:50%; object-fit:cover; border:3px solid #ddd; }
.client-name { font-weight:600; font-size:1.2rem; }
.client-details-rt { width:100%; border-collapse:collapse; margin-bottom:20px; }
.client-details-rt td { padding:6px 10px; border-bottom:1px solid #eee; }
.upload-documents { margin-top:20px; }
.cld-upload-btn { display:inline-flex; align-items:center; gap:8px; padding:10px 16px; background-color:#007bff; color:#fff!important; border:none; border-radius:6px; font-size:14px; font-weight:500; cursor:pointer; transition:0.3s; }
.cld-upload-btn:hover { background-color:#155ab6; transform:scale(1.02); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const clientModal = document.getElementById('clientDetailsModal');
    const closeBtn = document.getElementById('closeClientDetailsModal');
    const uploadBtn = clientModal.querySelector('.cld-upload-btn');

    // Close modal
    if(closeBtn && clientModal){
        closeBtn.addEventListener('click', () => clientModal.style.display='none');
        clientModal.addEventListener('click', e => { if(e.target === clientModal) clientModal.style.display='none'; });
    }

    // Open upload modal and hide client modal
    if(uploadBtn){
        const uploadModal = document.getElementById('cl-upload-document-modal');
        uploadBtn.addEventListener('click', function(){
            if(clientModal) clientModal.style.display='none';
            if(uploadModal) uploadModal.classList.add('show');
        });
    }
});

// Function to populate client modal dynamically
function populateClientDetails(client) {
    const modal = document.getElementById('clientDetailsModal');
    if(!modal) return;

    modal.querySelector('#clientAvatar').src = client.profile_picture || '<?php echo esc_url($image_url . "/2025/08/client-photo.jpg"); ?>';
    modal.querySelector('#clientName').textContent = client.full_name || '';
    modal.querySelector('#clientCompany').textContent = client.company_name || '';
    modal.querySelector('#clientNameCell').textContent = client.full_name || '';
    modal.querySelector('#clientEmailCell').textContent = client.email || '';
    modal.querySelector('#clientPhoneCell').textContent = client.phone || '';
    modal.querySelector('#clientNotesCell').textContent = client.note || '';
    modal.querySelector('#clientDobCell').textContent = client.date_of_birth || '';
    modal.querySelector('#clientHouseClosingCell').textContent = client.house_closing_date || '';

    // Property info
    modal.querySelector('#clientPropertyImage').src = client.property_image || '<?php echo esc_url($image_url . "/2025/08/lakeview-standard.png"); ?>';
    modal.querySelector('#clientPropertyTitle').textContent = client.property_title || '';
    modal.querySelector('#clientPropertyPrice').textContent = client.property_price || '';
    modal.querySelector('#clientPropertyLocation').textContent = client.property_location || '';
    
    // Optionally populate gallery
    const gallery = modal.querySelector('#clientPropertyGallery');
    gallery.innerHTML = '';
    if(client.property_gallery && client.property_gallery.length){
        client.property_gallery.forEach(img => {
            const image = document.createElement('img');
            image.src = img;
            gallery.appendChild(image);
        });
    }

    modal.style.display = 'flex';
}
</script>
