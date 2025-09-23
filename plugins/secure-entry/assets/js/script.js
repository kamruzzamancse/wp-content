jQuery(document).ready(function($) {
    // Enhanced login form handler
    $('#enhanced-login').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $message = $('.enhanced-login-message');
        const $submitBtn = $form.find('button[type="submit"]');
        const originalBtnText = $submitBtn.text(); // Store original button text
        
        // Reset states
        $message.removeClass('error success').html('').hide();
        $form.find('.is-invalid').removeClass('is-invalid');
        $submitBtn.prop('disabled', true).text('Authenticating...');
        
        // Add loading indicator
        $submitBtn.prepend('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>');
        
        $.ajax({
            type: 'POST',
            url: enhanced_login_vars.ajaxurl,
            data: {
                action: 'enhanced_login',
                nonce: enhanced_login_vars.nonce,
                username: $form.find('input[name="username"]').val().trim(),
                password: $form.find('input[name="password"]').val(),
                rememberme: $form.find('input[name="rememberme"]').is(':checked') ? 1 : 0,
                redirect: $form.find('input[name="redirect"]').val()
            },
            dataType: 'json',
            success: function(response) {
                // Clean up button state
                $submitBtn.prop('disabled', false).html(originalBtnText);
                
                if (response.success) {
                    $message.addClass('success').html(
                        '<span class="dashicons dashicons-yes"></span> Login successful. Redirecting...'
                    ).show();
                    
                    // Add slight delay for user to see success message
                    if (response.data.redirect) {
                        setTimeout(() => {
                            window.location.href = response.data.redirect;
                        }, 800);
                    }
                } else {
                    // Highlight problematic fields
                    if (response.data.field) {
                        $form.find(`[name="${response.data.field}"]`).addClass('is-invalid');
                    }
                    
                    $message.addClass('error').html(
                        `<span class="dashicons dashicons-no"></span> ${response.data.message || 'Invalid username or password.'}`
                    ).show();
                    
                    // Focus on first error field if available
                    if (response.data.field) {
                        $form.find(`[name="${response.data.field}"]`).focus();
                    }
                }
            },
            error: function(xhr) {
                $submitBtn.prop('disabled', false).html(originalBtnText);
                let errorMsg = 'Login failed. Please try again.';
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMsg = response.data && response.data.message 
                        ? response.data.message 
                        : 'Invalid username or password.';
                        
                    if (xhr.status === 403) {
                        errorMsg = 'Session expired. Please refresh and try again.';
                    }
                } catch (e) {
                    console.error('Login Error:', xhr.responseText, xhr.status);
                }
                
                $message.addClass('error').html(errorMsg).show();
            },
            complete: function() {
                // Remove spinner when request completes
                $submitBtn.find('.spinner-border').remove();
            }
        });
    });
    
    // Enhanced registration form handler
    $('#enhanced-register').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $message = $('.enhanced-register-message');
        const $submitBtn = $form.find('button[type="submit"]');
        
        $message.removeClass('error success').html('').hide();
        $submitBtn.prop('disabled', true).text('Registering...');
        
        $.ajax({
            type: 'POST',
            url: enhanced_login_vars.ajaxurl,
            data: {
                action: 'enhanced_register',
                nonce: enhanced_login_vars.nonce,
                username: $('#reg-username').val(),
                email: $('#reg-email').val(),
                first_name: $('#reg-first-name').val(),
                last_name: $('#reg-last-name').val(),
                role: $('#reg-role').val(),
                password: $('#reg-password').val(),
                confirm_password: $('#reg-confirm-password').val(),
                redirect: $form.find('input[name="redirect"]').val()
            },
            dataType: 'json',
            success: function(response) {
                $submitBtn.prop('disabled', false).text('Register');
                
                if (response.success) {
                    $message.addClass('success').html(
                        response.data.message || 'Registration successful!'
                    ).show();
                    
                    if (response.data.redirect) {
                        setTimeout(() => {
                            window.location.href = response.data.redirect;
                        }, 1500);
                    } else {
                        // Clear form on success
                        $form[0].reset();
                        $('.password-strength-meter').attr('data-strength', '0');
                    }
                } else {
                    $message.addClass('error').html(
                        response.data.message || 'Registration failed. Please check your details.'
                    ).show();
                }
            },
            error: function(xhr) {
                $submitBtn.prop('disabled', false).text('Register');
                let errorMsg = 'Registration failed. Please try again.';
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.data && response.data.message) {
                        errorMsg = response.data.message;
                    }
                } catch (e) {
                    console.error('Registration Error:', xhr.responseText);
                }
                
                $message.addClass('error').html(errorMsg).show();
            }
        });
    });
    
    // Password strength indicator
    $('#reg-password').on('keyup', function() {
        const password = $(this).val();
        const meter = $('.password-strength-meter');
        
        if (!password) {
            meter.attr('data-strength', '0');
            return;
        }
        
        let strength = 0;
        if (password.length >= 12) strength = 4;
        else if (password.length >= 8) strength = 3;
        else if (password.length >= 6) strength = 2;
        else strength = 1;
        
        meter.attr('data-strength', strength);
    });
});