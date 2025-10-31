jQuery(document).ready(function($) {
    const ajaxObj = window.cl_password_ajax || {};

    $('.sup-password-form').on('submit', function(e) {
        e.preventDefault();

        const $form = $(this);
        const old_password = $form.find('input:eq(0)').val().trim();
        const new_password = $form.find('input:eq(1)').val().trim();
        const confirm_password = $form.find('input:eq(2)').val().trim();

        if (!old_password || !new_password || !confirm_password) {
            alert('All fields are required.');
            return;
        }

        if (new_password !== confirm_password) {
            alert('New password and confirm password do not match.');
            return;
        }

        // Optional: simple strength check
        if (new_password.length < 8) {
            if (!confirm('New password is less than 8 characters. Continue?')) return;
        }

        const postData = {
            action: 'cl_update_password',
            nonce: ajaxObj.nonce,
            old_password: old_password,
            new_password: new_password,
            confirm_password: confirm_password
        };

        const submitBtn = $form.find('.sup-submit-button');
        const originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text('Updating...');

        $.post(ajaxObj.ajax_url, postData, function(res) {
            if (res && res.success) {
                alert(res.data);
                $form[0].reset();
            } else {
                const msg = res && res.data ? res.data : 'An error occurred. Please try again.';
                alert(msg);
            }
        }).fail(function() {
            alert('Network error. Please try again.');
        }).always(function() {
            submitBtn.prop('disabled', false).text(originalText);
        });
    });

    // Optional: "Forget password?" click — redirect to WP lost password
    $('#sup-forget-trigger').on('click', function(e) {
        e.preventDefault();
        // Get WP lost password url from data attribute, or fallback
        const wpLost = window.wp_lost_password_url || '/wp-login.php?action=lostpassword';
        window.location.href = wpLost;
    });
});
