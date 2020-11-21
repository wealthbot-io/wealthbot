/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 23.05.13
 * Time: 13:51
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    /*$(document).ajaxStop(function(){
        $("input:checkbox, input:radio, input:file").not('[data-no-uniform="true"],#uniform-is-ajax').uniform();
    });*/

    $(document).on('click','.client-management-transfer-form .form-actions a', function(event){
        $(this).button('loading');

        showTransferStep($(this).attr('href'));
        event.preventDefault();
    });

    $(document).on('submit','.client-management-transfer-form form', function(event){
        var form = $(this);

        form.find('input[type="submit"]').button('loading');

        var options = {
            success: function (data) {
                if (data.status == 'success') {
                    if (data.redirect_url) {
                        showTransferStep(data.redirect_url);
                    }
                } else {
                    if (data.status == 'error') {
                        if (data.message) {
                            alert(data.message);
                        }
                        if (data.form) {
                            form.replaceWith(data.form);
                        }
                    } else {
                        $('#result_container').html('<div class="client-management-transfer-form">'+data+'</div>');
                    }
                }
            }
        };

        event.preventDefault();
        form.ajaxSubmit(options);
    });
});

function showSuccessMessage()
{
    var url = $("#retirement_account_funds").attr("data-transfer-redirect-url");

    $.ajax({
        url: url,
        dataType: 'json',
        success: function(response){
            if(response.status == 'success'){
                showContentInRightBox('<h4>'+response.message+'</h4>');
                $('#accounts_table_container').html(response.account_table);
                $('#accounts_table_container').show();
                $('#account_continue_btn').hide();
            }
        }
    });
}

function successAccountForm(response, statusText, xhr, $form) {
    console.log(response);
    if (response.status == 'error') {
        if (response.form) {
            showContentInRightBox(response.form);
        }
        if (response.message) {
            var container = $("#account_type_form_container");
            container.find('form').before(getAlertMessage(response.message, 'error'));
        }
    } else if (response.status == 'success') {
        if (response.content.length) {
            if (response.transfer_url) {
                if (response.in_right_box) {
                    $.get(response.transfer_url, function(data) {
                        showContentInRightBox(data);
                    });
                } else {
                    showTransferStep(response.transfer_url);
                }

            } else {
                showContentInRightBox(response.content);

                if (response.show_portfolio_button) {
                    showPortfolioActionButton();
                    uncheckAccounts();
                }
            }
        } else {
            showContentInRightBox("Some error, please try again.");
        }
    } else {
        $('#result_container').html('<div class="client-management-transfer-form">'+response.content+'</div>');
    }

    updateAutoNumeric();
}

function showTransferStep(transferUrl) {
    var container = $('#result_container');

    $.get(transferUrl, function(data) {
        var html = '<div class="client-management-transfer-form">'+data.content+'</div>';
        container.html(html);
        init();
    });
}