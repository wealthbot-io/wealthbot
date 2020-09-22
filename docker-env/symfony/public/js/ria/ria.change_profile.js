/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 01.07.13
 * Time: 13:31
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    updateCustodianQuestionsBlock();

    $(document).on('click','input:radio[name="ria_custodian[custodian]"]',function() {
        updateCustodianQuestionsBlock();
    });

    var groupList = $("#wealthbot_riabundle_createuser_groups");
    if (groupList.length > 0) {
        groupList.pickList();

    }


    $("#riacompanyinformationtype_phone_number").inputmask("mask", {"mask": "(999) 999-9999"});
    $("#riacompanyinformationtype_fax_number").inputmask("mask", {"mask": "(999) 999-9999"});


    $(document).on('click','.website-test.btn', function(event) {
        var value = $('#riacompanyinformationtype_website').val();

        if (!value || value === 'http://') {
            alert('Enter the value.');
            event.preventDefault();
        } else {
            $(this).attr('href', value);
        }
    });

    function updateUsersForm() {
        $.ajax({
            url: $('#user-form').attr('action'),
            cache: false,
            success: function(response) {
                $('#user_management').html(response);
                var groupList = $("#wealthbot_riabundle_createuser_groups");
                if (groupList.length > 0) {
                    groupList.pickList();
                }

                $("#riacompanyinformationtype_phone_number").inputmask("mask", {"mask": "(999) 999-9999"});
                $("#riacompanyinformationtype_fax_number").inputmask("mask", {"mask": "(999) 999-9999"});

            }
        });
    }

   /* $('.btn-ajax').on('click', function (event) {
        event.preventDefault();
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

        form.ajaxSubmit({
            target: form.closest('.tab-pane.active'),
            success: function () {
                $(".btn").button('reset');

                if ($(form).attr('id') == 'billing_n_accounts_form') {
                    updateFees();
                    hideAllIsFinalTierCheckbox();
                    showLastIsFinalTierCheckbox();
                    $('#advisor-codes-list').data('index', $('#advisor-codes-list').find(':input').length);
                }

                updateUsersForm();
            }
        });
    });

    */


    $(document).on('click','.edit-ria-user-btn, .delete-ria-user-btn, .cancel-edit-user-btn', function (event) {
        var button = $(this);

        button.button('loading');

        $.ajax({
            url: button.attr('href'),
            success: function(response) {
                button.button('reset');

                button.closest('.tab-pane').html(response);

                var groupList = $("#wealthbot_riabundle_createuser_groups");
                if (groupList.length > 0) {
                    groupList.pickList();

                }
            }
        });

        event.preventDefault();

    });

    $(document).on('click','.edit_group_btn, .delete_group_btn', function (event) {
        var button = $(this);
        var isDelete = button.hasClass('delete_group_btn');

        var process = function() {
            button.button('loading');

            $.ajax({
                url: button.attr('href'),
                success: function(response) {
                    button.button('reset');
                    button.closest('.tab-pane').html(response);
                    updateUsersForm();
                }
            });

        };

        if (isDelete) {
            if (confirm("Are you sure?")) {
                process();
            }
        } else {
            process();
        }

        event.preventDefault();

    });

    $(document).on('submit','#alerts_configuration_form', function(event) {
        var form = $(this);
        var btn = form.find('button[type="submit"]');

        btn.button('loading');

        form.ajaxSubmit({
            dataType: 'json',
            success: function(response) {
                if (response.status == 'error') {
                    form.html(response.content);
                }

                btn.button('reset');
            }
        });

        event.preventDefault();
    });

    $('.alertable input, .alertable select').on('keyup change', function() {
        var parentId = $(this).closest('.alertable').attr('id');
        $('#' + parentId + '_alert').show();
    });

    $('#custodian_id')
        .on('change', getAdvisorCodesList)
        .trigger('change');
    $(document).on('click','#new-id', addAdvisorCode);
    $(document).on('click','.remove-advisor-code', removeAdvisorCode);

    function getAdvisorCodesList() {
        var custodianId = $(this).val();
        $('#advisor-codes-list').load(Routing.generate('rx_ria_change_profile_advisor_codes', {'custodian_id': custodianId}), function() {
            $('#advisor-codes-list').data('index', $('#advisor-codes-list').find(':input').length);
            recountAdvisorCodes();
        });
        if (custodianId == '') {
            $('#new-id').hide();
        } else {
            $('#new-id').show();
        }
    }

    function addAdvisorCode(e) {
        e.preventDefault();

        var index = $('#advisor-codes-list').data('index');
        $('#advisor-codes-list').data('index', index + 1);

        var prototype =
            '<div>' +
            '<span class="advisor-number"></span> ' +
            '<input type="text" id="ria_advisor_codes_advisorCodes___name___name" name="ria_advisor_codes[advisorCodes][__name__][name]" required="required" class="input-small  form-control" /> ' +
            '<span class="icon-remove remove-advisor-code"></span>' +
            '</div>';
        var $newAdvisorCode = $(prototype.replace(/__name__/g, index));

        $(this).before($newAdvisorCode);

        recountAdvisorCodes();
        return false;
    }

    function removeAdvisorCode() {
        $(this).parent().remove();
        recountAdvisorCodes();
    }

    function recountAdvisorCodes() {
        var advisorCodeNumber = 1;
        $('.advisor-number:visible').each(function() {
            $(this).text(advisorCodeNumber);
            advisorCodeNumber++;
        });
    }
});

function updateCustodianQuestionsBlock() {
    var block = $('#custodian_questions');

    if (block.length > 0) {
        var checkedCustodian = $('input:radio[name="ria_custodian[custodian]"]:checked');
        if (checkedCustodian.length > 0) {
            block.show();
            return true;
        }
    }

    block.hide();
    return false;
}