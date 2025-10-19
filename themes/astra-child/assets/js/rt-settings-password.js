jQuery(document).ready(function($) {
    const ajaxObj = rt_password_ajax;

    $('.sup-password-form').on('submit', function(e) {
        e.preventDefault();

        const old_password = $(this).find('input:eq(0)').val();
        const new_password = $(this).find('input:eq(1)').val();
        const confirm_password = $(this).find('input:eq(2)').val();

        if (!old_password || !new_password || !confirm_password) {
            alert('All fields are required.');
            return;
        }

        $.post(ajaxObj.ajax_url, {
            action: 'rt_update_password',
            nonce: ajaxObj.nonce,
            old_password: old_password,
            new_password: new_password,
            confirm_password: confirm_password
        }, function(res) {
            if (res.success) {
                alert(res.data);
                $('.sup-password-form')[0].reset();
            } else {
                alert(res.data);
            }
        });
    });
});
