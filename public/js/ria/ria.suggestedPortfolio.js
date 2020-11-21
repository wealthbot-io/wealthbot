$(function () {
    $(document).on('change','#ria_client_account_group', function (event) {
        var e = $(this);
        var form = e.closest('form');
        var errors = form.find('ul.error-list');
        errors.remove();
        var errorField = form.find('.error');
        errorField.removeClass('error');
        var fieldsSelector = form.find('.advanced-fields');

        e.parent().append('<img class="ajax-loader" style="margin-left:10px; margin-top:-10px;" src="/img/ajax-loader.gif" />');
        form.addClass('ajax-process');

        var options = {
            url: form.attr('data-update-url'),
            dataType: 'json',
            type: 'POST',
            success: function (response) {
                if (response.status == 'error') {
                    alert(response.message);
                }
                if (response.status == 'success') {
                    fieldsSelector.html(response.content);

                    addCompleteTransferCustodianEvent('.fin-inst-typeahead', '#ria_client_account_transferInformation_transfer_custodian_id');

                    var sasCashField = form.find('.sas-cash-field');
                    var retirementHelp = form.find('.retirement-value-help');
                    var monthlyDistField = form.find('.monthly-distributions-field');

                    var estimatedValue = form.find('#ria_client_account_value');
                    var estimatedContributions = form.find('#ria_client_account_monthly_contributions');
                    var estimatedDistributions = form.find('#ria_client_account_monthly_distributions');
                    var sasCash = form.find('#ria_client_account_sas_cash');

                    estimatedValue.val('');
                    estimatedContributions.val('');
                    estimatedDistributions.val('');
                    sasCash.val('');

                    form.find('.account-owners-fields').html('');

                    if (response.group == 'employer_retirement') {
                        sasCashField.hide();
                        monthlyDistField.hide();
                        retirementHelp.show();
                    } else {
                        sasCashField.show();
                        monthlyDistField.show();
                        retirementHelp.hide();
                    }
                }
            },
            complete: function () {
                form.removeClass('ajax-process');
                form.find('.ajax-loader').remove();
            }
        };

        form.ajaxSubmit(options);
    });

    $(document).on('change','#ria_client_account_groupType, input[type="checkbox"].other-contact-owner', function (event) {
        var e = $(this);
        var form = e.closest('form');
        var errors = form.find('ul.error-list');
        errors.remove();
        var errorField = form.find('.error');
        errorField.removeClass('error');
        var fieldsSelector = form.find('.account-owners-fields');

        if (e.hasClass('other-contact-owner') && !e.is(':checked')) {
            form.find('.other-owner-fields').html('');

        } else if (!e.val().length) {
            fieldsSelector.html('');

        } else {
            e.parent().append('<img class="ajax-loader" style="margin-left:10px; margin-top:-10px;" src="/img/ajax-loader.gif" />');
            form.addClass('ajax-process');

            var options = {
                url: form.attr('data-update-owners-url'),
                dataType: 'json',
                type: 'POST',
                success: function (response) {
                    if (response.status == 'error') {
                        alert(response.message);
                    }
                    if (response.status == 'success') {
                        fieldsSelector.html(response.content);
                    }
                },
                complete: function () {
                    form.removeClass('ajax-process');
                    form.find('.ajax-loader').remove();
                }
            };

            form.ajaxSubmit(options);
        }
    });

    $(document).on('click','.toggle-risk-answers-btn',function (event) {
        var e = $(this);
        var container = e.parent().find('.answers-table');

        container.toggle("slow", function () {
            var txt = e.html();
            if (txt == 'View Client Risk Answers') {
                e.html('Hide Client Risk Answers');
            } else {
                e.html('View Client Risk Answers');
            }

        });

        event.preventDefault();
    });

    $(document).on('click','.toggle-client-information-btn',function (event) {
        var e = $(this);
        var container = e.parent().find('.information-table');

        container.toggle("slow", function () {
            var txt = e.html();
            if (txt == 'View Client Information') {
                e.html('Hide Client Information');
            } else {
                e.html('View Client Information');
            }

        });

        event.preventDefault();
    });

    $(document).on('click','.remove-client-account-btn', function (event) {
        if (confirm('Are you sure?')) {
            var e = $(this);

            var selector = $(this).closest('tr').find('.see-investments-btn');
            hideInvestments(selector);

            $.ajax({
                url: e.attr('href'),
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'success') {
                        var item = e.closest('tr');
                        var accountsSelector = $('.client-accounts-list');
                        var account_id = item.attr('data-account-row');

                        accountsSelector.find('tr[data-account-row="' + account_id + '"]').remove();
                        $('.outside-accounts-list').find('tr[data-account-row="' + account_id + '"]').remove();

                        if (accountsSelector.find('tr').length < 2) {
                            accountsSelector.find('.row-total .value').html('<strong>$0.00</strong>');
                            accountsSelector.find('.row-total .monthly-contributions').html('<strong>$0.00</strong>');
                            accountsSelector.find('.row-total .monthly-distributions').html('<strong>$0.00</strong>');
                            accountsSelector.find('.row-total .sas-cash').html('<strong>$0.00</strong>');

                        } else {
                            var value = parseFloat(response.total.value).formatMoney(2, '.', ',');
                            var monthlyContributions = parseFloat(response.total.monthly_contributions).formatMoney(2, '.', ',');
                            var monthlyDistributions = parseFloat(response.total.monthly_distributions).formatMoney(2, '.', ',');
                            var sasCash = parseFloat(response.total.sas_cash).formatMoney(2, '.', ',');

                            accountsSelector.find('.row-total .value').html('<strong>$' + value + '</strong>');
                            accountsSelector.find('.row-total .monthly-contributions').html('<strong>$' + monthlyContributions + '</strong>');
                            accountsSelector.find('.row-total .monthly-distributions').html('<strong>$' + monthlyDistributions + '</strong>');
                            accountsSelector.find('.row-total .sas-cash').html('<strong>$' + sasCash + '</strong>');
                        }
                        if (response.asset_location) {
                            if($("#asset_location")){
                                $("#asset_location").html(response.asset_location);
                            }
                        }
                    }
                    if (response.status == 'error') {
                        alert(response.message);
                    }
                }
            });

        }

        event.preventDefault();
    });

    $(document).on('click','.edit-client-account-btn', function (event) {
        var e = $(this);

        var selector = $(this).closest('tr').find('.see-investments-btn');
        hideInvestments(selector);

        $.ajax({
            url: e.attr('href'),
            dataType: 'json',
            success: function (response) {
                if (response.status == 'success') {
                    var formSelector = $('.client-account-form');
                    var existForm = formSelector.find('#edit_client_account_form');
                    var editForm;

                    $('#create_client_account_form').hide();

                    if (existForm.length) {
                        existForm.replaceWith(response.form);
                        editForm = existForm;
                    } else {
                        formSelector.append(response.form);
                        editForm = formSelector.find('#edit_client_account_form');
                    }

                    updateAutoNumeric();
                    addCompleteTransferCustodianEvent('.fin-inst-typeahead', '#ria_client_account_transferInformation_transfer_custodian_id');

                    var sasCashField = editForm.find('.sas-cash-field');
                    var retirementHelp = editForm.find('.retirement-value-help');
                    var monthlyDistField = editForm.find('.monthly-distributions-field');
                    if (response.group == 'employer_retirement') {
                        sasCashField.hide();
                        monthlyDistField.hide();
                        retirementHelp.show();
                    } else {
                        sasCashField.show();
                        monthlyDistField.show();
                        retirementHelp.hide();
                    }
                    scrollToElemId('edit_client_account_form', 'slow');
                }
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.cancel-edit-client-account-btn', function (event) {
        $(this).closest('form').remove();
        $('#create_client_account_form').show();

        event.preventDefault();
    });

    $(document).on('submit','#edit_client_account_form', function (event) {
        var form = $(this);

        if (!form.hasClass('ajax-process')) {
            var options = {
                dataType: 'json',
                type: 'POST',
                success: function (response) {
                    if (response.status == 'success') {
                        var accountsSelector = $('.suggested-portfolio-finaccounts .client-accounts-list');
                        var item = accountsSelector.find('tr[data-account-row="' + response.account_id + '"]');
                        var see_cons_btn = item.find('.see-consolidated-accounts-btn');

                        item.replaceWith(response.content);
                        hideConsolidatedAccounts(see_cons_btn);

                        $('.client-account-form').html(response.form);
                        $('.outside-accounts-list').html(response.outside_accounts);

                        updateAutoNumeric();

                        var value = parseFloat(response.total.value).formatMoney(2, '.', ',');
                        var monthlyContributions = parseFloat(response.total.monthly_contributions).formatMoney(2, '.', ',');
                        var monthlyDistributions = parseFloat(response.total.monthly_distributions).formatMoney(2, '.', ',');
                        var sasCash = parseFloat(response.total.sas_cash).formatMoney(2, '.', ',');

                        accountsSelector.find('.row-total .value').html('<strong>$' + value + '</strong>');
                        accountsSelector.find('.row-total .monthly-contributions').html('<strong>$' + monthlyContributions + '</strong>');
                        accountsSelector.find('.row-total .monthly-distributions').html('<strong>$' + monthlyDistributions + '</strong>');
                        accountsSelector.find('.row-total .sas-cash').html('<strong>$' + sasCash + '</strong>');

                        if (response.outside_accounts) {
                            $('.outside-accounts-list').html(response.outside_accounts);
                            $('.outside-funds-list').html('');
                        }

                        if (response.asset_location) {
                            if($("#asset_location")){
                                $("#asset_location").html(response.asset_location);
                            }
                        }
                    }
                    if (response.status == 'error' && response.form) {
                        form.replaceWith(response.form);
                        updateAutoNumeric();
                    }
                }
            };

            form.ajaxSubmit(options);
        }

        event.preventDefault();
    });

    $(document).on('submit','#create_client_account_form', function (event) {
        var form = $(this);
        var options = {
            dataType: 'json',
            type: 'POST',
            success: function (response) {
                if (response.status == 'success') {
                    var accountsSelector = $('.suggested-portfolio-finaccounts .client-accounts-list');

                    $('.client-account-form').html(response.form);
                    if (!response.consolidator_id) {
                        accountsSelector.find('.row-total').before(response.account);
                    } else {
                        var consolidator = accountsSelector.find('tr[data-account-row="' + response.consolidator_id +'"]');
                        var see_cons_btn = consolidator.find('.see-consolidated-accounts-btn');

                        consolidator.replaceWith(response.account);
                        hideConsolidatedAccounts(see_cons_btn);
                    }

                    updateAutoNumeric();

                    var value = parseFloat(response.total.value).formatMoney(2, '.', ',');
                    var monthlyContributions = parseFloat(response.total.monthly_contributions).formatMoney(2, '.', ',');
                    var monthlyDistributions = parseFloat(response.total.monthly_distributions).formatMoney(2, '.', ',');
                    var sasCash = parseFloat(response.total.sas_cash).formatMoney(2, '.', ',');

                    accountsSelector.find('.row-total .value').html('<strong>$' + value + '</strong>');
                    accountsSelector.find('.row-total .monthly-contributions').html('<strong>$' + monthlyContributions + '</strong>');
                    accountsSelector.find('.row-total .monthly-distributions').html('<strong>$' + monthlyDistributions + '</strong>');
                    accountsSelector.find('.row-total .sas-cash').html('<strong>$' + sasCash + '</strong>');

                    if (response.outside_accounts) {
                        $('.outside-accounts-list').html(response.outside_accounts);
                        $('.outside-funds-list').html('');
                    }
                    if (response.asset_location) {
                        if($("#asset_location")){
                            $("#asset_location").html(response.asset_location);
                        }
                    }
                }
                if (response.status == 'error') {
                    $('.client-account-form').html(response.form);
                    form = $('#create_client_account_form');
                    checkSelectedAccountType(form);
                    updateAutoNumeric();
                }
            }
        };

        form.ajaxSubmit(options);

        event.preventDefault();
    });

    $(document).on('submit','#add_client_outside_fund_form, #edit_client_outside_fund_form', function (event) {
        var form = $(this);
        var options = {
            dataType: 'json',
            type: 'POST',
            success: function (response) {
                $('.outside-funds-list').html(response.content);
            }
        };

        form.ajaxSubmit(options);
        event.preventDefault();
    });

    $(document).on('click','.remove-client-outside-fund-btn', function (event) {
        var e = $(this);

        if (confirm('Are you sure?')) {
            $.ajax({
                url: e.attr('href'),
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'error') {
                        alert(response.message);
                    }
                    if (response.status == 'success') {
                        e.closest('tr').remove();
                    }
                }
            });
        }

        event.preventDefault();
    });

    $('#suggested_portfolio_form_portfolio').change(function(){
        $(this).closest('form').submit();
    });

    $(document).on('click','.update-prospect-btn, .submit-final-portfolio-btn',function(){
        var elem = $(this);
        var form = $('#client_settings_form');
        var type = $('#suggested_portfolio_form_action_type');

        // Name of field in SuggestedPortfolioFormType class + []
        var unconsolidatedIdsFieldName = 'suggested_portfolio_form[unconsolidated_ids][]';

        removeUnconsolidatedIdsFields(form);

        if (elem.hasClass('update-prospect-btn')) {
            type.val('update');
            form.append(generateUnconsolidatedIdsFieldsHtml(unconsolidatedIdsFieldName));
            form.submit();
        } else {
            if (confirm('Are you sure? This action cannot be undone.')) {
                type.val('submit');
                form.append(generateUnconsolidatedIdsFieldsHtml(unconsolidatedIdsFieldName));
                form.submit();
            }

        }

        event.preventDefault();
    });

    $(document).on('click','.edit-client-outside-fund-btn', function (event) {
        var e = $(this);

        $.ajax({
            url: e.attr('href'),
            dataType: 'json',
            success: function (response) {
                if (response.status == 'error') {
                    alert(response.message);
                }
                if (response.status == 'success') {
                    $('#add_client_outside_fund_form').hide();

                    var editForm = $('#edit_client_outside_fund_form');
                    if (editForm.length > 0) {
                        editForm.replaceWith(response.content);
                    } else {
                        $('.outside-fund-form').append(response.content);
                    }
                }
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.see-investments-btn', function (event) {
        var e = $(this);
        var selector = e.closest('td');

        if (e.hasClass('active')) {
            hideInvestments(e);
        } else {
            selector.append('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

            $.ajax({
                url: e.attr('data-url'),
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'success') {
                        $('.outside-funds-list').html(response.content);
                    }
                    if (response.status == 'error') {
                        alert(response.message);
                    }

                    $('.see-investments-btn').removeClass('active').text('(See investments ▲)');
                    e.text('(Hide investments ▼)');
                    e.addClass('active');
                    scrollToElemId('outside_funds_list', 'slow');
                },
                complete: function () {
                    selector.find('.ajax-loader').remove();
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
            selector.append('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

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
                    selector.find('.ajax-loader').remove();
                }
            });
        }

        event.preventDefault();
    });

    $(document).on('click','.cancel-edit-fund-btn', function (event) {
        $('#edit_client_outside_fund_form').remove();
        $('#add_client_outside_fund_form').show();

        event.preventDefault();
    });

    $(document).on('click','.cancel-add-fund-btn', function (event) {
        $('.outside-funds-list').html('');
        event.preventDefault();
    });

    $(document).on('click',".qualified-toggle", function(event){
        var is_qualified = $(this).attr('data-value');
        $("#suggested_portfolio_form_is_qualified").val(is_qualified);
        event.preventDefault();
        $("#client_settings_form").submit();
    });

    lockCompleteTransferCustodianCheckbox('#ria_client_account_transferInformation_is_firm_not_appear');
});


function checkSelectedAccountType(form) {

    var group = $("#ria_client_account_group").val();
    var sasCashField = $(form).find('.sas-cash-field');
    var retirementHelp = $(form).find('.retirement-value-help');
    var monthlyDistField = $(form).find('.monthly-distributions-field');

    if (group == 'employer_retirement') {
        $(sasCashField).hide();
        $(monthlyDistField).hide();
        $(retirementHelp).show();
    } else {
        sasCashField.show();
        monthlyDistField.show();
        retirementHelp.hide();
    }
}

function selectRetirementAccount(url, accountId) {
    var newUrl = url.replace('account/0/outside-funds', 'account/' + accountId + '/outside-funds');

    $.ajax({
        url: newUrl,
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                $('.outside-funds-list').html(response.content);
            }
            if (response.status == 'error') {
                alert(response.message);
            }
        }
    });
}

function selectModel(url, modelId) {
    var newUrl = url.replace('model/0/details', 'model/' + modelId + '/details');

    $.ajax({
        url: newUrl,
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                $('.model-details').html(response.content);
            }
            if (response.status == 'error') {
                alert(response.message);
            }
        }
    });
}

function postToUrl(path, params, method) {
    method = method || "post";

    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);

    for (var key in params) {
        if (params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);

            form.appendChild(hiddenField);
        }
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function hideInvestments(container)
{
    showContentInOutsideFundList('');
    container.text('(See investments ▲)');
    container.removeClass('active');
}

function showContentInOutsideFundList(content)
{
    $('.outside-funds-list').html(content);
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

function generateUnconsolidatedIdsFieldsHtml(fieldName)
{
    var inputs = [];

    $('input[type="checkbox"].select-unconsolidate-account:checked').each(function(){
        inputs.push('<input class="unconsolidate-account-input" type="hidden" name="' + fieldName + '" value="' + $(this).val() + '">');
    });

    return inputs.join(', ');
}

function removeUnconsolidatedIdsFields(form)
{
    form.find('input[type="checkbox"].unconsolidate-account-input').each(function(){
        $(this).remove();
    });
}