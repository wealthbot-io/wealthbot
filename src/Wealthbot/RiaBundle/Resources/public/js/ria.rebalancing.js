$(function() {
    addCompleteTransferCustodianEvent(
        '.ria-find-clients-filter-form-type-search',
        '',
        function(object) {
            $('#rebalance_history_filter_form_client_id').val(object.id);
        },
        function() {
            $('#rebalance_history_filter_form_client_id').val('');
        }
    );

    $(document).on('click','.show-rebalancing-accounts-btn', function(event) {
        var btn = $(this);

        var account_table_box = btn.closest('.tab-pane').find('.rebalance-account-table-content');
        account_table_box.html('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

        $.ajax({
            url: btn.attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status = 'success') {
                    account_table_box.html(response.content);
                } else {
                    alert('Error: ' + response.content);
                }
            },
            complete: function() {
                $('.ajax-loader').remove();
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.rebalance-table-content table > thead a, .ajax-pagination a', function(event) {

        var btn = $(this);
        var rebalance_table_box = btn.closest('.rebalance-table-content');
        var account_table_box = btn.closest('.tab-pane').find('.rebalance-account-table-content');

        rebalance_table_box.html('<img class="ajax-loader" src="/img/ajax-loader.gif" />');
        account_table_box.html('');

        $.ajax({
            url: btn.attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status = 'success') {
                    rebalance_table_box.html(response.content);
                } else {
                    alert('Error: ' + response.content);
                }
            },
            complete: function() {
                $('.ajax-loader').remove();
            }
        });

        event.preventDefault();
    });

    $(document).on('submit','#admin_rebalancing_page_content #rebalance_form', function(event) {
        var checkedCheckboxes = $(this).find('.rebalance-table-content input[type="checkbox"]:checked')
        if (checkedCheckboxes.length) {

            var form = $(this);

            form.ajaxSubmit({
                type: 'post',
                success: function(response) {
                    if (response.status == 'success') {

                    }

                    if (response.status == 'error') {
                        alert('Error');
                    }
                }
            });

        } else {
            alert('Please select items for rebalance');
        }

        event.preventDefault();
    });

    $(document).on('submit','#rebalance_post_form', function(event) {
        var form = $(this);

        var checkedCheckboxes = form.find('.rebalance-table-content input[type="checkbox"]:checked');
        if (checkedCheckboxes.length) {

            form.ajaxSubmit({
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = response.redirect_url;
                    }

                    if (response.status == 'error') {
                        alert('Error: ' + response.message);
                    }
                }
            });
        } else {
            alert('Please select items.');
        }
        event.preventDefault();
    });

    $(document).on('click','#ria_rebalancing_page_content #rebalance_form .rebalance-form-submit-btn', function(event) {
        var elem = $(this);
        var form = elem.closest('form');

        var checkedCheckboxes = form.find('.rebalance-table-content input[type="checkbox"]:checked');
        if (checkedCheckboxes.length) {
            if (elem.attr('data-action-type') == 'rebalance') {
                  form.spinner128(true);
            }

            form.ajaxSubmit({
                url: elem.attr('href'),
                type: 'post',
                success: function(response) {
                    form.spinner128(false);

                    if (response.status == 'success') {
                        window.location.reload();
                    }

                    if (response.status == 'error') {
                        alert('Error: ' + response.message);
                    }

                    if (response.status == 'timeout') {
                        alert('Rebalancer activity is registered in queue and will be processed soon, please monitor the status change to proceed.');
                        setInterval(startRiaRebalanceCheckProgressAction, 10000);
                    }
                }
            });

        } else {
            alert('Please select items.');
        }

        event.preventDefault();
    });

    $(document).on('click','.rebalance-action-btn', function(event) {
        var btn = $(this);

        $.ajax({
            url: btn.attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    var modalDialog = $('#modal_dialog');
                    modalDialog.find('.modal-header h3').html('Details');
                    modalDialog.find('.modal-body').html(response.content);
                    modalDialog.modal('show');
                } else {
                    alert('ERROR');
                }
            }
        });

        event.preventDefault();
    });

    $(document).on('submit','#rebalance_history_filter_form', function(event) {
        var form = $(this);
        var history_rebalance_table_box = form.closest('#history_tab').find('.rebalance-table-content');

        history_rebalance_table_box.html('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

        form.ajaxSubmit({
            data: {'is_filter': true },
            type: 'post',
            success: function(response) {
                if (response.status == 'success') {
                    history_rebalance_table_box.html(response.content);
                } else {
                    $('#rebalance_history_filter_form').replaceWith(response.content);
                }
            }
        });
        event.preventDefault();
    });

    $(document).on('change','#rebalance_form_is_all', function(event) {
        var elem = $(this);

        var checkboxes = $('.rebalance-table-content input[name="rebalance_form[client_value][]"]');

        if ('checked' === elem.attr('checked')) {
            checkboxes.each(function(index, checkbox) {
                $(checkbox).attr('checked', 'checked');
            });
        } else {
            checkboxes.each(function(index, checkbox) {
                $(checkbox).removeAttr('checked');
            });
        }

        event.preventDefault();
    });

    $(document).on('change','.rebalance-table-content input[name="rebalance_form[client_value][]"]', function(event) {

        if ('checked' !== $(this).attr('checked')) {
            $('#rebalance_form_is_all').removeAttr('checked');
        }
        event.preventDefault();
    });

    $(document).on('click','.rebalancer-queue-change-state-btn', function(event) {
        var elem = $(this);

        var url;
        if (elem.attr('data-state') == 'active') {
            url = Routing.generate('rx_ria_rebalancing_rebalancer_queue_delete', {'id' : elem.attr('data-id')});
        } else {
            url = Routing.generate('rx_ria_rebalancing_rebalancer_queue_restore', {'id' : elem.attr('data-id')});
        }

        $.ajax({
            url: url,
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    if (response.state == 'deleted') {
                        elem.closest('tr').addClass('text-through-row');
                        elem.text('Restore');
                        elem.attr('data-state', 'deleted')
                    } else {
                        elem.closest('tr').removeClass('text-through-row');
                        elem.text('x');
                        elem.attr('data-state', 'active')
                    }

                    $('#rebalance_portfolio_allocation_table').replaceWith(response.allocation_table);
                    $('#rebalance_rebalancing_summary_table').replaceWith(response.summary_table);
                } else {
                    alert(response.message);
                }
            }
        });

        event.preventDefault();
    });

    $(document).on('submit','#trade_recon_form', function(event) {
        var submitButton = $(this).find('button:submit');

        submitButton.button('loading');

        $(this).ajaxSubmit({
            target: $(this).parent(),
            complete: function() {
                submitButton.button('reset');

                addCompleteTransferCustodianEvent(
                    '.ria-find-clients-filter-form-type-search',
                    '',
                    function(object) {
                        $('#form_client').val(object.id);
                    },
                    function() {
                        $('#form_client').val('');
                    }
                );
            }
        });

        event.preventDefault();
        return false;
    });
});

function startRiaRebalanceCheckProgressAction()
{
    $.ajax({
        url: Routing.generate('rx_ria_rebalancing_check_progress'),
        dataType: 'json',
        success: function(response) {
            if (response.status == 'success') {
                window.location.reload();
            }
        }
    });
}