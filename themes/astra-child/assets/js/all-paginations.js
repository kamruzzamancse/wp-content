jQuery(document).ready(function($){
    const ajaxurl = paginationData.ajaxurl; // ✅ Properly localized URL

    function renderPagination(container, totalPages, currentPage) {
        let html = '';
        for(let i=1; i<=totalPages; i++){
            html += `<button class="page-btn ${i===currentPage?'active':''}" data-page="${i}">${i}</button>`;
        }
        $(container).html(html);
    }

    function loadTable(type) {
        const page = parseInt($('#'+type+'Pagination').find('.page-btn.active').data('page')) || 1;
        const rows = parseInt($('#'+type+'Rows').val()) || 6; // ✅ default 6 rows
        const search = $('#'+type+'Search').val() || '';

        const action = type==='activeClients' ? 'get_active_clients_page' : 'get_leads_page';
        const nonce  = type==='activeClients' ? paginationData.clients_nonce : paginationData.leads_nonce;
        const bodyContainer = type==='activeClients' ? '#activeClientsBody' : '#leadsBody';
        const paginationContainer = type==='activeClients' ? '#activeClientsPagination' : '#leadsPagination';

        $(bodyContainer).html('<tr><td colspan="5" style="text-align:center;">Loading...</td></tr>');

        $.post(ajaxurl, { action, nonce, page, rows_per_page: rows, search }, function(res){
            console.log('Response:', res); // ✅ debug line
            if(res.success){
                $(bodyContainer).html(res.data.html);
                renderPagination(paginationContainer, res.data.total_pages, res.data.current_page);
            } else {
                $(bodyContainer).html('<tr><td colspan="5" style="text-align:center;">Error loading data</td></tr>');
            }
        }).fail(function(err){
            console.error('AJAX Error:', err);
            $(bodyContainer).html('<tr><td colspan="5" style="text-align:center;color:red;">AJAX Request Failed</td></tr>');
        });
    }

    // Initial load with 6 rows per table
    loadTable('activeClients');
    loadTable('leads');

    // Event listeners
    $('#activeClientsRows, #activeClientsSearch').on('change keyup', function(){ loadTable('activeClients'); });
    $('#leadsRows, #leadsSearch').on('change keyup', function(){ loadTable('leads'); });

    // Pagination button click
    $(document).on('click', '.page-btn', function(){
        const parent = $(this).closest('.pagination');
        parent.find('.page-btn').removeClass('active');
        $(this).addClass('active');
        if(parent.is('#activeClientsPagination')) loadTable('activeClients');
        if(parent.is('#leadsPagination')) loadTable('leads');
    });
});
