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
