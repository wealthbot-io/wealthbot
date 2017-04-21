/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 28.06.13
 * Time: 19:08
 * To change this template use File | Settings | File Templates.
 */

$(function(){

    updateAutoNumeric();

    $('#rx_admin_model_entity_form_assetClass, #rx_admin_model_entity_form_subclass,' +
        '#rx_admin_model_entity_form_securityAssignment, #rx_admin_model_entity_form_muniSubstitution').on('change', function(){

            //TODO: Andrey + controller + update url with model-entity-id
            var elem = $(this);
            var id = elem.attr('id');

            $('input.symbol').each(function() {
                $(this).val('');
            });

//            switch (id) {
//                case 'rx_ria_model_entity_form_assetClass':
//                    $('#rx_ria_model_entity_form_subclass').attr('disabled', 'disabled');
//                    $('#rx_ria_model_entity_form_securityAssignment').attr('disabled', 'disabled');
//                    $('#rx_ria_model_entity_form_muniSubstitution').attr('disabled', 'disabled');
//                    $('#rx_ria_model_entity_form_tax_loss_harvesting').attr('disabled', 'disabled');
//                    break;
//                case 'rx_ria_model_entity_form_subclass':
//                    $('#rx_ria_model_entity_form_securityAssignment').attr('disabled', 'disabled');
//                    $('#rx_ria_model_entity_form_muniSubstitution').attr('disabled', 'disabled');
//                    $('#rx_ria_model_entity_form_tax_loss_harvesting').attr('disabled', 'disabled');
//                    break;
//                case 'rx_ria_model_entity_form_securityAssignment':
//                    $('#rx_ria_model_entity_form_muniSubstitution').attr('disabled', 'disabled');
//                    $('#rx_ria_model_entity_form_tax_loss_harvesting').attr('disabled', 'disabled');
//                    break;
//            }
//
//            var form = elem.closest('form');


        var form = elem.closest('form');
        var index = elem.index('select');

        form.find('select:gt('+index+'):not(#rx_admin_model_entity_form_muniSubstitution)').attr('disabled', 'disabled');

        var options = {
            url: form.attr('data-update-url'),
            data: {
                modelSlug: form.attr('data-model-slug')
            },
            dataType: 'json',
            type: 'POST',
            success: function(response){
                if(response.status == 'success'){
                    form.find('.form-fields').html(response.content);
                }
                if(response.status == 'error'){
                    alert(response.message);
                }
            }
        };

        form.ajaxSubmit(options);
    });

    // Add handler to create Model form
    $(document).on('submit',"#create_model_form", function(event){
        $("#create_model_form").ajaxSubmit({
            target: "#models_tab"
        });
        event.preventDefault();
    });

    // Add handler to EDIT button for selected strategy
    $(document).on('click','.edit-strategy-btn', function(event) {
        var e = $(this);

        $.ajax({
            url: e.attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $('#modal_dialog .modal-header h3').html('Edit 3<sup>rd</sup> Party Name');
                    $('#modal_dialog .modal-body').html(response.content);
                    $('#modal_dialog').modal('show');
                }
            }
        });

        event.preventDefault();
    });

    // Add Edit strategy form handler
    $(document).on('submit','#edit_strategy_form', function(event) {
        var form = $(this);
        var config = {
            dataType: 'json',
            success: function(response) {
                if (response.status == 'error') {
                    form.replaceWith(response.content);
                }

                if (response.status == 'success') {
                    window.location.href = response.redirect_url;
                }
            }
        };
        form.ajaxSubmit(config);
        event.preventDefault();
    });

    //Model assumptions button handler
    $(document).on('click','.model-assumption-edit-btn', function(event) {
        $.ajax({
            url: $(this).attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $('#modal_dialog .modal-header h3').html('Edit Model Assumptions');
                    $('#modal_dialog .modal-body').html(response.content);
                    $('#modal_dialog').modal('show');
                }
                updateAutoNumeric();
            }
        });
        event.preventDefault();
    });

    $(document).on('submit','#model_form', function(event){
        var form = $(this);

        var options = {
            dataType: 'json',
            type: 'POST',
            success: function(response){
                if (response.status == 'success'){
                    $('#models_list').find('.empty').remove();
                    $('#models_list').append(response.content);
                    updateTotalPercent();
                }

                form.replaceWith(response.form);
            }
        };

        form.ajaxSubmit(options);
        event.preventDefault();
    });

    $(document).on('submit','#edit_model_form', function(event) {
        var form = $(this);
        var modelsList = $('#models_list');
        var row = modelsList.find('tr[data-row="' + form.attr('data-edit-row') + '"]');
        var selector = form.find('.assumption-commission');

        selector.each(function(i, element) {
            if ($(element).length > 0) {
                var notAutoNumeric = $(element).autoNumeric('get');
                $(element).val(notAutoNumeric);
            }
        });

        var config = {
            dataType: 'json',
            success: function(response) {

                if (response.status == 'error') {
                    form.replaceWith(response.content);
                    updateAutoNumeric();
                }

                if (response.status == 'success') {
                    if (response.redirect_url) {
                        window.location.href = response.redirect_url
                    } else {
                        form.replaceWith(response.form);
                        row.replaceWith(response.content);

                        var percent = 0.0;
                        modelsList.find('tr').each(function(i, element){
                            percent += parseFloat($(element).find('td.percent').text());
                        });

                        modelsList.closest('.table').find('td.total-percent').html('<strong>' + percent.toFixed(2) + ' %</strong>');
                    }
                }
            }
        };

        form.ajaxSubmit(config);
        event.preventDefault();
    });

    $(document).on('click','#models_list .delete-btn', function(event){
        if(confirm('Are you sure?')) {
            var e = $(this);
            var row = e.closest('tr');

            $.ajax({
                url: e.attr('href'),
                dataType: 'json',
                success: function(response){
                    if(response.status == 'success'){
                        row.remove();
                        updateTotalPercent();
                    }
                }
            });
        }

        event.preventDefault();
    });

    $(document).on('click','#models_list .edit-btn', function(event){
        var e = $(this);
        var form = $('.model-entity-form');

        $.ajax({
            url: e.attr('href'),
            dataType: 'json',
            success: function(response){
                if(response.status == 'success'){
                    form.replaceWith(response.form);
                }
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.edit-model-btn', function(event) {
        var e = $(this);

        $.ajax({
            url: e.attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $('#modal_dialog .modal-header h3').html('Edit Model Name');
                    $('#modal_dialog .modal-body').html(response.content);
                    $('#modal_dialog').modal('show');
                }
                updateAutoNumeric();
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.delete-model-btn', function(event) {
        if (!confirm('Are you sure?')) {
            event.preventDefault();
        }
    });

    $(document).on('change','#rx_admin_model_entity_form_percent', function() {
        percentRound($(this));
    });
});

function percentRound(element) {
    var value = parseFloat(element.val());
    element.val((Math.round( value * 100 ) / 100).toFixed(2));
}

function updateTotalPercent() {
    var total = 0;

    $('.percent').each(function(){
        total += parseFloat($(this).text());
    });
    $('.total-percent strong').text(total.formatMoney(2, '.', ',') + ' %');
}

/**
 * Created with JetBrains PhpStorm.
 * User: maksim
 * Date: 12.04.13
 * Time: 12:27
 * To change this template use File | Settings | File Templates.
 */

// Element where we store prototypes for assets and subclasses. Also it is holder of elements.
var assetCollectionHolder = $('#asset_collections');
var isUpdated = false;

$(function(){
    window.onbeforeunload = function (evt) {
        if (isUpdated) {
            var message = "Categories were not saved. Are you sure want to leave this page?";
            if (typeof evt == "undefined") {
                evt = window.event;
            }
            if (evt) {
                evt.returnValue = message;
            }

            return message;
        }
    };

    $(document).on('click','a.add-asset', function(event){
        event.preventDefault();
        addAsset();
    });

    $(document).on('submit',"#categories_form", function(event){
        event.preventDefault();
        var button = $(this).find("input[type=submit]");
        button.button('loading');
        $(this).ajaxSubmit({
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success'){
                    window.onbeforeunload = function(evt) { evt = window.event };
                    location.href = response.success_url;
                }
                if(response.status == 'form'){
                    $("#categories_tab").html(response.content);
                }
            }
        });
    });
});


function addAsset()
{
    isUpdated = true;
    var assetCollectionHolder = $('#asset_collections');
    var prototype = assetCollectionHolder.attr('data-prototype');
    var form = prototype.replace(/__name__/g, assetCollectionHolder.find('.assets').length);
    assetCollectionHolder.append(form);
}

function addSubclass(holder, assetIndex)
{
    isUpdated = true;
    var assetCollectionHolder = $('#asset_collections');
    holder = $(holder).closest('tr');
    var index = assetCollectionHolder.find('tr[id^="asset_'+ assetIndex +'_subclass_"]').length;
    index = searchSubclassFreeIndex(assetIndex, index);
    var prototype = assetCollectionHolder.attr('data-subclass-prototype');
    form = prototype.replace(/__name__/g, assetIndex);
    form = form.replace(/__index__/g, index);

    if(index != 0){
        var lastSubclassRow = assetCollectionHolder.find('tr[id^="asset_'+ assetIndex +'_subclass_"]').last();
        lastSubclassRow.after(form);
    }else{
        holder.after(form);
    }

    return false;
}

function deleteAsset(holder, assetIndex, is_exist)
{
    isUpdated = true;
    var url = $(holder).attr('href');

    holder = $(holder).closest('tr');
    subclasses = assetCollectionHolder.find('tr[id^="asset_'+ assetIndex +'_subclass_"]');

    holder.remove();
    subclasses.remove();
    return false;
}

function deleteSubclass(holder, is_exist)
{
    isUpdated = true;
    var url = $(holder).attr('href');
    holder = $(holder).closest('tr');

    holder.remove();
    return false;
}

function searchSubclassFreeIndex(assetIndex, index)
{
    var subclassRow = assetCollectionHolder.find('tr[id^="asset_'+ assetIndex +'_subclass_'+index+'"]');
    if(subclassRow.length){
        return searchSubclassFreeIndex(assetIndex, ++index);
    }
    return index;
}

function rebuildPriority(element) {
    $('.subclass-priority').find('option').remove();

    var priorities = [];

    $('.account-type-input').each(function(i, element) {
        var current_account_type = $(element).selected().val();

        if (priorities[current_account_type] == undefined) {
            priorities[current_account_type] = 1;
        } else {
            priorities[current_account_type]++;
        }
    });

    var priorities_select = [];
    $('.account-type-input').each(function(i, element) {
        var current_account_type = $(element).selected().val();
        if (priorities_select[current_account_type] == undefined) {
            priorities_select[current_account_type] = 1;
        } else {
            priorities_select[current_account_type]++;
        }

        for(var j=1;j<=priorities[current_account_type];j++) {
            var html;
            if (priorities_select[current_account_type] == j) {
                html = '<option value="'+j+'" selected="selected">'+j+'</option>';
            } else {
                html = '<option value="'+j+'">'+j+'</option>';
            }

            $(element).closest('tr').find('.subclass-priority').append(html);
        }
    });
}

function buildSortedDomForSecurity(dom, accountTypes, currentValue) {
    for (var i=0;i<accountTypes.length;i++) {
        if (accountTypes[i][2] == currentValue) {
            if (dom[accountTypes[i][0]] == undefined) {
                dom[accountTypes[i][0]] = [];
            }

            var html = '<tr id="asset_'+accountTypes[i][0]+'_subclass_'+accountTypes[i][1]+'_row" class="asset-'+accountTypes[i][0]+' subclass" data-asset-index="'+accountTypes[i][0]+'" data-subclass-index="'+accountTypes[i][1]+'">';
            html += $('#asset_'+accountTypes[i][0]+'_subclass_'+accountTypes[i][1]+'_row').html();
            html += '</tr>';

            dom[accountTypes[i][0]].push(html);
        }
    }
    return dom;
}

function orderByAccountType(btn) {
    var accountTypes = [];
    var order = $(btn).attr('data-order');

    $('.subclass').each(function(i, element) {
        var asset_index = parseInt($(element).attr('data-asset-index'));
        var subclass_index = parseInt($(element).attr('data-subclass-index'));
        var account = $(element).find('.account-type-input');
        var account_type = parseInt(account.selected().val());

        if (isNaN(account_type)) {
            account_type = 0;
        }

        var row = [asset_index, subclass_index, account_type];
        accountTypes.push(row);

    });

    var dom = [];
    if (order == 'ASC') {
        for (var j=0; j<4; j++) {
            dom = buildSortedDomForSecurity(dom, accountTypes, j);
        }
        $(btn).attr('data-order', 'DESC');
    } else {
        for (var j=3; j>=0; j--) {
            dom = buildSortedDomForSecurity(dom, accountTypes, j);
        }
        $(btn).attr('data-order', 'ASC');
    }

    for (var i in dom) {
        $('.asset-'+i).remove();
        var html = '';
        for (var j=0;j<dom[i].length;j++) {
            html += dom[i][j];
        }
        $('#categories_assets_'+i+'_name').closest('tr').after(html);
    }
}
/**
 * Created with JetBrains PhpStorm.
 * User: maksim
 * Date: 17.04.13
 * Time: 11:53
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    $(document).on('submit',"#security_form", function(event) {
        event.preventDefault();
        $(this).ajaxSubmit({
            target: '#securities_tab',
            success: function(){
                addCompleteTransferCustodianEvent('#model_security_form_fund_symbol', '', fillSubclassParameters);
            }
        });
    });

    $(document).on('change','#model_security_form_asset_class_id', function() {
        var url = $(this).attr('data-complete-url');
        $.get(url, { asset_id: $(this).val() }, function(data){
            $('#model_security_form_subclass_id').html(data);
        });
    });

    $(document).on('click',".delete-model-security-btn", function(event) {
        var url = $(this).attr('href');
        $.get(url, {}, function(data){
            $('#securities_tab').html(data);
            addCompleteTransferCustodianEvent('#model_security_form_fund_symbol', '', fillSubclassParameters);
        });
        event.preventDefault();
    });

    $(document).on('click',".edit-model-security-btn", function(event) {
        var url = $(this).attr('href');
        $.get(url, {}, function(data){
            $('#securities_tab').html(data);
            addCompleteTransferCustodianEvent('#model_security_form_fund_symbol', '', fillSubclassParameters);
        });
        event.preventDefault();
    });

    $(document).on('click','.security-transaction-edit-btn', function(event) {
        $.ajax({
            url: $(this).attr('href'),
            dataType: 'json',
            success: function(response) {
                $('#modal_dialog .modal-header h3').html('Edit Transaction');
                $('#modal_dialog .modal-body').html(response.content);
                $('#modal_dialog').modal('show');
                updateAutoNumeric();
            }
        });
        event.preventDefault();
    });

    $(document).on('submit','#edit_security_transaction_form', function(event) {
        var form = $(this);
        var config = {
            dataType: 'json',
            success: function(response) {
                if (response.status == 'error') {
                    form.replaceWith(response.content);
                    updateAutoNumeric();
                }

                if (response.status == 'success') {
                    $('#modal_dialog').modal('hide');
                }
            }
        };

        form.ajaxSubmit(config);
        event.preventDefault();
    });

    addCompleteTransferCustodianEvent('#model_security_form_fund_symbol', '', fillSubclassParameters);
});

//function addTypeaheadEvent()
//{
//    $('.typeahead').typeahead({
//        source: function (query, process) {
//            var url = typeahead_url;
//            return $.get(url, { query: query }, function (data) {
//                map = {};
//                items = [];
//                $.each(data, function (i, record) {
//                    map[record.symbol] = record;
//                    items.push(record.symbol);
//                });
//                return process(items);
//            });
//        },
//        updater: function(item) {
//            fillSubclassParameters(map[item]);
//            return item;
//        }
//    });
//}

function fillSubclassParameters(params)
{
    $("#security_name_row").html(params.security_name);
    $("#security_type_row").html(params.type);
    $("#security_expense_ratio_row").html(params.expense_ratio);

    $("#model_security_form_type").val(params.type);
    $("#model_security_form_expense_ratio").val(params.expense_ratio);
    $("#model_security_form_security_id").val(params.id);
}