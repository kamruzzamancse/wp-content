jQuery(document).ready(function($){
    const ajaxurl = paginationData.ajaxurl;

    function renderPagination(container, totalPages, currentPage) {
        let html = '';
        for(let i=1; i<=totalPages; i++){
            html += `<button class="page-btn ${i===currentPage?'active':''}" data-page="${i}">${i}</button>`;
        }
        $(container).html(html);
    }

    function loadTable(type) {
        const page = parseInt($('#'+type+'Pagination').find('.page-btn.active').data('page')) || 1;
        const rows = parseInt($('#'+type+'Rows').val()) || 10;
        const search = $('#'+type+'Search').val() || '';

        const action = type==='activeClients' ? 'get_active_clients_page' : 'get_leads_page';
        const nonce  = type==='activeClients' ? paginationData.clients_nonce : paginationData.leads_nonce;
        const bodyContainer = type==='activeClients' ? '#activeClientsBody' : '#leadsBody';
        const paginationContainer = type==='activeClients' ? '#activeClientsPagination' : '#leadsPagination';

        $(bodyContainer).html('<tr><td colspan="5" style="text-align:center;">Loading...</td></tr>');

        $.post(ajaxurl, { action, nonce, page, rows_per_page: rows, search }, function(res){
            console.log(res); // Debug
            if(res.success){
                $(bodyContainer).html(res.data.html);
                renderPagination(paginationContainer, res.data.total_pages, res.data.current_page);
            } else {
                $(bodyContainer).html('<tr><td colspan="5" style="text-align:center;">Error loading data</td></tr>');
            }
        });
    }

    // Initial load
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

jQuery(document).ready(function ($) {
    // -------- ADDRESS BOOK (AJAX Pagination) -------- //

    function renderAddressBookPagination(totalPages, currentPage) {
        let html = '';
        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="ab-page-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }
        $('#addressBookPagination').html(html);
    }

    function loadAddressBook(page = 1) {
        const rows = parseInt($('#addressBookRows').val()) || 10;
        const search = $('#addressBookSearch').val() || '';

        $('#addressBookBody').html('<tr><td colspan="7" style="text-align:center;">Loading...</td></tr>');

        $.post(clientActionData.ajax_url, {
            action: 'get_address_book_page',
            nonce: clientActionData.edit_nonce,
            page: page,
            rows_per_page: rows,
            search: search
        }, function (res) {
            if (res.success) {
                $('#addressBookBody').html(res.data.html);
                renderAddressBookPagination(res.data.total_pages, res.data.current_page);
            } else {
                $('#addressBookBody').html('<tr><td colspan="7" style="text-align:center;">Error loading data</td></tr>');
            }
        });
    }

    // Initial Load
    loadAddressBook();

    // Search and Rows Change
    $('#addressBookSearch, #addressBookRows').on('keyup change', function () {
        loadAddressBook(1);
    });

    // Pagination Button Click
    $(document).on('click', '.ab-page-btn', function (e) {
        e.preventDefault(); // prevent URL change
        const page = $(this).data('page');
        $('.ab-page-btn').removeClass('active');
        $(this).addClass('active');
        loadAddressBook(page);
    });
});

