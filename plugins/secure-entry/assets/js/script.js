jQuery(document).ready(function($) {
    // Handle login form submission
    $('#enhanced-login').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $message = $('.enhanced-login-message');
        
        $message.removeClass('error success').html('');
        
        $.ajax({
            type: 'POST',
            url: enhanced_login_vars.ajaxurl,
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $message.addClass('success').html('Login successful. Redirecting...');
                    window.location.href = response.data.redirect;
                } else {
                    $message.addClass('error').html(response.data.message);
                }
            },
            error: function() {
                $message.addClass('error').html('An error occurred. Please try again.');
            }
        });
    });
    
    // Handle registration form submission
    $('#enhanced-register').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $message = $('.enhanced-register-message');
        
        $message.removeClass('error success').html('');
        
        $.ajax({
            type: 'POST',
            url: enhanced_login_vars.ajaxurl,
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $message.addClass('success').html('Registration successful. Redirecting...');
                    window.location.href = response.data.redirect;
                } else {
                    $message.addClass('error').html(response.data.message);
                }
            },
            error: function() {
                $message.addClass('error').html('An error occurred. Please try again.');
            }
        });
    });
    
    // Password strength meter
    $('#reg-password').on('keyup', function() {
        var password = $(this).val();
        var meter = $('.password-strength-meter');
        
        if (!password) {
            meter.attr('data-strength', '0');
            return;
        }
        
        // Use WordPress' password strength meter if available
        if (typeof wp !== 'undefined' && wp.hasOwnProperty('passwordStrength')) {
            var strength = wp.passwordStrength.meter(
                password,
                wp.passwordStrength.userInputBlacklist(),
                $('#reg-username').val()
            );
            
            meter.attr('data-strength', strength);
        } else {
            // Fallback simple strength check
            var strength = 0;
            
            // At least 8 characters
            if (password.length >= 8) strength++;
            
            // Contains lowercase
            if (/[a-z]/.test(password)) strength++;
            
            // Contains uppercase
            if (/[A-Z]/.test(password)) strength++;
            
            // Contains number
            if (/[0-9]/.test(password)) strength++;
            
            // Contains special character
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            // Normalize to 0-4 scale
            strength = Math.min(4, Math.floor(strength / 1.25));
            
            meter.attr('data-strength', strength);
        }
    });
});