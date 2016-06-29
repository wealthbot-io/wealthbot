/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 01.07.13
 * Time: 15:33
 * To change this template use File | Settings | File Templates.
 */

$(function(){

//    updateCustodianQuestionsBlock();
//
//    $(document).on('click','input:radio[name="step_one[custodian]"]',function() {
//        updateCustodianQuestionsBlock();
//    });

    $('.fee-value').each(function(){
        var e = $(this);
        var value = e.text();

        e.text(Number(value).toFixed(4));
    });

    $('.tax-loss-harvesting .auto-numeric').each(function(i, element) {
        if (!$(element).val()) {
            $(element).val('00.00');
        }
    });

    $(document).on('click','.form-actions input.btn-info', function(event){
        var transactionAmountSelector = $('#wealthbot_riabundle_riacompanyinformationtype_transaction_amount');
        var transactionAmountPercentSelector = $('#wealthbot_riabundle_riacompanyinformationtype_transaction_amount_percent');

        if (transactionAmountSelector.autoNumeric('getSettings')) {
            transactionAmountSelector.val(transactionAmountSelector.autoNumeric('get'));
        }

        if (transactionAmountPercentSelector.autoNumeric('getSettings')) {
            transactionAmountPercentSelector.val(transactionAmountPercentSelector.autoNumeric('get'));
        }

        $(this).closest('form').submit();

        event.preventDefault();
    });

    var phoneNumber = $("#wealthbot_riabundle_riacompanyinformationtype_phone_number");
    if (phoneNumber.length > 0) {
        phoneNumber.inputmask("mask", {"mask": "(999) 999-9999"});
    }

    var faxNumber = $("#wealthbot_riabundle_riacompanyinformationtype_fax_number");
    if (faxNumber.length > 0) {
        faxNumber.inputmask("mask", {"mask": "(999) 999-9999"});
    }

    $(document).on('click','.website-test.btn',function (event) {
        var value = $('#wealthbot_riabundle_riacompanyinformationtype_website').val();

        if (!value || value === 'http://') {
            alert('Enter the value.');
            event.preventDefault();
        } else {
            $(this).attr('href', value);
        }
    });

    $('#wealthbot_riabundle_riacompanyinformationtype_slug').keyup(function() {
        var elem = $(this);
        var parent = elem.parent();
        var _data =  $.data(elem[0], 'xhr');

        if (_data) {
            _data.abort();
        }

        if (!elem.hasClass('loading')) {
            elem.addClass('loading');
            elem.after('<img class="ajax-loader" src="/img/ajax-loader.gif" />');
        }

        var xhr = $.ajax({
            url: elem.attr('data-url'),
            data: { slug: elem.val() },
            success: function(response) {
                if (response.is_valid == true) {
                    elem.removeClass('error');
                } else {
                    elem.addClass('error');
                }
            },
            complete: function() {
                elem.removeClass('loading');
                parent.find('.ajax-loader').remove();
            }
        });

        $.data(elem[0], 'xhr', xhr);
    });

//    $(document).on('change','input:radio[name="wealthbot_riabundle_riacompanyinformationtype[is_allow_retirement_plan]"]', function() {
//        updateOutsideRetirementAlertMessage();
//    });
//
//    $(document).on('change','input:radio[name="wealthbot_riabundle_riacompanyinformationtype[account_managed]"]', function() {
//
//    })
});

//function updateCustodianQuestionsBlock() {
//    var block = $('#custodian_questions');
//
//    if (block.length > 0) {
//        var checkedCustodian = $('input:radio[name="step_one[custodian]"]:checked');
//        if (checkedCustodian.length > 0) {
//            block.show();
//            return true;
//        }
//    }
//
//    block.hide();
//    return false;
//}