<div class="enhanced-register-form">
    <!-- Add this heading section -->
    <div class="form-header">
        <h2><?php _e('Registration', 'enhanced-login'); ?></h2>
    </div>
    
    <form id="enhanced-register" method="post">
        <div class="form-group">
            <label for="reg-username"><?php _e('Username', 'enhanced-login'); ?></label>
            <input type="text" name="username" id="reg-username" required>
        </div>

        <div class="form-group">
            <label for="reg-email"><?php _e('Email', 'enhanced-login'); ?></label>
            <input type="email" name="email" id="reg-email" required>
        </div>

        <div class="form-group">
            <label for="reg-first-name"><?php _e('First Name', 'enhanced-login'); ?></label>
            <input type="text" name="first_name" id="reg-first-name">
        </div>

        <div class="form-group">
            <label for="reg-last-name"><?php _e('Last Name', 'enhanced-login'); ?></label>
            <input type="text" name="last_name" id="reg-last-name">
        </div>

        <div class="form-group">
            <label for="reg-role"><?php _e('Register As', 'enhanced-login'); ?></label>
            <select name="role" id="reg-role" required>
                <option value=""><?php _e('Select Role', 'enhanced-login'); ?></option>
                <option value="realtor"><?php _e('Realtor', 'enhanced-login'); ?></option>
                <option value="client"><?php _e('Client', 'enhanced-login'); ?></option>
            </select>
        </div>

        <div class="form-group">
            <label for="reg-password"><?php _e('Password', 'enhanced-login'); ?></label>
            <input type="password" name="password" id="reg-password" required>
            <div class="password-strength-meter"></div>
        </div>

        <div class="form-group">
            <label for="reg-confirm-password"><?php _e('Confirm Password', 'enhanced-login'); ?></label>
            <input type="password" name="confirm_password" id="reg-confirm-password" required>
        </div>

        <div class="form-group">
            <button type="submit"><?php _e('Register', 'enhanced-login'); ?></button>
        </div>

        <div class="form-links">
            <a href="<?php echo wp_login_url(); ?>"><?php _e('Already have an account? Login', 'enhanced-login'); ?></a>
        </div>

        <input type="hidden" name="redirect" value="<?php echo esc_url($redirect); ?>">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('enhanced-login-nonce'); ?>">
        <input type="hidden" name="action" value="enhanced_register">
    </form>

    <div class="enhanced-register-message"></div>
</div>