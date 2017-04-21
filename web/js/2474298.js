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
            if (!form.length ){
                alert('Please select account in left box');
                hasErrors = true;

            } else {
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
/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 18.02.13
 * Time: 17:18
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    $(document).on('click','.remove-account-btn', function(event){
        var elem = $(this);

        if(confirm('Are you sure?')){
            $.ajax({
                url: elem.attr('href'),
                dataType: 'json',
                success: function(response){
                    if(response.status == 'success'){
                        var row = elem.closest('tr');
                        var rowClass = row.attr('class');
                        var accountsSelector = elem.closest('.client-accounts');

                        var removeRow = $('.'+rowClass);
                        var retirementAccountFundsSelector = $('.client-retirement-account .'+rowClass).find('.select-retirement-account:checked');

                        if (!accountsSelector.length) {
                            accountsSelector = removeRow.closest('.client-accounts-list');
                        }

                        removeRow.remove();

                        if (accountsSelector.find('tr').length < 3) {
                            accountsSelector.find('.row-total .value').html('<strong>$0.00</strong>');
                            accountsSelector.find('.row-total .monthly-contributions').html('<strong>$0.00</strong>');
                            accountsSelector.find('.row-total .monthly-distributions').html('<strong>$0.00</strong>');
                        } else {
                            var value = parseFloat(response.total.value).formatMoney(2, '.', ',');
                            var monthlyContributions = parseFloat(response.total.monthly_contributions).formatMoney(2, '.', ',');
                            var monthlyDistributions = parseFloat(response.total.monthly_distributions).formatMoney(2, '.', ',');

                            accountsSelector.find('.row-total .value').html('<strong>$'+value+'</strong>');
                            accountsSelector.find('.row-total .monthly-contributions').html('<strong>$'+monthlyContributions+'</strong>');
                            accountsSelector.find('.row-total .monthly-distributions').html('<strong>$'+monthlyDistributions+'</strong>');
                        }

                        if(retirementAccountFundsSelector.length > 0){
                            $('.client-retirement-account-funds').html('');
                        }

                    }
                    if(response.status == 'error'){
                        alert('Error: '+response.message);
                    }
                }
            });
        }

        event.preventDefault();
    });

    $(document).on('click','.edit-account-btn', function(event){
        var elem = $(this);

        $.ajax({
            url: elem.attr('href'),
            dataType: 'json',
            success: function(response){
                if(response.status == 'success'){
                    $('#edit_account_modal .modal-body').html(response.form);
                    $('#edit_account_modal').modal('show');
                }
                if(response.status == 'error'){
                    alert('Error: '+response.message);
                }
                updateAutoNumeric();
            }
        });

        event.preventDefault();
    });

    $(document).on('submit','#edit_client_account_form', function(event){
        event.preventDefault();

        var form = $(this);

        var options = {
            dataType: 'json',
            type: 'POST',
            success: function(response){
                if(response.status === 'success'){
                    $('.client-accounts').html(response.accounts);
                    $('.client-accounts-list').html(response.accounts);
                    $('.client-retirement-account').html(response.retirement_accounts);

                    $('#edit_account_modal').modal('hide');
                    $('#edit_account_modal .modal-body').html('');
                } else {
                    form.replaceWith(response.form);
                }
            }
        };

        form.ajaxSubmit(options);
    });

    $(document).on('click','.update-account-btn',function(event){
        $('#edit_client_account_form').submit();
        event.preventDefault();
    });

    $(document).on('change','.select-retirement-account', function(event){
        var elem = $(this);
        var url = elem.data('url');

        $.ajax({
            url: url,
            dataType: 'json',
            success: function(response){
                if(response.status == 'success'){
                    $('.client-retirement-account-funds').html(response.content);
                }
            }
        });
    });

    $(document).on('submit','#retirement_account_fund_form', function(event){
        var form = $(this);
        var accountId = $('.select-retirement-account:checked').val();

        if(!accountId) {
            accountId = $("#outside_fund_account_id").val();
        }

        if(accountId){
            var options = {
                dataType: 'json',
                type: 'POST',
                data: { account_id: accountId },
                success: function(response){
                    if(response.status == 'success'){
                        $('.retirement-account-funds').html(response.content);
                        form.find('input[type="text"]').each(function(){
                            $(this).val('');
                        });
                    }
                    if(response.status == 'error'){
                        if (response.content) {
                            form.replaceWith(response.content);
                        } else {
                            alert(response.message);
                        }
                    }
                }
            };

            form.ajaxSubmit(options);
        }

        event.preventDefault();
    });

    $(document).on('click','.remove-outside-fund-btn', function(event){
        var elem = $(this);

        if(confirm('Are you sure?')){
            $.ajax({
                url: elem.attr('href'),
                dataType: 'json',
                success: function(response){
                    if(response.status == 'success'){
                        elem.closest('tr').remove();
                    }
                    if(response.status == 'error'){
                        alert('Error: '+response.message);
                    }
                }
            });
        }

        event.preventDefault();
    });

    selectAccountGroup();

    $(document).on('submit','#client_account_form', function(event){
        var form = $(this);

        var options = {
            dataType: 'json',
            type: 'POST',
            success: function(response){
                form.replaceWith(response.form);
                updateAutoNumeric();

                if(response.status === 'success'){
                    var clientRetirementAccount = $('.client-retirement-account');

                    $('.client-accounts').html(response.accounts);
                    if(clientRetirementAccount){
                        clientRetirementAccount.html(response.retirementAccounts);
                    }
                    $('.client-retirement-account-funds').html('');
                }

                selectAccountGroup();
            }
        };

        form.ajaxSubmit(options);
        event.preventDefault();
    });

    $('#wealthbot_userbundle_client_retirement_account_type_accountType').change(function(event){
        var selectOption = $(this).find('option:selected');
        $('.current-account-type').text(selectOption.text());
    });

    $(document).on('submit','#retirement_account_form', function(event){
        var form = $(this);

        var options = {
            dataType: 'json',
            type: 'POST',
            success: function(response){
                if(response.status === 'success'){
                    $('#retirement_account_form').replaceWith(response.content);
                }
                if(response.status === 'error'){
                    if(response.message == 'Not valid.') {
                        $('#retirement_account_form').replaceWith(response.content);
                    }
                }
            }
        };

        form.ajaxSubmit(options);
        event.preventDefault();
    });

    $(document).on('submit','#retirement_account_fund_form', function(event){
        var form = $(this);

        var options = {
            dataType: 'json',
            type: 'POST',
            success: function(response){
                if(response.status === 'success'){
                    $('.client-retirement-account-funds-list').html(response.content);
                    form[0].reset();
                }
                if(response.status === 'error'){
                    if(response.message == 'Not valid.'){
                        $('#retirement_account_fund_form').replaceWith(response.content);
                    }
                }
            }
        };

        form.ajaxSubmit(options);
        event.preventDefault();
    });
});

function selectAccountGroup() {
    var stepThreeForm = $('step_three_form');
    if (stepThreeForm.length > 0) {
        checkSelectedAccountGroup(stepThreeForm.attr('data-selected-group'));
    }
}

function checkSelectedAccountGroup(group){
    var selector = $('#wealthbot_userbundle_client_account_type_monthly_distributions').closest('.form-group');

    if (group == 'employer_retirement') {
        selector.hide();
        $('.retirement-value-help').show();
    } else {
        selector.show();
        $('.retirement-value-help').hide();
    }
}

/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 18.02.13
 * Time: 19:23
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    $(document).on('click','.see-investments-btn', function(event){
        var e = $(this);
        var selector = e.closest('td');

        if (e.hasClass('active')) {
            hideInvestments(e);
        } else {

            showAjaxLoader(selector);

            $.ajax({
                url: e.attr('data-url'),
                dataType: 'json',
                success: function(response){

                    if (response.status == 'success') {
                        showContentInOutsideFundList(response.content)
                    }
                    if (response.status == 'error') {
                        alert(response.message);
                    }

                    $('.see-investments-btn').removeClass('active').text('(See investments ▲)');
                    e.text('(Hide investments ▼)');
                    e.addClass('active');
                    scrollToElemId('outside_funds_list', 'slow');
                },
                complete: function() {
                    hideAjaxLoader(selector);
                }
            });
        }

        event.preventDefault();
    });

    $(document).on('click','.see-consolidated-accounts-btn', function (event) {
        var e = $(this);
        var selector = e.closest('td');

        if (e.hasClass('active')) {
            hideConsolidatedAccounts(e);
        } else {
            showAjaxLoader(selector);

            $.ajax({
                url: e.attr('data-url'),
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'success') {
                        showContentInConsolidatedAccountsList(response.content);
                    }
                    if (response.status == 'error') {
                        alert(response.message);
                    }

                    $('.see-consolidated-accounts-btn').removeClass('active').text('(See all accounts ▲)');
                    e.text('(Hide all accounts ▼)');
                    e.addClass('active');
                    scrollToElemId('consolidated_accounts_list', 'slow');
                },
                complete: function () {
                    hideAjaxLoader(selector);
                }
            });
        }

        event.preventDefault();
    });

    $(document).on('change','input[type="radio"].selected-model', function(){
        var url = $(this).attr('data-url');

        $.ajax({
            url: url,
            dataType: 'json',
            success: function(response){
                if (response.status == 'success') {
                    $('.model-details').html(response.content);
                }
                if (response.status == 'error') {
                    alert(response.message);
                }
            }
        });
    });

    $(document).on('click',".remove-account-btn", function(){
        var selector = $(this).closest('tr').find('.see-investments-btn');
        hideInvestments(selector);
    });

    $(document).on('click',".edit-account-btn", function(){
        var selector = $(this).closest('tr').find('.see-investments-btn');
        hideInvestments(selector);
    });
});

function showAjaxLoader(container)
{
    $(container).append('<img class="ajax-loader" src="/img/ajax-loader.gif" />');
}

function hideAjaxLoader(container)
{
    $(container).find('.ajax-loader').remove();
}

function showContentInOutsideFundList(content)
{
    $('.outside-funds-list').html(content);
}

function hideInvestments(container)
{
    showContentInOutsideFundList('');
    container.text('(See investments ▲)');
    container.removeClass('active');
}

function hideConsolidatedAccounts(container)
{
    showContentInConsolidatedAccountsList('');
    container.text('(See all accounts ▲)');
    container.removeClass('active');
}

function showContentInConsolidatedAccountsList(content)
{
    $('#consolidated_accounts_list').html(content);
}
