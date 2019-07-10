$(function(){
    updateOutsideRetirementAccountsBlock();
    $(document).on('click','input:radio[name="wealthbot_riabundle_ria_proposals_form[portfolio_processing]"]',function(){
        $('input:radio[name="ria_company_information[account_managed]"]:checked').removeAttr('checked');
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

    $(document).on('click','input:radio[name="ria_company_information[account_managed]"]', function(){
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

    $(document).on('change','input:radio[name="ria_company_information[is_allow_retirement_plan]"]', function() {
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


$(".step-four-form form").ajaxSubmit({
        url: $(".step-four-form form").attr('action'),
              target: '#form_subclass_container',
        success: function () {
            updateAutoNumeric();
            updateRiaAssetSettingsTable();
        }
});

//function municipalBondsToggle()
//{
//    var block = $('#portfolio_managment_level_block');
//    var account_type_id = block.find('#account_type_container input:checked').val();
//    var qualified_id = block.find('#qualified_container input:checked').val();
//    if (account_type_id == 2 || (account_type_id != 2 && qualified_id == 0)) {
//        $('#municipal_bonds_block').show();
//    } else {
//        $(document).on('click','#ria_company_information_use_municipal_bond_1',);
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
    var is_allow_retirement_plan = parseInt($('input:radio[name="ria_company_information[is_allow_retirement_plan]"]:checked').val());
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
    var is_allow_retirement_plan = parseInt($('input:radio[name="ria_company_information[is_allow_retirement_plan]"]:checked').val());
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
    var is_allow_retirement_plan = parseInt($('input:radio[name="ria_company_information[is_allow_retirement_plan]"]:checked').val());
    var account = $('input:radio[name="ria_company_information[account_managed]"]:checked');
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
    var clientByClient = $('#ria_company_information_account_managed_2').closest('label');

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