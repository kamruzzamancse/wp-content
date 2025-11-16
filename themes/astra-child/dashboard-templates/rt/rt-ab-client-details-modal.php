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
                </span>
            </div>
        </div>

        <div class="modal-body">
            <table class="client-details-rt">
                <tr><td>Client Name</td><td id="clientNameCell">—</td></tr>
                <tr><td>Email</td><td id="clientEmailCell">—</td></tr>
                <tr><td>Phone Number</td><td id="clientPhoneCell">—</td></tr>
                <tr><td>Notes</td><td id="clientNotesCell">—</td></tr>
                <tr><td>Status</td><td id="clientStatusCell">—</td></tr>
            </table>

            <h2 class="modal-title" style="margin-bottom: 10px">Associated Properties</h2>
            <div id="clientPropertiesContainer">
                <p>Loading properties...</p>
            </div>
        </div>

        <div class="modal-footer">
            <button class="close-btn" id="closeClientDetailsModal">Close</button>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let currentClientId = null;
    let currentClientData = null;

    const clientModal = document.getElementById('clientDetailsModal');
    const closeModalBtn = document.getElementById('closeClientDetailsModal');
    const propertiesContainer = document.getElementById('clientPropertiesContainer');

    if (closeModalBtn) closeModalBtn.addEventListener('click', () => clientModal.style.display = 'none');
    if (clientModal) clientModal.addEventListener('click', e => { if(e.target === clientModal) clientModal.style.display = 'none'; });

    window.openClientDetailsModal = async function(clientId) {
        if (!clientId) return;
        clientModal.style.display = 'flex';
        showLoadingState();

        try {
            const response = await fetch(rtClientAjax.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'fetch_realtor_client_full_ajax',
                    nonce: rtClientAjax.edit_nonce,
                    client_id: clientId
                })
            });
            const result = await response.json();
            if (!result.success) throw new Error(result.data || 'API returned failure');

            currentClientData = result.data;
            currentClientId = clientId;
            updateClientUI(result.data);

        } catch(error) {
            alert('Error loading client data: ' + error.message);
            showErrorState();
        }
    };

    function showLoadingState() {
        document.getElementById('clientName').textContent = 'Loading...';
        document.getElementById('clientNameCell').textContent = 'Loading...';
        propertiesContainer.innerHTML = '<p>Loading properties...</p>';
    }

    function showErrorState() {
        document.getElementById('clientName').textContent = 'Error Loading';
        document.getElementById('clientNameCell').textContent = 'Error';
        propertiesContainer.innerHTML = '<p>Failed to load properties</p>';
    }

    function updateClientUI(client) {
        document.getElementById('clientAvatar').src = client.profile_picture || (rtClientAjax?.default_avatar || '<?php echo esc_url($image_url . '/2025/08/client-photo.jpg'); ?>');
        document.getElementById('clientName').textContent = client.full_name || '—';
        document.getElementById('clientNameCell').textContent = client.full_name || '—';
        document.getElementById('clientEmailCell').textContent = client.email || '—';
        document.getElementById('clientPhoneCell').textContent = client.phone || '—';
        document.getElementById('clientNotesCell').textContent = client.note || '—';
        document.getElementById('clientStatusCell').textContent = client.status || '—';

        updatePropertiesUI(client.assigned_properties || []);
    }

    function updatePropertiesUI(properties) {
        if (!properties || properties.length === 0) {
            propertiesContainer.innerHTML = '<p>No properties assigned to this client.</p>';
            return;
        }

        let html = '';
        properties.forEach(prop => {
            const price = prop.price ? '$' + numberWithCommas(prop.price) : 'Price not set';
            html += `
                <div class="property-item-modal">
                    <img src="${prop.image_url || '<?php echo esc_url($image_url . '/assets/images/default-property.png'); ?>'}" alt="Property Image" class="main-image client-details-property-details">
                    <div class="property-details">
                        <h3 class="property-title">${prop.address || 'Property Title'}</h3>
                        <div class="property-price">${price}</div>
                        <div class="property-location">${prop.city || ''}, ${prop.state || ''}</div>
                        <div class="property-features">
                            <span class="feature">${prop.bedrooms || '—'} beds</span>
                            <span class="feature">${prop.bathrooms || '—'} baths</span>
                            <span class="feature">${prop.sqft ? numberWithCommas(prop.sqft) : '—'} sqft</span>
                        </div>
                    </div>
                </div>
            `;
        });

        propertiesContainer.innerHTML = html;
    }

    function numberWithCommas(x) {
        return x ? x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") : '0';
    }

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
.property-item-modal {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    border: 1px solid #eee;
    padding: 10px;
    border-radius: 5px;
}
.property-details { flex: 1; }
.main-image.client-details-property-details {
    width: 150px;
    height: 100px;
    object-fit: cover;
    border-radius: 4px;
}
</style>
