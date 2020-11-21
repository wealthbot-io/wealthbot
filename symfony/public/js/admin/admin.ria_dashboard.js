/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 28.06.13
 * Time: 19:19
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    $('.pop').popover();

    //FROM Marketing form
    var isSearchable = $('input:radio[name="wealthbot_riabundle_riacompanyinformationtype[is_searchable_db]"]:checked').val();

    if(isSearchable == 0){
        $('#wealthbot_riabundle_riacompanyinformationtype_min_asset_size').closest('.form-group').hide();
    }

    $(document).on('click','#company_profile_form .btn-ajax, #marketing_form .btn-ajax, #billing_n_accounts_form .btn-ajax, #portfolio_management_form .btn-ajax', function(event){
        var button = this;
        var form = $(button).closest('form');

        $(button).button('loading');

        if($(form).attr('id') == 'billing_n_accounts_form'){

            if(!isValidateFees()){
                alert("Fee should be more than 0 and less then 1.");
                $(".btn").button('reset');
                return false;
            }

            if(!isValidateTiers()){
                alert("Please enter the valid tier top value.");
                $(".btn").button('reset');
                return false;
            }

            validateIsOnlyOneTier();
        }

        $(form).ajaxSubmit({
            target:  form.parent(),
            success: function(){
                $(".btn").button('reset');

                if($(form).attr('id') == 'billing_n_accounts_form'){
                    updateFees();
                    hideAllIsFinalTierCheckbox();
                    showLastIsFinalTierCheckbox();
                }
            }
        });
        event.preventDefault();
    });

    $('.alertable input, .alertable select').on('keyup change', function() {
        var parentId = $(this).closest('.alertable').attr('id');
        $('#' + parentId + '_alert').show();
    });

    $('input:radio[name="wealthbot_riabundle_riacompanyinformationtype[is_searchable_db]"]').change(function(){
        var isSearchable = $('input:radio[name="wealthbot_riabundle_riacompanyinformationtype[is_searchable_db]"]:checked').val();

        if(isSearchable == 0){
            $('#wealthbot_riabundle_riacompanyinformationtype_min_asset_size').closest('.form-group').hide();
        } else {
            $('#wealthbot_riabundle_riacompanyinformationtype_min_asset_size').closest('.form-group').show();
        }
    });

    //FROM Company information
    $("#wealthbot_riabundle_riacompanyinformationtype_phone_number").inputmask("mask", {"mask": "(999) 999-9999"});

    $(document).on('click','.website-test.btn', function (event) {
        var value = $('#wealthbot_riabundle_riacompanyinformationtype_website').val();

        if (!value || value === 'http://') {
            alert('Enter the value.');
            event.preventDefault();
        } else {
            $(this).attr('href', value);
        }
    });

    // FROM Dashboard pagination
    $(document).on('click','div.pagination a', function(event) {
        $.ajax({
            url: $(this).attr('href'),
            dataType: 'json',
            success: function (response) {
                if (response.status == 'success') {
                    if (response.pagination_type == 'clients') {
                        $('#tab2').html(response.content);
                    } else if (response.pagination_type == 'history') {
                        $('#tab4').html(response.content);
                    }

                }
            }
        });

        event.preventDefault();
    });

    $(document).on('click','div#tab2 table thead th a', function(event) {
        $.ajax({
            url: $(this).attr('href'),
            dataType: 'json',
            success: function (response) {
                if (response.status == 'success') {
                    $('#tab2').html(response.content);
                }
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.activate-checkbox', function() {
        var e = $(this);
        var parent = e.parent();
        var isChecked = e.is(':checked');

        parent.append('<img class="ajax-loader pull-right" src="/img/ajax-loader.gif" />');

        $.ajax({
            url: e.attr('data-url'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'error') {
                    e.attr('checked', !isChecked);
                    alert('Error: ' + response.message);
                }

                if (response.status == 'success') {
                    e.attr('data-url', response.url);
                }
            },
            complete: function() {
                parent.find('.ajax-loader').remove();
            }
        });
    });

    $(document).on('click','input[name="ria_relationship_form[relationship_type]"]', function(event) {

        var form = $(this).closest('#ria_update_relationship_form');

        var block = form.find('#ria_relationship_form_relationship_type')

        block.append('<img class="ajax-loader pull-right" src="/img/ajax-loader.gif" />');

        form.ajaxSubmit({
            type: 'post',
            success: function(respose) {
                if (respose.status == 'success') {
                    $('#admin_fees_content').html(respose.fees_content);
                }

                if (respose.status == 'error') {
                    alert('error');
                }

            },
            complete: function() {
                block.find('.ajax-loader').remove();
            }
        });

    });
});