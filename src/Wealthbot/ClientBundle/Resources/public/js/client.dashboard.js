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
