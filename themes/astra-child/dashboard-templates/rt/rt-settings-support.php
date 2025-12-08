<div class="back-link">
    <a href="?tab=settings" class="pd-back-link">
        <span class="pd-back-link__arrow">←</span>
        <h1 class="header-title">⚙️ Settings</h1>
    </a>
</div>

<div class="ss-contact-form-container">
    <h2 class="ss-contact-form-title">✉️ Send Us a Message</h2>
    
    <form id="ss-contact-form">
        <div class="ss-form-row">
            <div class="ss-form-group">
                <label class="ss-form-label">Name</label>
                <input type="text" name="name" placeholder="Enter name" class="ss-form-input" required>
            </div>
            <div class="ss-form-group">
                <label class="ss-form-label">Email</label>
                <input type="email" name="email" placeholder="Enter Email" class="ss-form-input" required>
            </div>
        </div>

        <div class="ss-form-group">
            <label class="ss-form-label">Phone</label>
            <input type="tel" name="phone" placeholder="Enter phone" class="ss-form-input">
        </div>

        <div class="ss-form-group">
            <label class="ss-form-label">Message</label>
            <textarea name="message" placeholder="Message" class="ss-form-textarea" required></textarea>
        </div>

        <div class="ss-submit-wrapper">
            <button type="submit" class="ss-submit-button">Send Us</button>
        </div>
    </form>
    <div id="ss-contact-result"></div>

</div>

<script>
jQuery(document).ready(function($) {
    $('#ss-contact-form').on('submit', function(e) {
        e.preventDefault();

        var formData = {
            action: 'ss_send_support_message',
            nonce: '<?php echo wp_create_nonce("ss_support_nonce"); ?>',
            name: $('input[name="name"]').val(),
            email: $('input[name="email"]').val(),
            phone: $('input[name="phone"]').val(),
            message: $('textarea[name="message"]').val()
        };

        $('#ss-contact-result').html('<p style="color:#555;">Sending message...</p>');

        $.post('<?php echo admin_url("admin-ajax.php"); ?>', formData, function(response) {
            if (response.success) {
                $('#ss-contact-result').html('<p style="color:green;">✅ ' + response.data + '</p>');
                $('#ss-contact-form')[0].reset();
            } else {
                $('#ss-contact-result').html('<p style="color:red;">❌ ' + response.data + '</p>');
            }
        });
    });
});
</script>

<style>
.ss-contact-form-container {
    max-width: 700px;
    padding: 25px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    font-family: Arial, sans-serif;
}

.ss-contact-form-title {
    font-size: 1.375rem!important;
    font-weight: bold;
    margin-bottom: 25px;
    color: #333;
}

.ss-form-row {
    display: flex;
    gap: 20px;
}

.ss-form-group {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.ss-form-label {
    font-size: 14px;
    font-weight: bold;
    margin-top: 15px;
    margin-bottom: 5px;
    color: #555;
}

.ss-form-input {
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    width: 100%;
    box-sizing: border-box;
}

.ss-form-textarea {
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    min-height: 120px;
    resize: vertical;
    width: 100%;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

.ss-contact-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.ss-submit-wrapper {
    text-align: right;
    margin-top: 15px;
}

/* Force style for the button */
.ss-submit-button {
    background-color: #3498db !important; /* 🔹 Force background color */
    color: #ffffff !important;            /* 🔹 Force text color white */
    border: none !important;
    padding: 12px 28px;
    border-radius: 6px !important;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: inline-block;
    transition: all 0.3s ease;
    text-transform: none;
    box-shadow: 0 2px 6px rgba(52, 152, 219, 0.25);
}

.ss-submit-button:hover {
    background-color: #2c82c9 !important; /* 🔹 Slightly darker on hover */
    color: #ffffff !important;
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(52, 152, 219, 0.3);
}

/* Responsive adjustments */
@media (max-width: 600px) {
    .ss-form-row {
        flex-direction: column;
        gap: 20px;
    }
    
    .ss-contact-form-container {
        padding: 20px;
    }
}
</style>