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
/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 01.07.13
 * Time: 14:30
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    $(document).on('change','.account-type-input', function() {
        var is_show_subclasses_priority = $('#categories_tab').attr('data-is-show-subclasses-priority');
        if (is_show_subclasses_priority) {
            rebuildPriority(this);
        }

        var tmp = $(this).val();
        $(this).closest('.account-type-input').find('option').removeAttr('selected');
        $(this).find('option[value="'+tmp+'"]').attr('selected', 'selected');
    });

    $(document).on('change','.subclass-priority', function() {
        var tmp = $(this).val();
        $(this).closest('.subclass-priority').find('option').removeAttr('selected');
        $(this).find('option[value="'+tmp+'"]').attr('selected', 'selected');
    });

    $(document).on('change','.subclass input', function() {
        $(this).attr('value', $(this).val());
    });
});
/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 01.07.13
 * Time: 14:47
 * To change this template use File | Settings | File Templates.
 */

$(function() {

    riskRatingSlider();
    decorateDecimalInput('.low-market-return', 2);

    $(document).on('click','.edit-ria-model-btn', function (event) {
        var e = $(this);

        $.ajax({
            url: e.attr('href'),
            dataType: 'json',
            success: function (response) {
                if (response.status == 'success') {
                    var modalDialog =  $('#modal_dialog');
                    modalDialog.find('.modal-header h3').html('Edit Model Parameters');
                    modalDialog.find('.modal-body').html(response.content);
                    modalDialog.modal('show');
                }
                updateAutoNumeric();
            }
        });

        event.preventDefault();
    });

    $(document).on('submit','#edit_ria_model_form', function (event) {
        var form = $(this);

        var config = {
            dataType: 'json',
            success: function (response) {
                if (response.status == 'error') {
                    form.replaceWith(response.content);
                }

                if (response.status == 'success') {
                    var modalDialog =  $('#modal_dialog');
                    modalDialog.modal('hide');

                    $('#models_list_btns').replaceWith(response.models_list);
                    $('#model_view_row').replaceWith(response.model_view);
                }
            }
        };

        form.ajaxSubmit(config);
        event.preventDefault();
    });

    $('#rx_ria_model_entity_form_assetClass, #rx_ria_model_entity_form_subclass,' +
        '#rx_ria_model_entity_form_securityAssignment, #rx_ria_model_entity_form_muniSubstitution,' +
        '#rx_ria_model_entity_form_tax_loss_harvesting').on('change', function() {

            var elem = $(this);
            var id = elem.attr('id');

            $('input.symbol').each(function() {
                $(this).val('');
            });

            switch (id) {
                case 'rx_ria_model_entity_form_assetClass':
                    $('#rx_ria_model_entity_form_subclass').attr('disabled', 'disabled');
                    $('#rx_ria_model_entity_form_securityAssignment').attr('disabled', 'disabled');
                    $('#rx_ria_model_entity_form_muniSubstitution').attr('disabled', 'disabled');
                    $('#rx_ria_model_entity_form_tax_loss_harvesting').attr('disabled', 'disabled');
                    break;
                case 'rx_ria_model_entity_form_subclass':
                    $('#rx_ria_model_entity_form_securityAssignment').attr('disabled', 'disabled');
                    $('#rx_ria_model_entity_form_muniSubstitution').attr('disabled', 'disabled');
                    $('#rx_ria_model_entity_form_tax_loss_harvesting').attr('disabled', 'disabled');
                    break;
                case 'rx_ria_model_entity_form_securityAssignment':
                    $('#rx_ria_model_entity_form_muniSubstitution').attr('disabled', 'disabled');
                    $('#rx_ria_model_entity_form_tax_loss_harvesting').attr('disabled', 'disabled');
                    break;
            }

            var form = elem.closest('form');
            updateEntityForm(form);
        });

    $(document).on('submit','#ria_model_form', function (event) {
        var form = $(this);

        var options = {
            dataType: 'json',
            type: 'POST',
            success: function (response) {
                if (response.status == 'success') {
                    $('#model_entities_list').find('.empty').remove();
                    $('#model_entities_list').append(response.content);
                    updateTotalPercent();
                }

                form.replaceWith(response.form);
            }
        };

        form.ajaxSubmit(options);
        event.preventDefault();
    });

    $(document).on('change','#rx_ria_model_entity_form_percent', function() {
        percentRound($(this));
    });

    $(document).on('click','#model_entities_list .delete-btn', function (event) {
        if (confirm('Are you sure?')) {
            var e = $(this);
            var row = e.closest('tr');

            $.ajax({
                url: e.attr('href'),
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'success') {
                        row.remove();
                        updateTotalPercent();
                    }

                    if (response.status == 'error') {
                        alert('Error: ' + response.message);
                    }
                }
            });
        }

        event.preventDefault();
    });

    $(document).on('click','#model_entities_list .edit-btn', function (event) {
        var e = $(this);
        var form = $('.ria-model-entity-form');

        $.ajax({
            url: e.attr('href'),
            dataType: 'json',
            success: function (response) {
                if (response.status == 'success') {
                    form.replaceWith(response.form);
                    percentRound($('#rx_ria_model_entity_form_percent'));
                }

                if (response.status == 'error') {
                    alert(response.message);
                }
            }
        });

        event.preventDefault();
    });

    $(document).on('submit','#ria_edit_model_form', function (event) {
        var form = $(this);
        var rowIndex = form.data('edit-row');

        var options = {
            dataType: 'json',
            type: 'POST',
            success: function (response) {
                if (response.status == 'success') {
                    $('#model_entities_list tr[data-row="' + rowIndex + '"]').replaceWith(response.content);
                    form.replaceWith(response.form);
                    updateTotalPercent();
                }
                if (response.status == 'error') {
                    form.replaceWith(response.form);
                }

                form.replaceWith(response.form);
            }
        };

        form.ajaxSubmit(options);
        event.preventDefault();
    });

    $(document).on('submit','#create_model_form', function(event) {
        var form = $(this);
        var button = form.find('button[type="submit"]');

        $(button).button('loading');
        $(this).ajaxSubmit({
            success: function(response){
                form.replaceWith(response.form);
                $('#models_list').html(response.models_list);
                $(button).button('reset');
            }
        });
        event.preventDefault();
    });

    $(document).on('click','#models_list a:not(:last)', function(event) {
        var btn = $(this);

        $.ajax({
            url: btn.attr('href'),
            dataType: 'json',
            success: function (response) {
                if (response.status == 'success') {
                    var modelView = $('#model_view_row');
                    if (modelView.length > 0) {
                        modelView.replaceWith(response.content)
                    } else {
                        $('#models_tab').append(response.content);
                    }

                    leaveModelPageEvent();
                }

                if (response.status == 'error') {
                    alert('Error: ' + response.error_message);
                }
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.delete-ria-model-btn', function (event) {
        var elem = $(this);

        if (confirm('Are you sure?')) {
            $.ajax({
                url: elem.attr('href'),
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'success') {
                        $('#model_view_row').remove();
                        $('#models_list a.active').remove();
                    }

                    if (response.status == 'error') {
                        alert('Error: ' + response.error_message);
                    }
                }
            });
        }

        event.preventDefault();
    });

    $(document).on('change',"#q_container_id :input", function(){
        var url = $("#q_container_id").attr('data-url');
        var is_qualified = $(this).val();
        $.ajax({
            url: url,
            data: { is_qualified: is_qualified },
            dataType: 'html',
            success: function (response) {

                $("#me_container_id").html(response);
                updateEntityForm();
            }
        });
    });
});

function riskRatingSlider() {
    var form = $('#model_risk_rating_form');
    var maxRating = form.attr('data-max-rating');
    var selects = $('.risk-rating-slider');

    $(selects).each(function(){
        var select = this;

        var slider = $( "<div id='slider_" + $(select).attr('id') + "'></div>").insertAfter( select ).slider({
            min: 1,
            max: maxRating,
            range: "min",
            value: select.selectedIndex + 1,
            slide: function( event, ui ) {
                select.selectedIndex = ui.value - 1;
            }
        });

        $(select).change(function() {
            var e = this;
            slider.slider( "value", e.selectedIndex + 1 );
        });
    });
}

function updateEntityForm(form) {
    var index = form.index('select');

    form.find('select:gt(' + index + '):not(#rx_ria_model_entity_form_muniSubstitution)').attr('disabled', 'disabled');

    var options = {
        url: form.attr('data-update-url'),
        data: {
            slug: form.attr('data-slug')
        },
        dataType: 'json',
        type: 'POST',
        success: function (response) {
            if (response.status == 'success') {
                form.find('.form-fields').html(response.content);
            }
            if (response.status == 'error') {
                alert(response.message);
            }
        }
    };

    form.ajaxSubmit(options);
}

function leaveModelPageEvent() {
    window.onbeforeunload = function (evt) {
        var total = 0;
        $('.percent').each(function () {
            total += parseFloat($(this).text());
        });

        if (total < 100) {
            var message = "Model does not add up to 100%";
            if (typeof evt == "undefined") {
                evt = window.event;
            }
            if (evt) {
                evt.returnValue = message;
            }

            return message;
        }
    };
}

$(function() {
    addCompleteTransferCustodianEvent(
        '.ria-find-clients-filter-form-type-search',
        '',
        function(object) {
            $('#rebalance_history_filter_form_client_id').val(object.id);
        },
        function() {
            $('#rebalance_history_filter_form_client_id').val('');
        }
    );

    $(document).on('click','.show-rebalancing-accounts-btn', function(event) {
        var btn = $(this);

        var account_table_box = btn.closest('.tab-pane').find('.rebalance-account-table-content');
        account_table_box.html('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

        $.ajax({
            url: btn.attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status = 'success') {
                    account_table_box.html(response.content);
                } else {
                    alert('Error: ' + response.content);
                }
            },
            complete: function() {
                $('.ajax-loader').remove();
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.rebalance-table-content table > thead a, .ajax-pagination a', function(event) {

        var btn = $(this);
        var rebalance_table_box = btn.closest('.rebalance-table-content');
        var account_table_box = btn.closest('.tab-pane').find('.rebalance-account-table-content');

        rebalance_table_box.html('<img class="ajax-loader" src="/img/ajax-loader.gif" />');
        account_table_box.html('');

        $.ajax({
            url: btn.attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status = 'success') {
                    rebalance_table_box.html(response.content);
                } else {
                    alert('Error: ' + response.content);
                }
            },
            complete: function() {
                $('.ajax-loader').remove();
            }
        });

        event.preventDefault();
    });

    $(document).on('submit','#admin_rebalancing_page_content #rebalance_form', function(event) {
        var checkedCheckboxes = $(this).find('.rebalance-table-content input[type="checkbox"]:checked')
        if (checkedCheckboxes.length) {

            var form = $(this);

            form.ajaxSubmit({
                type: 'post',
                success: function(response) {
                    if (response.status == 'success') {

                    }

                    if (response.status == 'error') {
                        alert('Error');
                    }
                }
            });

        } else {
            alert('Please select items for rebalance');
        }

        event.preventDefault();
    });

    $(document).on('submit','#rebalance_post_form', function(event) {
        var form = $(this);

        var checkedCheckboxes = form.find('.rebalance-table-content input[type="checkbox"]:checked');
        if (checkedCheckboxes.length) {

            form.ajaxSubmit({
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = response.redirect_url;
                    }

                    if (response.status == 'error') {
                        alert('Error: ' + response.message);
                    }
                }
            });
        } else {
            alert('Please select items.');
        }
        event.preventDefault();
    });

    $(document).on('click','#ria_rebalancing_page_content #rebalance_form .rebalance-form-submit-btn', function(event) {
        var elem = $(this);
        var form = elem.closest('form');

        var checkedCheckboxes = form.find('.rebalance-table-content input[type="checkbox"]:checked');
        if (checkedCheckboxes.length) {
            if (elem.attr('data-action-type') == 'rebalance') {
                  form.spinner128(true);
            }

            form.ajaxSubmit({
                url: elem.attr('href'),
                type: 'post',
                success: function(response) {
                    form.spinner128(false);

                    if (response.status == 'success') {
                        window.location.reload();
                    }

                    if (response.status == 'error') {
                        alert('Error: ' + response.message);
                    }

                    if (response.status == 'timeout') {
                        alert('Rebalancer activity is registered in queue and will be processed soon, please monitor the status change to proceed.');
                        setInterval(startRiaRebalanceCheckProgressAction, 10000);
                    }
                }
            });

        } else {
            alert('Please select items.');
        }

        event.preventDefault();
    });

    $(document).on('click','.rebalance-action-btn', function(event) {
        var btn = $(this);

        $.ajax({
            url: btn.attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    var modalDialog = $('#modal_dialog');
                    modalDialog.find('.modal-header h3').html('Details');
                    modalDialog.find('.modal-body').html(response.content);
                    modalDialog.modal('show');
                } else {
                    alert('ERROR');
                }
            }
        });

        event.preventDefault();
    });

    $(document).on('submit','#rebalance_history_filter_form', function(event) {
        var form = $(this);
        var history_rebalance_table_box = form.closest('#history_tab').find('.rebalance-table-content');

        history_rebalance_table_box.html('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

        form.ajaxSubmit({
            data: {'is_filter': true },
            type: 'post',
            success: function(response) {
                if (response.status == 'success') {
                    history_rebalance_table_box.html(response.content);
                } else {
                    $('#rebalance_history_filter_form').replaceWith(response.content);
                }
            }
        });
        event.preventDefault();
    });

    $(document).on('change','#rebalance_form_is_all', function(event) {
        var elem = $(this);

        var checkboxes = $('.rebalance-table-content input[name="rebalance_form[client_value][]"]');

        if ('checked' === elem.attr('checked')) {
            checkboxes.each(function(index, checkbox) {
                $(checkbox).attr('checked', 'checked');
            });
        } else {
            checkboxes.each(function(index, checkbox) {
                $(checkbox).removeAttr('checked');
            });
        }

        event.preventDefault();
    });

    $(document).on('change','.rebalance-table-content input[name="rebalance_form[client_value][]"]', function(event) {

        if ('checked' !== $(this).attr('checked')) {
            $('#rebalance_form_is_all').removeAttr('checked');
        }
        event.preventDefault();
    });

    $(document).on('click','.rebalancer-queue-change-state-btn', function(event) {
        var elem = $(this);

        var url;
        if (elem.attr('data-state') == 'active') {
            url = Routing.generate('rx_ria_rebalancing_rebalancer_queue_delete', {'id' : elem.attr('data-id')});
        } else {
            url = Routing.generate('rx_ria_rebalancing_rebalancer_queue_restore', {'id' : elem.attr('data-id')});
        }

        $.ajax({
            url: url,
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    if (response.state == 'deleted') {
                        elem.closest('tr').addClass('text-through-row');
                        elem.text('Restore');
                        elem.attr('data-state', 'deleted')
                    } else {
                        elem.closest('tr').removeClass('text-through-row');
                        elem.text('x');
                        elem.attr('data-state', 'active')
                    }

                    $('#rebalance_portfolio_allocation_table').replaceWith(response.allocation_table);
                    $('#rebalance_rebalancing_summary_table').replaceWith(response.summary_table);
                } else {
                    alert(response.message);
                }
            }
        });

        event.preventDefault();
    });

    $(document).on('submit','#trade_recon_form', function(event) {
        var submitButton = $(this).find('button:submit');

        submitButton.button('loading');

        $(this).ajaxSubmit({
            target: $(this).parent(),
            complete: function() {
                submitButton.button('reset');

                addCompleteTransferCustodianEvent(
                    '.ria-find-clients-filter-form-type-search',
                    '',
                    function(object) {
                        $('#form_client').val(object.id);
                    },
                    function() {
                        $('#form_client').val('');
                    }
                );
            }
        });

        event.preventDefault();
        return false;
    });
});

function startRiaRebalanceCheckProgressAction()
{
    $.ajax({
        url: Routing.generate('rx_ria_rebalancing_check_progress'),
        dataType: 'json',
        success: function(response) {
            if (response.status == 'success') {
                window.location.reload();
            }
        }
    });
}
/**
* @license Input Mask plugin for jquery
* http://github.com/RobinHerbots/jquery.inputmask
* Copyright (c) 2010 - 2012 Robin Herbots
* Licensed under the MIT license (http://www.opensource.org/licenses/mit-license.php)
* Version: 1.2.2
*/

(function ($) {
    if ($.fn.inputmask == undefined) {
        $.inputmask = {
            //options default
            defaults: {
                placeholder: "_",
                optionalmarker: {
                    start: "[",
                    end: "]"
                },
                escapeChar: "\\",
                mask: null,
                oncomplete: $.noop, //executes when the mask is complete
                onincomplete: $.noop, //executes when the mask is incomplete and focus is lost
                oncleared: $.noop, //executes when the mask is cleared
                repeat: 0, //repetitions of the mask
                greedy: true, //true: allocated buffer for the mask and repetitions - false: allocate only if needed
                autoUnmask: false, //automatically unmask when retrieving the value with $.fn.val or value if the browser supports __lookupGetter__ or getOwnPropertyDescriptor
                clearMaskOnLostFocus: true,
                insertMode: true, //insert the input or overwrite the input
                clearIncomplete: false, //clear the incomplete input on blur
                aliases: {}, //aliases definitions => see jquery.inputmask.extensions.js
                onKeyUp: $.noop, //override to implement autocomplete on certain keys for example
                onKeyDown: $.noop, //override to implement autocomplete on certain keys for example
                showMaskOnHover: true, //show the mask-placeholder when hovering the empty input
                //numeric basic properties
                numericInput: false, //numericInput input direction style (input shifts to the left while holding the caret position)
                radixPoint: ".", // | ","
                //numeric basic properties
                definitions: {
                    '9': {
                        validator: "[0-9]",
                        cardinality: 1
                    },
                    'a': {
                        validator: "[A-Za-z\u0410-\u044F\u0401\u0451]",
                        cardinality: 1
                    },
                    '*': {
                        validator: "[A-Za-z\u0410-\u044F\u0401\u04510-9]",
                        cardinality: 1
                    }
                },
                keyCode: { ALT: 18, BACKSPACE: 8, CAPS_LOCK: 20, COMMA: 188, COMMAND: 91, COMMAND_LEFT: 91, COMMAND_RIGHT: 93, CONTROL: 17, DELETE: 46, DOWN: 40, END: 35, ENTER: 13, ESCAPE: 27, HOME: 36, INSERT: 45, LEFT: 37, MENU: 93, NUMPAD_ADD: 107, NUMPAD_DECIMAL: 110, NUMPAD_DIVIDE: 111, NUMPAD_ENTER: 108,
                    NUMPAD_MULTIPLY: 106, NUMPAD_SUBTRACT: 109, PAGE_DOWN: 34, PAGE_UP: 33, PERIOD: 190, RIGHT: 39, SHIFT: 16, SPACE: 32, TAB: 9, UP: 38, WINDOWS: 91
                },
                ignorables: [8, 9, 13, 16, 17, 18, 20, 27, 33, 34, 35, 36, 37, 38, 39, 40, 46, 91, 93, 108]
            },
            val: $.fn.val //store the original jquery val function
        };

        $.fn.inputmask = function (fn, options) {
            var opts = $.extend(true, {}, $.inputmask.defaults, options);
            var pasteEvent = isInputEventSupported('paste') ? 'paste' : 'input';

            var iphone = navigator.userAgent.match(/iphone/i) != null;
            var android = navigator.userAgent.match(/android.*mobile safari.*/i) != null;
            if (android) {
                var browser = navigator.userAgent.match(/mobile safari.*/i);
                var version = parseInt(new RegExp(/[0-9]+/).exec(browser));
                android = version <= 533;
            }
            var caretposCorrection = null;

            if (typeof fn == "string") {
                switch (fn) {
                    case "mask":
                        //init buffer
                        var _buffer = getMaskTemplate();
                        var tests = getTestingChain();

                        return this.each(function () {
                            mask(this);
                        });
                        break;
                    case "unmaskedvalue":
                        var tests = this.data('inputmask')['tests'];
                        var _buffer = this.data('inputmask')['_buffer'];
                        opts.greedy = this.data('inputmask')['greedy'];
                        opts.repeat = this.data('inputmask')['repeat'];
                        opts.definitions = this.data('inputmask')['definitions'];
                        return unmaskedvalue(this);
                        break;
                    case "remove":
                        var tests, _buffer;
                        return this.each(function () {
                            var $input = $(this), input = this;
                            setTimeout(function () {
                                if ($input.data('inputmask')) {
                                    tests = $input.data('inputmask')['tests'];
                                    _buffer = $input.data('inputmask')['_buffer'];
                                    opts.greedy = $input.data('inputmask')['greedy'];
                                    opts.repeat = $input.data('inputmask')['repeat'];
                                    opts.definitions = $input.data('inputmask')['definitions'];
                                    //writeout the unmaskedvalue
                                    input._valueSet(unmaskedvalue($input, true));
                                    //clear data
                                    $input.removeData('inputmask');
                                    //unbind all events
                                    $input.unbind(".inputmask");
                                    $input.removeClass('focus.inputmask');
                                    //restore the value property
                                    var valueProperty;
                                    if (Object.getOwnPropertyDescriptor)
                                        valueProperty = Object.getOwnPropertyDescriptor(input, "value");
                                    if (valueProperty && valueProperty.get) {
                                        if (input._valueGet) {
                                            Object.defineProperty(input, "value", {
                                                get: input._valueGet,
                                                set: input._valueSet
                                            });
                                        }
                                    } else if (document.__lookupGetter__ && input.__lookupGetter__("value")) {
                                        if (input._valueGet) {
                                            input.__defineGetter__("value", input._valueGet);
                                            input.__defineSetter__("value", input._valueSet);
                                        }
                                    }
                                    delete input._valueGet;
                                    delete input._valueSet;
                                }
                            }, 0);
                        });
                        break;
                    case "getemptymask": //return the default (empty) mask value, usefull for setting the default value in validation
                        if (this.data('inputmask'))
                            return this.data('inputmask')['_buffer'].join('');
                        else return "";
                    case "hasMaskedValue": //check wheter the returned value is masked or not; currently only works reliable when using jquery.val fn to retrieve the value 
                        return this.data('inputmask') ? !this.data('inputmask')['autoUnmask'] : false;
                    default:
                        //check if the fn is an alias
                        if (!resolveAlias(fn)) {
                            //maybe fn is a mask so we try
                            //set mask
                            opts.mask = fn;
                        }
                        //init buffer
                        var _buffer = getMaskTemplate();
                        var tests = getTestingChain();

                        return this.each(function () {
                            mask(this);
                        });

                        break;
                }
            } if (typeof fn == "object") {
                opts = $.extend(true, {}, $.inputmask.defaults, fn);
                resolveAlias(opts.alias); //resolve aliases
                //init buffer
                var _buffer = getMaskTemplate();
                var tests = getTestingChain();

                return this.each(function () {
                    mask(this);
                });
            }

            //helper functions
            function isInputEventSupported(eventName) {
                var el = document.createElement('input'),
		  eventName = 'on' + eventName,
		  isSupported = (eventName in el);
                if (!isSupported) {
                    el.setAttribute(eventName, 'return;');
                    isSupported = typeof el[eventName] == 'function';
                }
                el = null;
                return isSupported;
            }

            function resolveAlias(aliasStr) {
                var aliasDefinition = opts.aliases[aliasStr];
                if (aliasDefinition) {
                    if (aliasDefinition.alias) resolveAlias(aliasDefinition.alias); //alias is another alias
                    $.extend(true, opts, aliasDefinition);  //merge alias definition in the options
                    $.extend(true, opts, options);  //reapply extra given options
                    return true;
                }
                return false;
            }

            function getMaskTemplate() {
                var escaped = false, outCount = 0;
                if (opts.mask.length == 1 && opts.greedy == false) { opts.placeholder = ""; } //hide placeholder with single non-greedy mask
                var singleMask = $.map(opts.mask.split(""), function (element, index) {
                    var outElem = [];
                    if (element == opts.escapeChar) {
                        escaped = true;
                    }
                    else if ((element != opts.optionalmarker.start && element != opts.optionalmarker.end) || escaped) {
                        var maskdef = opts.definitions[element];
                        if (maskdef && !escaped) {
                            for (var i = 0; i < maskdef.cardinality; i++) {
                                outElem.push(getPlaceHolder(outCount + i));
                            }
                        } else {
                            outElem.push(element);
                            escaped = false;
                        }
                        outCount += outElem.length;
                        return outElem;
                    }
                });

                //allocate repetitions
                var repeatedMask = singleMask.slice();
                for (var i = 1; i < opts.repeat && opts.greedy; i++) {
                    repeatedMask = repeatedMask.concat(singleMask.slice());
                }

                return repeatedMask;
            }

            //test definition => {fn: RegExp/function, cardinality: int, optionality: bool, newBlockMarker: bool, offset: int, casing: null/upper/lower, def: definitionSymbol}
            function getTestingChain() {
                var isOptional = false, escaped = false;
                var newBlockMarker = false; //indicates wheter the begin/ending of a block should be indicated

                return $.map(opts.mask.split(""), function (element, index) {
                    var outElem = [];

                    if (element == opts.escapeChar) {
                        escaped = true;
                    } else if (element == opts.optionalmarker.start && !escaped) {
                        isOptional = true;
                        newBlockMarker = true;
                    }
                    else if (element == opts.optionalmarker.end && !escaped) {
                        isOptional = false;
                        newBlockMarker = true;
                    }
                    else {
                        var maskdef = opts.definitions[element];
                        if (maskdef && !escaped) {
                            var prevalidators = maskdef["prevalidator"], prevalidatorsL = prevalidators ? prevalidators.length : 0;
                            for (var i = 1; i < maskdef.cardinality; i++) {
                                var prevalidator = prevalidatorsL >= i ? prevalidators[i - 1] : [], validator = prevalidator["validator"], cardinality = prevalidator["cardinality"];
                                outElem.push({ fn: validator ? typeof validator == 'string' ? new RegExp(validator) : new function () { this.test = validator; } : new RegExp("."), cardinality: cardinality ? cardinality : 1, optionality: isOptional, newBlockMarker: isOptional == true ? newBlockMarker : false, offset: 0, casing: maskdef["casing"], def: element });
                                if (isOptional == true) //reset newBlockMarker
                                    newBlockMarker = false;
                            }
                            outElem.push({ fn: maskdef.validator ? typeof maskdef.validator == 'string' ? new RegExp(maskdef.validator) : new function () { this.test = maskdef.validator; } : new RegExp("."), cardinality: maskdef.cardinality, optionality: isOptional, newBlockMarker: newBlockMarker, offset: 0, casing: maskdef["casing"], def: element });
                        } else {
                            outElem.push({ fn: null, cardinality: 0, optionality: isOptional, newBlockMarker: newBlockMarker, offset: 0, casing: null, def: element });
                            escaped = false;
                        }
                        //reset newBlockMarker
                        newBlockMarker = false;
                        return outElem;
                    }
                });
            }

            function isValid(pos, c, buffer, strict) { //strict true ~ no correction or autofill
                if (pos < 0 || pos >= getMaskLength()) return false;
                var testPos = determineTestPosition(pos), loopend = c ? 1 : 0, chrs = '';
                for (var i = tests[testPos].cardinality; i > loopend; i--) {
                    chrs += getBufferElement(buffer, testPos - (i - 1));
                }

                if (c) { chrs += c; }
                //return is false or a json object => { pos: ??, c: ??}
                return tests[testPos].fn != null ? tests[testPos].fn.test(chrs, buffer, pos, strict, opts) : false;
            }

            function isMask(pos) {
                var testPos = determineTestPosition(pos);
                var test = tests[testPos];

                return test != undefined ? test.fn : false;
            }

            function determineTestPosition(pos) {
                return pos % tests.length;
            }

            function getPlaceHolder(pos) {
                return opts.placeholder.charAt(pos % opts.placeholder.length);
            }

            function getMaskLength() {
                var calculatedLength = _buffer.length;
                if (!opts.greedy && opts.repeat > 1) {
                    calculatedLength += (_buffer.length * (opts.repeat - 1));
                }
                return calculatedLength;
            }

            //pos: from position
            function seekNext(buffer, pos) {
                var maskL = getMaskLength();
                if (pos >= maskL) return maskL;
                var position = pos;
                while (++position < maskL && !isMask(position)) { };
                return position;
            }
            //pos: from position
            function seekPrevious(buffer, pos) {
                var position = pos;
                if (position <= 0) return 0;

                while (--position > 0 && !isMask(position)) { };
                return position;
            }

            function setBufferElement(buffer, position, element) {
                //position = prepareBuffer(buffer, position);

                var test = tests[determineTestPosition(position)];
                var elem = element;
                if (elem != undefined) {
                    switch (test.casing) {
                        case "upper":
                            elem = element.toUpperCase();
                            break;
                        case "lower":
                            elem = element.toLowerCase();
                            break;
                    }
                }

                buffer[position] = elem;
            }
            function getBufferElement(buffer, position, autoPrepare) {
                if (autoPrepare) position = prepareBuffer(buffer, position);
                return buffer[position];
            }

            //needed to handle the non-greedy mask repetitions
            function prepareBuffer(buffer, position, isRTL) {
                var j;
                if (isRTL) {
                    while (position < 0 && buffer.length < getMaskLength()) {
                        j = _buffer.length - 1;
                        position = _buffer.length;
                        while (_buffer[j] !== undefined) {
                            buffer.unshift(_buffer[j--]);
                        }
                    }
                } else {
                    while (buffer[position] == undefined && buffer.length < getMaskLength()) {
                        j = 0;
                        while (_buffer[j] !== undefined) { //add a new buffer
                            buffer.push(_buffer[j++]);
                        }
                    }
                }

                return position;
            }

            function writeBuffer(input, buffer, caretPos) {
                input._valueSet(buffer.join(''));
                if (caretPos != undefined) {
                    if (android) {
                        setTimeout(function () {
                            caret(input, caretPos);
                        }, 100);
                    }
                    else caret(input, caretPos);
                }
            };
            function clearBuffer(buffer, start, end) {
                for (var i = start, maskL = getMaskLength(); i < end && i < maskL; i++) {
                    setBufferElement(buffer, i, getBufferElement(_buffer.slice(), i));
                }
            };

            function setReTargetPlaceHolder(buffer, pos) {
                var testPos = determineTestPosition(pos);
                setBufferElement(buffer, pos, getBufferElement(_buffer, testPos));
            }

            function checkVal(input, buffer, clearInvalid, skipRadixHandling) {
                var isRTL = $(input).data('inputmask')['isRTL'],
                    inputValue = truncateInput(input._valueGet(), isRTL).split('');

                if (isRTL) { //align inputValue for RTL/numeric input
                    var maskL = getMaskLength();
                    var inputValueRev = inputValue.reverse(); inputValueRev.length = maskL;

                    for (var i = 0; i < maskL; i++) {
                        var targetPosition = determineTestPosition(maskL - (i + 1));
                        if (tests[targetPosition].fn == null && inputValueRev[i] != getBufferElement(_buffer, targetPosition)) {
                            inputValueRev.splice(i, 0, getBufferElement(_buffer, targetPosition));
                            inputValueRev.length = maskL;
                        } else {
                            inputValueRev[i] = inputValueRev[i] || getBufferElement(_buffer, targetPosition);
                        }
                    }
                    inputValue = inputValueRev.reverse();
                }
                clearBuffer(buffer, 0, buffer.length);
                buffer.length = _buffer.length;
                var lastMatch = -1, checkPosition = -1, np, maskL = getMaskLength(), ivl = inputValue.length, rtlMatch = ivl == 0 ? maskL : -1;
                for (var i = 0; i < ivl; i++) {
                    for (var pos = checkPosition + 1; pos < maskL; pos++) {
                        if (isMask(pos)) {
                            var c = inputValue[i];
                            if ((np = isValid(pos, c, buffer, !clearInvalid)) !== false) {
                                if (np !== true) {
                                    pos = np.pos || pos; //set new position from isValid
                                    c = np.c || c; //set new char from isValid
                                }
                                setBufferElement(buffer, pos, c);
                                lastMatch = checkPosition = pos;
                            } else {
                                setReTargetPlaceHolder(buffer, pos);
                                if (c == getPlaceHolder(pos)) {
                                    checkPosition = pos;
                                    rtlMatch = pos;
                                }
                            }
                            break;
                        } else {   //nonmask
                            setReTargetPlaceHolder(buffer, pos);
                            if (lastMatch == checkPosition) //once outsync the nonmask cannot be the lastmatch
                                lastMatch = pos;
                            checkPosition = pos;
                            if (inputValue[i] == getBufferElement(buffer, pos))
                                break;
                        }
                    }
                }
                //Truncate buffer when using non-greedy masks
                if (opts.greedy == false) {
                    var newBuffer = truncateInput(buffer.join(''), isRTL).split('');
                    while (buffer.length != newBuffer.length) {  //map changes into the original buffer
                        isRTL ? buffer.shift() : buffer.pop();
                    }
                }

                if (clearInvalid) {
                    writeBuffer(input, buffer);
                }
                return isRTL ? (opts.numericInput ? ($.inArray(opts.radixPoint, buffer) != -1 && skipRadixHandling !== true ? $.inArray(opts.radixPoint, buffer) : seekNext(buffer, maskL)) : seekNext(buffer, rtlMatch)) : seekNext(buffer, lastMatch);
            }

            function escapeRegex(str) {
                var specials = ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'];
                return str.replace(new RegExp('(\\' + specials.join('|\\') + ')', 'gim'), '\\$1');
            }
            function truncateInput(inputValue, rtl) {
                return rtl ? inputValue.replace(new RegExp("^(" + escapeRegex(_buffer.join('')) + ")*"), "") : inputValue.replace(new RegExp("(" + escapeRegex(_buffer.join('')) + ")*$"), "");
            }

            function clearOptionalTail(input, buffer) {
                checkVal(input, buffer, false);
                var tmpBuffer = buffer.slice();
                if ($(input).data('inputmask')['isRTL']) {
                    for (var pos = 0; pos <= tmpBuffer.length - 1; pos++) {
                        var testPos = determineTestPosition(pos);
                        if (tests[testPos].optionality) {
                            if (getPlaceHolder(pos) == buffer[pos] || !isMask(pos))
                                tmpBuffer.splice(0, 1);
                            else break;
                        } else break;
                    }
                } else {
                    for (var pos = tmpBuffer.length - 1; pos >= 0; pos--) {
                        var testPos = determineTestPosition(pos);
                        if (tests[testPos].optionality) {
                            if (getPlaceHolder(pos) == buffer[pos] || !isMask(pos))
                                tmpBuffer.pop();
                            else break;
                        } else break;
                    }
                }
                writeBuffer(input, tmpBuffer);
            }

            //functionality fn
            function unmaskedvalue($input, skipDatepickerCheck) {
                var input = $input[0];
                if (tests && (skipDatepickerCheck === true || !$input.hasClass('hasDatepicker'))) {
                    var buffer = _buffer.slice();
                    checkVal(input, buffer);
                    return $.map(buffer, function (element, index) {
                        return isMask(index) && element != getBufferElement(_buffer.slice(), index) ? element : null;
                    }).join('');
                }
                else {
                    return input._valueGet();
                }
            }

            function caret(input, begin, end) {
                var npt = input.jquery && input.length > 0 ? input[0] : input;
                if (typeof begin == 'number') {
                    end = (typeof end == 'number') ? end : begin;
                    if (opts.insertMode == false && begin == end) end++; //set visualization for insert/overwrite mode
                    if (npt.setSelectionRange) {
                        npt.setSelectionRange(begin, end);
                    } else if (npt.createTextRange) {
                        var range = npt.createTextRange();
                        range.collapse(true);
                        range.moveEnd('character', end);
                        range.moveStart('character', begin);
                        range.select();
                    }
                    npt.focus();
                    if (android && end != npt.selectionEnd) caretposCorrection = { begin: begin, end: end };
                } else {
                    var caretpos = android ? caretposCorrection : null, caretposCorrection = null;
                    if (caretpos == null) {
                        if (npt.setSelectionRange) {
                            begin = npt.selectionStart;
                            end = npt.selectionEnd;
                        } else if (document.selection && document.selection.createRange) {
                            var range = document.selection.createRange();
                            begin = 0 - range.duplicate().moveStart('character', -100000);
                            end = begin + range.text.length;
                        }
                        caretpos = { begin: begin, end: end };
                    }
                    return caretpos;
                }
            };

            function mask(el) {
                var $input = $(el);
                if (!$input.is(":input")) return;

                //correct greedy setting if needed
                opts.greedy = opts.greedy ? opts.greedy : opts.repeat == 0;

                //handle maxlength attribute
                var maxLength = $input.prop('maxLength');
                if (getMaskLength() > maxLength && maxLength > -1) { //FF sets no defined max length to -1 
                    if (maxLength < _buffer.length) _buffer.length = maxLength;
                    if (opts.greedy == false) {
                        opts.repeat = Math.round(maxLength / _buffer.length);
                    }
                }
                $input.prop('maxLength', getMaskLength() * 2);

                //store tests & original buffer in the input element - used to get the unmasked value
                $input.data('inputmask', {
                    'tests': tests,
                    '_buffer': _buffer,
                    'greedy': opts.greedy,
                    'repeat': opts.repeat,
                    'autoUnmask': opts.autoUnmask,
                    'definitions': opts.definitions,
                    'isRTL': false
                });

                patchValueProperty(el);

                //init vars
                var buffer = _buffer.slice(),
                undoBuffer = el._valueGet(),
                skipKeyPressEvent = false, //Safari 5.1.x - modal dialog fires keypress twice workaround
                ignorable = false,
                lastPosition = -1,
                firstMaskPos = seekNext(buffer, -1),
                lastMaskPos = seekPrevious(buffer, getMaskLength()),
                isRTL = false;
                if (el.dir == "rtl" || opts.numericInput) {
                    el.dir = "ltr"
                    $input.css("text-align", "right");
                    $input.removeAttr("dir");
                    var inputData = $input.data('inputmask');
                    inputData['isRTL'] = true;
                    $input.data('inputmask', inputData);
                    isRTL = true;
                }

                //unbind all events - to make sure that no other mask will interfere when re-masking
                $input.unbind(".inputmask");
                $input.removeClass('focus.inputmask');
                //bind events
                $input.bind("mouseenter.inputmask", function () {
                    var $input = $(this), input = this;
                    if (!$input.hasClass('focus.inputmask') && opts.showMaskOnHover) {
                        var nptL = input._valueGet().length;
                        if (nptL < buffer.length) {
                            if (nptL == 0)
                                buffer = _buffer.slice();
                            writeBuffer(input, buffer);
                        }
                    }
                }).bind("blur.inputmask", function () {
                    var $input = $(this), input = this, nptValue = input._valueGet();
                    $input.removeClass('focus.inputmask');
                    if (nptValue != undoBuffer) {
                        $input.change();
                    }
                    if (opts.clearMaskOnLostFocus) {
                        if (nptValue == _buffer.join(''))
                            input._valueSet('');
                        else { //clearout optional tail of the mask
                            clearOptionalTail(input, buffer);
                        }
                    }
                    if (!isComplete(input)) {
                        $input.trigger("incomplete");
                        if (opts.clearIncomplete) {
                            if (opts.clearMaskOnLostFocus)
                                input._valueSet('');
                            else {
                                buffer = _buffer.slice();
                                writeBuffer(input, buffer);
                            }
                        }
                    }
                }).bind("focus.inputmask", function () {
                    var $input = $(this), input = this;
                    if (!$input.hasClass('focus.inputmask') && !opts.showMaskOnHover) {
                        var nptL = input._valueGet().length;
                        if (nptL < buffer.length) {
                            if (nptL == 0)
                                buffer = _buffer.slice();
                            caret(input, checkVal(input, buffer, true));
                        }
                    }
                    $input.addClass('focus.inputmask');
                    undoBuffer = input._valueGet();
                }).bind("mouseleave.inputmask", function () {
                    var $input = $(this), input = this;
                    if (opts.clearMaskOnLostFocus) {
                        if (!$input.hasClass('focus.inputmask')) {
                            if (input._valueGet() == _buffer.join('') || input._valueGet() == '')
                                input._valueSet('');
                            else { //clearout optional tail of the mask
                                clearOptionalTail(input, buffer);
                            }
                        }
                    }
                }).bind("click.inputmask", function () {
                    var input = this;
                    setTimeout(function () {
                        var selectedCaret = caret(input);
                        if (selectedCaret.begin == selectedCaret.end) {
                            var clickPosition = selectedCaret.begin;
                            lastPosition = checkVal(input, buffer, false);
                            if (isRTL)
                                caret(input, clickPosition > lastPosition && (isValid(clickPosition, buffer[clickPosition], buffer, true) !== false || !isMask(clickPosition)) ? clickPosition : lastPosition);
                            else
                                caret(input, clickPosition < lastPosition && (isValid(clickPosition, buffer[clickPosition], buffer, true) !== false || !isMask(clickPosition)) ? clickPosition : lastPosition);
                        }
                    }, 0);
                }).bind('dblclick.inputmask', function () {
                    var input = this;
                    setTimeout(function () {
                        caret(input, 0, lastPosition);
                    }, 0);
                }).bind("keydown.inputmask", keydownEvent
                ).bind("keypress.inputmask", keypressEvent
                ).bind("keyup.inputmask", keyupEvent
                ).bind(pasteEvent + ".inputmask, dragdrop.inputmask, drop.inputmask", function () {
                    var input = this;
                    setTimeout(function () {
                        caret(input, checkVal(input, buffer, true));
                    }, 0);
                }).bind('setvalue.inputmask', function () {
                    var input = this;
                    undoBuffer = input._valueGet();
                    checkVal(input, buffer, true);
                    if (input._valueGet() == _buffer.join(''))
                        input._valueSet('');
                }).bind('complete.inputmask', opts.oncomplete)
                .bind('incomplete.inputmask', opts.onincomplete)
                .bind('cleared.inputmask', opts.oncleared);

                //apply mask
                lastPosition = checkVal(el, buffer, true);

                // Wrap document.activeElement in a try/catch block since IE9 throw "Unspecified error" if document.activeElement is undefined when we are in an IFrame.
                var activeElement;
                try {
                    activeElement = document.activeElement;
                } catch (e) { }
                if (activeElement === el) { //position the caret when in focus
                    $input.addClass('focus.inputmask');
                    caret(el, lastPosition);
                } else if (opts.clearMaskOnLostFocus) {
                    if (el._valueGet() == _buffer.join('')) {
                        el._valueSet('');
                    } else {
                        clearOptionalTail(el, buffer);
                    }
                }

                installEventRuler(el);

                //private functions
                function isComplete(npt) {
                    var complete = true, nptValue = npt._valueGet(), ml = nptValue.length;
                    for (var i = 0; i < ml; i++) {
                        if (isMask(i) && nptValue.charAt(i) == getPlaceHolder(i)) {
                            complete = false;
                            break;
                        }
                    }
                    return complete;
                }


                function installEventRuler(npt) {
                    var events = $._data(npt).events;

                    $.each(events, function (eventType, eventHandlers) {
                        $(npt).bind(eventType + ".inputmask", function (event) {
                            if (this.readOnly || this.disabled) {
                                event.stopPropagation();
                                event.stopImmediatePropagation();
                                event.preventDefault();
                                return false;
                            }
                        });
                        //!! the bound handlers are executed in the order they where bound
                        //reorder the events
                        var ourHandler = eventHandlers[eventHandlers.length - 1];
                        for (var i = eventHandlers.length - 1; i > 0; i--) {
                            eventHandlers[i] = eventHandlers[i - 1];
                        }
                        eventHandlers[0] = ourHandler;
                    });
                }

                function patchValueProperty(npt) {
                    var valueProperty;
                    if (Object.getOwnPropertyDescriptor)
                        valueProperty = Object.getOwnPropertyDescriptor(npt, "value");
                    if (valueProperty && valueProperty.get) {
                        if (!npt._valueGet) {

                            npt._valueGet = valueProperty.get;
                            npt._valueSet = valueProperty.set;

                            Object.defineProperty(npt, "value", {
                                get: function () {
                                    var $self = $(this), inputData = $(this).data('inputmask');
                                    return inputData && inputData['autoUnmask'] ? $self.inputmask('unmaskedvalue') : this._valueGet() != inputData['_buffer'].join('') ? this._valueGet() : '';
                                },
                                set: function (value) {
                                    this._valueSet(value); $(this).triggerHandler('setvalue.inputmask');
                                }
                            });
                        }
                    } else if (document.__lookupGetter__ && npt.__lookupGetter__("value")) {
                        if (!npt._valueGet) {
                            npt._valueGet = npt.__lookupGetter__("value");
                            npt._valueSet = npt.__lookupSetter__("value");

                            npt.__defineGetter__("value", function () {
                                var $self = $(this), inputData = $(this).data('inputmask');
                                return inputData && inputData['autoUnmask'] ? $self.inputmask('unmaskedvalue') : this._valueGet() != inputData['_buffer'].join('') ? this._valueGet() : '';
                            });
                            npt.__defineSetter__("value", function (value) {
                                this._valueSet(value); $(this).triggerHandler('setvalue.inputmask');
                            });
                        }
                    } else {
                        if (!npt._valueGet) {
                            npt._valueGet = function () { return this.value; }
                            npt._valueSet = function (value) { this.value = value; }
                        }
                        if ($.fn.val.inputmaskpatch != true) {
                            $.fn.val = function () {
                                if (arguments.length == 0) {
                                    var $self = $(this);
                                    if ($self.data('inputmask')) {
                                        if ($self.data('inputmask')['autoUnmask'])
                                            return $self.inputmask('unmaskedvalue');
                                        else {
                                            var result = $.inputmask.val.apply($self);
                                            return result != $self.data('inputmask')['_buffer'].join('') ? result : '';
                                        }
                                    } else return $.inputmask.val.apply($self);
                                } else {
                                    var args = arguments;
                                    return this.each(function () {
                                        var $self = $(this);
                                        var result = $.inputmask.val.apply($self, args);
                                        if ($self.data('inputmask')) $self.triggerHandler('setvalue.inputmask');
                                        return result;
                                    });
                                }
                            };
                            $.extend($.fn.val, {
                                inputmaskpatch: true
                            });
                        }
                    }
                }
                //shift chars to left from start to end and put c at end position if defined
                function shiftL(start, end, c) {
                    while (!isMask(start) && start - 1 >= 0) start--;
                    for (var i = start; i < end && i < getMaskLength(); i++) {
                        if (isMask(i)) {
                            setReTargetPlaceHolder(buffer, i);
                            var j = seekNext(buffer, i);
                            var p = getBufferElement(buffer, j);
                            if (p != getPlaceHolder(j)) {
                                if (j < getMaskLength() && isValid(i, p, buffer, true) !== false && tests[determineTestPosition(i)].def == tests[determineTestPosition(j)].def) {
                                    setBufferElement(buffer, i, getBufferElement(buffer, j));
                                    setReTargetPlaceHolder(buffer, j); //cleanup next position
                                } else {
                                    if (isMask(i))
                                        break;
                                }
                            } else if (c == undefined) break;
                        } else {
                            setReTargetPlaceHolder(buffer, i);
                        }
                    }
                    if (c != undefined)
                        setBufferElement(buffer, isRTL ? end : seekPrevious(buffer, end), c);

                    buffer = truncateInput(buffer.join(''), isRTL).split('');
                    if (buffer.length == 0) buffer = _buffer.slice();

                    return start; //return the used start position
                }
                function shiftR(start, end, c, full) { //full => behave like a push right ~ do not stop on placeholders
                    for (var i = start; i <= end && i < getMaskLength(); i++) {
                        if (isMask(i)) {
                            var t = getBufferElement(buffer, i);
                            setBufferElement(buffer, i, c);
                            if (t != getPlaceHolder(i)) {
                                var j = seekNext(buffer, i);
                                if (j < getMaskLength()) {
                                    if (isValid(j, t, buffer, true) !== false && tests[determineTestPosition(i)].def == tests[determineTestPosition(j)].def)
                                        c = t;
                                    else {
                                        if (isMask(j))
                                            break;
                                        else c = t;
                                    }
                                } else break;
                            } else if (full !== true) break;
                        } else
                            setReTargetPlaceHolder(buffer, i);
                    }
                    var lengthBefore = buffer.length;
                    buffer = truncateInput(buffer.join(''), isRTL).split('');
                    if (buffer.length == 0) buffer = _buffer.slice();

                    return end - (lengthBefore - buffer.length);  //return new start position
                };

                function keydownEvent(e) {
                    //Safari 5.1.x - modal dialog fires keypress twice workaround
                    skipKeyPressEvent = false;

                    var input = this, k = e.keyCode, pos = caret(input);

                    //set input direction according the position to the radixPoint
                    if (opts.numericInput) {
                        var nptStr = input._valueGet();
                        var radixPosition = nptStr.indexOf(opts.radixPoint);
                        if (radixPosition != -1) {
                            isRTL = pos.begin <= radixPosition || pos.end <= radixPosition;
                        }
                    }

                    //backspace, delete, and escape get special treatment
                    if (k == opts.keyCode.BACKSPACE || k == opts.keyCode.DELETE || (iphone && k == 127)) {//backspace/delete
                        var maskL = getMaskLength();
                        if (pos.begin == 0 && pos.end == maskL) {
                            buffer = _buffer.slice();
                            writeBuffer(input, buffer);
                            caret(input, checkVal(input, buffer, false));
                        } else if ((pos.end - pos.begin) > 1 || ((pos.end - pos.begin) == 1 && opts.insertMode)) {
                            clearBuffer(buffer, pos.begin, pos.end);
                            writeBuffer(input, buffer, isRTL ? checkVal(input, buffer, false) : pos.begin);
                        } else {
                            var beginPos = pos.begin - (k == opts.keyCode.DELETE ? 0 : 1);
                            if (beginPos < firstMaskPos && k == opts.keyCode.DELETE) {
                                beginPos = firstMaskPos;
                            }
                            if (beginPos >= firstMaskPos) {
                                if (opts.numericInput && opts.greedy && k == opts.keyCode.DELETE && buffer[beginPos] == opts.radixPoint) {
                                    beginPos = seekNext(buffer, beginPos);
                                    isRTL = false;
                                }
                                if (isRTL) {
                                    beginPos = shiftR(firstMaskPos, beginPos, getPlaceHolder(beginPos), true);
                                    beginPos = (opts.numericInput && opts.greedy && k == opts.keyCode.BACKSPACE && buffer[beginPos + 1] == opts.radixPoint) ? beginPos + 1 : seekNext(buffer, beginPos);
                                } else beginPos = shiftL(beginPos, maskL);
                                writeBuffer(input, buffer, beginPos);
                            }
                        }
                        if (input._valueGet() == _buffer.join(''))
                            $(input).trigger('cleared');

                        return false;
                    } else if (k == opts.keyCode.END || k == opts.keyCode.PAGE_DOWN) { //when END or PAGE_DOWN pressed set position at lastmatch
                        setTimeout(function () {
                            var caretPos = checkVal(input, buffer, false, true);
                            if (!opts.insertMode && caretPos == getMaskLength() && !e.shiftKey) caretPos--;
                            caret(input, e.shiftKey ? pos.begin : caretPos, caretPos);
                        }, 0);
                        return false;
                    } else if (k == opts.keyCode.HOME || k == opts.keyCode.PAGE_UP) {//Home or page_up
                        caret(input, 0, e.shiftKey ? pos.begin : 0);
                        return false;
                    }
                    else if (k == opts.keyCode.ESCAPE) {//escape
                        input._valueSet(undoBuffer);
                        caret(input, 0, checkVal(input, buffer));
                        return false;
                    } else if (k == opts.keyCode.INSERT) {//insert
                        opts.insertMode = !opts.insertMode;
                        caret(input, !opts.insertMode && pos.begin == getMaskLength() ? pos.begin - 1 : pos.begin);
                        return false;
                    } else if (e.ctrlKey && k == 88) {
                        setTimeout(function () {
                            caret(input, checkVal(input, buffer, true));
                        }, 0);
                    } else if (!opts.insertMode) { //overwritemode
                        if (k == opts.keyCode.RIGHT) {//right
                            var caretPos = pos.begin == pos.end ? pos.end + 1 : pos.end;
                            caretPos = caretPos < getMaskLength() ? caretPos : pos.end;
                            caret(input, e.shiftKey ? pos.begin : caretPos, e.shiftKey ? caretPos + 1 : caretPos);
                            return false;
                        } else if (k == opts.keyCode.LEFT) {//left
                            var caretPos = pos.begin - 1;
                            caretPos = caretPos > 0 ? caretPos : 0;
                            caret(input, caretPos, e.shiftKey ? pos.end : caretPos);
                            return false;
                        }
                    }

                    opts.onKeyDown.call(this, e, opts); //extra stuff to execute on keydown
                    ignorable = $.inArray(k, opts.ignorables) != -1;
                }

                function keypressEvent(e) {
                    //Safari 5.1.x - modal dialog fires keypress twice workaround
                    if (skipKeyPressEvent) return false;
                    skipKeyPressEvent = true;

                    var input = this, $input = $(input);

                    e = e || window.event;
                    var k = e.which || e.charCode || e.keyCode;

                    if (opts.numericInput && k == opts.radixPoint.charCodeAt(opts.radixPoint.length - 1)) {
                        var nptStr = input._valueGet();
                        var radixPosition = nptStr.indexOf(opts.radixPoint);
                        caret(input, seekNext(buffer, radixPosition != -1 ? radixPosition : getMaskLength()));
                    }

                    if (e.ctrlKey || e.altKey || e.metaKey || ignorable) {//Ignore
                        return true;
                    } else {
                        if (k) {
                            $input.trigger('input');

                            var pos = caret(input), c = String.fromCharCode(k), maskL = getMaskLength();
                            clearBuffer(buffer, pos.begin, pos.end);

                            if (isRTL) {
                                var p = opts.numericInput ? pos.end : seekPrevious(buffer, pos.end), np;
                                if ((np = isValid(p == maskL || getBufferElement(buffer, p) == opts.radixPoint ? seekPrevious(buffer, p) : p, c, buffer, false)) !== false) {
                                    if (np !== true) {
                                        p = np.pos || pos; //set new position from isValid
                                        c = np.c || c; //set new char from isValid
                                    }

                                    var firstUnmaskedPosition = firstMaskPos;
                                    if (opts.insertMode == true) {
                                        if (opts.greedy == true) {
                                            var bfrClone = buffer.slice();
                                            while (getBufferElement(bfrClone, firstUnmaskedPosition, true) != getPlaceHolder(firstUnmaskedPosition) && firstUnmaskedPosition <= p) {
                                                firstUnmaskedPosition = firstUnmaskedPosition == maskL ? (maskL + 1) : seekNext(buffer, firstUnmaskedPosition);
                                            }
                                        }

                                        if (firstUnmaskedPosition <= p && (opts.greedy || buffer.length < maskL)) {
                                            if (buffer[firstMaskPos] != getPlaceHolder(firstMaskPos) && buffer.length < maskL) {
                                                var offset = prepareBuffer(buffer, -1, isRTL);
                                                if (pos.end != 0) p = p + offset;
                                                maskL = buffer.length;
                                            }
                                            shiftL(firstUnmaskedPosition, opts.numericInput ? seekPrevious(buffer, p) : p, c);
                                        } else return false;
                                    } else setBufferElement(buffer, opts.numericInput ? seekPrevious(buffer, p) : p, c);
                                    writeBuffer(input, buffer, opts.numericInput && p == 0 ? seekNext(buffer, p) : p);
                                    setTimeout(function () { //timeout needed for IE
                                        if (isComplete(input))
                                            $input.trigger("complete");
                                    }, 0);
                                } else if (android) writeBuffer(input, buffer, pos.begin);
                            }
                            else {
                                var p = seekNext(buffer, pos.begin - 1), np;
                                prepareBuffer(buffer, p, isRTL);
                                if ((np = isValid(p, c, buffer, false)) !== false) {
                                    if (np !== true) {
                                        p = np.pos || p; //set new position from isValid
                                        c = np.c || c; //set new char from isValid
                                    }
                                    if (opts.insertMode == true) {
                                        var lastUnmaskedPosition = getMaskLength();
                                        var bfrClone = buffer.slice();
                                        while (getBufferElement(bfrClone, lastUnmaskedPosition, true) != getPlaceHolder(lastUnmaskedPosition) && lastUnmaskedPosition >= p) {
                                            lastUnmaskedPosition = lastUnmaskedPosition == 0 ? -1 : seekPrevious(buffer, lastUnmaskedPosition);
                                        }
                                        if (lastUnmaskedPosition >= p)
                                            shiftR(p, buffer.length, c);
                                        else return false;
                                    }
                                    else setBufferElement(buffer, p, c);
                                    var next = seekNext(buffer, p);
                                    writeBuffer(input, buffer, next);

                                    setTimeout(function () { //timeout needed for IE
                                        if (isComplete(input))
                                            $input.trigger("complete");
                                    }, 0);
                                } else if (android) writeBuffer(input, buffer, pos.begin);
                            }
                            return false;
                        }
                    }
                }

                function keyupEvent(e) {
                    var $input = $(this), input = this;
                    var k = e.keyCode;
                    opts.onKeyUp.call(this, e, opts); //extra stuff to execute on keyup
                    if (k == opts.keyCode.TAB && $input.hasClass('focus.inputmask') && input._valueGet().length == 0) {
                        buffer = _buffer.slice();
                        writeBuffer(input, buffer);
                        if (!isRTL) caret(input, 0);
                        undoBuffer = input._valueGet();
                    }
                }
            }
        };
    }
})(jQuery);