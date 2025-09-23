<div class="back-link">
    <a href="?tab=settings" class="pd-back-link">
        <span class="pd-back-link__arrow">←</span>
        <h1 class="header-title">Settings</h1>
    </a>
</div>

<div class="sup-password-form-container">
    <h2 class="sup-form-title">Update Password</h2>
    
    <form class="sup-password-form">
        <div class="sup-form-group">
            <label class="sup-form-label">Enter old password</label>
            <input type="password" class="sup-form-input" placeholder="Enter old password">
        </div>
        
        <div class="sup-form-group">
            <label class="sup-form-label">New Password</label>
            <input type="password" class="sup-form-input" placeholder="Enter new Password">
        </div>
        
        <div class="sup-form-group">
            <label class="sup-form-label">Confirm Password</label>
            <input type="password" class="sup-form-input" placeholder="Confirm New Password">
        </div>
        
        <div class="sup-form-footer">
            <a href="#" class="sup-forget-link" id="sup-forget-trigger">Forget password?</a>
            <button type="submit" class="sup-submit-button">Update Password</button>
        </div>
    </form>
</div>

<style>
.sup-password-form-container {
    max-width: 700px;
    padding: 25px;
    background-color: #fff;
    border-radius: 8px;
}

.sup-form-title {
    font-size: 1.375rem!important;
    font-weight: bold;
    margin-bottom: 25px;
    color: #333;
}

.sup-password-form {
    display: flex;
    flex-direction: column;
}

.sup-form-group {
    display: flex;
    flex-direction: column;
}

.sup-form-label {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 8px;
    color: #555;
}

.sup-form-input {
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    width: 100%;
    box-sizing: border-box;
}

.sup-form-input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.sup-form-footer {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
}

.sup-forget-link {
    color: #3498db;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s;
}

.sup-forget-link:hover {
    color: #2980b9;
    text-decoration: underline;
}

.sup-submit-button {
    background-color: #3498db;
    color: #FFF!important;
    border: none;
    padding: 12px 25px;
    border-radius: 4px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s;
}

.sup-submit-button:hover {
    background-color: #2980b9;
}

/* Responsive adjustments */
@media (max-width: 600px) {
    .sup-form-footer {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .sup-password-form-container {
        padding: 20px;
    }
}
</style>



<!-- Forgot Password Modal -->
<div class="sup-modal" id="sup-forgot-modal">
    <div class="sup-modal-content">
        <div class="sup-modal-header">
            <h2 class="sup-modal-title">Forgot Password</h2>
        </div>
        <div class="sup-modal-body">
            <p class="sup-modal-text">Please enter your email address to reset your password</p>
            <div class="sup-form-group">
                <label class="sup-form-label">Email</label>
                <input type="email" class="sup-form-input" placeholder="Enter Your Email" id="sup-email-input">
            </div>
        </div>
        <div class="sup-modal-footer">
            <button type="button" class="sup-modal-button sup-modal-button-primary" id="sup-send-otp">Send OTP</button>
        </div>
    </div>
</div>

<!-- Verify Email Modal -->
<div class="sup-modal" id="sup-verify-modal">
    <div class="sup-modal-content">
        <div class="sup-modal-header">
            <button class="sup-back-button" id="sup-back-to-forgot">←</button>
            <h2 class="sup-modal-title">Verify Email</h2>
        </div>
        <div class="sup-modal-body">
            <p class="sup-modal-text">Please enter the OTP we have sent you in your email.</p>
            
            <div class="sup-otp-container">
                <input type="text" class="sup-otp-input" maxlength="1" pattern="\d" inputmode="numeric">
                <input type="text" class="sup-otp-input" maxlength="1" pattern="\d" inputmode="numeric">
                <input type="text" class="sup-otp-input" maxlength="1" pattern="\d" inputmode="numeric">
                <input type="text" class="sup-otp-input" maxlength="1" pattern="\d" inputmode="numeric">
            </div>
            
            <div class="sup-resend-container">
                <span class="sup-resend-text">Didn't receive the code?</span>
                <button type="button" class="sup-resend-button" id="sup-resend-otp">Resend</button>
            </div>
        </div>
        <div class="sup-modal-footer">
            <button type="button" class="sup-modal-button sup-modal-button-primary" id="sup-verify-otp">Verify</button>
        </div>
    </div>
</div>

<style>
/* Modal Styles */
.sup-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

.sup-modal-content {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    width: 100%;
    max-width: 450px;
    overflow: hidden;
    animation: sup-fadeIn 0.3s ease;
}

@keyframes sup-fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.sup-modal-header {
    padding: 20px 25px;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
}

.sup-modal-title {
    font-size: 22px;
    font-weight: bold;
    margin: 0;
    color: #333;
}

.sup-back-button {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    margin-right: 15px;
    color: #3498db;
    padding: 0;
    line-height: 1;
}

.sup-modal-body {
    padding: 25px;
}

.sup-modal-text {
    font-size: 15px;
    color: #555;
    margin-bottom: 20px;
    line-height: 1.5;
}

/* Form Styles */
.sup-form-group {
    margin-bottom: 20px;
}

.sup-form-label {
    display: block;
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 8px;
    color: #555;
}

.sup-form-input {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

/* OTP Styles */
.sup-otp-container {
    display: flex;
    justify-content: space-between;
    margin: 25px 0;
    gap: 15px;
}

.sup-otp-input {
    width: 60px;
    height: 60px;
    text-align: center;
    font-size: 24px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.sup-otp-input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

/* Resend Link */
.sup-resend-container {
    text-align: center;
    margin-bottom: 15px;
}

.sup-resend-text {
    font-size: 14px;
    color: #666;
    margin-right: 5px;
}

.sup-resend-button {
    background: none;
    border: none;
    color: #3498db;
    cursor: pointer;
    font-size: 14px;
    padding: 0;
}

.sup-resend-button:hover {
    text-decoration: underline;
}

/* Footer Styles */
.sup-modal-footer {
    padding: 15px 25px;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: flex-end;
}

.sup-modal-button {
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    border: 1px solid #ddd;
    background-color: #f8f9fa;
    color: #333;
}

.sup-modal-button:hover {
    color: #FFF!important;
}

.sup-modal-button-primary {
    background-color: #3498db;
    color: #FFF!important;
    border-color: #3498db;
    margin-left: 10px;
}

.sup-modal-button-primary:hover {
    background-color: #2980b9;
    border-color: #2980b9;
}

/* Responsive */
@media (max-width: 480px) {
    .sup-otp-container {
        gap: 10px;
    }
    .sup-otp-input {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Modal elements
    const forgotModal = $('#sup-forgot-modal');
    const verifyModal = $('#sup-verify-modal');
    const sendOtpBtn = $('#sup-send-otp');
    const backButton = $('#sup-back-to-forgot');
    const resendOtpBtn = $('#sup-resend-otp');
    const verifyOtpBtn = $('#sup-verify-otp');
    const emailInput = $('#sup-email-input');
    const otpInputs = $('.sup-otp-input');

    // Show Forgot Password modal
    $(document).on('click', '#sup-forget-trigger', function(e) {
        e.preventDefault();
        forgotModal.css('display', 'flex');
    });

    // Close modal when clicking outside
    $(document).on('click', '.sup-modal', function(e) {
        if (e.target === this) {
            $(this).css('display', 'none');
        }
    });

    // Send OTP handler
    sendOtpBtn.on('click', function() {
        if (emailInput.val().trim() === '') {
            alert('Please enter your email address');
            return;
        }
        
        // Hide Forgot Password and show Verify Email modal
        forgotModal.css('display', 'none');
        verifyModal.css('display', 'flex');
        
        // Focus first OTP input
        setTimeout(() => otpInputs.first().focus(), 100);
    });

    // Back button handler
    backButton.on('click', function() {
        verifyModal.css('display', 'none');
        forgotModal.css('display', 'flex');
    });

    // Resend OTP handler
    resendOtpBtn.on('click', function() {
        alert('New OTP has been sent to ' + emailInput.val());
    });

    // Verify OTP handler
    verifyOtpBtn.on('click', function() {
        let otp = '';
        otpInputs.each(function() {
            otp += $(this).val();
        });
        
        if (otp.length !== 4) {
            alert('Please enter the complete 4-digit OTP');
            return;
        }
        
        alert('OTP verified successfully!');
        verifyModal.css('display', 'none');
    });

    // OTP input navigation
    otpInputs.on('input', function() {
        if (this.value.length === 1) {
            $(this).next('.sup-otp-input').focus();
        }
    }).on('keydown', function(e) {
        if (e.key === 'Backspace' && this.value === '') {
            $(this).prev('.sup-otp-input').focus();
        }
    });

    // Close with Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            forgotModal.css('display', 'none');
            verifyModal.css('display', 'none');
        }
    });
});
</script>