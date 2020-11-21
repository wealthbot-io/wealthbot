/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 01.07.13
 * Time: 13:40
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    updateAutoNumeric();
    updateFormFields();

    $("#create_client_form_id").ajaxForm({
        method: 'post',
        dataType: 'html',
        beforeSubmit: function(arr, form, options) {
            $(".form-actions .btn").hide();
            $(".form-actions .progress").show();
        },
        success: function(response){
            $(".create-client-form").html(response);
        }
    });

    $(document).on('change',"#wealthbot_riabundle_riacreateclienttype_profile_model", function(event){
        $('#wealthbot_riabundle_riacreateclienttype_profile_suggested_portfolio').attr('disabled', 'disabled');

        var form = $(this).closest('form');
        var model_id = $(this).val();

        $.ajax({
            url: form.attr('data-complete-models-url'),
            data: { model_id: model_id },
            success: function(data) {
                $("#wealthbot_riabundle_riacreateclienttype_profile_suggested_portfolio").html(data.options);
            },
            complete: function() {
                $('#wealthbot_riabundle_riacreateclienttype_profile_suggested_portfolio').attr('disabled', false);
            }
        });
    });

    // Event for button Apply
    $(document).on('click',"#save_client_btn_id", function(event){
        var options = {
            dataType: 'json',
            type:     'POST',
            success:  function (response) {
                $('.create-prospect-content').replaceWith(response.content);
                $("html, body").animate({ scrollTop: 0 }, "slow");
            }
        };

        $("#create_client_form_id").ajaxSubmit(options);

        event.preventDefault();
    });

    $(document).on('change','#ria_client_account_account_type', function (event) {
        checkSelectedAccountType(accountTypes, '#ria_client_account_account_type');
    });
});

function updateFormFields() {
    var form = $('#create_client_form_id');

    if (form.hasClass('create-continue-form')) {
        var hasRetirementAccount = form.attr('data-retirement-accounts');
        var accountTypes = JSON.parse(form.attr('data-account-types'));

        if (hasRetirementAccount) {
            $('#current_employer_retirement_plan_1').attr('checked', true);
        }

        checkSelectedAccountType(accountTypes, '#wealthbot_userbundle_client_account_type_account_type');
    }
}