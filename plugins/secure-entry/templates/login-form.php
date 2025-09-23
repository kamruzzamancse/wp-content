<div class="enhanced-login-form bg-gradient">  
    <!-- Login Form -->
    <form id="enhanced-login" method="post">
        <div class="form-logo">
            <img src="http://localhost/mary/wp-content/uploads/2025/08/mary-logo.png" alt="Company Logo">
            <!-- <img src="https://maryasfour.livewebsite.space/wp-content/uploads/2025/08/mary-logo.png" alt="Company Logo"> -->
        </div>

        <div class="form-header">
            <h2><?php _e('Login', 'enhanced-login'); ?></h2>
        </div>
        <div class="form-group">
            <label for="username"><?php _e('Username or Email', 'enhanced-login'); ?></label>
            <input type="text" name="username" id="username" required>
        </div>
        <div class="form-group">
            <label for="password"><?php _e('Password', 'enhanced-login'); ?></label>
            <input type="password" name="password" id="password" required>
        </div>
        <div class="form-group remember-me">
            <input type="checkbox" name="rememberme" id="rememberme">
            <label for="rememberme"><?php _e('Remember Me', 'enhanced-login'); ?></label>
        </div>
        <div class="enhanced-login-message"></div>
        <div class="form-group">
            <button type="submit"><?php _e('Log In', 'enhanced-login'); ?></button>
        </div>
        <div class="form-links">
            <a href="<?php echo wp_lostpassword_url(); ?>"><?php _e('Lost your password?', 'enhanced-login'); ?></a>
            <?php if (get_option('users_can_register')) : ?>
                | <a href="<?php echo home_url('/registration/'); ?>"><?php _e('Register', 'enhanced-login'); ?></a>
            <?php endif; ?>
        </div>
        <input type="hidden" name="redirect" value="<?php echo esc_url($redirect); ?>">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('enhanced-login-nonce'); ?>">
        <input type="hidden" name="action" value="enhanced_login">
    </form>
</div>
<script>
    // Toggle password visibility
    const loginPasswordToggle = document.createElement('span');
    loginPasswordToggle.textContent = '👁️';
    loginPasswordToggle.style.position = 'absolute';
    loginPasswordToggle.style.right = '10px';
    loginPasswordToggle.style.top = '75%';
    loginPasswordToggle.style.transform = 'translateY(-50%)';
    loginPasswordToggle.style.cursor = 'pointer';
    loginPasswordToggle.style.color = '#fff';
    const passwordField = document.getElementById('password');
    passwordField.parentElement.style.position = 'relative';
    passwordField.parentElement.appendChild(loginPasswordToggle);
    loginPasswordToggle.addEventListener('click', function() {
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            this.textContent = '🔒';
        } else {
            passwordField.type = 'password';
            this.textContent = '👁️';
        }
    });
</script>

<style>
.form-logo {
    text-align: left;
    margin-bottom: 25px;
}
.form-logo img {
    max-width: 200px;
    height: auto;
    display: inline-block;
    margin-top: -20px;
    filter: drop-shadow(0 2px 6px rgba(0,0,0,0.4)); /* subtle shadow */
}
</style>