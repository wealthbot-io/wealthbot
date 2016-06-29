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
