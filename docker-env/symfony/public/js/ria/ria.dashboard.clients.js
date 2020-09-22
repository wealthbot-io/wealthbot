/**
 * Created with JetBrains PhpStorm.
 * User: vovik
 * Date: 9/18/13
 * Time: 5:03 PM
 * To change this template use File | Settings | File Templates.
 */

function ajaxLoadPage(url, successFunction) {
   /// $('#ria_dashboard_client_content').empty();
    $.ajax({
        url: url,
        dataType: 'json',
        success: function(response) {

                if (typeof(successFunction) != 'undefined') {
                    successFunction();
                }
                $('#ria_dashboard_client_content').html(response.content);

                $('#client_view_btn').attr('data-redirect-action', response.active_tab);

                switch (response.active_tab) {
                    case 'overview':
                        drawStatsChart('#overview_stats_chart');
                        break;
                    case 'allocation':
                        drawModelCharts('.pie-chart');
                        break;
                    default:
                        break

            }

            addBoldClosedTextInOption();
            updateAutoNumeric();
            $('.ajax-loader').remove();
        },
        complete: function() {
        }
    });
}

$(function() {
    addCompleteTransferCustodianEvent('.ria-find-clients-form-type-search', '', function(item) {
        window.location.href = item.redirect_url;
    });

    addCompleteTransferCustodianEvent('.filter-by-client-name', '', function(item) {
        this.val(item.name);
    });

    $(document).on('click','#ria_dashboard_client_menu ul.main-menu a', function(event) {
        var btn = $(this);
        var li = btn.closest('li');
        var ul = li.closest('ul.main-menu');
        btn.append('<img class="ajax-loader" src="/img/ajax-loader.gif">');

        if  (!li.hasClass('active')) {
            ajaxLoadPage(btn.attr('href'), function() {
                ul.find('li.active').removeClass('active');
                li.addClass('active');
            });
        }

        event.preventDefault();
    });

    ///$('#ria_dashboard_client_content').spinner128();

    var activeButton = $('#ria_dashboard_client_menu ul.main-menu li.active a');
    activeButton.append('<img class="ajax-loader" src="/img/ajax-loader.gif">');
    ajaxLoadPage(activeButton.attr('href'), function() {
        $('#ria_dashboard_client_content').spinner128(false);
    });

    $(document).on('submit','#sas_cash_collection_form', function(event) {
        var form = $(this);
        var btn = form.find('input[type="submit"]');

        btn.button('loading');

        form.ajaxSubmit({
            success: function(responce) {
                $('#ria_dashboard_client_content').html(responce.content);
                drawStatsChart('.stats-chart');
            }
        });

        event.preventDefault();
    });

    $(document).on('click','#client_view_btn', function(event) {
        var btn = $(this);
        var href = btn.attr('href') + '?redirect-action=' + btn.attr('data-redirect-action');

        window.open(href);
        event.preventDefault();
    });

    $(document).on('click','.initial-rebalance-btn', function(event) {
        var btn = $(this);
        var accounts = [];

        $('input[name="rebalance_accounts[]"]').each(function() {
            var checkbox = $(this);
            if (checkbox.is(':checked')) {
                accounts.push(checkbox.val());
            }
        });

        btn.button('loading');
        $.ajax({
            type: "POST",
            url: btn.attr('data-url'),
            data: { rebalance_accounts: accounts },
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    var html = getAlertMessage(response.message, response.status) + response.content;

                    $('#ria_dashboard_client_content').html(html);
                    drawStatsChart('.stats-chart');
                }
            },
            complete: function() {
                btn.button('reset');
            }
        });

        event.preventDefault();
    });
});

