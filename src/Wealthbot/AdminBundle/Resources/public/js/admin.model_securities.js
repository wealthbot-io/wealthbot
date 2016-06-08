/**
 * Created with JetBrains PhpStorm.
 * User: maksim
 * Date: 17.04.13
 * Time: 11:53
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    $("#security_form").live('submit', function(event) {
        event.preventDefault();
        $(this).ajaxSubmit({
            target: '#securities_tab',
            success: function(){
                addCompleteTransferCustodianEvent('#model_security_form_fund_symbol', '', fillSubclassParameters);
            }
        });
    });

    $('#model_security_form_asset_class_id').live('change', function() {
        var url = $(this).attr('data-complete-url');
        $.get(url, { asset_id: $(this).val() }, function(data){
            $('#model_security_form_subclass_id').html(data);
        });
    });

    $(".delete-model-security-btn").live('click', function(event) {
        var url = $(this).attr('href');
        $.get(url, {}, function(data){
            $('#securities_tab').html(data);
            addCompleteTransferCustodianEvent('#model_security_form_fund_symbol', '', fillSubclassParameters);
        });
        event.preventDefault();
    });

    $(".edit-model-security-btn").live('click', function(event) {
        var url = $(this).attr('href');
        $.get(url, {}, function(data){
            $('#securities_tab').html(data);
            addCompleteTransferCustodianEvent('#model_security_form_fund_symbol', '', fillSubclassParameters);
        });
        event.preventDefault();
    });

    $('.security-transaction-edit-btn').live('click', function(event) {
        $.ajax({
            url: $(this).attr('href'),
            dataType: 'json',
            success: function(response) {
                $('#modal_dialog .modal-header h3').html('Edit Transaction');
                $('#modal_dialog .modal-body').html(response.content);
                $('#modal_dialog').modal('show');
                updateAutoNumeric();
            }
        });
        event.preventDefault();
    });

    $('#edit_security_transaction_form').live('submit', function(event) {
        var form = $(this);
        var config = {
            dataType: 'json',
            success: function(response) {
                if (response.status == 'error') {
                    form.replaceWith(response.content);
                    updateAutoNumeric();
                }

                if (response.status == 'success') {
                    $('#modal_dialog').modal('hide');
                }
            }
        };

        form.ajaxSubmit(config);
        event.preventDefault();
    });

    addCompleteTransferCustodianEvent('#model_security_form_fund_symbol', '', fillSubclassParameters);
});

//function addTypeaheadEvent()
//{
//    $('.typeahead').typeahead({
//        source: function (query, process) {
//            var url = typeahead_url;
//            return $.get(url, { query: query }, function (data) {
//                map = {};
//                items = [];
//                $.each(data, function (i, record) {
//                    map[record.symbol] = record;
//                    items.push(record.symbol);
//                });
//                return process(items);
//            });
//        },
//        updater: function(item) {
//            fillSubclassParameters(map[item]);
//            return item;
//        }
//    });
//}

function fillSubclassParameters(params)
{
    $("#security_name_row").html(params.security_name);
    $("#security_type_row").html(params.type);
    $("#security_expense_ratio_row").html(params.expense_ratio);

    $("#model_security_form_type").val(params.type);
    $("#model_security_form_expense_ratio").val(params.expense_ratio);
    $("#model_security_form_security_id").val(params.id);
}