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

/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 27.02.13
 * Time: 17:43
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    $(document).on('click','.actions-list > li > a, .open-or-transfer-account-btn', function(event){
        var elem = $(this);
        var info = elem.next('.action-info');

        $('.actions-list').find('.action-info').each(function(){
            var _this = $(this);

            _this.slideUp();
            _this.find('input:checked').each(function(){
                var el = $(this);
                var subList = el.parent().next('.sub-list');

                el.attr('checked', false);
                if (subList.length > 0) {
                    subList.hide();
                }
            });
        });

        if (info.is(':visible')) {
            info.slideUp();
        } else {
            info.slideDown();
        }

        $('#result_container').html('');

        event.preventDefault();
    });

    $(document).on('change','input[type=radio].select-account', function() {
        var elem = $(this);
        var parent = elem.parent();

        parent.append('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

        $.ajax({
            url: elem.attr('data-url'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $('#result_container').html(response.content);
                }
            },
            complete: function(){
                parent.find('.ajax-loader').remove();
            }
        });
    });

    $(document).on('change','input[type=radio].change-account-contribution', function(){
        var revealElem = $(this).closest('label').next();
        $('.contribute-account-action input[type=radio].select-contribute-account').attr('checked', false);

        $('.contribute-account-action').slideUp();
        revealElem.slideDown();

        $('#result_container').html('');
    });

    $(document).on('change','input[type=radio].change-account-distribution', function(){
        var revealElem = $(this).closest('label').next();

        $('.distribute-account-action input[type=radio].select-distribute-account').attr('checked', false);

        $('.distribute-account-action').slideUp();
        revealElem.slideDown();

        $('#result_container').html('');
    });

    $(document).on('click','.open-or-transfer-account-btn, .change-portfolio-btn, .change-profile-btn, .is-qualified-switcher a', function(event){
        var elem = $(this);
        var parent = elem.parent();

        parent.append('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

        $.ajax({
            url: elem.attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $('#result_container').html(response.content);
                    drawModelCharts('.pie-chart');
                    $('#personal_information_marital_status, [name=personal_information\\[employment_type\\]]').change();
                }
            },
            complete: function(){
                parent.find('.ajax-loader').remove();
            }
        });

        event.preventDefault();
    });


    $(document).on('click','.close-account-reason-checkbox:not(:first)', function() {
        updateCloseAccountMessageBlock();
        updateCloseAllAccountMessageBlock();
    });

    $(document).on('click','#change_portfolio_form .btn-ajax, #approve_portfolio_form .btn-ajax',function(event){
        var button = this;
        var form = $(button).closest('form');
        var approve_url = form.attr('data-approve-url');

        $(button).button('loading');

        $(form).ajaxSubmit({
            target: (approve_url && approve_url == "rx_client_change_profile_approve_another_portfolio" ? $('#your_portfolio') : $('#result_container')),
            success: function(responseText, statusText, xhr, $form){
                updatePieCharts();
            },
            complete: function() {
                $(".btn").button('reset');
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.change-address-btn', function(event){
        var elem = $(this);
        var parent = elem.parent();

        parent.append('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

        $.ajax({
            url: elem.attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $('#result_container').html(response.content);

                    var isDifferentAddress = $('#client_address_is_different_address');

                    if (isDifferentAddress.length && isDifferentAddress.is(':checked')) {
                        $('.mailing-address-block').show();
                    }
                }
            },
            complete: function(){
                parent.find('.ajax-loader').remove();
            }
        });

        event.preventDefault();
    });

    $(document).on('submit','#edit_retirement_account_info_form', function(event){
        var form = $(this);

        ajaxSubmitForm(form, function(response) {
            var activeRadio = $('input[type=radio].edit-retirement-account-info:checked');

            activeRadio.attr('checked', false);
            activeRadio.next('span').text(response.account_title);
        });

        event.preventDefault();
    });

    $(document).on('submit','#edit_account_beneficiaries_form', function(event){
        var form = $(this);

        ajaxSubmitForm(form, function(response) {
            var activeRadio = $('input[type=radio].edit-account-benificiaries:checked');
            activeRadio.attr('checked', false);
        });

        event.preventDefault();
    });

    $(document).on('submit','#change_address_form', function(event){
        var form = $(this);

        ajaxSubmitForm(form, function(response) {});

        event.preventDefault();
    });

    $(document).on('click','.cancel-btn', function(event){
        $('#result_container').html('');
        $('.actions-list .action-info input[type=radio]:checked').attr('checked', false);

        event.preventDefault();
    });

    $(document).on('click','#add_bene',function(event) {
        var collectionHolder = $('#' + $(this).attr('data-target'));
        var prototype = collectionHolder.attr('data-prototype');
        var form = prototype.replace(/__name__/g, collectionHolder.children().length);
        collectionHolder.append(form);

        event.preventDefault();
    });

    $(document).on('click','.add-beneficiary-btn', function(event){
        var elem = $(this);

        elem.after('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

        $.ajax({
            url: elem.attr('href'),
            dataType: 'json',
            success: function(response){
                if (response.status == 'success') {
                    var form = $('#create_beneficiary_form');

                    if (form.length > 0) {
                        form.replaceWith(response.content);
                    } else {
                        elem.after(response.content);
                    }

                    elem.hide();
                    $('#beneficiaries_signature_form').hide();
                }
            },
            complete: function(){
                $('.ajax-loader').remove();
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.edit-beneficiary-btn', function(event){
        var elem = $(this);
        var parent = elem.closest('.beneficiary-item');

        parent.append('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

        $.ajax({
            url: elem.attr('href'),
            dataType: 'json',
            success: function(response){
                if (response.status == 'success') {
                    var form = $('#edit_beneficiary_form');
                    var addForm = $('#create_beneficiary_form');

                    if (addForm.length > 0) {
                        addForm.remove();
                    }

                    if (form.length > 0) {
                        form.replaceWith(response.content);
                    } else {
                        elem.closest('.beneficiaries-list').after(response.content);
                    }

                    $('.add-beneficiary-btn').hide();
                    $('#beneficiaries_signature_form').hide();
                }
            },
            complete: function(){
                parent.find('.ajax-loader').remove();
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.delete-beneficiary-btn', function(event){
        var elem = $(this);
        var parent = elem.closest('.beneficiary-item');

        parent.append('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

        $.ajax({
            url: elem.attr('href'),
            dataType: 'json',
            success: function(response){
                if (response.status == 'success') {
                    parent.remove();
                }
                if (response.status == 'error') {
                    alert(response.message);
                }
            }
        });

        event.preventDefault();
    });

    $(document).on('submit','#create_beneficiary_form', function(event){
        var form = $(this);

        ajaxSubmitForm(form, function(response){
            var beneficiariesList = $('.beneficiaries-list');

            beneficiariesList.append(response.content);
            if (response.form) {
                var beneficiariesSignForm = $('#beneficiaries_signature_form');
                if (beneficiariesSignForm.length) {
                    beneficiariesSignForm.replaceWith(response.form);
                } else {
                    $('#result_container').append(response.form);
                }
            }

            form.remove();
            $('.add-beneficiary-btn').show();
        });

        event.preventDefault();
    });

    $(document).on('submit','#edit_beneficiary_form', function(event){
        var form = $(this);

        ajaxSubmitForm(form, function(response){
            var beneficiariesList = $('.beneficiaries-list');

            beneficiariesList.find("[data-item='" + response.beneficiary_id + "']").replaceWith(response.content);
            if (response.form) {
                var beneficiariesSignForm = $('#beneficiaries_signature_form');
                if (beneficiariesSignForm.length) {
                    beneficiariesSignForm.replaceWith(response.form);
                } else {
                    $('#result_container').append(response.form);
                }
            }

            form.remove();
            $('.add-beneficiary-btn').show();
        });

        event.preventDefault();
    });

    $(document).on('submit','#beneficiaries_signature_form', function(event) {
        var form = $(this);

        ajaxSubmitForm(form, function() {
            form.remove();
        });
        event.preventDefault();
    });

    $(document).on('click','.close-bene', function(event){
        $('.add-beneficiary-btn').show();
        event.preventDefault();
    });

    $(document).on('click','.cancel-edit-beneficiary-btn', function(event){
        $(this).closest('form').remove();
        $('.add-beneficiary-btn').show();
        $('#beneficiaries_signature_form').show();

        event.preventDefault();
    });

    $(document).on('click','.close-bene', function(event) {
        $('#beneficiaries_signature_form').show();
    });

    $(document).on('change','input[type=radio].select-contribute-account', function() {
        var elem = $(this);
        var parent = elem.parent();

        parent.append('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

        $.ajax({
            url: elem.attr('data-url'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $('#result_container').html(response.content);

                    var type = $('input[id*="transfer_funding_type_type"]:checked');
                    if (type.length) {
                        checkFundingType(type.val());
                    } else {
                        $('#banktrans, #wiretrans, #mailcheck, #notfunding').slideUp();
                    }

                    updateInputmask();
                    updateAutoNumeric();
                }

                if (response.status == 'error') {
                    $('#result_container').html('');
                }

                if (response.message) {
                    var message = '<div class="alert alert-' + response.status + '">' + response.message +
                        '<a class="close" data-dismiss="alert" href="#">&times;</a></div>'

                    var alert = $('#result_container').find('.alert');
                    if (alert.length > 0) {
                        alert.replaceWith(message);
                    } else {
                        $('#result_container').prepend(message);
                    }
                }
            },
            complete: function(){
                parent.find('.ajax-loader').remove();
            }
        });
    });

    $(document).on('change','input[type=radio].select-distribute-account', function() {
        var elem = $(this);
        var parent = elem.parent();

        parent.append('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

        $.ajax({
            url: elem.attr('data-url'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $('#result_container').html(response.content);

                    var distributionType = $('input.distribution-type-radio:checked');

                    if (response.type == 'one_time') {
                        var typeVal = distributionType.length ? distributionType.val() : null;
                        checkDistributionType(typeVal);

                        updateInputmask();
                        updateAutoNumeric();
                    } else {
                        if (distributionType.length > 0) {
                            distributionType.change();
                        } else {
                            var autoDistrib = $('#auto_distribution');
                            if (autoDistrib.length > 0) {
                                var e = $('#auto_distribution');
                                updateDistributionForm(e, e, 'bank_transfer');
                            }
                        }
                    }
                }

                if (response.status == 'error') {
                    $('#result_container').html('');
                }

                if (response.message) {
                    var message = getAlertMessage(response.message, response.status);

                    var alert = $('#result_container').find('.alert');
                    if (alert.length > 0) {
                        alert.replaceWith(message);
                    } else {
                        $('#result_container').prepend(message);
                    }
                }
            },
            complete: function(){
                parent.find('.ajax-loader').remove();
            }
        });
    });

    $(document).on('change','input[id*="transfer_funding_type_type"]', function(){
        var value = $('input[id*="transfer_funding_type_type"]:checked').val();

        checkFundingType(value);
    });

    $(document).on('change','input.distribution-type-radio', function(){
        var elem = $(this);
        var value = $('input.distribution-type-radio:checked').val();

        updateDistributionForm(elem, elem.parent().next(), value);
    });

    $(document).on('click','.edit-bank-info-btn', function(event){
        var elem = $(this);
        var parent = elem.parent('.bank-short-info');

        parent.next('.bank-info').slideDown();
        parent.remove();

        event.preventDefault();
    });

    $(document).on('submit','#contribute_account_form', function(event){
        var form = $(this);

        var onSuccess = function(response){
            form.remove();

            if (response.content) {
                $('#result_container').html(response.content);
                addDocusignEventListeners();
            } else {
                $('.contribute-account-action input[type=radio]').attr('checked', false);
                location.reload();
            }
        };

        var onError = function(reponse){
            var type = $('input[id*="transfer_funding_type_type"]:checked');
            if (type.length) {
                checkFundingType(type.val());
            } else {
                $('#banktrans, #wiretrans').slideUp();
                $('#mailcheck, #wiretrans').slideUp();
                $('#banktrans, #mailcheck').slideUp();
            }

            updateInputmask();
            updateAutoNumeric();
        };

        ajaxSubmitForm(form, onSuccess, onError);

        event.preventDefault();
    });

    $(document).on('submit','#distribute_account_form', function(event){
        var form = $(this);

        var onSuccess = function(response) {
            form.remove();

            if (response.content) {
                $('#result_container').html(response.content);
                addDocusignEventListeners();
            } else {
                $('.distribute-account-action input[type=radio]').attr('checked', false);
                location.reload();
            }
        };

        var onError = function(){
            var checkedType = $('input.distribution-type-radio:checked');
            if (checkedType.length > 0) {
                checkedType.change();
            } else {
                var autoDistrib = $('#auto_distribution');
                if (autoDistrib.length > 0) {
                    var e = $('#auto_distribution');
                    updateDistributionForm(e, e, 'bank_transfer');
                }
            }
        };

        ajaxSubmitForm(form, onSuccess, onError);

        event.preventDefault();
    });

    $(document).on('submit','#contribution_signature_form, #distribution_signature_form, #bank_information_signature_form', function(event) {
        var form = $(this);
        var callback = function(response) {
            var container = $('#result_container');
            var alert = getAlertMessage(response.message, response.status);

            if (response.content) {
                container.html(response.content);
                container.prepend(alert);
            } else {
                container.html(alert);
            }

        };

        ajaxSubmitForm(form, callback, callback);
        event.preventDefault();
    });

    $(document).on('click','.close-selected-account', function(){
        var elem = $(this);
        var parent = elem.parent();
        var checked = $('.close-selected-account:checked');
        var form = $('#close_accounts_form');

        updateCloseAllAccountMessageBlock();
        if (form.length < 1) {
            parent.append('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

            $.ajax({
                url: elem.closest('.action-info').attr('data-action-url'),
                dataType: 'json',
                success: function(response) {
                    if (response.status == 'success') {
                        $('#result_container').html(response.content);
                        updateCloseAllAccountMessageBlock();
                    }
                    if (response.status == 'error') {
                        $('#result_container').html('');
                    }
                    if (response.message) {
                        var message = '<div class="alert alert-' + response.status + '">' + response.message +
                            '<a class="close" data-dismiss="alert" href="#">&times;</a></div>'

                        var alert = $('#result_container').find('.alert');
                        if (alert.length > 0) {
                            alert.replaceWith(message);
                        } else {
                            $('#result_container').prepend(message);
                        }
                    }
                },
                complete: function(){
                    parent.find('.ajax-loader').remove();
                }
            });
        }
    });

    $(document).on('submit','#close_accounts_form', function(event){
        var form = $(this);

        var accountsIds = $(".close-selected-account:checked").map(function () {
            return this.value;
        }).get();

        // data for firm field with id: system_accounts_closing_accounts_ids
        var extraData = { close_accounts: { accounts_ids: accountsIds } };

        var onSuccess = function(){
            form.remove();
            $('.close-selected-account').attr('checked', false);
            $('.close-accounts-btn').hide();
            location.reload();
        };

        ajaxSubmitForm(form, onSuccess, null,  extraData);

        event.preventDefault();
    });

    $(document).on('click','.close-account-reason-checkbox:first', function() {
        var elem = $(this);
        var href = $('.open-or-transfer-account-btn').attr('href');
        elem.find('span').remove();
        elem.closest('li').append('<span>You must use <a href="' + href + '" class="open-or-transfer-account-btn">Option #2 - Open or transfer an account</a> to perform this action</span>');
    });

    $(document).on('click','.pagination a', function(event) {
        var btn = $(this);
        $.ajax({
            url: btn.attr('href'),
            dataType: 'json',
            success: function(response) {
                $('#ria_dashboard_client_content').html(response.content);
            }
        });

        event.preventDefault()
    });

    $(document).on('click','#close_account_message a.close', function() {
        is_show_close_account_rebalancer_message = false;
    });

});

function updateDistributionForm(elem, rootSelector, value) {
    var generalFormFields = elem.closest('form').attr('data-general-fields');
    var transferInfoFormFields = elem.closest('form').attr('data-transfer-info-fields');
    var bankInfoFields = elem.closest('form').attr('data-bank-info-fields');

    var generalFieldsSelector = rootSelector.find('.distribution-form-general-fields');
    var transferInfoFieldsSelector = rootSelector.find('.distribution-form-transfer-info-fields');

    $('.distribution-form-general-fields').html('');
    $('.distribution-form-transfer-info-fields').html('');
    $('.distribution-form-bank-info-fields').html('');

    if (value == 'bank_transfer' || value == 'wire_transfer') {
        rootSelector.find('.bank-info .distribution-form-bank-info-fields').html(bankInfoFields);
    }

    if (generalFormFields.length > 0) {
        generalFieldsSelector.html(generalFormFields);
        generalFieldsSelector.show();
    } else {
        generalFieldsSelector.hide();
    }

    if (transferInfoFieldsSelector.length > 0) {
        transferInfoFieldsSelector.html(transferInfoFormFields);
        transferInfoFieldsSelector.show();
    } else {
        transferInfoFieldsSelector.hide();
    }

    updateAutoNumeric();
    checkDistributionType(value);
}

function checkDistributionType(type) {
    type = type || null;

    switch (type) {
        case 'receive_check':
            $('#receive_check').slideDown();
            $('#bank_transfer, #wire_transfer, #not_funding').slideUp();
            break;
        case 'bank_transfer':
            $('#bank_transfer').slideDown();
            $('#receive_check, #wire_transfer, #not_funding').slideUp();
            break;
        case 'wire_transfer':
            $('#wire_transfer').slideDown();
            $('#receive_check, #bank_transfer, #not_funding').slideUp();
            break;
        case 'not_funding':
            $('#not_funding').slideDown();
            $('#receive_check, #bank_transfer, #wire_transfer').slideUp();
            break;
        default:
            $('#receive_check, #wire_transfer, #bank_transfer, #not_funding').slideUp();
            break;
    }
}

function ajaxSubmitForm(form, onSuccessCallback, onErrorCallback, extraData) {
    extraData = extraData || null;

    form.find('input[type="submit"]').after('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

    var options = {
        dataType: 'json',
        type: 'POST',
        data: extraData,
        success: function(response){
            if(response.status == 'success'){
                if (onSuccessCallback) {
                    onSuccessCallback(response);
                }
            }

            if(response.status == 'error'){
                form.replaceWith(response.content);

                if (onErrorCallback) {
                    onErrorCallback(response);
                }
            }

            if (response.message) {
                var message = '<div class="alert alert-' + response.status + '">' + response.message +
                    '<a class="close" data-dismiss="alert" href="#">&times;</a></div>'

                var alert = $('#result_container').find('.alert');
                if (alert.length > 0) {
                    alert.replaceWith(message);
                } else {
                    $('#result_container').prepend(message);
                }
            }
        },
        complete: function() {
            form.find('.ajax-loader').remove();
        }
    };

    form.ajaxSubmit(options);
}

function updatePieCharts() {
    $('.pie-chart').each(function (key, element) {
        drawModelChart(element);
    });
}

var is_show_close_account_rebalancer_message = true;
function updateCloseAccountMessageBlock() {
    var count_reasons = $('.close-account-reason-checkbox:not(:first):checked').length;

    var close_account_message_block = $('#close_account_message');

    if (is_show_close_account_rebalancer_message && count_reasons > 0) {
        close_account_message_block.show();
    } else {
        close_account_message_block.hide();
    }
}

function updateCloseAllAccountMessageBlock() {
    var account_remaining = $('.close-selected-account:not(:checked)').length;
    var close_all_account_message_block = $('#close_all_account_message');

    if (account_remaining > 0) {
        close_all_account_message_block.hide();
    } else {
        close_all_account_message_block.show();
    }
}

/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 14.02.13
 * Time: 13:29
 * To change this template use File | Settings | File Templates.
 */
$(function(){
    init();

    $('#transfer_basic_is_different_address, #transfer_additional_basic_is_different_address, ' +
        '#client_address_is_different_address, #personal_information_is_different_address').on('change', function () {
        var isChecked = $(this).is(':checked');

        if (isChecked) {
            $('.mailing-address-block').show();
        } else {
            $('.mailing-address-block').hide();
        }
    });

    $(document).on('change','.employment-status input[type="radio"]', function() {
        var val = $(this).parent().find('input[type="radio"]:checked').val();
        updateEmploymentStatus(val);
    });

    $(document).on('change','.broker-security-exchange-person-block input[type="radio"]', function(){
        checkVisible('broker-security-exchange-person');
    });
    $(document).on('change','.publicly-traded-company-block input[type="radio"]', function(){
        checkVisible('publicly-traded-company');
    });
    $(document).on('change','.political-figure-block input[type="radio"]', function(){
        checkVisible('political-figure');
    });

    $(document).on('change','#personal_information_marital_status', function () {
        var value = $(this).val();

        if (value === 'Married') {
            $('.spouse-fields-block').slideDown();
        } else {
            $('.spouse-fields-block').slideUp();
        }
    });

    $(document).on('click','.close', function(event){
        $(this).parent('.well').remove();
    });

    $(document).on('click','#transfer_funding_distributing_has_funding', function(){
        toggleFundingBlock($(this).is(':checked'));
    });
    $(document).on('click','#transfer_funding_distributing_has_distributing', function(){
        toggleDistributingBlock($(this).is(':checked'));
    });
    $(document).on('change','input[id*="transfer_funding_distributing_funding_type"]', function(){
        var value = $('input[id*="transfer_funding_distributing_funding_type"]:checked').val();
        checkFundingType(value);
    });

    $(document).on('click','.edit-client_info-btn', function (event) {
        var e = $(this);

        e.after('<img class="ajax-loader" style="margin-left:5px;" src="/img/ajax-loader.gif" />');

        $.ajax({
            url: e.attr('href'),
            dataType: 'json',
            success: function (response) {
                if (response.status == 'success') {
                    e.addClass('account-status-lnk completed');
                    e.attr('data-checked', true);

                    $('#modal_dialog .modal-header h3').html('Review Your Information');
                    $('#modal_dialog .modal-body').html(response.content);

                    init();

                    $('#modal_dialog').modal('show');
                }

                if (response.status == 'error') {
                    alert(response.message);
                }
            },
            complete: function() {
                e.parent().find('.ajax-loader').remove();
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.update-owner-info-btn', function(event){
        $('#review_owner_information_form').submit();
        event.preventDefault();
    });

    $(document).on('submit','#review_owner_information_form', function(event){
        var form = $(this);

        $('#modal_dialog .modal-footer').append('<img class="ajax-loader" style="margin-left:5px;" src="/img/ajax-loader.gif" />');

        var options = {
            dataType: 'json',
            type: 'POST',
            success: function(response){
                if (response.status == 'success') {
                    $('.client-info').html(response.content);
                    $('#modal_dialog .modal-body').html('');
                    $('#modal_dialog').modal('hide');
                }
                if (response.status == 'error') {
                    if (response.content) {
                        $('#modal_dialog .modal-body').html(response.content);
                        init();
                    }
                    if (response.message) {
                        alert(response.message);
                    }
                }
            },
            complete: function() {
                $('#modal_dialog .modal-footer').find('.ajax-loader').remove();
            }
        };

        form.ajaxSubmit(options);
        event.preventDefault();
    });

    $(document).on('click','#transfer_information_is_penalty_free', function(){
        $('#transfer_information_penalty_amount').val('');
    });

    $('#transfer_information_penalty_amount').on('focus', function(){
        $('#transfer_information_is_penalty_free').attr('checked', false);
    });

    $(document).on('change','input[id*=transfer_information_transfer_from]', function(){
        var elem = $(this);
        var form = elem.closest('form');
        var current_id = elem.attr('id');

        form.find('.child-block').slideUp();
        elem.parent().next('.child-block').slideDown();

        $('input[id*=transfer_information_transfer_from]:not([id='+current_id+'])').each(function(){
            var childrenInputsBlock = $(this).parent().next('.child-block');

            childrenInputsBlock.find('input[type=text]').val('');
            childrenInputsBlock.find('input[type=radio]').attr('checked', false);
        });

        // If checked radio Certificates of Deposit then check  Redeem my CD immediately radio
        var isCertificatesDeposit = $('#transfer_information_transfer_from_3').attr('checked');
        $('#transfer_information_redeem_certificates_deposit').attr('checked', isCertificatesDeposit);
    });

    $(document).on('click','#add_bene', function(event) {
        var collectionHolder = $('#' + $(this).attr('data-target'));
        var prototype = collectionHolder.attr('data-prototype');
        var form = prototype.replace(/__name__/g, collectionHolder.children().length);
        collectionHolder.append(form);

        event.preventDefault();
    });

    $(document).on('click','.add-new-bank-btn', function(event) {
        var elem = $(this);
        var html = $('.def-form').attr('data-bank-info-form');
        var parent = elem.closest('.inner-ch');

        parent.find('.add-new-bank-form').html(html);
        parent.find('#bank_information_form_fields').attr('data-type', elem.attr('data-type'));
        parent.find('.add-new-bank-btn').hide();

        updateInputmask();

        event.preventDefault();
    });

    $(document).on('click','.cancel-create-bank-info-btn', function(event) {
        var elem = $(this);

        elem.closest('#bank_information_form_fields').remove();
        $('.add-new-bank-btn').show();

        event.preventDefault();
    });


    $(document).on('click','.add-joint-account-owner-btn', function(event) {
        var block = $(this).closest('.form-group');

        block.next('.joint-account-owner').show();
        block.hide();

        event.preventDefault();
    });

    $(document).on('click','#create_bank_information_btn', function(event) {
        var btn = $(this);
        var formFieldsSelector = btn.closest('#bank_information_form_fields');
        var type = formFieldsSelector.attr('data-type');
        var form = btn.closest('form');

        btn.button('loading');

        var options = {
            url: formFieldsSelector.attr('data-url'),
            type: 'POST',
            dataType: 'json',
            success: function(response){
                if (response.status == 'success') {
                    if (response.form_fields) {
                        $('#banktrans').html(response.form_fields);
                    }
                    if (response.bank_account_item) {
                        $('.bank-accounts-list').append(response.bank_account_item);
                        $('.add-new-bank-form').html('');
                    }

                    if (type && type == 'account-management') {
                        updateContributionDistributionForm(form.attr('action'));
                    } else {
                        $('.add-new-bank-btn').show();
                    }
                }

                if (response.status == 'error') {
                    formFieldsSelector.replaceWith(response.form);
                    updateInputmask();
                }
            },
            complete: function() {
                btn.button('reset');
            }
        };

        form.ajaxSubmit(options);
        event.preventDefault();
    });

    $(document).on('click','.edit-bank-account-btn', function(event) {
        var elem = $(this);
        var parent = elem.parent();

        $('.add-new-bank-form').html('');
        $('.add-new-bank-btn').show();
        parent.append('<img class="ajax-loader" style="margin-left:5px;" src="/img/ajax-loader.gif" />');

        $.ajax({
            url: elem.attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $('#modal_dialog .modal-header h3').html('Edit Bank');
                    $('#modal_dialog .modal-body').html(response.content);

                    updateInputmask();

                    $('#modal_dialog').modal('show');
                }
                if (response.status == 'error') {
                    if (response.message) {
                        alert(response.message);
                    }
                }
            },
            complete: function() {
                parent.find('.ajax-loader').remove();
            }
        });

        event.preventDefault();
    });

    $(document).on('submit','#edit_bank_account_form', function(event) {
        var form = $(this);

        $('#modal_dialog .modal-footer').append('<img class="ajax-loader" style="margin-left:5px;" src="/img/ajax-loader.gif" />');

        var options = {
            dataType: 'json',
            type: 'POST',
            success: function(response){
                if (response.status == 'success') {
                    if (response.form_fields) {
                        $('#banktrans').html(response.form_fields);
                    }

                    if (response.bank_account_id && response.bank_account_item) {
                        $('.bank-item[data-bank-account-item="' + response.bank_account_id + '"]').replaceWith(response.bank_account_item);
                    }

                    if (response.content && $('.client-account-management').length > 0) {
                        $('#result_container').html(response.content);
                    }

                    $('#modal_dialog .modal-header h3').html('');
                    $('#modal_dialog .modal-body').html('');
                    $('#modal_dialog').modal('hide');
                }
                if (response.status == 'error') {
                    if (response.content) {
                        form.replaceWith(response.content);
                        updateInputmask();
                    }
                    if (response.message) {
                        alert(response.message);
                    }
                }
            },
            complete: function() {
                $('#modal_dialog .modal-footer').find('.ajax-loader').remove();
            }
        };

        form.ajaxSubmit(options);
        event.preventDefault();
    });

    $(document).on('click','.delete-bank-account-btn', function(event) {
        event.preventDefault();

        var elem = $(this);
        var parent = elem.parent();

        if (confirm('Are you sure?')) {
            parent.append('<img class="ajax-loader" style="margin-left:5px;" src="/img/ajax-loader.gif" />');

            $.ajax({
                url: elem.attr('href'),
                dataType: 'json',
                success: function(response) {
                    if (response.status == 'success') {
                        elem.closest('.bank-item').remove();
                    }
                    if (response.status == 'error') {
                        alert(response.message);
                    }
                },
                complete: function() {
                    parent.find('.ajax-loader').remove();
                }
            });
        }
    });

    $(document).on('change','#transfer_account_form_sections .policy-information input[type="radio"], #transfer_account_form_sections .transfer-custodian-question input[type="radio"]', function(event) {
        var form = $(this).closest('form');
        var btn = form.find('input[type="submit"]');
        var sections = $('#transfer_account_form_sections');

        var options = {
            url: form.attr('data-update-url'),
            dataType: 'json',
            type: 'POST',
            success: function(response) {
                if (response.status === 'error') {
                    alert(response.message);
                }

                if (response.status === 'success') {
                    var questionSection = sections.find('.transfer-custodian-question');
                    var discrepanciesSection = sections.find('.account-discrepancies');

                    if (questionSection.length > 0) {
                        questionSection.replaceWith(response.custodian_questions_fields);
                    } else {
                        sections.find('.policy-information').after(response.custodian_questions_fields);
                    }

                    if (discrepanciesSection.length > 0) {
                        discrepanciesSection.replaceWith(response.account_discrepancies_fields);
                    } else {
                        sections.find('.financial-institution-information').after(response.account_discrepancies_fields);
                    }
                }
            },
            complete: function() {
                btn.button('reset');
            }
        };

        btn.button('loading');
        form.ajaxSubmit(options);
    });

    $(document).on('click','.electronically-signing-btn', function(event) {
        var btn = $(this);

        var ownersInfoList = $('.account-owners-information-list');
        if (ownersInfoList.length > 0) {
            var notCheckedInfo = ownersInfoList.find('[data-checked="false"]');
            if (notCheckedInfo.length > 0) {
                alert('Please review personal information of account owners.');
                return false;
            }
        }

        var docusignWindow = window.open(btn.attr('data-url'));

        event.preventDefault();
    });

    $('#modal_dialog').on('hidden', function() {
        var dialog = $(this);

        if (dialog.hasClass('electronic-sign-modal')) {
            dialog.removeClass('electronic-sign-modal');
            dialog.find('.modal-footer').show();
        }
    });

    $(document).on('click','input[type="checkbox"].check-not-signed-applications', function(event) {
        var elem = $(this);
        var parent = elem.parent();
        var errorSelector = parent.parent().find('.error, .error-list');

        if (elem.is(':checked')) {
            elem.attr('disabled', 'disabled');
            parent.append('<img class="ajax-loader" src="/img/ajax-loader.gif" style="margin-left: 5px" />');

            $.ajax({
                url: elem.attr('data-url'),
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        if (errorSelector.length > 0) {
                            errorSelector.remove();
                        }
                    }

                    if (response.status === 'error') {
                        if (errorSelector.length > 0) {
                            errorSelector.html(response.message);
                        } else {
                            parent.before('<p class="error">' + response.message + '</p>');
                        }

                        elem.removeAttr('checked');
                    }
                },
                complete: function() {
                    elem.removeAttr('disabled');
                    parent.find('.ajax-loader').remove();
                }
            });
        }
    });
});


function init() {
    updateAutoNumeric();
    updateInputmask();

    var isBasicDifferentAddress = $('#transfer_basic_is_different_address');
    var isAdditionalBasicDifferentAddress = $('#transfer_additional_basic_is_different_address');
    var isPersonalDifferentAddress = $('#personal_information_is_different_address');
    var employerStatus = $('.employment-status input[type="radio"]:checked');

    if (
        (isBasicDifferentAddress.length && isBasicDifferentAddress.is(':checked')) ||
        (isAdditionalBasicDifferentAddress.length && isAdditionalBasicDifferentAddress.is(':checked')) ||
        (isPersonalDifferentAddress.length && isPersonalDifferentAddress.is(':checked'))
    ) {
        $('.mailing-address-block').show();
    }

    if (employerStatus.length > 0) {
        updateEmploymentStatus(employerStatus.val());
    }

    checkVisible('political-figure');
    checkVisible('publicly-traded-company');
    checkVisible('broker-security-exchange-person');

    var fundingBlock = $('#funding');
    if (fundingBlock.length) {
        checkFundingType($('input[id*="transfer_funding_distributing_funding_type"]:checked').val());
    }

    var isMarried = $('#personal_information_marital_status').val() == 'Married';
    if (isMarried) {
        $('.spouse-fields-block').slideDown();
    }

    $('.def-form .child-block').slideUp();
    $('input[id*=transfer_information_transfer_from]:checked').parent().next('.child-block').slideDown();
}

function updateEmploymentStatus(status) {
    var employedBlock = $('.employed-block');
    var unemployedBlock = $('.unemployed-block');

    if (employedBlock.length && unemployedBlock.length) {
        if (status == 'Employed' || status == 'Self-Employed') {
            employedBlock.slideUp();
            unemployedBlock.slideDown();
        }

        if (status == 'Retired' || status == 'Unemployed') {
            employedBlock.slideDown();
            unemployedBlock.slideUp();
        }
    }
}

function checkVisible(selectorClass)
{
    var block = $('.'+selectorClass+'-block').find('input[type="radio"]:checked');
    var fields = $('.'+selectorClass+'-fields');
    var value;

    if (fields.length) {
        if (block.length > 0) {
            value = block.val();
        }

        if (value == 1) {
            fields.slideDown();
        } else {
            fields.slideUp();
        }
    }
}

function toggleFundingBlock(isShow) {
    var fundingBlock = $('#funding');

    if (fundingBlock.length) {
        if (isShow) {
            fundingBlock.slideDown();
        } else {
            fundingBlock.slideUp();
        }
    }
}

function toggleDistributingBlock(isShow) {
    var distributingBlock = $('#distrib');

    if (distributingBlock.length) {
        if (isShow) {
            distributingBlock.slideDown();
        } else {
            distributingBlock.slideUp();
        }
    }
}

function checkFundingType(type) {
    switch (type) {
        case 'funding_mail_check':
            $('#mailcheck').slideDown();
            $('#banktrans, #wiretrans, #notfunding').slideUp();
            break;
        case 'funding_bank_transfer':
            $('#banktrans').slideDown();
            $('#mailcheck, #wiretrans, #notfunding').slideUp();
            break;
        case 'funding_wire_transfer':
            $('#wiretrans').slideDown();
            $('#banktrans, #mailcheck, #notfunding').slideUp();
            break;
        case 'not_funding':
            $('#notfunding').slideDown();
            $('#banktrans, #mailcheck, #wiretrans').slideUp();
            break;
        default:
            $('#banktrans, #mailcheck, #wiretrans, #notfunding').slideUp();
            break;
    }
}

function updateContributionDistributionForm(url) {
    $('#result_container').html('<div class="ajax-loader" style="text-align: center;"><img src="/img/ajax-loader.gif" /></div>');

    $.ajax({
        url: url,
        dataType: 'json',
        success: function(response) {
            if (response.status == 'success') {
                $('#result_container').html(response.content);

                var value = $('input[id*="transfer_funding_distributing_funding_type"]:checked').val();
                checkFundingType(value);

                var e = $('#auto_distribution');
                if (e.length > 0) {
                    updateDistributionForm(e, e, 'bank_transfer');
                } else {
                    checkDistributionType();
                }
            }

            if (response.status == 'error') {
                alert(response.message);
            }
        },
        complete: function() {
            $('#result_container').find('.ajax-loader').remove();
        }
    });
}
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
        $('#result_container').html('<div class="client-management-transfer-form">'+response+'</div>');
    }

    updateAutoNumeric();
}

function showTransferStep(transferUrl) {
    var container = $('#result_container');

    $.get(transferUrl, function(data) {
        var html = '<div class="client-management-transfer-form">'+data+'</div>';
        container.html(html);
        init();
    });
}
/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 01.07.13
 * Time: 17:15
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    updateIsMarriedAndIsDifferentAddressFields();
    updateInputmask();

    $('#wealthbot_client_bundle_profile_type_marital_status').change(function () {
        var value = $(this).val();
        var citizenship_label = $('#citizenship .main-label label');

        if (value === 'Married') {
            $('.spouse-fields-block').show();
            citizenship_label.text('Are you and your spouse both U.S. citizens?');
        } else {
            $('.spouse-fields-block').hide();
            citizenship_label.text('Are you a U.S. citizen?');
        }
    });

    $('#wealthbot_client_bundle_profile_type_is_different_address').change(function () {
        var isChecked = $(this).is(':checked');

        if (isChecked) {
            $('.mailing-address-block').show();
        } else {
            $('.mailing-address-block').hide();
        }
    });

    $('input[name="wealthbot_client_bundle_profile_type[citizenship]"]').change(function () {
        if ($(this).val() == 0) {
            alert('You must be a U.S. Citizen to use our services. You may not continue if you are not a U.S. citizen.');
        }
    });

    $(document).on('click','#step_one_logout_btn', function() {
        var elem = $(this);
        var form = $('form[data-save="true"]');

        if (form.length < 1) {
            document.location.reload();
        } else {
            form.ajaxSubmit({
                success: function(){
                    window.location.href = elem.attr('data-url');
                }
            });
        }
    });
});

function updateIsMarriedAndIsDifferentAddressFields() {
    var isMarried = $('#wealthbot_client_bundle_profile_type_marital_status').val() == 'Married' ? true : false;
    var isDifferentAddress = $('#wealthbot_client_bundle_profile_type_is_different_address').is(':checked');

    if (isMarried) {
        $('.spouse-fields-block').show();
    }

    if (isDifferentAddress) {
        $('.mailing-address-block').show();
    }
}
$(function(){
    $(document).on('click','#your_information input[type="submit"]', function(event) {
        var btn = $(this);
        var form = btn.closest('form');

        btn.button('loading');

        var options = {
            dataType: 'json',
            type: 'POST',
            success: function(response){
                form.replaceWith(response.form);

                init();
            },
            complete: function() {
                btn.button('reset');
            }
        };

        form.ajaxSubmit(options);

        event.preventDefault();
    });

    $(document).on('click','#update_password .btn-ajax, #manage_users .btn-ajax',function(event){
        var button = this;
        var form = $(button).closest('form');

        $(button).button('loading');

        $(form).ajaxSubmit({
            target:  form.closest('.tab-pane'),
            success: function(responseText, statusText, xhr, $form){

            },
            complete: function() {
                $(".btn").button('reset');
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.manage-users-edit-btn, .manage-users-delete-btn, .manage-users-cancel-edit-btn', function(event){
        var button = $(this);

        button.button('loading');

        $.ajax({
            url: button.attr('href'),
            success: function(response) {
                button.button('reset');
                button.closest('.tab-pane').html(response);
            }
        });
        event.preventDefault();

    });

    $(document).on('click','.change-profile-temp-rebalance-btn, .show-previous-client-portfolio-btn', function(event) {
        var btn = $(this);
        btn.parent().append('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

        $.ajax({
            url: btn.attr('href'),
            success: function(response) {
                if (response.status == 'success') {
                    btn.closest('#your_portfolio').html(response.content);
                    drawModelChart('.pie-chart');
                }

                if (response.status == 'error') {
                    alert('Error: ' + response.content);
                }
            },
            complete: function() {
                $('.ajax-loader').remove();
            }
        });

        event.preventDefault();
    });

    $(document).on('submit','#choose_another_portfolio_form', function(event) {
        var form = $(this);
        var btn = form.find('input[type="submit"]');

        btn.button('loading');

        form.ajaxSubmit({
            target: $('#your_portfolio'),
            success: function() {
                drawModelChart('.pie-chart');
            }
        });

        event.preventDefault();
    });

});

function ajaxChangeProfile(url) {
    window.counter = 0;
    var elem = $('.actions-list .change-profile-btn:first');
    if (url && elem.length > 0) {
        var parent = elem.parent();

        parent.append('<img class="ajax-loader" src="/img/ajax-loader.gif" />');
        window.counter++;

        $.ajax({
            url: url,
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $('#result_container').html(response.content);
                    drawModelCharts('.pie-chart');
                }
            },
            complete: function(){
                parent.find('.ajax-loader').remove();
            }
        });

        event.preventDefault();
    }
}

var billing = {};

$(function() {

    billing.curYear = null;
    billing.curQuarter = null;
    billing.is_client_view = null;
    billing.showedQuarter = null;
    billing.client_created_at = null;
    billing.container = null;

    billing.init = function() {
        var today = new Date();

        billing.curYear = today.getFullYear();
        billing.curQuarter = Math.floor((today.getMonth() + 3) / 3);
        billing.showedQuarter = billing.curYear + '-' + billing.curQuarter;
        billing.is_client_view = 0;
        billing.container = $('#billingDataContent');
        billing.client_created_at = billing.container.attr('data-client-created-at');

        billing.initQuarters();
    };

    billing.initQuarters = function(){
        var quarters = $('a[data-role="quarter"]');
        var currentDate = new Date();
        var riaCreatedAt = new Date(billing.client_created_at);

        quarters.hide();
        quarters.removeClass('active');

        for (var i=1; i<=4; i++) {
            var date = new Date(billing.curYear + '/' + (i * 3) + '/01 12:00:00 AM');
            date.setMonth(date.getMonth() + 1);
            if (date.getTime() > riaCreatedAt.getTime()) {
                date = new Date(billing.curYear + '/' + ((i-1) * 3 + 1) + '/01 12:00:00 AM');
                if (date.getTime() < currentDate.getTime()) {
                    quarters.eq(i-1).show();
                    if (billing.showedQuarter == billing.curYear + '-' + i) {
                        quarters.eq(i-1).addClass('active');
                    }
                }
            }
        }

    };

    billing.changeYear = function(e) {
        var self = $(this);

        billing.curYear = self.val();
        billing.initQuarters();
    };

    $(document).on('change','#years', billing.changeYear);

    billing.clickQuarter = function(e) {
        e.preventDefault();

        var self = $(this);

        $('a[data-role="quarter"]').removeClass('active');
        self.addClass('active');

        billing.curQuarter = self.data('value');

        var url = Routing.generate('wealthbot_client_billing_period', {
            year: billing.curYear,
            quarter: billing.curQuarter,
            is_client_view: billing.is_client_view
        });

        self.addClass('js-loading disabled');

        $.ajax({
            url: url,
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success'){
                    billing.container.html(response.content);
                    billing.showedQuarter = billing.curYear + '-' + billing.curQuarter;
                    billing.initQuarters();
                }
            }
        });
    };

    $(document).on('click','a[data-role="quarter"]', billing.clickQuarter);

    // Start module
    billing.init();
});
var performance = {};

$(function() {

    performance.curPeriod = null;
    performance.container = null;
    performance.is_client_view = null;

    performance.init = function() {
        performance.container = $('#performanceDataContent');
        performance.is_client_view = 0;
    };

    performance.clickPeriod = function(e) {
        e.preventDefault();

        var self = $(this);

        $('a[data-role="period"]').removeClass('active');
        self.addClass('active');

        performance.curPeriod = self.data('value');

        var url = Routing.generate('wealthbot_client_performance_period', {
            period: performance.curPeriod,
            account_id: $('#select_account_type').val(),
            is_client_view: performance.is_client_view
        });

        self.addClass('js-loading disabled');

        $.ajax({
            url: url,
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success') {
                    performance.container.html(response.content);
                    self.removeClass('js-loading disabled');
                }
            }
        });
    };

    $(document).on('click','a[data-role="period"]', performance.clickPeriod);

    // Start module
    performance.init();
});