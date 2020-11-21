/**
 * Created with JetBrains PhpStorm.
 * User: maksim
 * Date: 14.03.13
 * Time: 18:19
 * To change this template use File | Settings | File Templates.
 */

// --------------- Registration STEP 3 - ACCOUNTS --------------------------//
$(function(){
    var isAjax = false;

    $(document).ajaxStart(function(){
        isAjax = true;
        $('#account_continue_btn').button('loading');
    });

    $(document).ajaxStop(function(){
        isAjax = false;
        $('#account_continue_btn').button('reset');
    });

    $(document).on('click',"#client_account_types_groups input[type='radio']", function(event){
        if (isAjax) {
            event.preventDefault();
        } else {
            hidePortfolioActionButton();
            showContentInRightBox('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

            var form = $(this).closest('form');

            var options = {
                success: showAccountForm,
                complete: function() {
                    disabledLeftBox();
                    $('.ajax-loader').remove();
                }
            };
            $(form).ajaxSubmit(options);
        }
    });

    // Account owner choices and deposit account groups handler
    $(document).on('click',"#client_account_types_group_type input[type='radio']", function(){
        var form = $(this).closest('form');

        var options = {
            success: showAccountForm
        };
        $(form).ajaxSubmit(options);
    });

    // Continue button Handler
    $(document).on('click',"#account_continue_btn", function(){
        var form = $("#account_type_form_container form");
        var hasErrors = false;


        if (!isAjax) {
            if (form.length ){
                var attrId = $(form).attr('id');
                var contributionTypes = $('.contribution-type-choices');

                if (attrId == 'client_account_types_form') {
                    alert('Please select account type in right box');
                    hasErrors = true;
                }

                if (attrId == 'retirement_account_fund_form') {
                    showSuccessMessage();
                    hasErrors = true;
                }

                if (attrId == 'client_account_form' && contributionTypes.length) {
                    if (!contributionTypes.find('input[type="radio"]:checked').length) {
                        alert('Will you be making contributions or withdrawing money from the account?');
                        hasErrors = true;
                    }
                }
            }

            if (hasErrors) {
                return false;
            }

            var options = {
                success: successAccountForm
            };

            $(form).ajaxSubmit(options);
        }
    });

    //back button handler
    $(document).on('click','#account_back_btn', function(event) {
        var elem = $(this);

        $.ajax({
            url: elem.attr('href'),
            dataType: 'json',
            success: function(response){
                if (response.status == 'success') {
                    showContentInRightBox(response.content);
                    if (response.step == 1) {
                        enabledLeftBox();
                        showAccountsTable();
                        $('#client_account_types_groups input[type="radio"]:checked').removeAttr('checked');
                    }

                    updateAutoNumeric();
                }

                if (response.status == 'error') {
                    window.location.href = response.redirect_url;
                }
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.contribution-type-choices input[type="radio"]', function(event) {
        var elem = $(this);
        var form = $(this).closest('form');

        var options = {
            url: elem.closest('.contribution-type-choices').attr('data-url'),
            success: function(response) {
                if (response.status === 'success') {
                    $('#contribution_distribution_fields').replaceWith(response.content);
                    updateAutoNumeric();
                }
            }
        };

        form.ajaxSubmit(options);
    });

    $(document).on('click','input[type="checkbox"].other-contact-owner', function() {
        var elem = $(this);
        var parent = elem.parent();
        var form = elem.closest('form');
        var container = $('#other_contact_owner_fields');

        if (elem.is(':checked')) {
            parent.append('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

            var options = {
                url: elem.attr('data-url'),
                dataType: 'json',
                success: function(response) {
                    container.html(response.content);
                },
                complete: function() {
                    parent.find('.ajax-loader').remove();
                }
            };

            form.ajaxSubmit(options);

        } else {
            container.html('');
        }
    });

    $(document).on('click','#account_suggested_btn',function(event){
        var elem = $(this);
        var errorSelect = $('#form_error_message');

        elem.button('loading');

        $.ajax({
            url: elem.attr('data-check-url'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    errorSelect.text('');
                    window.location.href = elem.attr('data-url');
                }

                if (response.status == 'error') {
                    errorSelect.text(response.message);
                }
            },
            complete: function() {
                elem.button('reset');
            }
        });

        event.preventDefault();
    });

    lockCompleteTransferCustodianCheckbox('#wealthbot_userbundle_client_account_type_transferInformation_is_firm_not_appear');
});

function showAccountForm(response)
{
    if(response.status == 'success'){
        showContentInRightBox(response.form);
        updateAutoNumeric();
        hideMainTitleMessage();
        addCompleteTransferCustodianEvent('.fin-inst-typeahead', '#wealthbot_userbundle_client_account_type_transferInformation_transfer_custodian_id');
    }
}

function successAccountForm(response)
{
    if(response.status == 'error') {
        if (response.form) {
            showContentInRightBox(response.form);
        }
        if (response.message) {
            var container = $("#account_type_form_container");
            container.find('form').before(getAlertMessage(response.message, 'error'));
        }
    }

    if(response.status == 'success') {
        if(response.content.length) {

            enabledLeftBox();
            showContentInRightBox(response.content);

            if(response.show_accounts_table) {
                showAccountsTable();
            }
            if(response.show_portfolio_button) {
                uncheckAccounts();
                showPortfolioActionButton();
            }

        }else{
            showContentInRightBox("Some error, please try again.");
        }
    }

    updateAutoNumeric();
}

function showContentInRightBox(content)
{
    var container = $("#account_type_form_container");
    container.html(content);

    var typehead = container.find('.fin-inst-typeahead');
    if (typehead.length > 0) {
        addCompleteTransferCustodianEvent('.fin-inst-typeahead', '#wealthbot_userbundle_client_account_type_transferInformation_transfer_custodian_id');
    }

    $("#accounts_table_container").hide();
}

function showPortfolioActionButton()
{
    $("#account_continue_btn").hide();
    $("#account_suggested_btn").show();
}

function hidePortfolioActionButton()
{
    $("#account_continue_btn").show();
    $("#account_suggested_btn").hide();
}

function hideMainTitleMessage()
{
    $("#main_title_message").hide();
}

function disabledLeftBox()
{
    $('#client_account_types_groups input[type="radio"]').attr('disabled', 'disabled');
}

function enabledLeftBox()
{
    $('#client_account_types_groups input[type="radio"]').removeAttr('disabled');
}

function showAccountsTable()
{
    var url = $("#accounts_table_container").attr("data-fetch-url");

    $.ajax({
        url: url,
        dataType: 'html',
        success: function(response){
            if (response.length > 0) {
                $("#accounts_table_container").html(response);
                $("#accounts_table_container").show();
                showPortfolioActionButton();
                scrollToElemId("accounts_table_container", "slow");
            } else {
                hidePortfolioActionButton();
            }

            updateAutoNumeric();
        }
    });
}

function showSuccessMessage()
{
    var url = $("#account_type_form_container").attr("data-success-url");

    $.ajax({
        url: url,
        dataType: 'json',
        success: function(response){
            if(response.status == 'success'){

                showContentInRightBox(response.content);

                if(response.show_accounts_table) {
                    showAccountsTable();
                }
                if(response.show_portfolio_button) {
                    uncheckAccounts();
                    showPortfolioActionButton();
                }
            }
            if(response.status == 'error'){
                alert('Error: '+response.message);
            }
        }
    });
}

function uncheckAccounts()
{
    $("#client_account_types_groups input[type='radio']").attr('checked', false);
}
// --------------- End Registration STEP 3 - ACCOUNTS --------------------------//