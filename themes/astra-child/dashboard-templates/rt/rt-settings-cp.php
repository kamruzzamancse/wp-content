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
            <?php 
                $lost_password_url = wp_lostpassword_url(); 
            ?>
            <a href="<?php echo esc_url($lost_password_url); ?>" class="sup-forget-link">
                Forget password?
            </a>
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
    margin-bottom: 20px;
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
    background-color: #3498db!important;
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
    background-color: #2980b9!important;
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
