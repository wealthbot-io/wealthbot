// NEW REALISATION
$(function(){

    updateFees();

    hideAllIsFinalTierCheckbox();
    showLastIsFinalTierCheckbox();

    $('#step_three_form, #billing_n_accounts_form').submit(function (event) {
        if(!isValidateFees()){
            alert("Fee should be more than 0 and less then 1.");
            event.preventDefault();
        }

        if(!isValidateTiers()){
            alert("Please enter the valid tier top value.");
            event.preventDefault();
        }

        validateIsOnlyOneTier();
    });

    $(document).on('click','.is-final-tier-checkbox input[type="checkbox"]', function (event) {
        var e = $(this);
        var tierTop = e.closest('.tier').find('input[id*="tier_top"]');

        if (e.is(':checked')) {
            tierTop.attr('data-value', tierTop.val());
            tierTop.attr('disabled', 'disabled');
            tierTop.val('');
        } else {
            tierTop.val(tierTop.attr('data-value'));
            tierTop.attr('data-value', '');
            tierTop.attr('disabled', false);
        }
    });

    $(document).on('click',".fees-form-block", function(event){
        $("#fees_alert_id").show();
    });

    $(document).on('click','.btn-add', function (event) {
        $('.btn-remove').hide();
        hideAllIsFinalTierCheckbox();

        var collectionHolder = $('#' + $(this).attr('data-target'));
        var lastIndex = collectionHolder.find('tr').index();
        var prototype = collectionHolder.attr('data-prototype');
        var form = prototype.replace(/__name__/g, collectionHolder.children().length);
        collectionHolder.append(form);

        updateFees();
        showLastIsFinalTierCheckbox();
        event.preventDefault();
    });

    decorateDecimalInput("#form_fees tr td input[id*='fee_without_retirement']", 4);

    //Trigger for change Tier Bottom when Tier Top was changed
    $(document).on('change',"#form_fees tr td input[id*='tier_top']", function () {
        updateFees();
    });


    $(document).on('click','.btn-remove', function (event) {
        var name = $(this).attr('data-related');
        var prev = $('*[data-content="' + name + '"]').prev();

        $('*[data-content="' + name + '"]').find('.auto-numeric').autoNumeric('destroy');
        if (prev) $(prev).find("a.btn-remove").show();
        $('*[data-content="' + name + '"]').remove();

        showLastIsFinalTierCheckbox();
        event.preventDefault();
    });


    $(document).on('click','.btn-preview-fee', function (event) {
        var data = {};
        var href = $(this).attr('href');

        $("#form_fees tr input").each(function () {
            var e = $(this);

            var name = e.attr('name');
            var value = '';
            if (e.attr('type') == 'checkbox') {
                value = e.is(':checked') ? 1 : 0;
            } else {
                value = e.autoNumeric('getSettings') ? e.autoNumeric('get') : e.val();
            }

            data[name] = value;
        });

        $.ajax({
            url: href,
            data: data,
            success: function (data) {
                $('#fee_preview .modal-body').html(data);
                $('#fee_preview').modal('show');
            }
        });
        event.preventDefault();
    });
});

function isValidateFees()
{
    var validFlag = true;
    $("#form_fees tr td input[id*='fee_without_retirement']").each(function () {
        if(validateIsEmptyFee(this, 4)){
            validFlag = false;
            $(this).addClass('error');
        }else{
            $(this).removeClass('error');
        }
    });
    return validFlag;
}

function validateIsEmptyTier(input)
{
    var e = $(input);

    var value;
    if (e.autoNumeric('getSettings')) {
        value = parseFloat(e.autoNumeric('get')).toFixed(4);
    } else {
        value = parseFloat(e.val()).toFixed(4);
    }

    if ( !value || value <= 0 ) {
        return true;
    }
    return false;
}

function isLastTier(input)
{
    var last_tier = $("#form_fees tr:last td input[id*='tier_top']");
    if(input.attr('id') == last_tier.attr('id')) {
        return true;
    }
    return false;
}

function isValidateTiers()
{
    var validFlag = true;
    $("#form_fees tr").each(function () {
        var fee_selector = $(this).find("input[id*='fee_without_retirement']");
        var bottom_selector = $(this).find("input[id*='tier_bottom']");
        var top_selector = $(this).find("input[id*='tier_top']");
        // Check empty values
        if( top_selector && validateIsEmptyTier( top_selector ) && !isLastTier(top_selector) ) {
            validFlag = false;
            top_selector.addClass('error');
        } else {
            if(top_selector.hasClass('error')){
                top_selector.removeClass('error');
            }
        }
        // Check if bottom less then top
        var parsedBottomVal, parsedTopVal;

        if (bottom_selector.autoNumeric('getSettings')) {
            parsedBottomVal = parseFloat(bottom_selector.autoNumeric('get')).toFixed(4);
        } else {
            parsedBottomVal = parseFloat(bottom_selector.val()).toFixed(4);
        }

        if (top_selector.autoNumeric('getSettings')) {
            parsedTopVal = parseFloat(top_selector.autoNumeric('get')).toFixed(4);
        } else {
            parsedTopVal = parseFloat(top_selector.val()).toFixed(4);
        }

        if( ( parseFloat(parsedBottomVal) > parseFloat(parsedTopVal) ) && !isLastTier(top_selector) ) {
            validFlag = false;
            top_selector.addClass('error');
            bottom_selector.addClass('error');
        } else {
            if( top_selector.hasClass('error') && bottom_selector.hasClass('error') ) {
                top_selector.removeClass('error');
                bottom_selector.removeClass('error');
            }
        }
    });
    return validFlag;
}

function validateIsOnlyOneTier()
{

    $("#form_fees tr:last td input[id*='is_final_tier']").attr('checked', 'checked');
    var element = $("#form_fees tr:last td input[id*='tier_top']");
    element.attr('disabled', 'disabled');
    element.val('');
}

/**
 * Show final tier checkbox
 */
function showLastIsFinalTierCheckbox() {
    var e = $('#form_fees tr:last-child').find('.is-final-tier-checkbox');
    var tierTop = e.closest('.tier').find('input[id*="tier_top"]');
    var checkBox = e.find('input[type="checkbox"]');

    if(checkBox.attr('checked') == 'checked'){
        tierTop.attr('data-value', tierTop.val());
        tierTop.attr('disabled', 'disabled');
        tierTop.val('');
    } else {
        if(!parseFloat(tierTop.val())){
            checkBox.attr('checked', 'checked');
            tierTop.attr('data-value', tierTop.val());
            tierTop.attr('disabled', 'disabled');
            tierTop.val('');
        }
    }

    if ($('.is-final-tier-checkbox').length == 1) {
        var container = checkBox.parent().find('span');
        container.text('Is this your only tier?');
    }

    e.show();
}

/**
 * Hide final tier checkbox
 */
function hideAllIsFinalTierCheckbox() {
    $('.is-final-tier-checkbox').each(function () {
        var e = $(this);
        var tierTop = e.closest('.tier').find('input[id*="tier_top"]');

        if (tierTop.attr('disabled') == 'disabled') {
            tierTop.val(tierTop.attr('data-value'));
            tierTop.attr('data-value', '');
            tierTop.attr('disabled', false);
        }

        e.find('input[type="checkbox"]').attr('checked', false);
        e.hide();
    });
}
$(function(){
    updateOutsideRetirementAccountsBlock();
    $(document).on('click','input:radio[name="wealthbot_riabundle_ria_proposals_form[portfolio_processing]"]',function(){
        $('input:radio[name="wealthbot_riabundle_riacompanyinformationtype[account_managed]"]:checked').removeAttr('checked');
        updateOutsideRetirementAccountsBlock();
        qualifiedModelsToggle();
    });

    updateRiaAssetSettingsTable();

    var portfolio_model = $('.portfolio-model input[type="radio"]:checked').attr('class');
    if (portfolio_model != undefined && portfolio_model == 'strategy-choice') {
        $('.strategy-list').show();
    }
    //municipalBondsToggle();
    toleranceBandToggle();
    qualifiedModelsToggle();

    $(document).on('change','select[id*="rebalanced_frequency"]', function(){
        toleranceBandToggle();
    });

    $('.portfolio-model input[type="radio"]').change(function(){
        var strategy_list_selector = $('.strategy-list');

        if ($(this).attr('class') == 'strategy-choice') {
            strategy_list_selector.show();
        } else {
            $('.strategy-list input[type="radio"]').attr('checked', false);
            strategy_list_selector.hide();
        }
        updateRiaAssetSettingsTable();
    });

    $(document).on('click',".form-group input[id*=strategy_model]", function(){
        completeSubclasses();
    });

    initClientsTaxBracketField();

    $('.use-municipal-bond input[type="radio"]').change(function(){
        updateClientsTaxBracketField($(this).val());
    });

    $('.tax-loss-harvesting .use-tlh-radio input[type="radio"]').change(function() {
        updateTaxLossHarvesting($(this).val());
    });

    $(document).on('click','input:radio[name="wealthbot_riabundle_riacompanyinformationtype[account_managed]"]', function(){
        updateRiaAssetSettingsTable();
        updateOutsideRetirementAlertMessage();
        qualifiedModelsToggle();
    });

    $(document).on('click',"input[id*='is_show_client_expected_asset_class']", function(){
        updateRiaAssetSettingsTable();
    });

//    $(document).on('click',"#portfolio_managment_level_block input", function() {
//        municipalBondsToggle();
//    });

    $(document).on('change','input:radio[name="wealthbot_riabundle_riacompanyinformationtype[is_allow_retirement_plan]"]', function() {
        updateOutsideRetirementAlertMessage();
        qualifiedModelsToggle();
    });
});

function initClientsTaxBracketField()
{
    var value = $('.use-municipal-bond input[type="radio"]:checked').val();
    updateClientsTaxBracketField(value);
}

function updateClientsTaxBracketField(value) {
    var client_tax_selector = $('.clients-tax-bracket');
    if (value == 1) {
        client_tax_selector.show();
    } else {
        client_tax_selector.hide();
    }
}

function updateTaxLossHarvesting(value) {
    var tax_loss_harvesting_selector = $('.tax-loss-harvesting-controls');
    if (value == 1) {
        tax_loss_harvesting_selector.show();
    } else {
        tax_loss_harvesting_selector.hide();
    }
}

function completeSubclasses()
{
    var container = $("#form_subclass_container");

    $("#step_four_form").ajaxSubmit({
        url: container.attr('data-complete-url'),
        target: '#form_subclass_container',
        success: function () {
            updateAutoNumeric();
            updateRiaAssetSettingsTable();
        }
    });
}

//function municipalBondsToggle()
//{
//    var block = $('#portfolio_managment_level_block');
//    var account_type_id = block.find('#account_type_container input:checked').val();
//    var qualified_id = block.find('#qualified_container input:checked').val();
//    if (account_type_id == 2 || (account_type_id != 2 && qualified_id == 0)) {
//        $('#municipal_bonds_block').show();
//    } else {
//        $(document).on('click','#wealthbot_riabundle_riacompanyinformationtype_use_municipal_bond_1',);
//        $('#municipal_bonds_block').hide();
//    }
//}

function toleranceBandToggle()
{
    var rebalance_frequency = $("select[id*=rebalanced_frequency]:eq(0)");
    if(rebalance_frequency.val() == 4){
        $("#tolerance_band_container").show();
    }else{
        $("#tolerance_band_container").hide();
    }
}

function qualifiedModelsToggle()
{
    var is_allow_retirement_plan = parseInt($('input:radio[name="wealthbot_riabundle_riacompanyinformationtype[is_allow_retirement_plan]"]:checked').val());
    var account_managed = parseInt($("input[id*='account_managed']:checked").val());
    var qualified_container_selector = $("#qualified_container");

    if (account_managed == 1) {
        qualified_container_selector.show();
    } else {
        qualified_container_selector.hide();
    }

    if(account_managed == 1 && is_allow_retirement_plan ) {
        var label = "For clients who do not hold outside retirement accounts, will you be offering qualified and non-qualified models depending on the account type?";
        $("#qualified_container").show();
        updateQualifiedLabel(label);
    }
    if(account_managed == 1 && !is_allow_retirement_plan) {
        var label = "Will you be offering qualified and non-qualified models depending on the account type?";
        $("#qualified_container").show();
        updateQualifiedLabel(label);
    }
    if(account_managed == 2 || account_managed == 3){
        $("#qualified_container").hide();
    }
}

function updateQualifiedLabel(label)
{
    $("#qualified_container label:eq(0)").html(label);
}


function updateRiaAssetSettingsTable(){
    var is_allow_retirement_plan = parseInt($('input:radio[name="wealthbot_riabundle_riacompanyinformationtype[is_allow_retirement_plan]"]:checked').val());
    var account_managed = parseInt($("input[id*='account_managed']:checked").val());
    var is_show_asset = parseInt($("input[id*='is_show_client_expected_asset_class']:checked").val());
    var model = $("input[name*='model_type']:checked").val();

    var table = $('table.asset-settings-table');

    var account_type_title = $('table.asset-settings-table tr:eq(0) th:eq(2)');
    var account_type_items = $("table.asset-settings-table td.account-type");

    var expected_performance_title = $('table.asset-settings-table tr:eq(0) th:eq(1)');
    var expected_performance_items = $("table.asset-settings-table td.expected-performance");

    var asset_block = $('.asset-block');
    var asset_block_message_1 = asset_block.find('ol li.message-1');
    var asset_block_message_2 = asset_block.find('ol li.message-2');

    if(model == 2){
        asset_block.hide();
        return;
    }

    // Show performance, do not show accounts
    if (is_show_asset == 1 && is_allow_retirement_plan != 1 && account_managed == 1) {
        asset_block.show();

        expected_performance_title.show();
        expected_performance_items.show();

        account_type_title.hide();
        account_type_items.hide();

        asset_block_message_1.show();
        asset_block_message_2.hide();
    }

    // Hide table
    //console.log(is_show_asset, is_allow_retirement_plan, account_managed);
    if (is_show_asset != 1 && is_allow_retirement_plan != 1 && account_managed == 1) {
        asset_block.hide();
    }

    // Show full table
    if ( (is_allow_retirement_plan == 1 && is_show_asset == 1) ||
        (is_allow_retirement_plan != 1 && is_show_asset == 1 && account_managed != 1) )
    {
        asset_block.show();

        expected_performance_title.show();
        expected_performance_items.show();

        account_type_title.show();
        account_type_items.show();

        asset_block_message_1.show();
        asset_block_message_2.show();
    }

    // Show accounts, do not show performance
    if ( (is_allow_retirement_plan == 1 && is_show_asset != 1) ||
        (is_allow_retirement_plan != 1 && is_show_asset != 1 && account_managed != 1) )
    {
        asset_block.show();

        account_type_title.show();
        account_type_items.show();

        expected_performance_title.hide();
        expected_performance_items.hide();

        asset_block_message_1.hide();
        asset_block_message_2.show();
    }
}

function updateOutsideRetirementAlertMessage() {
    var alert_select = $('#outside_retirement_alert_id');
    var is_allow_retirement_plan = parseInt($('input:radio[name="wealthbot_riabundle_riacompanyinformationtype[is_allow_retirement_plan]"]:checked').val());
    var account = $('input:radio[name="wealthbot_riabundle_riacompanyinformationtype[account_managed]"]:checked');
    var account_id = account.val();
    var portfolio_processing = parseInt($('input:radio[name="wealthbot_riabundle_ria_proposals_form[portfolio_processing]"]:checked').val());

    if (portfolio_processing == 2 && is_allow_retirement_plan == 1 && (account_id == 1 || account_id == 3)) {
        var account_name = account.closest('label').find('.account-type-label').html();
        $('#alert_message_account_type').html(account_name);
        alert_select.show();
    } else {
        alert_select.hide();
    }
}

function updateOutsideRetirementAccountsBlock() {
    var elem = $('input:radio[name="wealthbot_riabundle_ria_proposals_form[portfolio_processing]"]:checked');
    var select = $('#outside_retirement_accounts_block');
    var clientByClient = $('#wealthbot_riabundle_riacompanyinformationtype_account_managed_2').closest('label');

    updateOutsideRetirementAlertMessage();

    if (elem.length > 0) {
        var portfolioProcessingValue = elem.val();

        if (portfolioProcessingValue == 1) {
            if (clientByClient.length > 0) {
                clientByClient.hide();
            }

            select.hide();

        } else {
            if (clientByClient.length > 0) {
                clientByClient.show();
            }
            //{#code_v2: NOT DELETE THIS CODE #}
            //select.show();
        }
    }
}
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

    //Company profile
    var phoneNumber = $('#wealthbot_riabundle_riacompanyinformationtype_phone_number');
    if (phoneNumber.length > 0) {
        $("#wealthbot_riabundle_riacompanyinformationtype_phone_number").inputmask("mask", {"mask": "(999) 999-9999"});
    }

    var groupList = $("#wealthbot_riabundle_createuser_groups");
    if (groupList.length > 0) {
        groupList.pickList();

    }


    $(document).on('click','.website-test.btn', function(event) {
        var value = $('#wealthbot_riabundle_riacompanyinformationtype_website').val();

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
            }
        });
    }

    $('#company_profile_form .btn-ajax, #proposal_form .btn-ajax, #billing_n_accounts_form .btn-ajax, #portfolio_management_form .btn-ajax,' +
        '#update_password .btn-ajax, #user_management .btn-ajax, #user_password_management .btn-ajax').on('click', function (event) {
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

        event.preventDefault();
    });

    $(document).on('click','', function (event) {

        var button = $(this);

        button.button('loading');

        $.ajax({
            url: button.attr('href'),
//            method: POST,
            success: function(response) {
                button.button('reset');
                button.closest('form').html(response);
            }
        });

        event.preventDefault();

    });

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

    $(document).on('submit','#ria_documents_form', function(event) {
        var form = $(this);
        var btn = form.find('input[type="submit"]');

        btn.button('loading');

        form.ajaxSubmit({target: ".ria-documents-form", complete: function(){ btn.button('reset') } });
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