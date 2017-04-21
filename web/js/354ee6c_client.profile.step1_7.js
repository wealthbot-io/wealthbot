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