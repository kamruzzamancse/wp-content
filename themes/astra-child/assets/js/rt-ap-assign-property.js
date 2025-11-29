jQuery(document).ready(function($) {
    // Initialize when document is ready
    function initAssignProperty() {
        // ---------------------------
        // PROPERTY SEARCH AUTOCOMPLETE
        // ---------------------------
        $('#property-search').on('input', function() {
            let term = $(this).val().trim();
            
            if (typeof propertySearchTimeout !== 'undefined') {
                clearTimeout(propertySearchTimeout);
            }
            
            propertySearchTimeout = setTimeout(function() {
                if (term.length < 2) { 
                    $('#property-suggestions').hide().empty(); 
                    return; 
                }
                
                $.ajax({
                    url: rtAssignPropertyAjax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'search_properties_for_assignment',
                        term: term,
                        _wpnonce: rtAssignPropertyAjax.nonce
                    },
                    success: function(res) {
                        const $box = $('#property-suggestions');
                        $box.empty();
                        
                        if (res.success && res.data && res.data.length > 0) {
                            res.data.forEach(function(property) {
                                $box.append(
                                    $('<div>', {
                                        class: 'suggestion-item',
                                        'data-id': property.id,
                                        text: property.address
                                    })
                                );
                            });
                            $box.show();
                        } else {
                            $box.hide();
                        }
                    },
                    error: function() {
                        $('#property-suggestions').hide();
                    }
                });
            }, 300);
        });

        // Property suggestion click handler
        $(document).on('click', '#property-suggestions .suggestion-item', function() {
            const propertyId = $(this).data('id');
            const propertyAddress = $(this).text();
            
            $('#property-id').val(propertyId);
            $('#property-search').val(propertyAddress);
            $('#property-suggestions').hide();
        });

        // ---------------------------
        // CLIENT SEARCH AUTOCOMPLETE
        // ---------------------------
        $('#client-search').on('input', function() {
            let term = $(this).val().trim().replace(/\s+/g, ' ');

            if (term.length < 2) { 
                $('#client-suggestions').hide().empty(); 
                return; 
            }

            $.ajax({
                url: rtAssignPropertyAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'search_clients',
                    term: term,
                    _wpnonce: rtAssignPropertyAjax.nonce
                },
                success: function(res) {
                    const $box = $('#client-suggestions');
                    $box.empty();
                    
                    if (res.success && res.data && res.data.length > 0) {
                        res.data.forEach(function(client) {
                            $box.append(
                                $('<div>', {
                                    class: 'suggestion-item',
                                    'data-id': client.client_id,
                                    text: client.full_name
                                })
                            );
                        });
                        $box.show();
                    } else {
                        $box.hide();
                    }
                },
                error: function() {
                    $('#client-suggestions').hide();
                }
            });
        });

        // Client suggestion click handler
        $(document).on('click', '#client-suggestions .suggestion-item', function() {
            const clientId = $(this).data('id');
            const clientName = $(this).text();
            
            $('#client-id').val(clientId);
            $('#client-search').val(clientName);
            $('#client-suggestions').hide();
        });

        // ---------------------------
        // ASSIGN PROPERTY BUTTON
        // ---------------------------
        $('#assign-btn').on('click', function() {
            const client_id = $('#client-id').val();
            const property_id = $('#property-id').val();

            if (!client_id || !property_id) { 
                alert('Please select both a client and a property'); 
                return; 
            }

            const $btn = $(this);
            $btn.prop('disabled', true).text('Assigning...');

            $.ajax({
                url: rtAssignPropertyAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'assign_property_to_client',
                    client_id: client_id,
                    property_id: property_id,
                    _wpnonce: rtAssignPropertyAjax.nonce
                },
                success: function(res) {
                    if (res.success) {
                        alert(res.data.message || 'Property assigned successfully!');
                        location.reload();
                    } else {
                        alert(res.data.message || 'Error assigning property');
                    }
                },
                error: function() {
                    alert('Network error occurred. Please try again.');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Assign Property');
                }
            });
        });

        // ---------------------------
        // EDIT ASSIGNMENT MODAL
        // ---------------------------
        $(document).on('click', '.edit-assignment', function() {
            const assignmentId = $(this).data('assignment-id');
            const currentClientId = $(this).data('client-id');
            const currentPropertyId = $(this).data('property-id');
            
            const editModal = $('#edit-assignment-modal');
            if (editModal.length) {
                $('#edit-assignment-id').val(assignmentId);
                $('#edit-client-id').val(currentClientId);
                $('#edit-property-id').val(currentPropertyId);
                
                const row = $(this).closest('tr');
                const clientName = row.find('td:first').text();
                const propertyAddress = row.find('td:nth-child(2)').text();
                
                $('#edit-client-search').val(clientName);
                $('#edit-property-search').val(propertyAddress);
                
                editModal.show();
            } else {
                alert('Edit modal not found. Please refresh the page.');
            }
        });

        // Edit modal client search
        $('#edit-client-search').on('input', function() {
            let term = $(this).val().trim().replace(/\s+/g, ' ');

            if (term.length < 2) { 
                $('#edit-client-suggestions').hide().empty(); 
                return; 
            }

            $.ajax({
                url: rtAssignPropertyAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'search_clients',
                    term: term,
                    _wpnonce: rtAssignPropertyAjax.nonce
                },
                success: function(res) {
                    const $box = $('#edit-client-suggestions');
                    $box.empty();
                    
                    if (res.success && res.data && res.data.length > 0) {
                        res.data.forEach(function(client) {
                            $box.append(
                                $('<div>', {
                                    class: 'suggestion-item',
                                    'data-id': client.client_id,
                                    text: client.full_name
                                })
                            );
                        });
                        $box.show();
                    } else {
                        $box.hide();
                    }
                },
                error: function() {
                    $('#edit-client-suggestions').hide();
                }
            });
        });

        // Edit modal property search
        $('#edit-property-search').on('input', function() {
            let term = $(this).val().trim();
            
            if (typeof editPropertySearchTimeout !== 'undefined') {
                clearTimeout(editPropertySearchTimeout);
            }
            
            editPropertySearchTimeout = setTimeout(function() {
                if (term.length < 2) { 
                    $('#edit-property-suggestions').hide().empty(); 
                    return; 
                }
                
                $.ajax({
                    url: rtAssignPropertyAjax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'search_properties_for_assignment',
                        term: term,
                        _wpnonce: rtAssignPropertyAjax.nonce
                    },
                    success: function(res) {
                        const $box = $('#edit-property-suggestions');
                        $box.empty();
                        
                        if (res.success && res.data && res.data.length > 0) {
                            res.data.forEach(function(property) {
                                $box.append(
                                    $('<div>', {
                                        class: 'suggestion-item',
                                        'data-id': property.id,
                                        text: property.address
                                    })
                                );
                            });
                            $box.show();
                        } else {
                            $box.hide();
                        }
                    },
                    error: function() {
                        $('#edit-property-suggestions').hide();
                    }
                });
            }, 300);
        });

        // Click handlers for edit modal suggestions
        $(document).on('click', '#edit-client-suggestions .suggestion-item', function() {
            const clientId = $(this).data('id');
            const clientName = $(this).text();
            
            $('#edit-client-id').val(clientId);
            $('#edit-client-search').val(clientName);
            $('#edit-client-suggestions').hide();
        });

        $(document).on('click', '#edit-property-suggestions .suggestion-item', function() {
            const propertyId = $(this).data('id');
            const propertyAddress = $(this).text();
            
            $('#edit-property-id').val(propertyId);
            $('#edit-property-search').val(propertyAddress);
            $('#edit-property-suggestions').hide();
        });

        // Edit assignment form submission
        $(document).on('submit', '#edit-assignment-form', function(e) {
            e.preventDefault();
            
            const assignmentId = $('#edit-assignment-id').val();
            const clientId = $('#edit-client-id').val();
            const propertyId = $('#edit-property-id').val();

            if (!clientId || !propertyId) { 
                alert('Please select both a client and a property'); 
                return; 
            }

            const $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).text('Updating...');

            $.ajax({
                url: rtAssignPropertyAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'update_assignment',
                    assignment_id: assignmentId,
                    client_id: clientId,
                    property_id: propertyId,
                    _wpnonce: rtAssignPropertyAjax.nonce
                },
                success: function(res) {
                    if (res.success) {
                        alert(res.data.message || 'Assignment updated successfully!');
                        $('#edit-assignment-modal').hide();
                        location.reload();
                    } else {
                        alert(res.data.message || 'Error updating assignment');
                    }
                },
                error: function() {
                    alert('Network error occurred. Please try again.');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Update Assignment');
                }
            });
        });

        // ---------------------------
        // DELETE ASSIGNMENT BUTTON
        // ---------------------------
        $(document).on('click', '.delete-assignment', function() {
            const assignmentId = $(this).data('assignment-id');
            
            if (confirm('Are you sure you want to delete this assignment?')) {
                const $btn = $(this);
                $btn.prop('disabled', true);

                $.ajax({
                    url: rtAssignPropertyAjax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'delete_assignment',
                        assignment_id: assignmentId,
                        _wpnonce: rtAssignPropertyAjax.nonce
                    },
                    success: function(res) {
                        if (res.success) {
                            alert(res.data.message || 'Assignment deleted successfully!');
                            location.reload();
                        } else {
                            alert(res.data.message || 'Error deleting assignment');
                        }
                    },
                    error: function() {
                        alert('Network error occurred. Please try again.');
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            }
        });

        // ---------------------------
        // MODAL HANDLERS
        // ---------------------------
        // Cancel button for edit modal
        $(document).on('click', '.clup-cancel', function() {
            $('#edit-assignment-modal').hide();
        });

        // Close edit modal
        $(document).on('click', '#edit-assignment-modal .clup-close-btn', function() {
            $('#edit-assignment-modal').hide();
        });

        // Close edit modal when clicking outside
        $(document).on('click', '#edit-assignment-modal', function(e) {
            if (e.target === this) {
                $(this).hide();
            }
        });

        // Escape key closes all modals
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $('#cl-upload-document-modal').hide();
                $('#edit-assignment-modal').hide();
            }
        });

        // Hide suggestion boxes when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.assign-field').length) {
                $('#property-suggestions, #client-suggestions, #edit-property-suggestions, #edit-client-suggestions').hide();
            }
        });
    }

    // Initialize the functionality
    initAssignProperty();
});
