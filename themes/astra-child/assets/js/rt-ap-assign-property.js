jQuery(document).ready(function($) {

    function debugLog(msg, data) {
        if (window.console) console.log(msg, data || '');
    }

    // ---------------------------
    // Client Search
    // ---------------------------
    $('#client-search').on('input', function() {
        const term = $(this).val();
        if (term.length < 2) { $('#client-suggestions').hide(); return; }

        $.post(rtAssignPropertyAjax.ajax_url, {
            action: 'search_clients',
            term: term,
            _wpnonce: rtAssignPropertyAjax.nonce
        }, function(res) {
            debugLog('Clients AJAX response:', res);
            const $box = $('#client-suggestions');
            $box.empty();
            if (res.success && res.data.length) {
                res.data.forEach(c => {
                    $box.append(`<div class="suggestion-item" data-id="${c.client_id}">${c.full_name}</div>`);
                });
                $box.show();
            } else {
                $box.hide();
            }
        });
    });

    $(document).on('click', '#client-suggestions .suggestion-item', function() {
        $('#client-id').val($(this).data('id'));
        $('#client-search').val($(this).text());
        $('#client-suggestions').hide();
    });

    // ---------------------------
    // Property Search
    // ---------------------------
    $('#property-search').on('input', function() {
        const term = $(this).val();
        if (term.length < 2) { $('#property-suggestions').hide(); return; }

        $.post(rtAssignPropertyAjax.ajax_url, {
            action: 'search_properties',
            term: term,
            _wpnonce: rtAssignPropertyAjax.nonce
        }, function(res) {
            debugLog('Properties AJAX response:', res);
            const $box = $('#property-suggestions');
            $box.empty();
            if (res.success && res.data.length) {
                res.data.forEach(p => {
                    $box.append(`<div class="suggestion-item" data-id="${p.id}">${p.address}</div>`);
                });
                $box.show();
            } else {
                $box.hide();
            }
        });
    });

    $(document).on('click', '#property-suggestions .suggestion-item', function() {
        $('#property-id').val($(this).data('id'));
        $('#property-search').val($(this).text());
        $('#property-suggestions').hide();
    });

    // ---------------------------
    // Assign Property
    // ---------------------------
    $('#assign-btn').on('click', function() {
        const client_id = $('#client-id').val();
        const property_id = $('#property-id').val();

        if (!client_id || !property_id) { alert('Please select a client and a property'); return; }

        $.post(rtAssignPropertyAjax.ajax_url, {
            action: 'assign_property_to_client',
            client_id: client_id,
            property_id: property_id,
            _wpnonce: rtAssignPropertyAjax.nonce
        }, function(res) {
            debugLog('Assign AJAX response:', res);
            if (res.success) {
                alert(res.data.message);
                location.reload();
            } else {
                alert(res.data.message);
            }
        });
    });
});
